<?php

namespace App\Http\Controllers;

use App\Jobs\MqttTestCmdJob;
use App\Models\CleanArea;
use App\Models\MqttCommand;
use App\Models\MqttCommandType;
use App\Models\RegionMgmt;
use App\Models\VehicleMgmt;
use App\Models\Vertex;
use Auth;
use Barryvdh\Reflection\DocBlock\Type\Collection;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Queue;
use function app\imgCoordToQuadrantCoordX;
use function app\imgCoordToQuadrantCoordY;

class MqttCommandController extends Collection {
    /**
     * @api              {get} /api/mqttCommands 撈取指令紀錄（最新5筆）
     * @apiGroup         MqttCommand
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} vehicle_id 車輛編號
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.mqttCommands 指令資訊列表
     * @apiSuccess {Object} data.mqttCommands.mqtt_command_type 任務類型資訊
     * @apiSuccess {String} data.mqttCommands.mqtt_command_type.name 任務類型名稱
     * @apiSuccess {String} data.mqttCommands.device_name 裝置名稱
     * @apiSuccess {Number} data.mqttCommands.sweep_start_goal_x_px 清掃起始位置x
     * @apiSuccess {Number} data.mqttCommands.sweep_start_goal_y_px 清掃起始位置y
     * @apiSuccess {Number} data.mqttCommands.sweep_end_goal_x_px 清掃結束位置x
     * @apiSuccess {Number} data.mqttCommands.sweep_end_goal_y_px 清掃結束位置y
     * @apiSuccess {Number} data.mqttCommands.goal_x_px 目標位置x
     * @apiSuccess {Number} data.mqttCommands.goal_y_px 目標位置y
     *
     * @apiSampleRequest off
     */
    public function index(Request $request) {
        $vehicleId = $request->input('vehicle_id');
        $mqttCommands = MqttCommand::whereVehicleId($vehicleId)->orderByDesc('id')->with('mqttCommandType')->skip(0)->take(5)->get();

        return [
            'status' => 0,
            'data'   => [
                'mqttCommands' => $mqttCommands
            ]
        ];
    }

    /**
     * @api              {post} /api/mqttCommands 新增mqtt指令
     * @apiGroup         MqttCommand
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {Number} command_type_id 指令類型，請參考「ACS_communication_v1.4」的編號而定
     * @apiParam {String} [device_name] 裝置名稱
     * @apiParam {String} [vertex_name] 站點名稱
     * @apiParam {String} [region] 區域編號
     * @apiParam {Number} [x] 座標x
     * @apiParam {Number} [y] 座標y
     * @apiParam {String} [laser_detection] on為打開測試，off為關閉測試
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.mqttCommand 指令資訊
     * @apiSuccess {Object} data.mqttCommand.transfer_processing 任務資訊
     * @apiSuccess {String} data.mqttCommand.transfer_processing.transfer_state 派任務狀態
     *
     * @apiSampleRequest off
     */
    public function store(Request $request): Response|array|Application|ResponseFactory {
        $mqttCommandTypeId = $request->input('mqtt_command_type_id');
        $deviceName = $request->input('device_name');
        $vehicleId = $request->input('vehicle_id');
        $priority = $request->input('priority', 1);
        $regionMgmtId = $request->input('region_mgmt_id');
        $userId = Auth::id();
        $mqttCommandType = MqttCommandType::find($mqttCommandTypeId);
        $mqttCommand = new MqttCommand();
        $mqttCommand->mqtt_command_type_id = $mqttCommandTypeId;
        $mqttCommand->region_mgmt_id = $regionMgmtId;
        $mqttCommand->vehicle_id = $vehicleId;
        $mqttCommand->user_id = $userId;
        $mqttCommand->priority = $priority;
        $vehicleMgmt = null;
        if($mqttCommandType->is_mission) {
            $vehicleMgmt = VehicleMgmt::find($vehicleId);
            $vehicleGroup = $vehicleMgmt->group;
            if($vehicleGroup) {
                $mqttCommand->vehicleGroup = $vehicleGroup;
            }
        }

        $regionMgmt = RegionMgmt::find($regionMgmtId);

        $x = $request->input('x');
        $y = $request->input('y');
        $x2 = $request->input('x2');
        $y2 = $request->input('y2');
        $laserDetection = $request->input('laser_detection');
        if($mqttCommandTypeId == 1) {
            $vertexName = $request->input('vertex_name');
            $vertex = Vertex::whereName($vertexName)->first();
            $mqttCommand->region_mgmt_id = $vertex->region_mgmt_id;
            $mqttCommand->data = [
                [
                    'typename' => $mqttCommandType->typename,
                    'id'       => 0,
                    'data'     => [
                        'typename' => 'vertex_name',
                        'data'     => $vertexName
                    ]
                ]
            ];
        } else if($mqttCommandTypeId == 2 || $mqttCommandTypeId == 11) {
            $unit = $request->input('unit', 'px');
            $theta = $request->input('theta');
            if($unit == 'px') {
                $x = imgCoordToQuadrantCoordX($regionMgmt->resolution, $x, $regionMgmt->origin_x);
                $y = imgCoordToQuadrantCoordY($regionMgmt->resolution, $y, $regionMgmt->img_height, $regionMgmt->origin_y);
                $theta = round($this->calculateDegrees($vehicleMgmt->position_x, $vehicleMgmt->position_y, $x, $y));
            }
            $mqttCommand->data = [
                [
                    'typename' => 'go',
                    'id'       => 0,
                    'data'     => [
                        'typename'        => 'goal',
                        'data'            => [
                            'x'     => $x,
                            'y'     => $y,
                            'theta' => $theta
                        ],
                        'laser_detection' => $laserDetection
                    ]
                ]
            ];
        } else if($mqttCommandTypeId == 3) {
            $vertex = Vertex::whereHas('vertexConfigurations', function(Builder $query) use ($deviceName) {
                $query->where('type', 'device_name');
                $query->where('data', $deviceName);
            })->first();
            $mqttCommand->region_mgmt_id = $vertex->region_mgmt_id;
            $mqttCommand->data = [
                [
                    'typename' => 'moveto',
                    'id'       => 0,
                    'data'     => [
                        'typename' => 'device_name',
                        'data'     => $deviceName
                    ]
                ],
                [
                    'typename' => $mqttCommandType->typename,
                    'id'       => 1,
                    'data'     => [
                        'typename' => 'device_name',
                        'data'     => $deviceName
                    ]
                ]
            ];
        } else if($mqttCommandTypeId == 4) {
            $cleanArea = new CleanArea();
            $cleanArea->region_mgmt_id = $regionMgmtId;
            $cleanArea->vehicle_mgmt_id = $vehicleId;
            $cleanArea->start_goal_x = imgCoordToQuadrantCoordX($regionMgmt->resolution, $x, $regionMgmt->origin_x);
            $cleanArea->start_goal_y = imgCoordToQuadrantCoordY($regionMgmt->resolution, $y, $regionMgmt->img_height, $regionMgmt->origin_y);
            $cleanArea->end_goal_x = imgCoordToQuadrantCoordX($regionMgmt->resolution, $x2, $regionMgmt->origin_x);
            $cleanArea->end_goal_y = imgCoordToQuadrantCoordY($regionMgmt->resolution, $y2, $regionMgmt->img_height, $regionMgmt->origin_y);
            $cleanArea->save();
            $mqttCommand->data = [
                [
                    'typename' => $mqttCommandType->typename,
                    'id'       => 0,
                    'data'     => [
                        'start_goal'      => [
                            'x' => $cleanArea->start_goal_x,
                            'y' => $cleanArea->start_goal_y
                        ],
                        'end_goal'        => [
                            'x' => $cleanArea->end_goal_x,
                            'y' => $cleanArea->end_goal_y
                        ],
                        'laser_detection' => $laserDetection
                    ]
                ]
            ];
        } else if($mqttCommandTypeId == 5 || $mqttCommandTypeId == 6) {
            $mqttCommand->data = [
                [
                    'typename' => $mqttCommandType->typename,
                    'id'       => 0
                ]
            ];
        } else if($mqttCommandTypeId == 7) {
            $uuid = $request->input('uuid');
            $parentMqttCommand = MqttCommand::whereCommandId($uuid)->first();
            $parentMqttCommand->is_completed = 1;
            $parentMqttCommand->save();
            CleanArea::whereRegionMgmtId($regionMgmtId)->where('vehicle_mgmt_id', $parentMqttCommand->vehicle_id)->update([
                'enable' => 0
            ]);

            $mqttCommand->data = [
                [
                    'uuid' => $uuid
                ]
            ];
        } else if($mqttCommandTypeId == 12) {
            $mqttCommand->data = [
                [
                    'typename' => $mqttCommandType->typename,
                    'id'       => 0
                ]
            ];
        } else if($mqttCommandTypeId == 30) {
            $doorId = $request->input('door_id');
            $action = $request->input('action');
            $mqttCommand->data = [
                'door_id' => $doorId,
                'action'  => $action
            ];
        } else if($mqttCommandTypeId == 31 || $mqttCommandTypeId == 32 || $mqttCommandTypeId == 34 || $mqttCommandTypeId == 35) {
            $elevatorId = $request->input('elevator_id');
            $mqttCommand->data = [
                'elevator_id' => $elevatorId
            ];
        } else if($mqttCommandTypeId == 33) {
            $elevatorId = $request->input('elevator_id');
            $targetFloor = $request->input('target_floor');
            $mqttCommand->data = [
                'elevator_id'  => $elevatorId,
                'target_floor' => $targetFloor
            ];
        } else if($mqttCommandTypeId == 36 || $mqttCommandTypeId == 37 || $mqttCommandTypeId == 38 || $mqttCommandTypeId == 39 || $mqttCommandTypeId == 41 || $mqttCommandTypeId == 42) {
            $years = $request->input('years');
            $month = $request->input('month');
            $week = $request->input('week');
            $day = $request->input('day');
            $hours = $request->input('hours');
            $minutes = $request->input('minutes');
            $systemId = $request->input('system_id');
            $parameter = $request->input('parameter');
            $mqttCommand->data = [
                'mission_type' => $mqttCommandType->typename,
                'system_id'    => $systemId,
                'date'         => [
                    'years'   => $years,
                    'month'   => $month,
                    'week'    => $week,
                    'day'     => $day,
                    'hours'   => $hours,
                    'minutes' => $minutes
                ],
                'parameter'    => $parameter
            ];
        } else if($mqttCommandTypeId == 40) {
            $missionId = $request->input('mission_id');
            $mqttCommand->data = [
                'mission_id' => $missionId
            ];
        } else if($mqttCommandTypeId == 43 || $mqttCommandTypeId == 44) {
            $action = $request->input('action');
            $mqttCommand->data = [
                "cleanstation_ID" => 'clean001',
                'action'          => $action
            ];
        }
        $mqttCommand->send_command = json_encode($mqttCommand->preview_send_command);
        Queue::connection('rabbitmq')->pushRaw($mqttCommand->send_command);
        $mqttCommand->save();
        if(config('app.env') == 'local') {
            if($mqttCommandTypeId == 2) {
                MqttTestCmdJob::dispatch($mqttCommand->preview_send_command, $regionMgmt->region, $vehicleId, false)->onQueue('high');
            } else if($mqttCommandTypeId == 4) {
                MqttTestCmdJob::dispatch($mqttCommand->preview_send_command, $regionMgmt->region, $vehicleId)->onQueue('high');
            } else if($mqttCommandTypeId == 11) {
                Queue::connection('rabbitmq-receiver')->pushRaw(json_encode([
                    'typename' => 'deploy_graph',
                    'priority' => 1,
                    'id'       => $mqttCommand->idData,
                    'reply'    => [
                        'condition'   => 'accepted',
                        'description' => null
                    ],
                    'data'     => [
                        'update_fail_account_list' => [
                            [
                                'account' => [
                                    'type' => 'agv',
                                    'name' => 'MR001'
                                ],
                                'reason'  => '1'
                            ]
                        ]
                    ]
                ]));
            } else if($mqttCommandTypeId == 43 || $mqttCommandTypeId == 44) {
                Queue::connection('rabbitmq-receiver')->pushRaw(json_encode([
                    'typename'        => $mqttCommandType->typename . '_completed',
                    'cleanstation_ID' => 'clean001',
                    'm_command_id'    => $mqttCommand->command_id
                ]));
            } else {
                Queue::connection('rabbitmq-receiver')->pushRaw(json_encode([
                    'typename' => $mqttCommandType->typename,
                    'id'       => [
                        'uuid' => $mqttCommand->command_id
                    ],
                    'reply'    => [
                        'condition'   => 'accepted',
                        'description' => '送出成功'
                    ]
                ]));
            }
        }

        return [
            'status' => 0,
            'data'   => [
                'mqttCommand' => $mqttCommand
            ]
        ];
    }

    private function calculateDegrees($x1, $y1, $x2, $y2) {
        $deltaX = $x2 - $x1;
        $deltaY = $y2 - $y1;
        $radian = atan2($deltaY, $deltaX);
        return ($radian * 180 / pi());
    }
}
