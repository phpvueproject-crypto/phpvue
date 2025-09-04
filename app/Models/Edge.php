<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Edge
 *
 * @property int $id
 * @property int $direction
 * @property int $weight
 * @property int $start_vertex_id
 * @property int $end_vertex_id
 * @property int $enable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $region
 * @property string|null $name
 * @property int $region_mgmt_id
 * @property int $is_deploy
 * @property-read \App\Models\DoorMgmt|null $doorMgmt
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EdgeConfiguration> $edgeConfigurations
 * @property-read int|null $edge_configurations_count
 * @property-read \App\Models\Vertex $endVertex
 * @property-read \App\Models\RegionMgmt $regionMgmt
 * @property-read \App\Models\Vertex $startVertex
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VehicleMgmt> $vehicleMgmts
 * @property-read int|null $vehicle_mgmts_count
 * @method static \Illuminate\Database\Eloquent\Builder|Edge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Edge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Edge query()
 * @method static \Illuminate\Database\Eloquent\Builder|Edge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Edge whereDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Edge whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Edge whereEndVertexId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Edge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Edge whereIsDeploy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Edge whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Edge whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Edge whereRegionMgmtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Edge whereStartVertexId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Edge whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Edge whereWeight($value)
 * @mixin Eloquent
 */
class Edge extends Eloquent {
    public function startVertex(): BelongsTo {
        return $this->belongsTo(Vertex::class, 'start_vertex_id');
    }

    public function endVertex(): BelongsTo {
        return $this->belongsTo(Vertex::class, 'end_vertex_id');
    }

    public function edgeConfigurations(): HasMany {
        return $this->hasMany(EdgeConfiguration::class);
    }

    public function doorMgmt(): HasOne {
        return $this->hasOne(DoorMgmt::class);
    }

    public function vehicleMgmts(): BelongsToMany {
        return $this->belongsToMany(VehicleMgmt::class, 'edge_vehicle_mgmt', 'edge_id', 'vehicle_id', 'id', 'vehicle_id');
    }

    public function regionMgmt(): BelongsTo {
        return $this->belongsTo(RegionMgmt::class);
    }
}
