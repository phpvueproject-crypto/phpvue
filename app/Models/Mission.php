<?php

namespace App\Models;

use App\Repositories\MissionRepository;
use Eloquent;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * App\Models\Mission
 *
 * @property string $guid
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $region
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MissionQueue> $missionQueues
 * @property-read int|null $mission_queues_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RemoteManagementSystemStatus> $remoteManagementSystemStatuses
 * @property-read int|null $remote_management_system_statuses_count
 * @method static \Illuminate\Database\Eloquent\Builder|Mission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mission query()
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereGuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Mission extends Eloquent {
    use HasRelationships;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'guid';
    private MissionRepository $mission;
    protected $fillable = [
        'guid',
        'name'
    ];

    protected $appends = [
        'region'
    ];

    public function __construct() {
        parent::__construct();
        $this->mission = new MissionRepository();
    }

    public function getRegionAttribute(): ?string {
        return $this->mission->getRegion($this->name);
    }

    public function missionQueues(): HasMany {
        return $this->hasMany(MissionQueue::class, 'mission_id', 'guid');
    }

    public function locations(): HasManyDeep {
        return $this->hasManyDeepFromRelations($this->missionQueues(), (new MissionQueue())->locations());
    }

    public function remoteManagementSystemStatuses(): HasMany {
        return $this->hasMany(RemoteManagementSystemStatus::class, 'mission_id', 'guid');
    }
}
