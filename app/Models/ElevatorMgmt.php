<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\ElevatorMgmt
 *
 * @property string $elevator_id
 * @property string|null $vertex_name
 * @property string|null $prefer_vehicle
 * @property bool|null $enable
 * @property string|null $macaddr
 * @property string|null $ipaddr
 * @property-read \App\Models\ElevatorStatus|null $elevatorStatus
 * @property-read \App\Models\Vertex|null $vertex
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vertex> $vertices
 * @property-read int|null $vertices_count
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorMgmt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorMgmt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorMgmt query()
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorMgmt whereElevatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorMgmt whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorMgmt whereIpaddr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorMgmt whereMacaddr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorMgmt wherePreferVehicle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorMgmt whereVertexName($value)
 * @mixin Eloquent
 */
class ElevatorMgmt extends Eloquent {
    protected $table = 'elevator_mgmt';
    protected $primaryKey = 'elevator_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function elevatorStatus(): HasOne {
        return $this->hasOne(ElevatorStatus::class, 'elevator_id', 'elevator_id');
    }

    public function vertex(): BelongsTo {
        return $this->belongsTo(Vertex::class, 'vertex_id', 'id');
    }

    public function vertices(): BelongsToMany {
        return $this->belongsToMany(Vertex::class, 'elevator_mgmt_vertex');
    }
}
