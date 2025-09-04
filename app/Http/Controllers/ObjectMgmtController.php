<?php

namespace App\Http\Controllers;

use App\Events\DoorMgmtCreated;
use App\Events\DoorMgmtDeleted;
use App\Events\DoorMgmtUpdated;
use App\Events\ElevatorMgmtCreated;
use App\Events\ElevatorMgmtDeleted;
use App\Events\ElevatorMgmtUpdated;
use App\Events\ElevatorMgmtVertexCreated;
use App\Events\ElevatorMgmtVertexDeleted;
use App\Events\StationMgmtCreated;
use App\Events\StationMgmtDeleted;
use App\Events\StationMgmtUpdated;
use App\Events\VehicleMgmtCreated;
use App\Events\VehicleMgmtDeleted;
use App\Events\VehicleMgmtUpdated;
use App\Models\DoorMgmt;
use App\Models\DoorStatus;
use App\Models\Edge;
use App\Models\EdgeConfiguration;
use App\Models\ElevatorMgmt;
use App\Models\ElevatorStatus;
use App\Models\EquipmentMgmt;
use App\Models\MqttCommand;
use App\Models\ObjectMgmt;
use App\Models\Project;
use App\Models\StationMgmt;
use App\Models\StationStatus;
use App\Models\VehicleMgmt;
use App\Models\VehicleStatus;
use App\Models\Vertex;
use App\Models\VertexConfiguration;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Queue;
use Validator;
use function app\randomColor;

class ObjectMgmtController extends Controller {
    /**
     * @api              {get} /api/objectMgmts 索取設備列表
     * @apiGroup         ObjectMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} [obj_id] 搜尋編號
     * @apiParam {String} [object_class] 搜尋設備類別
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.objectMgmts 設備列表
     * @apiSuccess {String} data.objectMgmts.obj_uid 物件編號
     * @apiSuccess {String} data.objectMgmts.obj_id 裝置編號
     * @apiSuccess {String} data.objectMgmts.create_ts 建立時間
     * @apiSuccess {String} data.objectMgmts.update_ts 更新時間
     * @apiSuccess {String} data.objectMgmts.region 區域
     * @apiSuccess {Object} data.objectMgmts.region_mgmt 區域資訊
     * @apiSuccess {Object} data.objectMgmts.region_mgmt.name 區域名稱
     * @apiSuccess {String} data.objectMgmts.object_class 設備類別
     * @apiSuccess {String} data.objectMgmts.vendor 供應商
     * @apiSuccess {Object} data.pagination 分頁資訊
     * @apiSuccess {Number} data.pagination.last_page 總共頁數
     * @apiSuccess {Number} data.pagination.current_page 目前頁數
     *
     * @apiSampleRequest off
     */
    public function index(Request $request) {
        $objectMgmts = new ObjectMgmt();
        $objId = $request->input('obj_id');
        if($objId) {
            $objectMgmts = $objectMgmts->where('obj_id', $objId);
        }

        $objectClass = $request->input('object_class');
        if($objectClass) {
            if(!is_array($objectClass)) {
                $objectMgmts = $objectMgmts->where('object_class', $objectClass);
            } else {
                $objectMgmts = $objectMgmts->whereIn('object_class', $objectClass);
            }
        }

        $user = Auth::user();
        $objectMgmts = $objectMgmts->with([
            'vehicleMgmt'
        ])->orderBy('obj_id')->with('vehicleMgmt.vehicleStatus')->selectRaw("*,
        (
             (
                 select count(*)
                 from vehicle_mgmt vm
                 join user_vehicle_mgmt uvm on uvm.vehicle_id = vm.vehicle_id
                 join users u on u.id = uvm.user_id
                 where vm.vehicle_id = object_mgmt.obj_id and u.id = $user->id
             ) > 0
        ) as has_permission");

        $project = Project::whereIsDeploy(1)->first();
        $objectMgmtsPaginate = $objectMgmts->with([
            'doorMgmt.edge'                  => function(BelongsTo $query) {
                $query->where('is_deploy', 1);
            },
            'doorMgmt.edge.regionMgmt'       => function(BelongsTo $query) use ($project) {
                $query->where('project_id', $project?->id);
            },
            'stationMgmt.vertex'             => function(BelongsTo $query) {
                $query->where('is_deploy', 1);
            },
            'stationMgmt.vertex.regionMgmt'  => function(BelongsTo $query) use ($project) {
                $query->where('project_id', $project?->id);
            },
            'elevatorMgmt.vertices'          => function(BelongsToMany $query) {
                $query->where('is_deploy', 1);
            },
            'elevatorMgmt.vertex.regionMgmt' => function(BelongsTo $query) use ($project) {
                $query->where('project_id', $project?->id);
            }
        ])->paginate(50);

        return [
            'status' => 0,
            'data'   => [
                'objectMgmts' => $objectMgmtsPaginate->items(),
                'pagination'  => [
                    'last_page'    => $objectMgmtsPaginate->lastPage(),
                    'current_page' => $objectMgmtsPaginate->currentPage()
                ],
                'project'     => $project
            ]
        ];
    }

    /**
     * @api              {get} /api/objectMgmt/{objUid} 索取單筆設備
     * @apiGroup         ObjectMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.objectMgmt 設備資料
     * @apiSuccess {String} data.objectMgmt.obj_uid 物件編號
     * @apiSuccess {String} data.objectMgmt.obj_id 裝置編號
     * @apiSuccess {String} data.objectMgmt.create_ts 建立時間
     * @apiSuccess {String} data.objectMgmt.update_ts 更新時間
     * @apiSuccess {String} data.objectMgmt.region 區域
     * @apiSuccess {String} data.objectMgmt.object_class 設備類別
     * @apiSuccess {String} data.objectMgmt.vendor 供應商
     *
     * @apiSampleRequest off
     */
    public function show($objUid): array {
        $objectMgmt = ObjectMgmt::findOrFail($objUid)->load([
            'vehicleMgmt.vehicleStatus',
            'doorMgmt.edge.regionMgmt',
            'stationMgmt.vertex.regionMgmt',
            'elevatorMgmt.vertices',
            'equipmentMgmt'
        ]);

        return [
            'status' => 0,
            'data'   => [
                'objectMgmt' => $objectMgmt
            ]
        ];
    }

    /**
     * @api              {post} /api/objectMgmts 新增單筆載具資料
     * @apiGroup         ObjectMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} obj_id 裝置編號
     * @apiParam {String} region 區域
     * @apiParam {String} object_class 設備類別
     * @apiParam {String} vendor 供應商
     * @apiParam {Object} [vehicle_mgmt] 車輛資訊
     * @apiParam {String} [vehicle_mgmt.carrier_class] 載貨物種類
     * @apiParam {Number} [vehicle_mgmt.slot_num] 槽位數量
     * @apiParam {Number} [vehicle_mgmt.battery_threshold_full] 電量飽
     * @apiParam {Number} [vehicle_mgmt.battery_threshold_high] 高
     * @apiParam {Number} [vehicle_mgmt.battery_threshold_low] 低
     * @apiParam {String} [vehicle_mgmt.macaddr] 車輛MAC
     * @apiParam {String} [vehicle_mgmt.ipaddr] 車輛IP
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功<br>-7：該裝置編號已存在
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.objectMgmt 載具資訊
     * @apiSuccess {String} data.objectMgmt.obj_uid 載具編號
     * @apiSuccess {String} data.objectMgmt.obj_id 裝置編號
     * @apiSuccess {String} data.objectMgmt.region 區域
     * @apiSuccess {Object} data.objectMgmt.region_mgmt 區域資訊
     * @apiSuccess {Object} data.objectMgmt.region_mgmt.name 區域名稱
     * @apiSuccess {String} data.objectMgmt.object_class 設備類別
     * @apiSuccess {String} data.objectMgmt.vendor 供應商
     * @apiSuccess {Object} data.objectMgmt.vehicle_mgmt 車輛資訊
     * @apiSuccess {String} data.objectMgmt.vehicle_mgmt.carrier_class 載貨物種類
     * @apiSuccess {Number} data.objectMgmt.vehicle_mgmt.slot_num 槽位數量
     * @apiSuccess {Number} data.objectMgmt.vehicle_mgmt.battery_threshold_full 電量飽
     * @apiSuccess {Number} data.objectMgmt.vehicle_mgmt.battery_threshold_high 高
     * @apiSuccess {Number} data.objectMgmt.vehicle_mgmt.battery_threshold_low 低
     * @apiSuccess {String} data.objectMgmt.vehicle_mgmt.macaddr 車輛MAC
     * @apiSuccess {String} data.objectMgmt.vehicle_mgmt.ipaddr 車輛IP
     *
     * @apiSampleRequest off
     */
    public function store(Request $request): Response|array|Application|ResponseFactory {
        $validator = $this->validateRule($request);
        if($validator->fails())
            return response(null, 422);

        $objId = $request->input('obj_id');
        $objectMgmt = ObjectMgmt::whereObjId($objId)->first();
        if($objectMgmt) {
            return [
                'status' => config('errors.data_repeat'),
                'data'   => [
                    'column' => 'obj_id'
                ]
            ];
        }

        $checkVehicleValidator = $this->checkVehicleMgmtDuplicate($request);
        if($checkVehicleValidator) {
            return $checkVehicleValidator;
        }

        $objectMgmt = new ObjectMgmt();
        $objectMgmt->obj_uid = Str::uuid()->toString();
        $objectMgmt = $this->saveModel($request, $objectMgmt);

        $objectMgmt = $objectMgmt->load([
            'vehicleMgmt',
            'doorMgmt',
            'equipmentMgmt',
            'stationMgmt.vertex.regionMgmt',
            'elevatorMgmt.vertex.regionMgmt'
        ]);

        return [
            'status' => 0,
            'data'   => [
                'objectMgmt' => $objectMgmt
            ]
        ];
    }

    /**
     * @api              {patch} /api/objectMgmts/{objUid} 更新單筆載具資料
     * @apiGroup         ObjectMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} obj_uid 物件編號
     * @apiParam {String} obj_id 裝置編號
     * @apiParam {String} region 區域
     * @apiParam {String} object_class 設備類別
     * @apiParam {String} vendor 供應商
     * @apiParam {Object} [vehicle_mgmt] 車輛資訊
     * @apiParam {String} [vehicle_mgmt.carrier_class] 載貨物種類
     * @apiParam {Number} [vehicle_mgmt.battery_threshold_full] 電量飽
     * @apiParam {Number} [vehicle_mgmt.battery_threshold_high] 高
     * @apiParam {Number} [vehicle_mgmt.battery_threshold_low] 低
     * @apiParam {String} [vehicle_mgmt.macaddr] 車輛MAC
     * @apiParam {String} [vehicle_mgmt.ipaddr] 車輛IP
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功<br>-7：該裝置編號已存在
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.objectMgmt 載具資訊
     * @apiSuccess {String} data.objectMgmt.obj_uid 載具編號
     * @apiSuccess {String} data.objectMgmt.obj_id 裝置編號
     * @apiSuccess {String} data.objectMgmt.region 區域編號
     * @apiSuccess {Object} data.objectMgmt.region_mgmt 區域資訊
     * @apiSuccess {Object} data.objectMgmt.region_mgmt.name 區域名稱
     * @apiSuccess {String} data.objectMgmt.object_class 設備類別
     * @apiSuccess {String} data.objectMgmt.vendor 供應商
     * @apiSuccess {Object} data.objectMgmt.vehicle_mgmt 車輛資訊
     * @apiSuccess {String} data.objectMgmt.vehicle_mgmt.carrier_class 載貨物種類
     * @apiSuccess {Number} data.objectMgmt.vehicle_mgmt.slot_num 槽位數量
     * @apiSuccess {Number} data.objectMgmt.vehicle_mgmt.battery_threshold_full 電量飽
     * @apiSuccess {Number} data.objectMgmt.vehicle_mgmt.battery_threshold_high 高
     * @apiSuccess {Number} data.objectMgmt.vehicle_mgmt.battery_threshold_low 低
     * @apiSuccess {String} data.objectMgmt.vehicle_mgmt.macaddr 車輛MAC
     * @apiSuccess {String} data.objectMgmt.vehicle_mgmt.ipaddr 車輛IP
     *
     * @apiSampleRequest off
     */
    public function update(Request $request, $objUid): Response|array|Application|ResponseFactory {
        $validator = $this->validateRule($request);
        if($validator->fails()) {
            return response(null, 422);
        }

        $objectMgmt = ObjectMgmt::findOrFail($objUid);
        $objId = $request->input('obj_id');
        if($objectMgmt->obj_id != $objId) {
            $repeatObjectMgmt = ObjectMgmt::where('obj_uid', '<>', $objUid)->whereObjId($objId)->first();
            if($repeatObjectMgmt) {
                return [
                    'status' => config('errors.data_repeat'),
                    'data'   => [
                        'column' => 'obj_id'
                    ]
                ];
            }
        }

        $checkVehicleValidator = $this->checkVehicleMgmtDuplicate($request);
        if($checkVehicleValidator) {
            return $checkVehicleValidator;
        }
        $objectMgmt = $this->saveModel($request, $objectMgmt);
        $objectMgmt = $objectMgmt->load([
            'vehicleMgmt',
            'doorMgmt',
            'equipmentMgmt',
            'stationMgmt.vertex.regionMgmt',
            'elevatorMgmt.vertices'
        ]);

        return [
            'status' => 0,
            'data'   => [
                'objectMgmt' => $objectMgmt
            ]
        ];
    }

    private function checkVehicleMgmtDuplicate(Request $request): ?array {
        $objectClass = $request->input('object_class');
        if($objectClass != 'AMDR') {
            return null;
        }

        $vehicleMgmts = new VehicleMgmt();
        $vehicleId = $request->input('vehicle_mgmt.vehicle_id');
        if($vehicleId) {
            $vehicleMgmts = $vehicleMgmts->where('vehicle_id', '<>', $vehicleId);
        }
        $ipaddr = $request->input('vehicle_mgmt.ipaddr');
        if($ipaddr) {
            $vehicleMgmt = $vehicleMgmts->clone()->where('ipaddr', $ipaddr)->first();
            if($vehicleMgmt) {
                return [
                    'status' => config('errors.data_repeat'),
                    'data'   => [
                        'column'     => 'ipaddr',
                        'vehicle_id' => $vehicleMgmt->vehicle_id
                    ]
                ];
            }
        }

        $macaddr = $request->input('vehicle_mgmt.macaddr');
        if($macaddr) {
            $vehicleMgmt = $vehicleMgmts->clone()->where('macaddr', $macaddr)->first();
            if($vehicleMgmt) {
                return [
                    'status' => config('errors.data_repeat'),
                    'data'   => [
                        'column'     => 'macaddr',
                        'vehicle_id' => $vehicleMgmt->vehicle_id
                    ]
                ];
            }
        }

        return null;
    }

    /**
     * @api              {delete} /api/objectMgmts/{objUid} 刪除單筆載具資料
     * @apiGroup         User
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam  {String} objUid 要刪除的載具編號
     *
     * @apiSampleRequest off
     */
    public function destroy($objUid): array {
        $objectMgmt = ObjectMgmt::findOrFail($objUid);
        if($objectMgmt->object_class == 'AMDR') {
            $vehicleMgmt = VehicleMgmt::whereVehicleId($objectMgmt->obj_id)->first();
            $vehicleMgmt?->delete();
            $vehicleStatus = VehicleStatus::whereVehicleId($objectMgmt->obj_id)->first();
            $vehicleStatus?->delete();
            if($vehicleMgmt) {
                event(new VehicleMgmtDeleted($vehicleMgmt));
            }
        } else if($objectMgmt->object_class == 'DOOR') {
            $doorMgmt = DoorMgmt::whereDoorId($objectMgmt->obj_id)->first();
            $doorMgmt?->delete();
            $doorStatus = DoorStatus::whereDoorId($objectMgmt->obj_id)->first();
            $doorStatus?->delete();
            if($doorMgmt) {
                event(new DoorMgmtDeleted($doorMgmt));
            }
        } else if($objectMgmt->object_class == 'STATION') {
            $stationMgmt = StationMgmt::whereStationId($objectMgmt->obj_id)->first();
            $stationMgmt?->delete();
            $stationStatus = StationStatus::whereStationId($objectMgmt->obj_id)->first();
            $stationStatus?->delete();
            if($stationMgmt) {
                event(new StationMgmtDeleted($stationMgmt));
            }
        } else if($objectMgmt->object_class == 'ELEVATOR') {
            $elevatorMgmt = ElevatorMgmt::whereElevatorId($objectMgmt->obj_id)->first();
            $elevatorMgmt?->delete();
            $elevatorStatus = ElevatorStatus::whereElevatorId($objectMgmt->obj_id)->first();
            $elevatorStatus?->delete();
            if($elevatorMgmt) {
                event(new ElevatorMgmtDeleted($elevatorMgmt));
            }
        }
        $objectMgmt->delete();

        return [
            'status' => 0
        ];
    }

    private function validateRule(Request $request): \Illuminate\Validation\Validator {
        if(!$request->has('object_port_mgmts')) {
            return Validator::make($request->all(), [
                'obj_id'       => 'required|max:256',
                'object_class' => 'required|string|exists:object_class,object_class',
                'vendor'       => 'required|string|exists:vendor_mgmt,vendor'
            ]);
        } else {
            return Validator::make($request->all(), [
                'obj_id'            => 'required|max:256',
                'object_port_mgmts' => 'nullable|array',
            ]);
        }
    }

    private function saveModel(Request $request, ObjectMgmt $objectMgmt): ObjectMgmt {
        $objectClass = $request->input('object_class', $objectMgmt->object_class);
        $objId = $request->input('obj_id', $objectMgmt->obj_id);

        $objectMgmt->obj_id = $objId;
        $objectMgmt->object_class = $objectClass;
        $objectMgmt->vendor = $request->input('vendor', $objectMgmt->vendor);
        $objectMgmt->save();

        $insertMode = false;
        $project = Project::whereIsDeploy(1)->first();
        if($objectClass == 'AMDR') {
            $userId = Auth::id();
            $vehicleMgmt = VehicleMgmt::find($objectMgmt->obj_id);
            if(!$vehicleMgmt) {
                $insertMode = true;
                $vehicleMgmt = new VehicleMgmt();
                $vehicleMgmt->vehicle_id = $objId;
                $vehicleMgmt->color = '#' . randomColor();
            }
            $vehicleMgmt->carrier_class = $request->input('vehicle_mgmt.carrier_class', $vehicleMgmt->carrier_class);
            $vehicleMgmt->slot_num = $request->input('vehicle_mgmt.slot_num', $vehicleMgmt->slot_num);
            $vehicleMgmt->macaddr = $request->input('vehicle_mgmt.macaddr', $vehicleMgmt->macaddr);
            $vehicleMgmt->ipaddr = $request->input('vehicle_mgmt.ipaddr', $vehicleMgmt->ipaddr);
            $vehicleMgmt->chargeable = $request->input('vehicle_mgmt.chargeable', $vehicleMgmt->chargeable);
            $vehicleMgmt->vehicle_type = $request->input('vehicle_mgmt.vehicle_type', $vehicleMgmt->vehicle_type);
            $vehicleMgmt->assign_switch = $request->input('vehicle_mgmt.assign_switch', $vehicleMgmt->assign_switch);
            $vehicleMgmt->group = null;
            $vehicleMgmt->low_speed = $request->input('vehicle_mgmt.low_speed', $vehicleMgmt->low_speed);
            $vehicleMgmt->normal_speed = $request->input('vehicle_mgmt.normal_speed', $vehicleMgmt->normal_speed);
            $vehicleMgmt->high_speed = $request->input('vehicle_mgmt.high_speed', $vehicleMgmt->high_speed);
            if(!$insertMode) {
                $speedTypes = ['low_speed', 'normal_speed', 'high_speed'];
                foreach($speedTypes as $speedType) {
                    if(!$vehicleMgmt->isDirty($speedType)) {
                        continue;
                    }
                    $mqttCommand = new MqttCommand();
                    $mqttCommand->mqtt_command_type_id = 23;
                    $mqttCommand->vehicle_id = $vehicleMgmt->vehicle_id;
                    $mqttCommand->user_id = $userId;
                    $mqttCommand->data = [
                        'speed' => [
                            'type'  => str_replace('_speed', '', $speedType),
                            'value' => $vehicleMgmt->{$speedType},
                        ]
                    ];
                    $mqttCommand->send_command = json_encode($mqttCommand->preview_send_command);
                    Queue::connection('rabbitmq')->pushRaw($mqttCommand->send_command);
                    $mqttCommand->save();
                }
            }
            $vehicleMgmt->stoppable_second = $request->input('vehicle_mgmt.stoppable_second', $vehicleMgmt->stoppable_second);
            $vehicleMgmt->save();

            $vehicleStatus = VehicleStatus::whereVehicleId($vehicleMgmt->vehicle_id)->first();
            if(!$vehicleStatus) {
                $vehicleStatus = new VehicleStatus();
                $vehicleStatus->vehicle_id = $vehicleMgmt->vehicle_id;
                $vehicleStatus->save();
            }

            $vehicleMgmt = $vehicleMgmt->load([
                'vehicleStatus.regionMgmt',
                'cleanArea.turningPoints'
            ]);
            if($insertMode) {
                event(new VehicleMgmtCreated($vehicleMgmt));
            } else {
                event(new VehicleMgmtUpdated($vehicleMgmt));
            }
        } else if($objectClass == 'DOOR') {
            $doorMgmt = DoorMgmt::find($objectMgmt->obj_id);
            if(!$doorMgmt) {
                $insertMode = true;
                $doorMgmt = new DoorMgmt();
                $doorMgmt->door_id = $objId;
            }

            $doorMgmt->edge_id = $request->input('door_mgmt.edge_id');
            $edge = Edge::find($doorMgmt->edge_id);
            $doorMgmt->edge_name = $edge->name;
            $doorMgmt->enable = $request->input('door_mgmt.enable', $doorMgmt->enable);
            $doorMgmt->prefer_vehicle = $request->input('door_mgmt.prefer_vehicle', $doorMgmt->prefer_vehicle);
            $doorMgmt->macaddr = $request->input('door_mgmt.macaddr', $doorMgmt->macaddr);
            $doorMgmt->ipaddr = $request->input('door_mgmt.ipaddr', $doorMgmt->ipaddr);
            if($insertMode) {
                event(new DoorMgmtCreated($doorMgmt));
            } else {
                event(new DoorMgmtUpdated($doorMgmt));
            }
            $doorMgmt->save();

            if($project) {
                $edgeConfiguration = EdgeConfiguration::where('type', 'auto_door')->where('data', $doorMgmt->door_id)->first();
                if(!$edgeConfiguration) {
                    $edgeConfiguration = new EdgeConfiguration();
                    $edgeConfiguration->type = 'auto_door';
                }
                $edgeConfiguration->edge_id = $doorMgmt->edge_id;
                $edgeConfiguration->data = $doorMgmt->door_id;
                $edgeConfiguration->save();
            }

            $doorStatus = DoorStatus::whereDoorId($doorMgmt->door_id)->first();
            if(!$doorStatus) {
                $doorStatus = new DoorStatus();
                $doorStatus->door_id = $doorMgmt->door_id;
                $doorStatus->save();
            }
        } else if($objectClass == 'STATION') {
            $stationMgmt = StationMgmt::find($objectMgmt->obj_id);
            if(!$stationMgmt) {
                $insertMode = true;
                $stationMgmt = new StationMgmt();
                $stationMgmt->station_id = $objId;
            }

            $stationMgmt->vertex_id = $request->input('station_mgmt.vertex_id', $stationMgmt->vertex_id);
            $vertex = Vertex::find($stationMgmt->vertex_id);
            $stationMgmt->vertex_name = $vertex?->name;
            $stationMgmt->device_name = $request->input('station_mgmt.device_name', $stationMgmt->device_name);
            $stationMgmt->station_group = $request->input('station_mgmt.station_group', $stationMgmt->station_group);
            $stationMgmt->enable = $request->input('station_mgmt.enable', $stationMgmt->enable);
            $stationMgmt->bypass = $request->input('station_mgmt.bypass', $stationMgmt->bypass);
            if($insertMode) {
                event(new StationMgmtCreated($stationMgmt));
            } else {
                event(new StationMgmtUpdated($stationMgmt));
            }
            $stationMgmt->save();

            $project = Project::whereIsDeploy(1)->first();
            $vertex = null;
            if($project) {
                $vertex = Vertex::whereRelation('regionMgmt.project', 'name', $project->name)->where('is_deploy', 1)->where('name', $stationMgmt->vertex_name)->with('vertexConfigurations')->first();
                if($vertex) {
                    $stationMgmt = $stationMgmt->load('vertex');
                    if($stationMgmt->vertex) {
                        event(new StationMgmtUpdated($stationMgmt));
                    }
                    /** @var VertexConfiguration $vertexConfiguration */
                    $vertexConfiguration = $vertex->vertexConfigurations->where('type', 'device_name')->first();
                    if(!$vertexConfiguration) {
                        $vertexConfiguration = new VertexConfiguration();
                        $vertexConfiguration->vertex_id = $vertex->id;
                        $vertexConfiguration->type = 'device_name';
                    }
                    if($vertexConfiguration->data != $stationMgmt->station_id) {
                        $vertexConfiguration->data = $stationMgmt->station_id;
                        $vertexConfiguration->save();
                    }
                }
            }

            $stationStatus = StationStatus::whereStationId($stationMgmt->station_id)->first();
            if(!$stationStatus) {
                $stationStatus = new StationStatus();
                $stationStatus->station_id = $stationMgmt->station_id;
                $stationStatus->save();
            }
        } else if($objectClass == 'ELEVATOR') {
            $elevatorMgmt = ElevatorMgmt::find($objectMgmt->obj_id);
            if(!$elevatorMgmt) {
                $insertMode = true;
                $elevatorMgmt = new ElevatorMgmt();
                $elevatorMgmt->elevator_id = $objId;
            }

            $elevatorMgmt->prefer_vehicle = $request->input('elevator_mgmt.prefer_vehicle');
            $elevatorMgmt->ipaddr = $request->input('elevator_mgmt.ipaddr', $elevatorMgmt->ipaddr);
            $elevatorMgmt->macaddr = $request->input('elevator_mgmt.macaddr', $elevatorMgmt->macaddr);
            $elevatorMgmt->enable = $request->input('elevator_mgmt.enable', $elevatorMgmt->enable);
            if($insertMode) {
                event(new ElevatorMgmtCreated($elevatorMgmt));
            } else {
                event(new ElevatorMgmtUpdated($elevatorMgmt));
            }
            $elevatorMgmt->save();

            $elevatorStatus = ElevatorStatus::whereElevatorId($elevatorMgmt->elevator_id)->first();
            if(!$elevatorStatus) {
                $elevatorStatus = new ElevatorStatus();
                $elevatorStatus->elevator_id = $elevatorMgmt->elevator_id;
                $elevatorStatus->save();
            }

            $vertices = collect($request->input('elevator_mgmt.vertices'));
            $vertexIds = $vertices->map(function($vertex) {
                return $vertex['id'];
            })->all();
            $elevatorMgmt = $elevatorMgmt->load('vertices');
            $oldVertexIds = $elevatorMgmt->vertices->pluck('id')->all();
            $deleteVertexIds = array_diff($oldVertexIds, $vertexIds);
            foreach($deleteVertexIds as $deleteVertexId) {
                event(new ElevatorMgmtVertexDeleted([
                    'vertex_id'                 => $deleteVertexId,
                    'elevator_mgmt_elevator_id' => $elevatorMgmt->elevator_id
                ]));
            }
            $elevatorMgmt->vertices()->sync($vertexIds);
            $vertices = Vertex::whereIn('id', $vertexIds)->get();
            $elevatorMgmt->vertex_name = $vertices->pluck('name')->implode(',');
            $elevatorMgmt->save();
            $insertVertexIds = array_diff($vertexIds, $oldVertexIds);
            foreach($insertVertexIds as $insertVertexId) {
                event(new ElevatorMgmtVertexCreated([
                    'vertex_id'                 => $insertVertexId,
                    'elevator_mgmt_elevator_id' => $elevatorMgmt->elevator_id
                ]));
            }
        } else {
            $equipmentMgmt = EquipmentMgmt::find($objId);
            if(!$equipmentMgmt) {
                $equipmentMgmt = new EquipmentMgmt();
                $equipmentMgmt->equipment_id = $objId;
            }
            $equipmentMgmt->enable = $request->input('equipment_mgmt.enable', $equipmentMgmt->enable);
            $equipmentMgmt->macaddr = $request->input('equipment_mgmt.macaddr', $equipmentMgmt->macaddr);
            $equipmentMgmt->ipaddr = $request->input('equipment_mgmt.ipaddr', $equipmentMgmt->ipaddr);
            $equipmentMgmt->save();
        }

        return $objectMgmt->load('vehicleMgmt');
    }
}
