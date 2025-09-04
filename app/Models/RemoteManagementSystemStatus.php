<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * App\Models\RemoteManagementSystemStatus
 *
 * @property int $id
 * @property int|null $location_id
 * @property int|null $status_A_time
 * @property int|null $status_B_time
 * @property int|null $status_C_time
 * @property int|null $status_D_time
 * @property int|null $status_E_time
 * @property int|null $status_F_time
 * @property int $is_latest
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $procedure_step
 * @property int|null $r2_value
 * @property int|null $r3_value
 * @property int|null $total_time
 * @property int $result
 * @property int|null $mission_queue_id
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property int $sampling_point
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GasSampling> $gasSamplings
 * @property-read int|null $gas_samplings_count
 * @property-read \App\Models\Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MicroOrganism> $microOrganisms
 * @property-read int|null $micro_organisms_count
 * @property-read \App\Models\MissionQueue|null $missionQueue
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MicroOrganism> $particles
 * @property-read int|null $particles_count
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus newModelQuery()
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus newQuery()
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus query()
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus safeJoin($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus safeLeftJoin($table, $first, $operator = null, $second = null)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereCreatedAt($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereId($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereIsLatest($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereLocationId($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereMissionQueueId($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereProcedureStep($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereR2Value($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereR3Value($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereResult($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereSamplingPoint($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereStartTime($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereStatusATime($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereStatusBTime($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereStatusCTime($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereStatusDTime($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereStatusETime($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereStatusFTime($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereTotalTime($value)
 * @method static \App\Models\CustomBuilder|RemoteManagementSystemStatus whereUpdatedAt($value)
 * @mixin Eloquent
 */
class RemoteManagementSystemStatus extends Eloquent {
    use HasRelationships;

    protected $casts = [
        'start_time' => 'datetime'
    ];

    protected static function booted(): void {
        static::saving(function($model) {
            $model->total_time = ($model->status_A_time ?? 0) + ($model->status_B_time ?? 0) + ($model->status_C_time ?? 0) + ($model->status_D_time ?? 0) + ($model->status_E_time ?? 0) + ($model->status_F_time ?? 0);
        });
    }

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class);
    }

    public function map(): HasOneDeep {
        return $this->hasOneDeepFromRelations($this->location(), (new Location)->map());
    }

    public function newEloquentBuilder($query): CustomBuilder {
        return new CustomBuilder($query);
    }

    public function missionQueue(): BelongsTo {
        return $this->belongsTo(MissionQueue::class);
    }

    public function microOrganisms(): HasMany {
        return $this->hasMany(MicroOrganism::class)->whereIn('organism_kind', [
            'suspended',
            'falling',
            'contact'
        ])->where('source', 2);
    }

    public function particles(): HasMany {
        return $this->hasMany(MicroOrganism::class)->whereIn('organism_kind', [
            'microparticle_dot_5',
            'microparticle_5'
        ])->where('source', 2);
    }

    public function gasSamplings(): HasMany {
        return $this->hasMany(GasSampling::class);
    }
}
