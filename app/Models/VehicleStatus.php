<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * App\Models\VehicleStatus
 *
 * @property string|null $update_ts
 * @property string|null $vehicle_id
 * @property string|null $vehicle_type
 * @property string|null $vehicle_location
 * @property string|null $water_box_status
 * @property string|null $spray_status
 * @property string|null $mopping_motor_status
 * @property int|null $air_laser_sensor_status
 * @property int|null $depth_camera_status
 * @property int|null $pipe_import_status
 * @property string|null $sweep_mode_status
 * @property int|null $vertex_id
 * @property int|null $deploy_status
 * @property string|null $deploy_fail_reason
 * @property-read \App\Models\VehicleMgmt|null $vehicleMgmt
 * @property-read \App\Models\Vertex|null $vertex
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus whereAirLaserSensorStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus whereDeployFailReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus whereDeployStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus whereDepthCameraStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus whereMoppingMotorStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus wherePipeImportStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus whereSprayStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus whereSweepModeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus whereUpdateTs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus whereVehicleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus whereVehicleLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus whereVehicleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus whereVertexId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleStatus whereWaterBoxStatus($value)
 * @mixin Eloquent
 */
class VehicleStatus extends Eloquent {
    use HasRelationships;

    protected $table = 'vehicle_status';
    protected $primaryKey = 'vehicle_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function vehicleMgmt(): BelongsTo {
        return $this->belongsTo(VehicleMgmt::class, 'vehicle_id', 'vehicle_id');
    }

    public function vertex(): BelongsTo {
        return $this->belongsTo(Vertex::class);
    }

    public function regionMgmt(): HasOneDeep {
        return $this->hasOneDeepFromRelations($this->vertex(), (new Vertex)->regionMgmt());
    }
}
