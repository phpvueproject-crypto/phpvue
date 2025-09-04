<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * App\Models\Location
 *
 * @property string|null $build       53
 * @property int|null $floors         B1
 * @property string|null $room        B113
 * @property string|null $vertex_name v1
 * @property int|null $vertex_id
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $device_name
 * @property string|null $x
 * @property string|null $y
 * @property float|null $x_px
 * @property float|null $y_px
 * @property string|null $bar_code
 * @property string|null $map_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GasSampling> $gasSamplings
 * @property-read int|null $gas_samplings_count
 * @property-read \App\Models\Map|null $map
 * @property-read \App\Models\MicroOrganism|null $microOrganism
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MicroOrganism> $microOrganisms
 * @property-read int|null $micro_organisms_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MissionQueue> $missionQueues
 * @property-read int|null $mission_queues_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MicroOrganism> $particles
 * @property-read int|null $particles_count
 * @property-read \App\Models\RoomEnvironment|null $roomEnvironment
 * @property-read \App\Models\Vertex|null $vertex
 * @method static \Illuminate\Database\Eloquent\Builder|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereBarCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereBuild($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereFloors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereMapId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereRoom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereVertexId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereVertexName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereXPx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereY($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereYPx($value)
 * @mixin Eloquent
 */
class Location extends Eloquent {
    use HasRelationships;

    protected $table = 'location';

    public function microOrganism(): HasOne {
        return $this->hasOne(MicroOrganism::class);
    }

    public function microOrganisms(): HasMany {
        return $this->hasMany(MicroOrganism::class);
    }

    public function particles(): HasMany {
        return $this->hasMany(MicroOrganism::class)->whereIn('organism_kind', [
            'microparticle_dot_5',
            'microparticle_5'
        ]);
    }

    public function roomEnvironment(): BelongsTo {
        return $this->belongsTo(RoomEnvironment::class, 'room', 'room_name');
    }

    public function regionMgmt(): HasOneDeep {
        return $this->hasOneDeepFromRelations($this->roomEnvironment(), (new RoomEnvironment)->regionMgmt());
    }

    public function floorRegionMgmt(): HasOneDeep {
        return $this->hasOneDeepFromRelations($this->roomEnvironment(), (new RoomEnvironment)->regionMgmt(), (new RegionMgmt)->floorRegionMgmt());
    }

    public function vertex(): BelongsTo {
        return $this->belongsTo(Vertex::class);
    }

    public function map(): BelongsTo {
        return $this->belongsTo(Map::class, 'map_id');
    }

    public function gasSamplings(): HasMany {
        return $this->hasMany(GasSampling::class);
    }

    public function missionQueues(): BelongsToMany {
        return $this->belongsToMany(MissionQueue::class);
    }
}
