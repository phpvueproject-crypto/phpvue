<?php

namespace App\Repositories;

use App\Events\RegionMgmtUpdated;
use App\Models\DoorMgmt;
use App\Models\Edge;
use App\Models\EdgeConfiguration;
use App\Models\ElevatorMgmt;
use App\Models\Location;
use App\Models\ParkingLotMgmt;
use App\Models\Project;
use App\Models\ProjectDeploy;
use App\Models\RegionMgmt;
use App\Models\StationMgmt;
use App\Models\UndeployLocation;
use App\Models\Vertex;
use App\Models\VertexConfiguration;
use DB;
use File;
use Illuminate\Database\Eloquent\Builder;
use Log;
use Throwable;

class ProjectRepository {
    public function deploy($projectName): void {
        $projectDeploy = ProjectDeploy::whereProjectName($projectName)->first();
        $project = Project::whereName($projectName)->with('regionMgmts')->first();
        $regionMgmts = $project->regionMgmts->where('is_exist_preview_background', true);
        $regionMgmts = $regionMgmts->load('roomEnvironment');
        foreach($regionMgmts as $regionMgmt) {
            $path = storage_path("app/projects/$projectDeploy->project_name/$regionMgmt->region");
            if(!File::exists($path))
                File::makeDirectory($path, 493, true);
            $previewFilepath = "$path/background_{$regionMgmt->region}_preview.png";
            $filepath = "$path/background_{$regionMgmt->region}.png";
            File::copy($previewFilepath, $filepath);
        }
        $regionMgmts = $projectDeploy->regionMgmts;

        try {
            DB::transaction(function() use ($project, $regionMgmts) {
                Vertex::whereRelation('regionMgmt', 'project_id', '<>', $project->id)->where('is_deploy', 1)->delete();
                $undeployVertices = Vertex::whereRelation('regionMgmt', 'project_id', $project->id)->where('is_deploy', 0)->with([
                    'vertexConfigurations',
                    'undeployLocation'
                ])->get();
                $deployVertices = Vertex::whereRelation('regionMgmt', 'project_id', $project->id)->where('is_deploy', 1)->whereIn('name', $undeployVertices->pluck('name'))->with([
                    'vertexConfigurations',
                    'location'
                ])->get();
                $deployVertexConfigurations = collect();
                foreach($undeployVertices as $undeployVertex) {
                    /** @var Vertex $deployVertex */
                    $deployVertex = $deployVertices->where('name', $undeployVertex->name)->first();
                    if(!$deployVertex) {
                        $deployVertex = new Vertex();
                        $deployVertex->region_mgmt_id = $undeployVertex->region_mgmt_id;
                    }
                    $deployVertex->vertex_type_id = $undeployVertex->vertex_type_id;
                    $deployVertex->tag = $undeployVertex->tag;
                    $deployVertex->x = $undeployVertex->x;
                    $deployVertex->y = $undeployVertex->y;
                    $deployVertex->z = $undeployVertex->z;
                    $deployVertex->name = $undeployVertex->name;
                    $deployVertex->theta = $undeployVertex->theta;
                    $deployVertex->is_deploy = 1;
                    $deployVertex->save();
                    $deployVertexIdx = $deployVertices->search(function(Vertex $existVertex) use ($deployVertex) {
                        return $existVertex->id == $deployVertex->id;
                    });
                    if($deployVertexIdx === false) {
                        $deployVertices = $deployVertices->push($deployVertex);
                    }

                    /** @var VertexConfiguration $vertexConfiguration */
                    $vertexConfiguration = $undeployVertex->vertexConfigurations->where('type', 'device_name')->first();
                    if($deployVertex->vertex_type_id == 4) {
                        if($deployVertex->location) {
                            $location = $deployVertex->location;
                        } else {
                            $location = new Location();
                            $location->vertex_id = $deployVertex->id;
                        }
                        if(!$undeployVertex->undeployLocation) {
                            $undeployLocation = new UndeployLocation();
                            $undeployLocation->name = $deployVertex->name;
                            $undeployLocation->vertex_id = $undeployVertex->id;
                            $undeployLocation->save();
                        }
                        /** @var RegionMgmt $regionMgmt */
                        $regionMgmt = $regionMgmts->where('id', $deployVertex->region_mgmt_id)->first();
                        $location->build = $project->name;
                        $location->floors = $regionMgmt->floors;
                        $location->room = $regionMgmt->roomEnvironment->room_name;
                        $location->vertex_name = $deployVertex->name;
                        $location->device_name = $vertexConfiguration->data;
                        $location->x = $deployVertex->x;
                        $location->y = $deployVertex->y;
                        $location->save();
                    }

                    foreach($undeployVertex->vertexConfigurations as $undeployVertexConfiguration) {
                        $deployVertexConfiguration = $deployVertex->vertexConfigurations->where('type', $undeployVertexConfiguration->type)->first();
                        if(!$deployVertexConfiguration) {
                            $deployVertexConfiguration = new VertexConfiguration();
                            $deployVertexConfiguration->vertex_id = $deployVertex->id;
                            $deployVertexConfiguration->type = $undeployVertexConfiguration->type;
                        }
                        $data = $undeployVertexConfiguration->data;
                        if(is_array($data)) {
                            $data = json_encode($data);
                        }
                        $deployVertexConfiguration->data = $data;
                        $deployVertexConfiguration->save();
                        $deployVertexConfigurations = $deployVertexConfigurations->push($deployVertexConfiguration);
                    }
                }
                Vertex::whereRelation('regionMgmt', 'project_id', $project->id)->where('is_deploy', 1)->whereNotIn('id', $deployVertices->pluck('id'))->delete();
                VertexConfiguration::whereHas('vertex', function(Builder $query) use ($project) {
                    $query->whereRelation('regionMgmt', 'project_id', $project->id);
                    $query->where('is_deploy', 1);
                })->whereNotIn('id', $deployVertexConfigurations->pluck('id'))->delete();
                Location::whereHas('vertex', function(Builder $query) use ($project) {
                    $query->whereRelation('regionMgmt', 'project_id', $project->id);
                })->whereNotIn('vertex_id', $deployVertices->pluck('id'))->whereNotNull('vertex_id')->delete();

                Edge::whereRelation('regionMgmt', 'project_id', '<>', $project->id)->where('is_deploy', 1)->delete();
                $undeployEdges = Edge::whereRelation('regionMgmt', 'project_id', $project->id)->where('is_deploy', 0)->with([
                    'edgeConfigurations',
                    'startVertex',
                    'endVertex'
                ])->get();
                $deployEdges = Edge::whereRelation('regionMgmt', 'project_id', $project->id)->where('is_deploy', 1)->whereIn('name', $undeployEdges->pluck('name'))->with('edgeConfigurations')->get();
                $deployEdgeConfigurations = collect();
                foreach($undeployEdges as $undeployEdge) {
                    /** @var Edge $deployEdge */
                    $deployEdge = $deployEdges->where('name', $undeployEdge->name)->first();
                    $startVertexName = $undeployEdge->startVertex->name;
                    $endVertexName = $undeployEdge->endVertex->name;
                    /** @var Vertex $deployStartVertex */
                    $deployStartVertex = $deployVertices->where('name', $startVertexName)->first();
                    /** @var Vertex $deployEndVertex */
                    $deployEndVertex = $deployVertices->where('name', $endVertexName)->first();
                    if(!$deployStartVertex || !$deployEndVertex) {
                        $deployEdge?->delete();
                        continue;
                    }
                    if(!$deployEdge) {
                        $deployEdge = new Edge();
                        $deployEdge->region_mgmt_id = $undeployEdge->region_mgmt_id;
                    }

                    $deployEdge->direction = $undeployEdge->direction;
                    $deployEdge->start_vertex_id = $deployStartVertex->id;
                    $deployEdge->end_vertex_id = $deployEndVertex->id;
                    $deployEdge->name = $undeployEdge->name;
                    $deployEdge->is_deploy = 1;
                    $deployEdge->save();
                    $deployEdgeIdx = $deployEdges->search(function(Edge $existEdge) use ($deployEdge) {
                        return $existEdge->id == $deployEdge->id;
                    });
                    if($deployEdgeIdx === false) {
                        $deployEdges = $deployEdges->push($deployEdge);
                    }
                    foreach($undeployEdge->edgeConfigurations as $undeployEdgeConfiguration) {
                        $deployEdgeConfiguration = $deployEdge->edgeConfigurations->where('type', $undeployEdgeConfiguration->type)->first();
                        if(!$deployEdgeConfiguration) {
                            $deployEdgeConfiguration = new EdgeConfiguration();
                            $deployEdgeConfiguration->edge_id = $deployEdge->id;
                            $deployEdgeConfiguration->type = $undeployEdgeConfiguration->type;
                        }
                        $data = $undeployEdgeConfiguration->data;
                        if(is_array($data)) {
                            $data = json_encode($data);
                        }
                        $deployEdgeConfiguration->data = $data;
                        $deployEdgeConfiguration->save();
                        $deployEdgeConfigurations = $deployEdgeConfigurations->push($deployEdgeConfiguration);
                    }
                }
                Edge::whereRelation('regionMgmt', 'project_id', $project->id)->where('is_deploy', 1)->whereNotIn('id', $deployEdges->pluck('id'))->delete();
                EdgeConfiguration::whereHas('edge', function(Builder $query) use ($project) {
                    $query->whereRelation('regionMgmt', 'project_id', $project->id);
                    $query->where('is_deploy', 1);
                })->whereNotIn('id', $deployEdgeConfigurations->pluck('id'))->delete();

                $doorMgmts = DoorMgmt::all();
                foreach($doorMgmts as $doorMgmt) {
                    if(!$doorMgmt->edge_name) {
                        $doorMgmt->edge_id = null;
                        $doorMgmt->save();
                        continue;
                    }
                    $deployEdge = $deployEdges->where('name', $doorMgmt->edge_name)->first();
                    if($deployEdge) {
                        $doorMgmt->edge_id = $deployEdge->id;
                    } else {
                        $doorMgmt->edge_id = null;
                    }
                    $doorMgmt->save();
                }

                $elevatorMgmts = ElevatorMgmt::all();
                foreach($elevatorMgmts as $elevatorMgmt) {
                    if(!$elevatorMgmt->vertex_name) {
                        $elevatorMgmt->vertices()->sync([]);
                        continue;
                    }
                    $vertexNames = explode(',', $elevatorMgmt->vertex_name);
                    $vertices = $deployVertices->whereIn('name', $vertexNames)->values();
                    $elevatorMgmt->vertices()->sync($vertices->pluck('id'));
                    $elevatorMgmt->save();
                }

                $parkingLotMgmts = ParkingLotMgmt::all();
                foreach($parkingLotMgmts as $parkingLotMgmt) {
                    if(!$parkingLotMgmt->vertex_name) {
                        $parkingLotMgmt->vertex_id = null;
                        $parkingLotMgmt->save();
                        continue;
                    }
                    $deployVertex = $deployVertices->where('name', $parkingLotMgmt->vertex_name)->first();
                    if($deployVertex) {
                        $parkingLotMgmt->vertex_id = $deployVertex->id;
                    } else {
                        $parkingLotMgmt->vertex_id = null;
                    }
                    $parkingLotMgmt->save();
                }

                $stationMgmts = StationMgmt::all();
                foreach($stationMgmts as $stationMgmt) {
                    if(!$stationMgmt->vertex_name) {
                        $stationMgmt->vertex_id = null;
                        $stationMgmt->save();
                        continue;
                    }
                    $deployVertex = $deployVertices->where('name', $stationMgmt->vertex_name)->first();
                    if($deployVertex) {
                        $stationMgmt->vertex_id = $deployVertex->id;
                    } else {
                        $stationMgmt->vertex_id = null;
                    }
                    $stationMgmt->save();
                }
            });
        } catch(Throwable $e) {
            Log::info($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        $project->is_deploy = 1;
        $project->save();
        Project::where('id', '<>', $project->id)->update([
            'is_deploy' => 0
        ]);

        foreach($project->regionMgmts as $regionMgmt) {
            $regionMgmt->deploy_background_file_md5 = $regionMgmt->undeploy_background_file_md5;
            $regionMgmt->deploy_route_file_md5 = $regionMgmt->undeploy_route_file_md5;
            $regionMgmt->save();
            event(new RegionMgmtUpdated($regionMgmt));
        }
    }
}
