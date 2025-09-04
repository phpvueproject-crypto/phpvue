<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * App\Models\MissionQueue
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $finished
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $state
 * @property string|null $mission_id
 * @property \Illuminate\Support\Carbon $started
 * @property string|null $remark
 * @property int|null $start_location_id
 * @property int|null $end_location_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Location|null $endLocation
 * @property-read \App\Models\Mission|null $mission
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RemoteManagementSystemStatus> $remoteManagementSystemStatuses
 * @property-read int|null $remote_management_system_statuses_count
 * @property-read \App\Models\Location|null $startLocation
 * @method static \App\Models\CustomBuilder|MissionQueue newModelQuery()
 * @method static \App\Models\CustomBuilder|MissionQueue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionQueue onlyTrashed()
 * @method static \App\Models\CustomBuilder|MissionQueue query()
 * @method static \App\Models\CustomBuilder|MissionQueue safeJoin($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static \App\Models\CustomBuilder|MissionQueue safeLeftJoin($table, $first, $operator = null, $second = null)
 * @method static \App\Models\CustomBuilder|MissionQueue whereCreatedAt($value)
 * @method static \App\Models\CustomBuilder|MissionQueue whereDeletedAt($value)
 * @method static \App\Models\CustomBuilder|MissionQueue whereEndLocationId($value)
 * @method static \App\Models\CustomBuilder|MissionQueue whereFinished($value)
 * @method static \App\Models\CustomBuilder|MissionQueue whereId($value)
 * @method static \App\Models\CustomBuilder|MissionQueue whereMissionId($value)
 * @method static \App\Models\CustomBuilder|MissionQueue whereRemark($value)
 * @method static \App\Models\CustomBuilder|MissionQueue whereStartLocationId($value)
 * @method static \App\Models\CustomBuilder|MissionQueue whereStarted($value)
 * @method static \App\Models\CustomBuilder|MissionQueue whereState($value)
 * @method static \App\Models\CustomBuilder|MissionQueue whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionQueue withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionQueue withoutTrashed()
 * @mixin Eloquent
 */
class MissionQueue extends Eloquent {
    use SoftDeletes;
    use HasRelationships;

    public $incrementing = false;
    protected $fillable = [
        'id',
        'mission_id',
        'finished',
        'state',
        'remark'
    ];

    protected $casts = [
        'finished' => 'datetime',
        'started'  => 'datetime:Y-m-d H:i:s',
    ];

    public function mission(): BelongsTo {
        return $this->belongsTo(Mission::class, 'mission_id');
    }

    public function remoteManagementSystemStatuses(): HasMany {
        return $this->hasMany(RemoteManagementSystemStatus::class);
    }

    public function locations(): HasManyDeep {
        return $this->hasManyDeepFromRelations($this->remoteManagementSystemStatuses(), (new RemoteManagementSystemStatus)->location());
    }

    public function startLocation(): BelongsTo {
        return $this->belongsTo(Location::class, 'start_location_id');
    }

    public function endLocation(): BelongsTo {
        return $this->belongsTo(Location::class, 'end_location_id');
    }

    public function newEloquentBuilder($query): CustomBuilder {
        return new CustomBuilder($query);
    }
}
