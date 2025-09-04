<?php

namespace App\Http\Controllers;

use App\Models\MqttCommand;
use App\Models\VehicleMgmt;
use App\Models\Vertex;
use App\Repositories\VertexRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VertexController extends Controller {
    private VertexRepository $vertices;

    public function __construct(VertexRepository $vertices) {
        $this->vertices = $vertices;
    }

    /**
     * @api              {get} /api/vertices 索取站點列表
     * @apiGroup         Vertex
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} region 區域，必填
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.vertices 站點資料列表
     * @apiSuccess {Number} data.vertices.x x軸
     * @apiSuccess {Number} data.vertices.y y軸
     * @apiSuccess {Number} data.vertices.z z軸
     * @apiSuccess {Number} data.vertices.vertex_type_id 站點類型編號
     * @apiSuccess {Object[]} data.vertices.vertex_configurations 站點自定義屬性，雖是陣列，但只會有一筆，type為vertex_name
     * @apiSuccess {String} data.vertices.vertex_configurations.type vertex_name
     * @apiSuccess {String} data.vertices.vertex_configurations.data vertex_name之內容
     * @apiSuccess {Object} data.vertices.vehicle_status 該站點車輛即時狀態資訊
     * @apiSuccess {Object} data.vertices.vehicle_status.vehicle_mgmt 車輛資訊
     * @apiSuccess {Object} data.vertices.vehicle_status.vehicle_mgmt.vehicle_color 車輛顏色資訊
     * @apiSuccess {String} data.vertices.vehicle_status.vehicle_mgmt.vehicle_color.color 車輛顏色
     *
     * @apiSampleRequest off
     */
    public function index(Request $request): Response|array|Application|ResponseFactory {
        $vertices = new Vertex();

        $regionMgmtId = $request->input('region_mgmt_id');
        if($regionMgmtId) {
            $vertices = $vertices->where('region_mgmt_id', $regionMgmtId);
        }

        $isDeploy = $request->input('is_deploy', 0);
        $vertices = $vertices->where('is_deploy', $isDeploy);

        $vertexTypeId = $request->input('vertex_type_id');
        if($vertexTypeId) {
            $vertices = $vertices->where('vertex_type_id', $vertexTypeId);
        }

        $mapType = $request->input('map_type');
        $relations = [
            'regionMgmt',
            'stationMgmt.stationStatus',
            'vertexConfigurations',
            'edges',
            'edges.startVertex',
            'edges.endVertex',
            'edges.edgeConfigurations',
            'edges.regionMgmt',
            'vehicleStatus.vehicleMgmt',
            'undeployLocation'
        ];
        if($mapType == 'cad') {
            $relations[] = 'location.microOrganism';
            $vertices = $vertices->where('vertex_type_id', 4);
        }
        $vertices = $vertices->orderBy('id')->with($relations)->whereNotNull('name')->whereNull('attach_vertex_id')->get();

        return [
            'status' => 0,
            'data'   => [
                'vertices' => $vertices
            ]
        ];
    }

    /**
     * @api              {get} /api/vertices/{id} 顯示單筆站點資料
     * @apiGroup         Vertex
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {Number} id 站點編號
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功<br>-7：該地圖已存在
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Number} data.vertex 站點資料
     * @apiSuccess {Number} data.vertex.region 區域編號
     * @apiSuccess {Number} data.vertex.x x軸
     * @apiSuccess {Number} data.vertex.y y軸
     * @apiSuccess {Number} data.vertex.z z軸
     * @apiSuccess {String} data.vertex.tag tag
     * @apiSuccess {Number} data.vertex.vertex_type_id 站點類型編號
     * @apiSuccess {Object[]} data.vertex.vertex_configurations 自訂屬性
     * @apiSuccess {Number} data.vertex.vertex_configurations.id 自訂屬性欄位編號
     * @apiSuccess {String} data.vertex.vertex_configurations.type 自訂屬性欄位名稱
     * @apiSuccess {String} data.vertex.vertex_configurations.data 自訂屬性欄位內容
     * @apiSuccess {Object} data.vertex.parking_lot_mgmt 停車場資訊
     * @apiSuccess {String} data.vertex.parking_lot_mgmt.parking_lot_id 停車場編號
     * @apiSuccess {Object} data.vertex.parking_lot_mgmt.parking_status 停車場即時狀態
     * @apiSuccess {String} data.vertex.parking_lot_mgmt.parking_status.booking 預定狀態
     * @apiSuccess {String} data.vertex.parking_lot_mgmt.parking_status.booking_owner 預定者
     * @apiSuccess {String} data.vertex.parking_lot_mgmt.parking_status.parking_vehicle_id 目前停放
     * @apiSuccess {String} data.vertex.parking_lot_mgmt.parking_status.update_ts 更新時間
     * @apiSuccess {Object} data.vertex.charger_mgmt 充電站資訊
     * @apiSuccess {String} data.vertex.charger_mgmt.charging_station_id 充電站編號
     * @apiSuccess {String} data.vertex.parking_lot_mgmt.charger_status.booking 預定狀態
     * @apiSuccess {String} data.vertex.parking_lot_mgmt.charger_status.booking_owner 預定者
     * @apiSuccess {String} data.vertex.parking_lot_mgmt.charger_status.parking_vehicle_id 目前停放
     * @apiSuccess {String} data.vertex.parking_lot_mgmt.charger_status.update_ts 更新時間
     * @apiSuccess {Object} data.vertex.elevator_mgmt 電梯站資訊
     * @apiSuccess {String} data.vertex.elevator_mgmt.id 電梯站編號
     * @apiSuccess {Object} data.vertex.object_mgmt 載具資訊
     * @apiSuccess {String} data.vertex.object_mgmt.obj_uid 子 類別名稱
     * @apiSuccess {String} data.vertex.object_mgmt.obj_id
     *
     * @apiSampleRequest off
     */
    public function show($id): array {
        $vertex = Vertex::with([
            'regionMgmt',
            'vertexConfigurations',
            'edges',
            'edges.startVertex',
            'edges.endVertex',
            'edges.edgeConfigurations',
            'edges.regionMgmt',
            'parkingLotMgmt.parkingLotStatus',
            'elevatorMgmts.elevatorStatus',
            'objectMgmt',
            'stationMgmt.stationStatus',
            'undeployLocation'
        ])->findOrFail($id);

        /** @var Vertex $vertex */
        $vertex = $vertex->load([
            'edges' => function(HasMany $query) use ($vertex) {
                $query->whereRelation('endVertex', 'region_mgmt_id', '<>', $vertex->region_mgmt_id);
            },
            'edges.endVertex'
        ]);

        $elevatorControlAuthorization = MqttCommand::whereIn('mqtt_command_type_id', [
            31,
            32
        ])->where('is_completed', 1)->orderByDesc('created_at')->first();

        $vehicleMgmts = VehicleMgmt::with('vehicleStatus')->get();

        return [
            'status' => 0,
            'data'   => [
                'vertex'                       => $vertex,
                'elevatorControlAuthorization' => $elevatorControlAuthorization,
                'vehicleMgmts'                 => $vehicleMgmts
            ]
        ];
    }

    /**
     * @api              {post} /api/vertices 新增單筆站點資料
     * @apiGroup         Vertex
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {Number} region 區域編號
     * @apiParam {Number} x x軸，無論是否為複製模式都需要填
     * @apiParam {Number} y y軸，無論是否為複製模式都需要填
     * @apiParam {Number} z z軸
     * @apiParam {String} [tag] tag
     * @apiParam {Number} vertex_type_id 站點類型編號
     * @apiParam {Object[]} vertex_configurations 自訂屬性
     * @apiParam {String} vertex_configurations.type 自訂屬性欄位名稱
     * @apiParam {String} vertex_configurations.data 自訂屬性欄位內容
     * @apiParam {Object} [parking_lot_mgmt] 停車場資訊
     * @apiParam {String} [parking_lot_mgmt.parking_lot_id] 停車場編號
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功<br>-9：停車格編號已被其他停車格使用<br>-10：自定義屬性的vertex_name已存在於別的站點中
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Number} data.vertex 站點資料
     * @apiSuccess {Number} data.vertex.region 區域編號
     * @apiSuccess {Number} data.vertex.x x軸
     * @apiSuccess {Number} data.vertex.y y軸
     * @apiSuccess {Number} data.vertex.z z軸
     * @apiSuccess {String} data.vertex.tag tag
     * @apiSuccess {Number} data.vertex.vertex_type_id 站點類型編號
     * @apiSuccess {Object[]} data.vertex.vertex_configurations 自訂屬性
     * @apiSuccess {String} data.vertex.vertex_configurations.id 自訂屬性欄位編號
     * @apiSuccess {String} data.vertex.vertex_configurations.type 自訂屬性欄位名稱
     * @apiSuccess {String} data.vertex.vertex_configurations.data 自訂屬性欄位內容
     * @apiSuccess {Object} data.vertex.charger_mgmt 充電站資訊
     * @apiSuccess {String} data.vertex.charger_mgmt.charging_station_id 充電站編號
     * @apiSuccess {Object} data.vertex.elevator_mgmt 電梯站資訊
     * @apiSuccess {String} data.vertex.elevator_mgmt.charging_station_id 電梯站編號
     * @apiSuccess {Object} data.vertex.object_mgmt 載具資訊
     * @apiSuccess {String} data.vertex.object_mgmt.obj_uid 子 類別名稱
     * @apiSuccess {String} data.vertex.object_mgmt.obj_id
     *
     * @apiSampleRequest off
     */
    public function store(Request $request): array {
        $name = $request->input('name');
        if($this->checkVertexNameDuplicate($request, null, $name)) {
            return [
                'status' => config('errors.data_repeat_vertex_name')
            ];
        }

        return [
            'status' => 0
        ];
    }

    /**
     * @api              {patch} /api/vertices/{id} 更新單筆站點資料
     * @apiGroup         Vertex
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {Number} id 站點編號
     * @apiParam {Number} region 區域編號
     * @apiParam {Number} x x軸
     * @apiParam {Number} y y軸
     * @apiParam {Number} z z軸
     * @apiParam {String} [tag] tag
     * @apiParam {Number} vertex_type_id 站點類型編號
     * @apiParam {Object[]} vertex_configurations 自訂屬性
     * @apiParam {String} vertex_configurations.id 自訂屬性欄位編號
     * @apiParam {String} vertex_configurations.type 自訂屬性欄位名稱
     * @apiParam {String} vertex_configurations.data 自訂屬性欄位內容
     * @apiParam {Object} [parking_lot_mgmt] 停車場資訊
     * @apiParam {String} [parking_lot_mgmt.parking_lot_id] 停車場編號
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功<br>-9：停車格編號已被其他停車格使用<br>-10：自定義屬性的vertex_name已存在於別的站點中
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Number} data.vertex 站點資料
     * @apiSuccess {Number} data.vertex.region 區域編號
     * @apiSuccess {Number} data.vertex.x x軸
     * @apiSuccess {Number} data.vertex.y y軸
     * @apiSuccess {Number} data.vertex.z z軸
     * @apiSuccess {Object[]} data.vertex.vertex_configurations 自訂屬性
     * @apiSuccess {String} data.vertex.vertex_configurations.id 自訂屬性欄位編號
     * @apiSuccess {String} data.vertex.vertex_configurations.type 自訂屬性欄位名稱
     * @apiSuccess {String} data.vertex.vertex_configurations.data 自訂屬性欄位內容
     * @apiSuccess {Object} data.vertex.charger_mgmt 充電站資訊
     * @apiSuccess {String} data.vertex.charger_mgmt.charging_station_id 充電站編號
     * @apiSuccess {Object} data.vertex.elevator_mgmt 電梯站資訊
     * @apiSuccess {String} data.vertex.elevator_mgmt.charging_station_id 電梯站編號
     * @apiSuccess {Object} data.vertex.object_mgmt 載具資訊
     * @apiSuccess {String} data.vertex.object_mgmt.obj_uid 子 類別名稱
     * @apiSuccess {String} data.vertex.object_mgmt.obj_id
     *
     * @apiSampleRequest off
     */
    public function update(Request $request, $id): array|Response|Application|ResponseFactory {
        $checkStatus = $request->input('check_status');
        $vertex = Vertex::findOrFail($id);
        if($checkStatus == config('errors.is_deploy')) {
            if($vertex->is_deploy == 0) {
                return [
                    'status' => config('errors.is_deploy')
                ];
            }
        } else if($checkStatus == config('errors.data_repeat_vertex_name')) {
            $name = $request->input('name');
            if($this->checkVertexNameDuplicate($request, $vertex->id, $name)) {
                return [
                    'status' => config('errors.data_repeat_vertex_name')
                ];
            }
        }

        if($request->has('enable')) {
            $enable = $request->input('enable');
            $oldEnable = $vertex->enable;
            $vertex->enable = $enable;
            $vertex->save();
            $vertexNames = Vertex::whereRegionMgmtId($vertex->region_mgmt_id)->orderBy('id')->where('enable', 0)->get()->pluck('name')->toArray();
            if($enable != $oldEnable) {
                $this->vertices->disableVertex($vertexNames, $vertex);
            }
        }

        return [
            'status' => 0
        ];
    }

    private function checkVertexNameDuplicate(Request $request, $id, $vertexName): bool {
        $regionMgmtId = $request->input('region_mgmt.id');
        $vertices = Vertex::where('is_deploy', 0)->where('region_mgmt_id', $regionMgmtId);
        if($id) {
            $vertices = $vertices->where('id', '<>', $id);
        }
        $existVertex = $vertices->whereName($vertexName)->first();
        if($existVertex) {
            return true;
        } else {
            return false;
        }
    }
}
