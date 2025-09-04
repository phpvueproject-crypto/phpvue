<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * App\Models\MicroOrganism
 *
 * @property string|null $bar_code
 * @property string|null $device_name
 * @property string|null $organism_kind
 * @property int|null $organism_value
 * @property string|null $room_name
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $location_id
 * @property \Illuminate\Support\Carbon|null $Time
 * @property int $is_serious
 * @property float $score
 * @property string|null $color
 * @property int $source
 * @property string|null $x
 * @property string|null $y
 * @property int|null $remote_management_system_status_id
 * @property-read string $organism_kind_name
 * @property-read \App\Models\Location|null $location
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism query()
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereBarCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereIsSerious($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereOrganismKind($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereOrganismValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereRemoteManagementSystemStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereRoomName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MicroOrganism whereY($value)
 * @mixin Eloquent
 */
class MicroOrganism extends Eloquent {
    use HasRelationships;

    protected $table = 'micro_organism';
    protected $dates = ['Time'];
    protected $appends = [
        'organism_kind_name'
    ];

    protected $fillable = [
        'source',
        'organism_kind',
        'room_name',
        'device_name',
        'location_id',
        'Time',
        'x',
        'y',
        'bar_code',
        'remote_management_system_status_id'
    ];

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class);
    }

    public function regionMgmt(): HasOneDeep {
        return $this->hasOneDeepFromReverse((new RegionMgmt())->microOrganisms());
    }

    public function getOrganismKindNameAttribute(): string {
        if($this->organism_kind == 'microparticle_dot_5') {
            return '微粒子(0.5µm)';
        } else if($this->organism_kind == 'microparticle_5') {
            return '微粒子(5µm)';
        } else if($this->organism_kind == 'suspended') {
            return '懸浮微生物';
        } else if($this->organism_kind == 'falling') {
            return '落下微生物';
        } else {
            return '接觸微生物';
        }
    }
}
