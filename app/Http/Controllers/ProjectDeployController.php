<?php

namespace App\Http\Controllers;

use App\Models\Edge;
use App\Models\EdgeConfiguration;
use App\Models\MqttCommand;
use App\Models\Project;
use App\Models\ProjectDeploy;
use App\Models\RegionMgmt;
use App\Models\VehicleMgmt;
use App\Models\Vertex;
use App\Models\VertexConfiguration;
use Auth;
use Carbon\Carbon;
use Exception;
use File;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;
use Queue;
use Str;
use Validator;

class ProjectDeployController extends Controller {
    /**
     * @api              {get} /api/projectDeploys/{projectName} 顯示發佈資料
     * @apiGroup         ProjectDeploy
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} projectName 區域名稱
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.projectDeploy 燒錄發佈資料
     * @apiSuccess {Number} data.projectDeploy.deploy_status 專案發佈狀態，null為「待燒入」，0為「燒入失敗」，1為「燒入成功
     * @apiSuccess {String} data.projectDeploy.deploy_fail_desc 發佈失敗的理由
     * @apiSuccess {Object[]} data.projectDeploy.regionMgmts 區域列表
     * @apiSuccess {Object[]} data.projectDeploy.regionMgmts.objectMgmts 載具列表
     * @apiSuccess {Object} data.projectDeploy.regionMgmts.objectMgmts.vehicleMgmt 車輛資訊
     * @apiSuccess {Number} data.projectDeploy.regionMgmts.objectMgmts.vehicleMgmt.deploy_status 車輛發佈狀態
     * @apiSuccess {String} data.projectDeploy.regionMgmts.objectMgmts.vehicleMgmt.deploy_fail_reason 車輛發佈失敗原因
     *
     * @apiSampleRequest off
     */
    public function show($projectName): array {
        $projectDeploy = ProjectDeploy::find($projectName);
        $vehicleMgmts = VehicleMgmt::orderBy('vehicle_id')->with('vehicleStatus')->get();

        return [
            'status' => 0,
            'data'   => [
                'projectDeploy' => $projectDeploy,
                'vehicleMgmts'  => $vehicleMgmts
            ]
        ];
    }

    /**
     * @api              {post} /api/projectDeploys 新增燒錄資料
     * @apiGroup         ProjectDeploy
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} project_name 區域名稱
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     *
     * @apiSampleRequest off
     */
    public function store(Request $request): Response|array|Application|ResponseFactory {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|exists:projects,name'
        ]);
        if($validator->fails()) {
            return response(null, 422);
        }

        $projectName = $request->input('project_name');
        $project = Project::whereName($projectName)->first();
        $vertices = Vertex::whereRelation('regionMgmt', 'project_id', $project->id)->where('is_deploy', 0)->whereDoesntHave('vertexConfigurations')->get();
        if($vertices->count() > 0) {
            return [
                'status' => config('errors.vertex_config_no_set'),
                'data'   => [
                    'vertices' => $vertices
                ]
            ];
        }

        $projectDeploy = new ProjectDeploy();
        $res = $this->saveModel($request, $projectDeploy);
        if($res['status'] != 0) {
            return response(null, $res['status']);
        }
        $projectDeploy = $res['data']['projectDeploy'];

        return [
            'status' => 0,
            'data'   => [
                'projectDeploy' => $projectDeploy
            ]
        ];
    }

    /**
     * @throws Exception
     * @api              {patch} /api/projectDeploys 更新燒錄資料
     * @apiGroup         ProjectDeploy
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} projectName 區域名稱
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     *
     * @apiSampleRequest off
     */
    public function update(Request $request, $projectName): Response|array|Application|ResponseFactory {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|exists:project_deploy,project_name',
        ]);
        if($validator->fails()) {
            return response(null, 422);
        }

        $project = Project::whereName($projectName)->first();
        $vertices = Vertex::whereRelation('regionMgmt', 'project_id', $project->id)->where('is_deploy', 0)->whereDoesntHave('vertexConfigurations')->get();
        if($vertices->count() > 0) {
            return [
                'status' => config('errors.vertex_config_no_set'),
                'data'   => [
                    'vertices' => $vertices
                ]
            ];
        }

        $projectDeploy = ProjectDeploy::find($projectName);
        $res = $this->saveModel($request, $projectDeploy);
        $projectDeploy = $res['data']['projectDeploy'];

        return [
            'status' => 0,
            'data'   => [
                'projectDeploy' => $projectDeploy
            ]
        ];
    }

    /**
     * @api              {delete} /api/projectDeploys/{projectName} 刪除燒錄資料（取消）
     * @apiGroup         ProjectDeploy
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} projectName 區域名稱
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     *
     * @apiSampleRequest off
     */
    public function destroy($projectName) {
        $projectDeploy = ProjectDeploy::findOrFail($projectName);
        $projectDeploy->delete();

        return [
            'status' => 0
        ];
    }

    #[ArrayShape([
        'graphConfigPath' => "string",
        'graphPath' => "string"
    ])]
    public static function syncJsonFiles($regionMgmtId): array {
        $regionMgmt = RegionMgmt::findOrFail($regionMgmtId)->load([
            'vertices.vertexConfigurations',
            'edges.edgeConfigurations',
            'edges.startVertex',
            'edges.endVertex',
            'project'
        ]);
        $region = $regionMgmt->region;
        $projectName = $regionMgmt->project->name;

        $dirPath = storage_path("app/projects/$projectName/$region");
        if(!File::exists($dirPath)) {
            File::makeDirectory($dirPath, 493, true);
        }

        $graphConfigPath = storage_path("app/projects/$projectName/$region/{$region}_graphconfig.json");
        $graphConfigJson = self::generateGraphConfigJson($regionMgmt);
        File::put($graphConfigPath, $graphConfigJson);

        $graphPath = storage_path("app/projects/$projectName/$region/$region.json");
        $graphJson = self::generateGraphJson($regionMgmt);
        File::put($graphPath, $graphJson);

        return [
            'graphConfigPath' => $graphConfigPath,
            'graphPath' => $graphPath
        ];
    }

    public static function getVertexConfigurations(Collection $inputVertexConfigurations): array {
        $vertexConfigurations = [];
        /** @var VertexConfiguration $vertexConfig */
        foreach($inputVertexConfigurations as $vertexConfig) {
            $type = $vertexConfig->type;
            $data = $vertexConfig->data;
            if($type == 'remapping' || $type == 'intersection') {
                $vertexConfiguration = [
                    'typename' => $type,
                ];
                if($data) {
                    foreach($data as $key => $val) {
                        $vertexConfiguration[$key] = $val;
                    }
                    $vertexConfigurations[] = $vertexConfiguration;
                }
            } else if($type == 'break_point' || $type == 'charger') {
                if($data == 'on') {
                    $vertexConfigurations[] = [
                        'typename' => $type
                    ];
                }
            } else {
                $vertexConfigurations[] = [
                    'typename' => $type,
                    'data'     => $data
                ];
            }
        }
        return $vertexConfigurations;
    }

    private static function generateGraphConfigJson(RegionMgmt $regionMgmt): string {
        $vertices = $regionMgmt->vertices->where('is_deploy', 0)->sortBy('id')->values()->map(function(Vertex $vertex) {
            $vertexConfigurations = ProjectDeployController::getVertexConfigurations($vertex->vertexConfigurations);
            return [
                'typename' => 'vertex',
                'data'     => $vertexConfigurations
            ];
        });

        $edges = $regionMgmt->edges->where('is_deploy', 0)->sortBy('id')->values()->map(function(Edge $edge) {
            $edgeConfigurations = [];
            foreach($edge->edgeConfigurations as &$edgeConfig) {
                $type = $edgeConfig->type;
                $data = $edgeConfig->data;
                if($type == 'rail_switch') {
                    $edgeConfiguration = [
                        'typename' => $type
                    ];
                    if($data) {
                        foreach($data as $key => $val) {
                            $edgeConfiguration[$key] = $val;
                        }
                        $edgeConfigurations[] = $edgeConfiguration;
                    }
                } else {
                    $edgeConfigurations[] = [
                        'typename' => $type,
                        'data'     => $data
                    ];
                }
            }

            return [
                'typename' => 'edge',
                'data'     => $edgeConfigurations
            ];
        });

        return json_encode([
            'id'       => 0,
            'typename' => 'GraphConfigure',
            'data'     => $vertices->merge($edges)
        ]);
    }

    private static function generateGraphJson(RegionMgmt $regionMgmt): string {
        $vertices = $regionMgmt->vertices->where('is_deploy', 0)->sortBy('id')->values()->map(function(Vertex $vertex) use ($regionMgmt) {
            $vertexConfigurations = $vertex->vertexConfigurations;
            return [
                'vertex_name' => $vertex->name,
                'pose'        => [
                    'x'     => $vertex->x,
                    'y'     => $vertex->y,
                    'z'     => (int)$vertex->z,
                    'theta' => (int)$vertex->theta
                ],
                'properties'  => ProjectDeployController::getVertexConfigurations($vertexConfigurations)
            ];
        });

        $edges = $regionMgmt->edges->where('is_deploy', 0)->sortBy('id')->values()->map(function(Edge $edge) {
            return [
                'name'         => $edge->name,
                'direction'    => $edge->direction == 1 ? 'directed' : 'undirected',
                'start_vertex' => $edge->startVertex ? $edge->startVertex->name : null,
                'end_vertex'   => $edge->endVertex ? $edge->endVertex->name : null,
                'properties'   => $edge->edgeConfigurations->map(function(EdgeConfiguration $edgeConfiguration) {
                    if($edgeConfiguration->type == 'rail_switch') {
                        $data = $edgeConfiguration->data;
                        return [
                            'typename' => $edgeConfiguration->type,
                            'switch'   => $data['switch'],
                            'angle'    => $data['angle']
                        ];
                    } else {
                        return [
                            'typename' => $edgeConfiguration->type,
                            'data'     => $edgeConfiguration->data
                        ];
                    }
                })
            ];
        });

        return json_encode([
            'kind'     => 'graph',
            'version'  => 'graph/v2',
            'metadata' => [
                [
                    'region_name' => $regionMgmt->region,
                    'source_hash' => $regionMgmt->undeploy_route_file_md5
                ]
            ],
            'items'    => [
                [
                    'kind'     => 'vertices',
                    'version'  => 'graph/v2',
                    'metadata' => $vertices
                ],
                [
                    'kind'     => 'edges',
                    'version'  => 'graph/v2',
                    'metadata' => $edges
                ]
            ]
        ]);
    }

    /**
     * @throws Exception
     */
    private function saveModel(Request $request, ProjectDeploy $projectDeploy): Response|array|Application|ResponseFactory {
        $now = Carbon::now();
        $projectDeploy = $projectDeploy->load('regionMgmts');
        $projectDeploy->project_name = $request->input('project_name', $projectDeploy->project_name);
        $projectDeploy->deploy_ts = $now;
        $idData = [
            'date' => "{$now->format('Y-m-d')}T{$now->format('H:i:s.v')}",
            'uuid' => Str::uuid()->toString()
        ];
        $project = Project::whereName($projectDeploy->project_name)->first();
        $regionMgmts = RegionMgmt::whereProjectId($project->id)->orderBy('region')->get();

        $regions = $regionMgmts->where('is_exist_preview_background', true)->map(function(RegionMgmt $regionMgmt) use ($projectDeploy) {
            $paths = self::syncJsonFiles($regionMgmt->id);
            $backgroundLink = storage_path("app/projects/$projectDeploy->project_name/$regionMgmt->region/background_{$regionMgmt->region}_preview.png");
            return [
                'graph'       => [
                    'region_id' => $regionMgmt->region,
                    'url'       => url("$projectDeploy->project_name/graph_$regionMgmt->region.json"),
                    'md5'       => md5_file($paths['graphPath'])
                ],
                'graphconfig' => [
                    'region_id' => $regionMgmt->region,
                    'url'       => url("$projectDeploy->project_name/graphconfig_{$regionMgmt->region}.json"),
                    'md5'       => md5_file($paths['graphConfigPath'])
                ],
                'background'  => [
                    'region_id' => $regionMgmt->region,
                    'url'       => url("$projectDeploy->project_name/background_{$regionMgmt->region}_preview.png"),
                    'md5'       => md5_file($backgroundLink)
                ],
            ];
        })->values()->toArray();

        $projectDeploy->profile_log = json_encode([
            'typename'     => 'deploy_graph',
            'id'           => $idData,
            'project_name' => $projectDeploy->project_name,
            'regions'      => $regions
        ]);
        $projectDeploy->deploy_status = 2;
        $projectDeploy->save();

        $mqttCommand = new MqttCommand();
        $mqttCommand->mqtt_command_type_id = 9;
        $mqttCommand->user_id = Auth::id();
        $mqttCommand->idData = $idData;
        $mqttCommand->data = [
            'project_name' => $projectDeploy->project_name,
            'regions'      => $regions
        ];
        $mqttCommand->send_command = json_encode($mqttCommand->preview_send_command);
        try {
            Queue::connection('rabbitmq')->pushRaw($mqttCommand->send_command);
            $mqttCommand->save();

            if(config('app.env') == 'local') {
                Queue::connection('rabbitmq-receiver')->pushRaw(json_encode([
                    'typename' => 'deploy_graph',
                    'priority' => 1,
                    'id'       => $mqttCommand->idData,
                    'reply'    => [
                        'condition'   => 'accepted',
                        'description' => null
                    ],
                    'data'     => [
                        'update_fail_account_list'         => [
                            [
                                'account' => [
                                    'type' => 'agv',
                                    'name' => 'MR001'
                                ],
                                'reason'  => '1'
                            ]
                        ],
                        'update_successfully_account_list' => [
                            [
                                'account' => [
                                    'type' => 'agv',
                                    'name' => 'MR003'
                                ]
                            ]
                        ]
                    ]
                ]));
            }
        } catch(Exception $e) {
            $projectDeploy->deploy_status = null;
            $projectDeploy->save();
            throw $e;
        }

        return [
            'status' => 0,
            'data'   => [
                'projectDeploy' => $projectDeploy
            ]
        ];
    }
}
