<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\ChargerStatus
 *
 * @property string $update_ts
 * @property string $charging_station_id
 * @property string|null $booking
 * @property string|null $booking_owner
 * @property string|null $charging_vehicle_id
 * @property string|null $charging_station_status
 * @property-read \App\Models\VehicleMgmt|null $vehicleMgmt
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerStatus whereBooking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerStatus whereBookingOwner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerStatus whereChargingStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerStatus whereChargingStationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerStatus whereChargingVehicleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerStatus whereUpdateTs($value)
 * @mixin Eloquent
 */
class ChargerStatus extends Eloquent {
    protected $table = 'charger_status';
    protected $primaryKey = 'charger_station_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function vehicleMgmt() {
        return $this->belongsTo(VehicleMgmt::class, 'charging_vehicle_id', 'vehicle_id');
    }
}
