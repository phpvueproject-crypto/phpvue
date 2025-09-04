<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\RoomEnvironment
 *
 * @property float|null $temperature
 * @property float|null $humidity
 * @property float|null $pressure_difference
 * @property string|null $room_name
 * @property int|null $test_points
 * @property int $region_mgmt_id
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Location> $locations
 * @property-read int|null $locations_count
 * @property-read \App\Models\RegionMgmt $regionMgmt
 * @method static \Illuminate\Database\Eloquent\Builder|RoomEnvironment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomEnvironment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomEnvironment query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomEnvironment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomEnvironment whereHumidity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomEnvironment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomEnvironment wherePressureDifference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomEnvironment whereRegionMgmtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomEnvironment whereRoomName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomEnvironment whereTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomEnvironment whereTestPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomEnvironment whereUpdatedAt($value)
 * @mixin Eloquent
 */
class RoomEnvironment extends Eloquent {
    public $table = 'room_environment';

    public function regionMgmt(): BelongsTo {
        return $this->belongsTo(RegionMgmt::class);
    }

    public function locations(): HasMany {
        return $this->hasMany(Location::class, 'room', 'room_name');
    }
}
