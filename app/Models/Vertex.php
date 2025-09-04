<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use function app\quadrantCoordToImgCoordX;
use function app\quadrantCoordToImgCoordY;

/**
 * App\Models\Vertex
 *
 * @property int $id
 * @property float $x
 * @property float $y
 * @property float $z
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $tag
 * @property int $vertex_type_id
 * @property string|null $region
 * @property int|null $attach_vertex_id
 * @property string|null $name
 * @property string|null $obj_uid
 * @property int|null $region_mgmt_id
 * @property int $is_deploy
 * @property int $enable
 * @property int|null $theta
 * @property-read \App\Models\ChargerMgmt|null $chargerMgmt
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Edge> $edges
 * @property-read int|null $edges_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ElevatorMgmt> $elevatorMgmts
 * @property-read int|null $elevator_mgmts_count
 * @property-read int|float|null $x_px
 * @property-read int|float|null $y_px
 * @property-read \App\Models\Location|null $location
 * @property-read \App\Models\ObjectMgmt|null $objectMgmt
 * @property-read \App\Models\ParkingLotMgmt|null $parkingLotMgmt
 * @property-read \App\Models\RegionMgmt|null $regionMgmt
 * @property-read \App\Models\StationMgmt|null $stationMgmt
 * @property-read \App\Models\UndeployLocation|null $undeployLocation
 * @property-read \App\Models\VehicleStatus|null $vehicleStatus
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VertexConfiguration> $vertexConfigurations
 * @property-read int|null $vertex_configurations_count
 * @method static Builder|Vertex newModelQuery()
 * @method static Builder|Vertex newQuery()
 * @method static Builder|Vertex query()
 * @method static Builder|Vertex whereAttachVertexId($value)
 * @method static Builder|Vertex whereCreatedAt($value)
 * @method static Builder|Vertex whereEnable($value)
 * @method static Builder|Vertex whereId($value)
 * @method static Builder|Vertex whereIsDeploy($value)
 * @method static Builder|Vertex whereName($value)
 * @method static Builder|Vertex whereObjUid($value)
 * @method static Builder|Vertex whereRegion($value)
 * @method static Builder|Vertex whereRegionMgmtId($value)
 * @method static Builder|Vertex whereTag($value)
 * @method static Builder|Vertex whereTheta($value)
 * @method static Builder|Vertex whereUpdatedAt($value)
 * @method static Builder|Vertex whereVertexTypeId($value)
 * @method static Builder|Vertex whereX($value)
 * @method static Builder|Vertex whereY($value)
 * @method static Builder|Vertex whereZ($value)
 * @mixin Eloquent
 */
class Vertex extends Eloquent {
    protected $appends = ['x_px', 'y_px'];

    public function getXPxAttribute(): float|int|null {
        if($this->regionMgmt) {
            $regionMgmt = $this->regionMgmt;
            if($this->x !== null) {
                return quadrantCoordToImgCoordX($regionMgmt->resolution, $this->x, $regionMgmt->origin_x);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getYPxAttribute(): float|int|null {
        if($this->regionMgmt) {
            $regionMgmt = $this->regionMgmt;
            if($this->y !== null) {
                return quadrantCoordToImgCoordY($regionMgmt->resolution, $this->y, $regionMgmt->img_height, $regionMgmt->origin_y);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function vertexConfigurations(): HasMany {
        return $this->hasMany(VertexConfiguration::class);
    }

    public function getXAttribute($value): float {
        return (double)$value;
    }

    public function getYAttribute($value): float {
        return (double)$value;
    }

    public function regionMgmt(): BelongsTo {
        return $this->belongsTo(RegionMgmt::class);
    }

    public function parkingLotMgmt(): HasOne {
        return $this->hasOne(ParkingLotMgmt::class);
    }

    public function chargerMgmt(): HasOne {
        return $this->hasOne(ChargerMgmt::class);
    }

    public function elevatorMgmts(): BelongsToMany {
        return $this->belongsToMany(ElevatorMgmt::class);
    }

    public function vehicleStatus(): HasOne {
        return $this->hasOne(VehicleStatus::class);
    }

    public function objectMgmt(): BelongsTo {
        return $this->belongsTo(ObjectMgmt::class, 'obj_uid', 'obj_uid');
    }

    public function stationMgmt(): HasOne {
        return $this->hasOne(StationMgmt::class);
    }

    public function edges(): HasMany {
        return $this->hasMany(Edge::class, 'start_vertex_id', 'id')->whereHas('endVertex', function(Builder $query) {
            $query->whereRaw('vertices.region_mgmt_id <> edges.region_mgmt_id');
        });
    }

    public function location(): HasOne {
        return $this->hasOne(Location::class);
    }

    public function undeployLocation(): HasOne {
        return $this->hasOne(UndeployLocation::class);
    }
}
