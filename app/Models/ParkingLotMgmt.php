<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\ParkingLotMgmt
 *
 * @property string $parking_lot_id
 * @property string|null $vertex_name
 * @property string|null $prefer_vehicle
 * @property string|null $attribute "charge" // -> is a charging station, 
 * "battery_switch" // -> is a battery switching station
 * @property bool|null $enable
 * @property int|null $vertex_id
 * @property-read \App\Models\ParkingLotStatus|null $parkingLotStatus
 * @property-read \App\Models\Vertex|null $vertex
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotMgmt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotMgmt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotMgmt query()
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotMgmt whereAttribute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotMgmt whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotMgmt whereParkingLotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotMgmt wherePreferVehicle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotMgmt whereVertexId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotMgmt whereVertexName($value)
 * @mixin Eloquent
 */
class ParkingLotMgmt extends Eloquent {
    protected $table = 'parking_lot_mgmt';
    protected $primaryKey = 'parking_lot_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function parkingLotStatus(): HasOne {
        return $this->hasOne(ParkingLotStatus::class, 'parking_lot_id', 'parking_lot_id');
    }

    public function vertex(): BelongsTo {
        return $this->belongsTo(Vertex::class);
    }
}
