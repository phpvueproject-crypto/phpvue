<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\ChargerMgmt
 *
 * @property string $charging_station_id
 * @property string|null $charging_station_location
 * @property string $prefer_vehicle
 * @property-read \App\Models\ChargerStatus|null $chargerStatus
 * @property-read \App\Models\Vertex|null $vertex
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerMgmt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerMgmt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerMgmt query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerMgmt whereChargingStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerMgmt whereChargingStationLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargerMgmt wherePreferVehicle($value)
 * @mixin Eloquent
 */
class ChargerMgmt extends Eloquent {
    protected $table = 'charger_mgmt';
    protected $primaryKey = 'charging_station_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function chargerStatus() {
        return $this->hasOne(ChargerStatus::class);
    }

    public function vertex() {
        return $this->BelongsTo(Vertex::class, 'charging_station_location', 'name');
    }
}
