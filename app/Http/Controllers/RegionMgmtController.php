<?php

namespace App\Http\Controllers;

use App\Models\Edge;
use App\Models\EdgeConfiguration;
use App\Models\Location;
use App\Models\Project;
use App\Models\RegionMgmt;
use App\Models\RoomEnvironment;
use App\Models\UndeployLocation;
use App\Models\Vertex;
use App\Models\VertexConfiguration;
use App\Repositories\ProjectRepository;
use Auth;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Intervention;
use Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use Validator;
use function app\imgCoordToQuadrantCoordX;
use function app\imgCoordToQuadrantCoordY;

class RegionMgmtController extends Controller {
    private ProjectRepository $project;

    public function __construct(ProjectRepository $project) {
        $this->project = $project;
    }

    /**
     * @api              {get} /api/regionMgmts 索取區域列表
     * @apiGroup         RegionMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} project_mgmt_project_name 專案名稱
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.regionMgmts 區域列表
     * @apiSuccess {String} data.regionMgmts.region 區域名稱
     *
     * @apiSampleRequest off
     */
    public function index(Request $request): array {
        $user = Auth::user();
        $regionMgmts = new RegionMgmt();

        $projectId = $request->input('project_id');
        if($projectId) {
            $regionMgmts = $regionMgmts->where('project_id', $projectId);
        } else {
            $project = Project::whereIsDeploy(1)->first();
            $regionMgmts = $regionMgmts->where('project_id', $project->id);
        }

        $all = $request->input('all', 0);
        if(!$all) {
            $isRead = $request->input('is_read');
            $isWrite = $request->input('is_write');
            $regionMgmts = $regionMgmts->whereHas('users', function(Builder $query) use ($user, $isRead, $isWrite) {
                if($user) {
                    $query->where('user_id', $user->id);
                }
                if($isRead !== null) {
                    $query->where('is_read', $isRead);
                }
                if($isWrite !== null) {
                    $query->where('is_write', $isWrite);
                }
            });

            $isFloor = $request->input('is_floor');
            if($isFloor) {
                $regionMgmts = $regionMgmts->whereNull('floor_region_mgmt_id');
            } else {
                $regionMgmts = $regionMgmts->whereNotNull('floor_region_mgmt_id');
            }
        }

        $regionMgmts = $regionMgmts->orderBy('floors')->orderBy('region')->with([
            'editUser',
            'roomEnvironment',
            'microOrganism',
            'roomRegionMgmts' => function(HasMany $query) {
                $query->orderBy('region');
            }
        ])->get();

        return [
            'status' => 0,
            'data'   => [
                'regionMgmts' => $regionMgmts
            ]
        ];
    }

    /**
     * @api              {get} /api/regionMgmts/{id} 獲取多筆區域資料
     * @apiGroup         RegionMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} region 區域編號
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.regionMgmt 區域資訊
     * @apiSuccess {String} data.regionMgmt.region 地區
     * @apiSuccess {Number} data.regionMgmt.mm 1px多少mm
     * @apiSuccess {Number} data.regionMgmt.img_width 圖片寬度
     * @apiSuccess {Number} data.regionMgmt.img_height 圖片高度
     * @apiSuccess {Object[]} data.regionMgmt.object_mgmts 載具列表
     * @apiSuccess {Object} data.regionMgmt.object_mgmts.vehicle_mgmt 車輛
     * @apiSuccess {Number} data.regionMgmt.object_mgmts.vehicle_mgmt.position_x x軸
     * @apiSuccess {Number} data.regionMgmt.object_mgmts.vehicle_mgmt.position_y y軸
     *
     * @apiSampleRequest off
     */
    public function show($id): array {
        /** @var RegionMgmt $regionMgmt */
        $regionMgmt = RegionMgmt::with([
            'project.projectDeploy',
            'roomEnvironment',
            'microOrganism',
            'roomRegionMgmts' => function(HasMany $query) {
                $query->orderBy('region');
            },
            'roomRegionMgmts.roomEnvironment',
            'roomRegionMgmts.microOrganism'
        ])->findOrFail($id);

        return [
            'status' => 0,
            'data'   => [
                'regionMgmt' => $regionMgmt
            ]
        ];
    }

    /**
     * @throws Throwable
     * @api              {post} /api/regionMgmts 新增單筆區域資料
     * @apiGroup         RegionMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiBody {String} name 地圖名稱
     * @apiBody {Number} mm 1px多少mm
     * @apiBody {String} name 區域名稱
     * @apiBody {String} region 區域代碼
     * @apiBody {File} file 底圖圖檔
     * @apiBody {File} yaml 底圖圖檔
     * @apiBody {Number} resolution 1px等於多少公尺
     * @apiBody {Number} origin_x 原點離左下角的X座標
     * @apiBody {Number} origin_y 原點離左下角的Y座標
     * @apiBody {Number} data.regionMgmt.img_width 圖片寬度
     * @apiBody {Number} data.regionMgmt.img_height 圖片高度
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功<br>-7：該區域代碼已存在
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.regionMgmt 區域資訊
     * @apiSuccess {String} data.regionMgmt.name 區域名稱
     * @apiSuccess {String} data.regionMgmt.region 地區
     * @apiSuccess {Number} data.regionMgmt.mm 1px多少mm
     * @apiSuccess {Number} data.regionMgmt.img_width 圖片寬度
     * @apiSuccess {Number} data.regionMgmt.img_height 圖片高度
     *
     * @apiSampleRequest off
     */
    public function store(Request $request): Response|array|Application|ResponseFactory {
        $validator = Validator::make($request->all(), [
            'region'     => 'required|string|max:256',
            'resolution' => 'required|numeric',
            'project_id' => 'required|string|exists:projects,id'
        ]);
        if($validator->fails()) {
            return response(null, 422);
        }

        $hasImage = $request->hasFile('file');
        if($hasImage) {
            $image = $request->file('file');
            $imageFile = Intervention::make($image)->orientate();
            if($imageFile->getWidth() > 3966 || $imageFile->getHeight() > 2016) {
                return [
                    'status' => config('errors.image_dimension_invalid'),
                    'data'   => [
                        'type' => 'radar'
                    ]
                ];
            }
        }

        $hasCad = $request->hasFile('cad');
        if($hasCad) {
            $cadImage = $request->file('cad');
            $cadImageFile = Intervention::make($cadImage)->orientate();
            if($cadImageFile->getWidth() > 3966 || $cadImageFile->getHeight() > 2016) {
                return [
                    'status' => config('errors.image_dimension_invalid'),
                    'data'   => [
                        'type' => 'cad'
                    ]
                ];
            }
        }

        $id = $request->input('id');
        $region = $request->input('region');
        $insertMode = false;
        $projectId = $request->input('project_id');
        if($id) {
            $regionMgmt = RegionMgmt::whereRegion($region)->where('project_id', $projectId)->where('id', '<>', $id)->first();
            if($regionMgmt) {
                return [
                    'status' => config('errors.data_repeat_region'),
                ];
            }
            $regionMgmt = RegionMgmt::find($id);
        } else {
            $insertMode = true;
            $regionMgmt = new RegionMgmt();
        }
        $projectId = $request->input('project_id');
        $regionMgmt->project_id = $projectId ? (int)$projectId : null;
        $regionMgmt->region = $region;
        $regionMgmt->x_px = $request->input('x_px');
        $regionMgmt->y_px = $request->input('y_px');
        $regionMgmt->cleanliness_grade = $request->input('cleanliness_grade');
        $regionMgmt->floor_region_mgmt_id = $request->input('floor_region_mgmt_id');
        $regionMgmt->save();
        $roomEnvironment = RoomEnvironment::whereRegionMgmtId($regionMgmt->id)->first();
        if(!$roomEnvironment) {
            $roomEnvironment = new RoomEnvironment();
            $roomEnvironment->region_mgmt_id = $regionMgmt->id;
        }
        $roomEnvironment->room_name = $request->input('room_environment_room_name');
        $roomEnvironment->save();
        $regionMgmt = $regionMgmt->load([
            'allRoomLocations',
            'roomEnvironment'
        ]);
        if($regionMgmt->floor_region_mgmt_id) {
            $regionMgmt->floors = $regionMgmt->floorRegionMgmt->floors;
            $regionMgmt->locations()->update([
                'floors' => $regionMgmt->floors
            ]);
        } else {
            $regionMgmt->floors = $request->input('floors');
            $regionMgmt->allRoomLocations()->update([
                'floors' => $regionMgmt->floors
            ]);
        }
        $regionMgmt->save();

        $user = Auth::user();
        if($insertMode) {
            $user->regionMgmts()->attach([
                $regionMgmt->id => [
                    'is_write' => 1,
                    'is_read'  => 1
                ]
            ]);
        }

        $project = Project::find($projectId);
        $path = storage_path("app/projects/$project->name/$regionMgmt->region");
        if(!File::exists($path))
            File::makeDirectory($path, 493, true);
        $filepath = "$path/background_{$regionMgmt->region}_preview.png";
        $oriFilepath = "$path/background_{$regionMgmt->region}_original.png";
        $cadFilepath = "$path/cad_$regionMgmt->region.png";

        if($hasImage) {
            $file = $request->file('file');
            File::put($oriFilepath, $file->getContent());
        }

        if($hasCad) {
            $cad = $request->file('cad');
            File::put($cadFilepath, $cad->getContent());
        }

        try {
            $isExist = File::exists($oriFilepath);
            $imgWidth = $request->input('img_width');
            $imgHeight = $request->input('img_height');
            if($isExist) {
                $newFileMd5 = md5_file($oriFilepath);
                if($regionMgmt->undeploy_background_file_md5 != $newFileMd5 || $imgWidth != $regionMgmt->img_width || $imgHeight != $regionMgmt->img_height) {
                    $regionMgmt = $regionMgmt->load('project.projectDeploy');
                    $imageFile = Intervention::make($oriFilepath)->orientate();
                    if($imgWidth != $imageFile->width() || $imgHeight != $imageFile->height()) {
                        $imageFile->resize($imgWidth, $imgHeight, function($constraint) {
                            $constraint->aspectRatio();
                        });
                    }
                    $imageFile->encode('png', 100);
                    $imageFile->save($filepath);
                    $regionMgmt->undeploy_background_file_md5 = $newFileMd5;
                    $regionMgmt->img_width = $imgWidth;
                    $regionMgmt->img_height = $imgHeight;
                }
            }

            $isCadExist = File::exists($cadFilepath);
            $cadWidth = $request->input('cad_width');
            $cadHeight = $request->input('cad_height');
            if($isCadExist) {
                $cadImageFile = Intervention::make($cadFilepath)->orientate();
                $cadImageFile->resize($cadWidth, $cadHeight, function($constraint) {
                    $constraint->aspectRatio();
                });
                $cadImageFile->encode('png', 100);
                $cadImageFile->save($cadFilepath);

                $regionMgmt->cad_width = $cadWidth;
                $regionMgmt->cad_height = $cadHeight;
                $regionMgmt = $regionMgmt->load('roomEnvironment');
                DB::transaction(function() use ($regionMgmt, $cadWidth, $cadHeight) {
                    $locations = Location::whereRoom($regionMgmt->roomEnvironment->room_name)->get();
                    foreach($locations as $location) {
                        if($location->x_px > $cadWidth || $location->y_px > $cadHeight) {
                            if($location->x_px > $cadWidth) {
                                $location->x_px = $cadWidth - 15;
                            }
                            if($location->y_px > $cadHeight) {
                                $location->y_px = $cadHeight - 15;
                            }
                        }
                        $location->save();
                    }
                });
            }
            $regionMgmt->save();
        } catch(FileNotFoundException) {
            return response(null, 404);
        }

        $hasYaml = $request->hasFile('yaml');
        if($hasYaml) {
            $this->saveYaml($request, $regionMgmt);
        } else {
            $regionMgmt->origin_x = $request->input('origin_x', $regionMgmt->origin_x);
            $regionMgmt->origin_y = $request->input('origin_y', $regionMgmt->origin_y);
            $regionMgmt->resolution = $request->input('resolution', $regionMgmt->resolution);
        }

        $hasRoute = $request->hasFile('route');
        if($hasRoute) {
            $this->saveRoute($request, $regionMgmt);
        }

        ProjectDeployController::syncJsonFiles($regionMgmt->id);
        $regionMgmt->route_id = null;
        $regionMgmt->save();
        $regionMgmt = $regionMgmt->load('roomEnvironment');

        return [
            'status' => 0,
            'data'   => [
                'regionMgmt' => $regionMgmt
            ]
        ];
    }

    /**
     * @api              {patch} /api/regionMgmts/{region} 更新該張區域所有站點跟軌道
     * @apiGroup         RegionMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} name 地圖名稱
     * @apiParam {Number} mm 1px多少mm
     * @apiParam {String} name 區域名稱
     * @apiParam {String} region 區域代碼
     * @apiParam {Object[]} vertices 站點資料，如要清除則餵空陣列即可
     * @apiParam {Number} vertices.id 站點編號
     * @apiParam {Number} vertices.x 站點x軸
     * @apiParam {Number} vertices.y 站點y軸
     * @apiParam {Number} vertices.z 站點z軸
     * @apiParam {String} vertices.tag 站點tag
     * @apiParam {Number} vertices.vertex_type_id 站點類型編號
     * @apiParam {Object[]} edges 軌道資料，如要清除則餵空陣列即可
     * @apiParam {Number} edges.id 軌道編號
     * @apiParam {Number} edge.start_vertex_id 起始站點
     * @apiParam {Number} edge.end_vertex_id 結束站點
     * @apiParam {Number} edge.direction 方向，0為雙向，1為站1~站2，2站2~站1
     * @apiParam {Number} edge.weight 權重，預設為1
     * @apiParam {Number} edge.enable 啟用狀態，1為啟用，0為禁用
     * @apiParam {Object[]} region_images 底圖列表
     * @apiParam {Number} region_images.id 底圖編號
     * @apiParam {String} region_images.name 底圖名稱
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.regionMgmt 區域資訊
     * @apiSuccess {String} data.regionMgmt.name 區域名稱
     * @apiSuccess {String} data.regionMgmt.region 地區
     * @apiSuccess {Number} data.regionMgmt.mm 1px多少mm
     * @apiSuccess {Number} data.regionMgmt.img_width 圖片寬度
     * @apiSuccess {Number} data.regionMgmt.img_height 圖片高度
     * @apiSuccess {Object[]} data.regionMgmt.region_images 底圖列表
     * @apiSuccess {Number} data.regionMgmt.region_images.id 底圖編號
     * @apiSuccess {String} data.regionMgmt.region_images.name 底圖名稱
     *
     * @apiSampleRequest off
     */
    public function update(Request $request, $id): array {
        /** @var RegionMgmt $regionMgmt */
        $regionMgmt = RegionMgmt::with('roomEnvironment')->findOrFail($id);
        $projectId = $regionMgmt->project_id;
        $inputVertices = $request->input('vertices');
        $vertexNames = array_column($inputVertices, 'name');
        $vertices = Vertex::whereRelation('regionMgmt', 'project_id', $projectId)->where('is_deploy', 0)->where('region_mgmt_id', '<>', $id)->whereIn('name', $vertexNames)->with([
            'regionMgmt.project'
        ])->get();
        if($vertices->count() > 0) {
            return [
                'status' => config('errors.data_repeat_vertex_name'),
                'data'   => $vertices
            ];
        }
        try {
            DB::transaction(function() use ($request, $regionMgmt) {
                $inputVertices = collect($request->input('vertices'));
                $inputEdges = collect($request->input('edges'));
                $vertices = Vertex::whereRegionMgmtId($regionMgmt->id)->whereIn('id', $inputVertices->where(function($inputVertex) {
                    return is_integer($inputVertex['id']);
                })->pluck('id'))->with([
                    'vertexConfigurations',
                    'undeployLocation'
                ])->get();
                $edges = Edge::whereRegionMgmtId($regionMgmt->id)->whereIn('id', $inputEdges->where(function($inputEdge) {
                    return is_integer($inputEdge['id']);
                })->pluck('id'))->with('edgeConfigurations')->get();
                foreach($inputVertices as $inputVertex) {
                    $inputVertexConfigurations = collect($inputVertex['vertex_configurations']);
                    $vertexConfiguration = $inputVertexConfigurations->where('type', 'vertex_name')->first();
                    $name = null;
                    if($vertexConfiguration) {
                        $name = $vertexConfiguration['data'];
                    }

                    $theta = null;
                    $vertexConfiguration = $inputVertexConfigurations->where('type', 'theta')->first();
                    if($vertexConfiguration) {
                        $theta = $vertexConfiguration['data'];
                    }

                    $vertex = $vertices->where('id', $inputVertex['id'])->first();
                    if(!$vertex) {
                        $vertex = new Vertex();
                    }
                    $vertex->region_mgmt_id = $regionMgmt->id;
                    $vertex->vertex_type_id = $inputVertex['vertex_type_id'];
                    $vertex->tag = $inputVertex['tag'];
                    $vertex->x = imgCoordToQuadrantCoordX($regionMgmt->resolution, $inputVertex['x_px'], $regionMgmt->origin_x);
                    $vertex->y = imgCoordToQuadrantCoordY($regionMgmt->resolution, $inputVertex['y_px'], $regionMgmt->img_height, $regionMgmt->origin_y);
                    $vertex->z = $inputVertex['z'];
                    $vertex->name = $name;
                    $vertex->theta = $theta;
                    $vertex->save();
                    $vertexIdx = $vertices->search(function(Vertex $existVertex) use ($vertex) {
                        return $existVertex->id == $vertex->id;
                    });
                    if($vertexIdx === false) {
                        $vertices = $vertices->push($vertex);
                    }

                    foreach($inputVertex['edges'] as $inputEdge) {
                        $inputEdge['start_vertex_id'] = $vertex->id;
                        $inputEdges = $inputEdges->push($inputEdge);
                        $vertex = Vertex::whereId($inputEdge['end_vertex_id'])->first();
                        $vertices = $vertices->push($vertex);
                    }

                    if(!is_integer($inputVertex['id'])) {
                        $edgeIdx = $inputEdges->search(function($inputEdge) use ($inputVertex) {
                            return $inputEdge['start_vertex_id'] == $inputVertex['id'] || $inputEdge['end_vertex_id'] == $inputVertex['id'];
                        });
                        if($edgeIdx !== false) {
                            $inputEdge = $inputEdges[$edgeIdx];
                            if($inputEdge['start_vertex_id'] == $inputVertex['id']) {
                                $inputEdge['start_vertex_id'] = $vertex->id;
                            } else if($inputEdge['end_vertex_id'] == $inputVertex['id']) {
                                $inputEdge['end_vertex_id'] = $vertex->id;
                            }
                            $inputEdges->offsetSet($edgeIdx, $inputEdge);
                        }
                    }

                    /** @var VertexConfiguration $vertexConfiguration */
                    if($vertex->vertex_type_id == 4) {
                        if($vertex->undeployLocation) {
                            $undeployLocation = $vertex->undeployLocation;
                        } else {
                            $undeployLocation = new UndeployLocation();
                        }
                        $undeployLocation->vertex_id = $vertex->id;
                        $undeployLocation->save();
                    }

                    foreach($inputVertex['vertex_configurations'] as $inputVertexConfiguration) {
                        $data = $inputVertexConfiguration['data'];
                        if(is_array($data)) {
                            $data = json_encode($data);
                        }

                        $type = $inputVertexConfiguration['type'];
                        $vertexConfiguration = $vertex->vertexConfigurations->where('type', $type)->first();
                        if(!$vertexConfiguration) {
                            $vertexConfiguration = new VertexConfiguration();
                        }
                        $vertexConfiguration->vertex_id = $vertex->id;
                        $vertexConfiguration->type = $type;
                        $vertexConfiguration->data = $data;
                        $vertexConfiguration->save();
                    }
                }
                Vertex::whereRegionMgmtId($regionMgmt->id)->where('is_deploy', 0)->whereNotIn('id', $vertices->pluck('id'))->delete();
                VertexConfiguration::whereRelation('vertex', function(Builder $query) use ($regionMgmt, $vertices) {
                    $query->where('region_mgmt_id', $regionMgmt->id);
                    $query->where('is_deploy', 0);
                    $query->whereNotIn('id', $vertices->pluck('id'));
                })->delete();
                UndeployLocation::whereRelation('vertex', 'region_mgmt_id', $regionMgmt->id)->whereNotIn('vertex_id', $vertices->pluck('id'))->delete();

                foreach($inputEdges as $inputEdge) {
                    $startVertexId = $inputEdge['start_vertex_id'];
                    $endVertexId = $inputEdge['end_vertex_id'];
                    $startVertex = $vertices->where('id', $startVertexId)->first();
                    $endVertex = $vertices->where('id', $endVertexId)->first();
                    $name = "({$startVertex->name},{$endVertex->name})";
                    $edge = $edges->where('id', $inputEdge['id'])->first();
                    if(!$edge) {
                        $edge = new Edge();
                    }
                    $edge->direction = $inputEdge['direction'];
                    $edge->start_vertex_id = $startVertex->id;
                    $edge->end_vertex_id = $endVertex->id;
                    $edge->region_mgmt_id = $regionMgmt->id;
                    $edge->name = $name;
                    $edge->save();
                    $edgeIdx = $edges->search(function(Edge $existEdge) use ($edge) {
                        return $existEdge->id == $edge->id;
                    });
                    if($edgeIdx === false) {
                        $edges = $edges->push($edge);
                    }
                    foreach($inputEdge['edge_configurations'] as $inputEdgeConfiguration) {
                        $edgeConfiguration = $edge->edgeConfigurations->where('type', $inputEdgeConfiguration['type'])->first();
                        if(!$edgeConfiguration) {
                            $edgeConfiguration = new EdgeConfiguration();
                        }
                        $data = $inputEdgeConfiguration['data'];
                        if(is_array($data)) {
                            $data = json_encode($data);
                        }
                        $edgeConfiguration->edge_id = $edge->id;
                        $edgeConfiguration->type = $inputEdgeConfiguration['type'];
                        $edgeConfiguration->data = $data;
                        $edgeConfiguration->save();
                    }
                }
                Edge::whereRegionMgmtId($regionMgmt->id)->where('is_deploy', 0)->whereNotIn('id', $edges->pluck('id'))->delete();
                EdgeConfiguration::whereRelation('edge', function(Builder $query) use ($regionMgmt, $edges) {
                    $query->where('region_mgmt_id', $regionMgmt->id);
                    $query->where('is_deploy', 0);
                    $query->whereNotIn('id', $edges->pluck('id'));
                })->delete();
            });
        } catch(Throwable $e) {
            Log::info($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        if(!$regionMgmt->route_id) {
            ProjectDeployController::syncJsonFiles($regionMgmt->id);
        }

        $regionMgmt->updated_at = Carbon::now();
        $regionMgmt->save();
        $regionMgmt = $regionMgmt->refresh();

        $project = Project::find($projectId);
        $this->project->deploy($project->name);

        return [
            'status' => 0,
            'data'   => [
                'regionMgmt' => $regionMgmt
            ]
        ];
    }

    /**
     * @api              {delete} /api/regionMgmts/{id} 刪除單筆區域資料
     * @apiGroup         RegionMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} region 區域編號
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     *
     * @apiSampleRequest off
     */
    public function destroy($regionMgmtId): array {
        $regionMgmt = RegionMgmt::findOrFail($regionMgmtId);
        $regionMgmt->delete();

        return [
            'status' => 0
        ];
    }

    public function display($projectName, $region, $type = ''): Response|Application|ResponseFactory {
        $regionMgmt = RegionMgmt::whereRelation('project', 'name', $projectName)->where('region', $region)->firstOrFail();
        $path = storage_path("app/projects/$projectName/$regionMgmt->region/background_{$regionMgmt->region}$type.png");
        try {
            $file = File::get($path);
        } catch(FileNotFoundException) {
            return response(null, 404);
        }

        return response($file, 200, [
            'Content-Type' => File::mimeType($path)
        ]);
    }

    public function displayCad($projectName, $region): Response|Application|ResponseFactory {
        $regionMgmt = RegionMgmt::whereProjectName($projectName)->where('region', $region)->with('project')->firstOrFail();
        $path = storage_path("app/projects/$projectName/$regionMgmt->region/cad_$regionMgmt->region.png");
        try {
            $file = File::get($path);
        } catch(FileNotFoundException) {
            return response(null, 404);
        }

        return response($file, 200, [
            'Content-Type' => File::mimeType($path)
        ]);
    }

    private function saveYaml(Request $request, RegionMgmt $regionMgmt): void {
        $file = $request->file('yaml');
        $regionMgmt = $regionMgmt->load('project');
        $projectName = $regionMgmt->project->name;
        $path = storage_path("app/projects/$projectName/$regionMgmt->region");
        if(!File::exists($path))
            File::makeDirectory($path, 493, true);

        $filepath = "$path/{$regionMgmt->region}.yaml";
        File::put($filepath, $file->getContent());

        $regionMgmt->resolution = $request->input('resolution');
        $regionMgmt->origin_x = $request->input('origin_x');
        $regionMgmt->origin_y = $request->input('origin_y');
        $regionMgmt->save();
        $regionMgmt = $regionMgmt->load('objectMgmt.vehicleMgmt');
        if($regionMgmt->objectMgmt && $regionMgmt->objectMgmt->vehicleMgmt) {
            $vehicleMgmt = $regionMgmt->objectMgmt->vehicleMgmt;
            $vehicleMgmt->position_x = $request->input('origin_x');
            $vehicleMgmt->position_y = $request->input('origin_y');
            $vehicleMgmt->save();
        }
    }

    private function saveRoute(Request $request, RegionMgmt $regionMgmt): void {
        $file = $request->file('route');
        $regionMgmt = $regionMgmt->load('project');
        $projectName = $regionMgmt->project->name;
        $path = storage_path("app/projects/$projectName/$regionMgmt->region");
        if(!File::exists($path))
            File::makeDirectory($path, 493, true);

        $filepath = "$path/routes.json";
        File::put($filepath, $file->getContent());

        $json = json_decode($file->getContent(), true);

        if(isset($json['origin'])) {
            $regionMgmt->origin_x = $json['origin']['x'] ?? 0;
            $regionMgmt->origin_y = $json['origin']['y'] ?? 0;
        }

        if(isset($json['vertices'])) {
            Edge::whereRegionMgmtId($regionMgmt->id)->delete();
            Vertex::whereRegionMgmtId($regionMgmt->id)->delete();
            foreach($json['vertices'] as $inputVertex) {
                $vertex = new Vertex();
                $vertex->region_mgmt_id = $regionMgmt->id;
                $vertex->name = $inputVertex['name'];
                $vertex->x = $inputVertex['x'];
                $vertex->y = $inputVertex['y'];
                $vertex->z = 0;
                $vertex->tag = $inputVertex['tag'];
                $vertex->save();

                $vertexConfiguration = new VertexConfiguration();
                $vertexConfiguration->vertex_id = $vertex->id;
                $vertexConfiguration->type = 'Vertex_name';
                $vertexConfiguration->data = $inputVertex['name'];
                $vertexConfiguration->save();
            }
        }
    }

    public function download($projectName, $filename): BinaryFileResponse {
        $region = '';
        if(str_starts_with($filename, 'graph_')) {
            $region = str_replace('graph_', '', $filename);
        } else if(str_starts_with($filename, 'routes_')) {
            $region = str_replace('routes_', '', $filename);
        }
        $regionMgmt = RegionMgmt::whereRelation('project', 'name', $projectName)->where('region', $region)->firstOrFail();
        ProjectDeployController::syncJsonFiles($region);
        $path = storage_path("app/projects/$projectName/$regionMgmt->region/$filename.json");

        return response()->download($path, "$filename.json");
    }

    /**
     * @api              {post} /api/regionMgmts/yaml 讀取yaml檔資訊
     * @apiGroup         RegionMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {File} file Yaml檔
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Number} data.resolution 比例（1px等於多少公尺）
     * @apiSuccess {Object} data.origin 離原點左下角的點資訊
     * @apiSuccess {Number} data.origin_x 原點離左下角的X座標
     * @apiSuccess {Number} data.origin_y 原點離左下角的Y座標
     *
     * @apiSampleRequest off
     */
    public function loadYaml(Request $request): array {
        $file = $request->file('file');
        $fileInfo = yaml_parse($file->getContent());

        return [
            'status' => 0,
            'data'   => $fileInfo
        ];
    }

    /**
     * @api              {post} /api/regionMgmts/image 讀取yaml檔資訊
     * @apiGroup         RegionMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {File} file Yaml檔
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Number} data.img_width 圖片寬度
     * @apiSuccess {Number} data.img_height 圖片高度
     *
     * @apiSampleRequest off
     */
    public function loadImage(Request $request): array {
        $file = $request->file('file');
        $imageFile = Intervention::make($file)->orientate();

        return [
            'status' => 0,
            'data'   => [
                'width'  => $imageFile->width(),
                'height' => $imageFile->height()
            ]
        ];
    }
}
