<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\DoorMgmt
 *
 * @property string $door_id
 * @property string|null $edge_name
 * @property bool|null $enable
 * @property string|null $prefer_vehicle empty: all vehicle can choose.
 * @property string|null $door_status
 * @property string $macaddr
 * @property string $ipaddr
 * @property int|null $edge_id
 * @property-read \App\Models\DoorStatus|null $doorStatus
 * @property-read \App\Models\Edge|null $edge
 * @method static \Illuminate\Database\Eloquent\Builder|DoorMgmt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DoorMgmt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DoorMgmt query()
 * @method static \Illuminate\Database\Eloquent\Builder|DoorMgmt whereDoorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DoorMgmt whereDoorStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DoorMgmt whereEdgeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DoorMgmt whereEdgeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DoorMgmt whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DoorMgmt whereIpaddr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DoorMgmt whereMacaddr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DoorMgmt wherePreferVehicle($value)
 * @mixin Eloquent
 */
class DoorMgmt extends Eloquent {
    protected $table = 'door_mgmt';
    protected $primaryKey = 'door_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function doorStatus() {
        return $this->hasOne(DoorStatus::class, 'door_id', 'door_id');
    }

    public function edge(): BelongsTo {
        return $this->belongsTo(Edge::class);
    }
}
