<?php

namespace App\Http\Controllers;

use App\Models\Edge;
use App\Models\MqttCommand;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Queue;
use Validator;

class EdgeController extends Controller {
    /**
     * @api              {get} /api/edges 索取軌道列表
     * @apiGroup         Edge
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} region 區域
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.edges 軌道資料列表
     * @apiSuccess {Number} data.edges.start_vertex_id 起始站點
     * @apiSuccess {Number} data.edges.end_vertex_id 結束站點
     * @apiSuccess {Number} data.edges.enable 啟用狀態，1為啟用，0為禁用
     * @apiSuccess {Object} data.edges.start_vertex 起點資料
     * @apiSuccess {Number} data.edges.start_vertex.id 起點編號
     * @apiSuccess {Number} data.edges.start_vertex.region 起點區域編號
     * @apiSuccess {Number} data.edges.start_vertex.x 起點x軸
     * @apiSuccess {Number} data.edges.start_vertex.y 起點y軸
     * @apiSuccess {Object} data.edges.end_vertex 終點資料
     * @apiSuccess {Number} data.edges.end_vertex.id 終點編號
     * @apiSuccess {Number} data.edges.end_vertex.region 終點區域編號
     * @apiSuccess {Number} data.edges.end_vertex.x 終點x軸
     * @apiSuccess {Number} data.edges.end_vertex.y 終點y軸
     *
     * @apiSampleRequest off
     */
    public function index(Request $request): Response|array|Application|ResponseFactory {
        $validator = Validator::make($request->all(), [
            'region_mgmt_id' => 'nullable|exists:region_mgmt,id'
        ]);
        if($validator->fails()) {
            return response(null, 422);
        }

        $edges = new Edge();
        $regionMgmtId = $request->input('region_mgmt_id');
        if($regionMgmtId) {
            $edges = $edges->whereRegionMgmtId($regionMgmtId)->whereHas('endVertex', function(Builder $query) use ($regionMgmtId) {
                $query->where('region_mgmt_id', $regionMgmtId);
            });
        }

        $isDeploy = $request->input('is_deploy', 0);
        $edges = $edges->where('is_deploy', $isDeploy);

        $edges = $edges->orderBy('id')->with([
            'startVertex',
            'endVertex',
            'edgeConfigurations',
            'regionMgmt'
        ])->get();

        return [
            'status' => 0,
            'data'   => [
                'edges' => $edges
            ]
        ];
    }

    /**
     * @api              {get} /api/edges/{id} 顯示單筆軌道資料
     * @apiGroup         Edge
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {Number} id 軌道編號
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Number} data.edge 軌道資料
     * @apiSuccess {Number} data.edge.region 區域編號
     * @apiSuccess {Number} data.edge.direction 方向，0為雙向，1為站1~站2，2站2~站1
     * @apiSuccess {Number} data.edge.weight 權重，預設為1
     * @apiSuccess {Number} data.edge.start_vertex_id 起始站點
     * @apiSuccess {Number} data.edge.end_vertex_id 結束站點
     * @apiSuccess {Number} data.edge.enable 啟用狀態，1為啟用，0為禁用
     * @apiSuccess {Object} data.edge.start_vertex 起點資料
     * @apiSuccess {Number} data.edge.start_vertex.id 起點編號
     * @apiSuccess {Number} data.edge.start_vertex.region 起點區域編號
     * @apiSuccess {Number} data.edge.start_vertex.x 起點x軸
     * @apiSuccess {Number} data.edge.start_vertex.y 起點y軸
     * @apiSuccess {Number} data.edge.start_vertex.z 起點z軸
     * @apiSuccess {String} data.edge.start_vertex.tag 起點tag
     * @apiSuccess {Number} data.edge.start_vertex.vertex_type_id 起點類型編號
     * @apiSuccess {Object[]} data.edge.start_vertex.vertex_configurations 起點自訂屬性
     * @apiSuccess {Number} data.edge.start_vertex.vertex_configurations.id 起點自訂屬性欄位編號
     * @apiSuccess {String} data.edge.start_vertex.vertex_configurations.type 起點自訂屬性欄位名稱
     * @apiSuccess {String} data.edge.start_vertex.vertex_configurations.data 起點自訂屬性欄位內容
     * @apiSuccess {Object} data.edge.end_vertex 終點資料
     * @apiSuccess {Number} data.edge.end_vertex.id 終點編號
     * @apiSuccess {Number} data.edge.end_vertex.region 終點區域編號
     * @apiSuccess {Number} data.edge.end_vertex.x 終點x軸
     * @apiSuccess {Number} data.edge.end_vertex.y 終點y軸
     * @apiSuccess {Number} data.edge.end_vertex.z 終點z軸
     * @apiSuccess {String} data.edge.end_vertex.tag 終點tag
     * @apiSuccess {Number} data.edge.end_vertex.vertex_type_id 終點類型編號
     * @apiSuccess {Object[]} data.edge.end_vertex.vertex_configurations 終點自訂屬性
     * @apiSuccess {Number} data.edge.end_vertex.vertex_configurations.id 終點自訂屬性欄位編號
     * @apiSuccess {String} data.edge.end_vertex.vertex_configurations.type 終點自訂屬性欄位名稱
     * @apiSuccess {String} data.edge.end_vertex.vertex_configurations.data 終點自訂屬性欄位內容
     *
     * @apiSampleRequest off
     */
    public function show($id): array {
        $edge = Edge::with([
            'startVertex',
            'endVertex',
            'edgeConfigurations',
            'regionMgmt',
            'doorMgmt.doorStatus'
        ])->findOrFail($id);

        return [
            'status' => 0,
            'data'   => [
                'edge' => $edge
            ]
        ];
    }

    public function store(Request $request): array {
        $name = $request->get('name');
        if($this->checkEdgeNameDuplicate($request, null, $name)) {
            return [
                'status' => config('errors.data_repeat')
            ];
        }

        return [
            'status' => 0
        ];
    }

    public function update(Request $request, $id): Application|ResponseFactory|Response|array {
        $checkStatus = $request->input('check_status', -7);
        $edge = Edge::findOrFail($id);
        if($checkStatus == config('errors.is_deploy')) {
            if($edge->is_deploy == 0) {
                return [
                    'status' => config('errors.is_deploy')
                ];
            }
        } else {
            $name = $request->get('name');
            if($this->checkEdgeNameDuplicate($request, $edge->id, $name)) {
                return [
                    'status' => config('errors.data_repeat')
                ];
            }
        }

        if($request->has('enable')) {
            $enable = $request->input('enable');
            $oldEnable = $edge->enable;
            $edge->enable = $enable;
            $edge->save();
            $edgeNames = Edge::whereRegionMgmtId($edge->region_mgmt_id)->orderBy('id')->where('enable', 0)->get()->pluck('name')->toArray();
            if($enable != $oldEnable) {
                $mqttCommand = new MqttCommand();
                $mqttCommand->mqtt_command_type_id = 12;
                $mqttCommand->region_mgmt_id = $edge->region_mgmt_id;
                $mqttCommand->user_id = Auth::id();
                $mqttCommand->data = $edgeNames;
                $mqttCommand->send_command = json_encode($mqttCommand->preview_send_command);
                Queue::connection('rabbitmq')->pushRaw($mqttCommand->send_command);
                $mqttCommand->save();
            }
        }

        return [
            'status' => 0
        ];
    }

    private function checkEdgeNameDuplicate(Request $request, $id, $edgeName): bool {
        $regionMgmtId = $request->input('region_mgmt.id');
        $edges = Edge::where('is_deploy', 0)->where('region_mgmt_id', $regionMgmtId);
        if($id) {
            $edges = $edges->where('id', '<>', $id);
        }
        $existEdge = $edges->whereName($edgeName)->first();
        if($existEdge) {
            return true;
        } else {
            return false;
        }
    }
}
