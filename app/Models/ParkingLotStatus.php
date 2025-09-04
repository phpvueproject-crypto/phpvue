<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ParkingLotStatus
 *
 * @property string $parking_lot_id
 * @property string $parking_lot_status
 * @property string|null $booking_vehicle_id
 * @property string|null $occupied_vehicle_id
 * @property string|null $update_ts
 * @property-read \App\Models\ParkingLotMgmt|null $parkingLotMgmt
 * @property-read \App\Models\VehicleMgmt|null $vehicleMgmt
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotStatus whereBookingVehicleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotStatus whereOccupiedVehicleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotStatus whereParkingLotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotStatus whereParkingLotStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingLotStatus whereUpdateTs($value)
 * @mixin Eloquent
 */
class ParkingLotStatus extends Eloquent {
    protected $table = 'parking_lot_status';
    protected $primaryKey = 'parking_lot_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function vehicleMgmt(): BelongsTo {
        return $this->belongsTo(VehicleMgmt::class, 'parking_vehicle_id', 'vehicle_id');
    }

    public function parkingLotMgmt(): BelongsTo {
        return $this->belongsTo(ParkingLotMgmt::class, 'parking_lot_id', 'parking_lot_id');
    }
}
