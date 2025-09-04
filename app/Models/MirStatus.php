<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * App\Models\MirStatus
 *
 * @property int $id
 * @property int $device_id
 * @property mixed $position
 * @property string $robot_model
 * @property string $mission_text
 * @property mixed $velocity
 * @property string $battery_percentage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $mission_queue_id
 * @property string|null $map_id
 * @property int|null $location_id
 * @property string|null $state_text
 * @property string|null $current_status
 * @property int|null $vehicle_error_type_id
 * @property string|null $vehicle_error_message
 * @property int|null $initial_petri_count 培養皿總數量
 * @property int|null $remaining_petri_count 培養皿剩餘數量
 * @property \Illuminate\Support\Carbon $device_time
 * @property int $state_id
 * @property-read \App\Models\Device $device
 * @property-read string|null $state_text_zh
 * @property-read \App\Models\Location|null $location
 * @property-read \App\Models\Map|null $map
 * @property-read \App\Models\MissionQueue|null $missionQueue
 * @property-read \App\Models\VehicleErrorType|null $vehicleErrorType
 * @property-read \App\Models\RoomEnvironment|null $roomEnvironment
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereBatteryPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereCurrentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereDeviceTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereInitialPetriCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereMapId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereMissionQueueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereMissionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereRemainingPetriCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereRobotModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereStateText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereVehicleErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereVehicleErrorTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MirStatus whereVelocity($value)
 * @mixin Eloquent
 */
class MirStatus extends Eloquent {
    use HasRelationships;

    protected $fillable = [
        'initial_petri_count'
    ];

    protected $casts = [
        'battery_percentage' => 'decimal:15',
        'device_time'        => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = [
        'state_text_zh'
    ];

    public function map(): BelongsTo {
        return $this->belongsTo(Map::class, 'map_id');
    }

    public function regionMgmt(): HasOneDeep {
        return $this->hasOneDeepFromRelations($this->map(), (new Map)->regionMgmt());
    }

    public function missionQueue(): BelongsTo {
        return $this->belongsTo(MissionQueue::class);
    }

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class);
    }

    public function roomEnvironment(): HasOneDeep {
        return $this->hasOneDeepFromRelations(
            $this->map(), (new Map)->regionMgmt(), (new RegionMgmt)->roomEnvironment()
        );
    }

    public function getStateTextZhAttribute(): ?string {
        if($this->state_text == 'Error') {
            return '錯誤';
        } else if($this->state_text == 'ManualControl') {
            return '手動控制';
        } else if($this->state_text == 'Ready') {
            return '準備就緒';
        } else if($this->state_text == 'Emergency stop') {
            return '緊急停止';
        } else if($this->state_text == 'Pause') {
            return '已暫停';
        } else {
            return '正在運行';
        }
    }

    public function vehicleErrorType(): BelongsTo {
        return $this->belongsTo(VehicleErrorType::class);
    }

    public function device(): BelongsTo {
        return $this->belongsTo(Device::class);
    }
}
