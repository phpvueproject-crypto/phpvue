<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\StationMgmt
 *
 * @property string $station_id
 * @property string|null $vertex_name
 * @property string|null $device_name
 * @property string|null $station_group Data from: dispatching_group table . Format: Red,Blue...
 * @property bool|null $enable
 * @property bool|null $bypass 不一定過站 vs 必過站
 * @property int|null $vertex_id
 * @property-read mixed $group
 * @property-read \App\Models\StationStatus|null $stationStatus
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \App\Models\Vertex|null $vertex
 * @method static \Illuminate\Database\Eloquent\Builder|StationMgmt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StationMgmt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StationMgmt query()
 * @method static \Illuminate\Database\Eloquent\Builder|StationMgmt whereBypass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StationMgmt whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StationMgmt whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StationMgmt whereStationGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StationMgmt whereStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StationMgmt whereVertexId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StationMgmt whereVertexName($value)
 * @mixin Eloquent
 */
class StationMgmt extends Eloquent {
    protected $table = 'station_mgmt';
    protected $primaryKey = 'station_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $appends = ['group'];

    public function stationStatus() {
        return $this->hasOne(StationStatus::class, 'station_id', 'station_id');
    }

    public function vertex() {
        return $this->belongsTo(Vertex::class);
    }

    public function getGroupAttribute() {
        return $this->station_group ? explode(',', $this->station_group) : [];
    }

    public function users(): BelongsToMany {
        return $this->belongsToMany(User::class, 'station_mgmt_user', 'station_id', 'user_id', 'station_id', 'id');
    }
}
