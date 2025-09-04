<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Str;
use function app\quadrantCoordToImgCoordX;

/**
 * App\Models\MqttCommand
 *
 * @property int $id
 * @property string|null $vehicle_id
 * @property string|null $obj_port_id
 * @property mixed|null $send_command
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $command_id
 * @property int $is_completed
 * @property int|null $mqtt_command_type_id
 * @property int $laser_detection
 * @property string|null $device_name
 * @property float|null $sweep_start_goal_x
 * @property float|null $sweep_start_goal_y
 * @property float|null $sweep_end_goal_x
 * @property float|null $sweep_end_goal_y
 * @property float|null $goal_x
 * @property float|null $goal_y
 * @property mixed|null $receive_command
 * @property string|null $typename
 * @property int $is_mission
 * @property string $sender_type
 * @property string $sender_name
 * @property string $receiver_type
 * @property string $receiver_name
 * @property int|null $region_mgmt_id
 * @property-read int|float|null $goal_x_px
 * @property-read int|float|null $goal_y_px
 * @property-read array|null $preview_send_command
 * @property-read int|float|null $sweep_end_goal_x_px
 * @property-read int|float|null $sweep_end_goal_y_px
 * @property-read int|float|null $sweep_start_goal_x_px
 * @property-read int|float|null $sweep_start_goal_y_px
 * @property-read \App\Models\MqttCommandType|null $mqttCommandType
 * @property-read \App\Models\RegionMgmt|null $regionMgmt
 * @property-read \App\Models\TransferProcessing|null $transferProcessing
 * @property-read \App\Models\User $user
 * @property-read \App\Models\VehicleMgmt|null $vehicleMgmt
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand query()
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereCommandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereGoalX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereGoalY($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereIsCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereIsMission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereLaserDetection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereMqttCommandTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereObjPortId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereReceiveCommand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereReceiverName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereReceiverType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereRegionMgmtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereSendCommand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereSenderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereSenderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereSweepEndGoalX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereSweepEndGoalY($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereSweepStartGoalX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereSweepStartGoalY($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereTypename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommand whereVehicleId($value)
 * @mixin Eloquent
 */
class MqttCommand extends Eloquent {
    protected $appends = [
        'preview_send_command',
        'sweep_start_goal_x_px',
        'sweep_start_goal_y_px',
        'sweep_end_goal_x_px',
        'sweep_end_goal_y_px',
        'goal_x_px',
        'goal_y_px'
    ];
    public int $priority = 1;
    public array|null $idData = null;
    public array $data = [];
    public string|null $vehicleGroup = null;

    public function getSweepStartGoalXPxAttribute(): float|int|null {
        if($this->regionMgmt) {
            $regionMgmt = $this->regionMgmt;
            if($this->sweep_start_goal_x) {
                return quadrantCoordToImgCoordX($regionMgmt->resolution, $this->sweep_start_goal_x, $regionMgmt->origin_x);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getSweepStartGoalYPxAttribute(): float|int|null {
        if($this->regionMgmt) {
            $regionMgmt = $this->regionMgmt;
            if($this->sweep_start_goal_y) {
                return quadrantCoordToImgCoordX($regionMgmt->resolution, $this->sweep_start_goal_y, $regionMgmt->origin_x);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getSweepEndGoalXPxAttribute(): float|int|null {
        if($this->regionMgmt) {
            $regionMgmt = $this->regionMgmt;
            if($this->sweep_end_goal_x) {
                return quadrantCoordToImgCoordX($regionMgmt->resolution, $this->sweep_end_goal_x, $regionMgmt->origin_x);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getSweepEndGoalYPxAttribute(): float|int|null {
        if($this->regionMgmt) {
            $regionMgmt = $this->regionMgmt;
            if($this->sweep_end_goal_y) {
                return quadrantCoordToImgCoordX($regionMgmt->resolution, $this->sweep_end_goal_y, $regionMgmt->origin_x);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getGoalXPxAttribute(): float|int|null {
        if($this->regionMgmt) {
            $regionMgmt = $this->regionMgmt;
            if($this->goal_x) {
                return quadrantCoordToImgCoordX($regionMgmt->resolution, $this->goal_x, $regionMgmt->origin_x);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getGoalYPxAttribute(): float|int|null {
        if($this->regionMgmt) {
            $regionMgmt = $this->regionMgmt;
            if($this->goal_y) {
                return quadrantCoordToImgCoordX($regionMgmt->resolution, $this->goal_y, $regionMgmt->origin_x);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function transferProcessing(): HasOne {
        return $this->hasOne(TransferProcessing::class, 'command_id', 'command_id');
    }

    public function regionMgmt(): BelongsTo {
        return $this->belongsTo(RegionMgmt::class);
    }

    public function mqttCommandType(): BelongsTo {
        return $this->belongsTo(MqttCommandType::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function vehicleMgmt(): BelongsTo {
        return $this->belongsTo(VehicleMgmt::class, 'vehicle_id', 'vehicle_id');
    }

    public function getPreviewSendCommandAttribute(): ?array {
        $mqttCommandType = $this->mqttCommandType;
        if(!$mqttCommandType) {
            return null;
        }

        if(!$this->idData) {
            $now = Carbon::now();
            $this->command_id = Str::uuid()->toString();
            $this->idData = [
                'date' => "{$now->format('Y-m-d')}T{$now->format('H:i:s.v')}",
                'uuid' => $this->command_id
            ];
        } else {
            $this->command_id = $this->idData['uuid'];
        }

        $res = [
            'priority' => $this->priority,
            'id'       => $this->idData,
            'sender'   => [
                'type' => $mqttCommandType->sender_type,
                'name' => $mqttCommandType->sender_name
            ],
            'receiver' => [
                'type' => $mqttCommandType->receiver_type,
                'name' => $mqttCommandType->receiver_name
            ]
        ];

        if($mqttCommandType->is_mission) {
            $typeName = 'add_mission';
            if($this->vehicle_id) {
                $res['vehicle_candidate'] = [
                    [
                        'type' => $this->vehicleMgmt ? $this->vehicleMgmt->vehicle_type : 'agv',
                        'name' => $this->vehicle_id
                    ]
                ];
            } else if($this->mqtt_command_type_id == 8) {
                $vehicleMgmts = VehicleMgmt::whereGroup('Red')->whereRelation('vehicleStatus', 'vehicle_status', '<>', 'DISCONNECTED')->get()->map(function(VehicleMgmt $vehicleMgmt) {
                    return [
                        'type' => $vehicleMgmt->vehicle_type,
                        'name' => $vehicleMgmt->vehicle_id
                    ];
                })->all();
                $res['vehicle_candidate'] = $vehicleMgmts;
            }

            if($mqttCommandType->mission_type) {
                $res['mission_type'] = $mqttCommandType->mission_type;
            }
            $res['account'] = [
                'type' => $this->vehicleMgmt ? $this->vehicleMgmt->vehicle_type : 'agv',
                'name' => $this->vehicle_id
            ];
        } else if($mqttCommandType->is_schedule) {
            $typeName = 'add_scheduled_mission';
        } else {
            $typeName = $mqttCommandType->typename;
            if($this->vehicle_id) {
                switch($this->mqtt_command_type_id) {
                    case 3:
                    case 13:
                    case 14:
                    case 20:
                    case 23:
                    case 26:
                    case 27:
                    case 28:
                    case 29:
                        $this->data['account'] = [
                            'type' => $this->vehicleMgmt ? $this->vehicleMgmt->vehicle_type : 'agv',
                            'name' => $this->vehicle_id
                        ];
                        break;
                }
            }
        }

        if($this->vehicleGroup) {
            $res['vehicle_group'] = $this->vehicleGroup;
        }

        if($this->user_id && $mqttCommandType->is_mission) {
            $res['operator_id'] = $this->user->account;
        }

        $res['typename'] = $typeName;
        $res['data'] = $this->data;

        return $res;
    }
}
