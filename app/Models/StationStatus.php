<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * App\Models\StationStatus
 *
 * @property string $station_id
 * @property string|null $station_status
 * @property-read array $station_status_obj
 * @property-read \App\Models\StationMgmt|null $stationMgmt
 * @method static \Illuminate\Database\Eloquent\Builder|StationStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StationStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StationStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|StationStatus whereStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StationStatus whereStationStatus($value)
 * @mixin Eloquent
 */
class StationStatus extends Eloquent {
    protected $table = 'station_status';
    protected $primaryKey = 'station_id';
    protected $keyType = 'string';
    public $timestamps = false;
    private VehicleMgmt|Collection $vehicleMgmts;

    protected $appends = [
        'station_status_obj'
    ];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);

        $this->vehicleMgmts = collect();
    }

    public function setVehicleMgmts(VehicleMgmt|Collection $vehicleMgmts): void {
        $this->vehicleMgmts = $vehicleMgmts;
    }

    public function getStationStatusObjAttribute(): array {
        $value = $this->station_status;
        if($value) {
            if(str_contains($value, 'next')) {
                $value = str_replace(['next,', '(', ')'], '', $value);
                return [
                    'type'          => 'next',
                    'vehicle_mgmts' => $this->getGroup($value)
                ];
            } else if(str_contains($value, 'goal')) {
                $value = str_replace(['goal,', '(', ')'], '', $value);
                return [
                    'type'          => 'goal',
                    'vehicle_mgmts' => $this->getGroup($value)
                ];
            } else {
                return [
                    'type'          => 'init',
                    'vehicle_mgmts' => []
                ];
            }
        } else {
            return [
                'type'          => 'init',
                'vehicle_mgmts' => []
            ];
        }
    }

    private function getGroup($inputVehicleText): array {
        $vehicleMgmts = $this->vehicleMgmts;
        return collect(explode(',', $inputVehicleText))->map(function($r) use ($vehicleMgmts) {
            $vehicleMgmt = $vehicleMgmts->where('vehicle_id', $r)->first();
            if($vehicleMgmt) {
                $group = $vehicleMgmt->group ? explode(',', $vehicleMgmt->group) : [];
            } else {
                $group = [];
            }
            return [
                'vehicle_id' => $r,
                'group'      => $group
            ];
        })->all();
    }

    public function stationMgmt(): BelongsTo {
        return $this->belongsTo(StationMgmt::class, 'station_id', 'station_id');
    }
}
