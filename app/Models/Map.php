<?php

namespace App\Models;

use App\Repositories\MapRepository;
use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Map
 *
 * @property string $guid
 * @property string $name
 * @property float $origin_x
 * @property float $origin_y
 * @property string $resolution
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $region_mgmt_id
 * @property-read string|null $region
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Location> $locations
 * @property-read int|null $locations_count
 * @property-read \App\Models\RegionMgmt|null $regionMgmt
 * @method static \Illuminate\Database\Eloquent\Builder|Map newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Map newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Map query()
 * @method static \Illuminate\Database\Eloquent\Builder|Map whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Map whereGuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Map whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Map whereOriginX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Map whereOriginY($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Map whereRegionMgmtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Map whereResolution($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Map whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Map extends Eloquent {
    protected $primaryKey = 'guid';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $appends = [
        'region'
    ];

    protected $fillable = [
        'guid',
        'name',
        'origin_x',
        'origin_y',
        'resolution'
    ];

    public function getRegionAttribute(): ?string {
        $map = new MapRepository();
        return $map->getRegion($this->name);
    }

    public function regionMgmt(): BelongsTo {
        return $this->belongsTo(RegionMgmt::class);
    }

    public function locations(): HasMany {
        return $this->hasMany(Location::class, 'map_id');
    }
}
