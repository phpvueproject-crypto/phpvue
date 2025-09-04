<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\VehicleEvent
 *
 * @property string $receive_time
 * @property string $system_type
 * @property string|null $system_id
 * @property string|null $event_code
 * @property string|null $event_name
 * @property string $event_level
 * @property string|null $vehicle_status
 * @property string|null $vehicle_location
 * @property string|null $vehicle_coordinate
 * @property string|null $vehicle_mission_uuid
 * @property string|null $comment
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleEvent whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleEvent whereEventCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleEvent whereEventLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleEvent whereEventName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleEvent whereReceiveTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleEvent whereSystemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleEvent whereSystemType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleEvent whereVehicleCoordinate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleEvent whereVehicleLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleEvent whereVehicleMissionUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleEvent whereVehicleStatus($value)
 * @mixin Eloquent
 */
class VehicleEvent extends Eloquent {
    protected $table = 'vehicle_event';
}
