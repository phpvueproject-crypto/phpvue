<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\HasOne;
use function app\quadrantCoordToimgCoordX;
use function app\quadrantCoordToimgCoordY;

/**
 * App\Models\VehicleMgmt
 *
 * @property string $vehicle_id
 * @property string|null $carrier_class
 * @property int|null $slot_num
 * @property int $battery_threshold_full
 * @property int $battery_threshold_high
 * @property int $battery_threshold_low
 * @property string|null $macaddr
 * @property string|null $ipaddr
 * @property string $vehicle_type
 * @property float $width_meter
 * @property float $length_meter
 * @property int $chargeable
 * @property int $load_unload_timeout_min
 * @property string|null $group
 * @property string|null $assign_switch
 * @property float|null $low_speed
 * @property float|null $normal_speed
 * @property float|null $high_speed
 * @property float|null $front_safe_distance_value
 * @property string $signal_tower_sound_state
 * @property string $color
 * @property int $stoppable_second
 * @property float|null $position_x 清消機器人的當前位置X軸
 * @property float|null $position_y 清消機器人的當前位置Y軸
 * @property int|null $theta 清消機器人的當前轉向角度
 * @property-read \App\Models\CleanArea|null $cleanArea
 * @property-read mixed $position_x_px
 * @property-read mixed $position_y_px
 * @property-read \App\Models\MqttCommand|null $mqttCommand
 * @property-read \App\Models\ObjectMgmt|null $objectMgmt
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \App\Models\VehicleStatus|null $vehicleStatus
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt query()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereAssignSwitch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereBatteryThresholdFull($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereBatteryThresholdHigh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereBatteryThresholdLow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereCarrierClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereChargeable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereFrontSafeDistanceValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereHighSpeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereIpaddr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereLengthMeter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereLoadUnloadTimeoutMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereLowSpeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereMacaddr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereNormalSpeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt wherePositionX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt wherePositionY($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereSignalTowerSoundState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereSlotNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereStoppableSecond($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereTheta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereVehicleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereVehicleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleMgmt whereWidthMeter($value)
 * @mixin Eloquent
 */
class VehicleMgmt extends Eloquent {
    protected $table = 'vehicle_mgmt';
    protected $primaryKey = 'vehicle_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $appends = ['position_x_px', 'position_y_px'];

    public function getPositionXPxAttribute() {
        if($this->vehicleStatus && $this->vehicleStatus->vertex && $this->vehicleStatus->vertex->regionMgmt) {
            $regionMgmt = $this->vehicleStatus->vertex->regionMgmt;
            return quadrantCoordToImgCoordX($regionMgmt->resolution, $this->position_x, $regionMgmt->origin_x);
        } else {
            return 0;
        }
    }

    public function getPositionYPxAttribute() {
        if($this->vehicleStatus && $this->vehicleStatus->vertex && $this->vehicleStatus->vertex->regionMgmt) {
            $regionMgmt = $this->vehicleStatus->vertex->regionMgmt;
            return quadrantCoordToImgCoordY($regionMgmt->resolution, $this->position_y, $regionMgmt->img_height, $regionMgmt->origin_y);
        } else {
            return 0;
        }
    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_vehicle_mgmt', 'vehicle_id', 'user_id', 'vehicle_id', 'id');
    }

    public function objectMgmt() {
        return $this->hasOne(ObjectMgmt::class, 'obj_id', 'vehicle_id');
    }

    public function vehicleStatus(): HasOne {
        return $this->hasOne(VehicleStatus::class, 'vehicle_id', 'vehicle_id');
    }

    public function mqttCommand() {
        return $this->hasOne(MqttCommand::class, 'vehicle_id', 'vehicle_id')->where('is_completed', 0);
    }

    public function cleanArea(): HasOne {
        return $this->hasOne(CleanArea::class, 'vehicle_mgmt_id', 'vehicle_id')->where('enable', 1);
    }
}
