<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\ObjectMgmt
 *
 * @property string $obj_uid
 * @property string $obj_id
 * @property \Illuminate\Support\Carbon $create_ts
 * @property \Illuminate\Support\Carbon $update_ts
 * @property string $object_class
 * @property string $vendor
 * @property-read \App\Models\DoorMgmt|null $doorMgmt
 * @property-read \App\Models\ElevatorMgmt|null $elevatorMgmt
 * @property-read \App\Models\EquipmentMgmt|null $equipmentMgmt
 * @property-read \App\Models\StationMgmt|null $stationMgmt
 * @property-read \App\Models\VehicleMgmt|null $vehicleMgmt
 * @property-read \App\Models\Vertex|null $vertex
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectMgmt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectMgmt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectMgmt query()
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectMgmt whereCreateTs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectMgmt whereObjId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectMgmt whereObjUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectMgmt whereObjectClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectMgmt whereUpdateTs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectMgmt whereVendor($value)
 * @mixin Eloquent
 */
class ObjectMgmt extends Eloquent {
    protected $table = 'object_mgmt';
    protected $primaryKey = 'obj_uid';
    public $incrementing = false;
    protected $keyType = 'string';
    const CREATED_AT = 'create_ts';
    const UPDATED_AT = 'update_ts';

    public function vehicleMgmt(): BelongsTo {
        return $this->belongsTo(VehicleMgmt::class, 'obj_id', 'vehicle_id');
    }

    public function vertex(): HasOne {
        return $this->hasOne(Vertex::class, 'obj_uid', 'obj_uid');
    }

    public function doorMgmt(): HasOne {
        return $this->hasOne(DoorMgmt::class, 'door_id', 'obj_id');
    }

    public function stationMgmt(): HasOne {
        return $this->hasOne(StationMgmt::class, 'station_id', 'obj_id');
    }

    public function elevatorMgmt(): HasOne {
        return $this->hasOne(ElevatorMgmt::class, 'elevator_id', 'obj_id');
    }

    public function equipmentMgmt(): HasOne {
        return $this->hasOne(EquipmentMgmt::class, 'equipment_id', 'obj_id');
    }
}
