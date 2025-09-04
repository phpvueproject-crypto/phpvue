<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\GasSampling
 *
 * @property int $id
 * @property int $location_id
 * @property string $average_volume
 * @property string $cumulative_volume
 * @property int $second_mark
 * @property int $is_latest
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $remote_management_system_status_id
 * @property-read \App\Models\Location $location
 * @method static \Illuminate\Database\Eloquent\Builder|GasSampling newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GasSampling newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GasSampling query()
 * @method static \Illuminate\Database\Eloquent\Builder|GasSampling whereAverageVolume($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GasSampling whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GasSampling whereCumulativeVolume($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GasSampling whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GasSampling whereIsLatest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GasSampling whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GasSampling whereRemoteManagementSystemStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GasSampling whereSecondMark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GasSampling whereUpdatedAt($value)
 * @mixin Eloquent
 */
class GasSampling extends Eloquent {
    protected $fillable = [
        'remote_management_system_status_id',
        'second_mark',
        'location_id',
        'average_volume',
        'cumulative_volume',
        'is_latest'
    ];

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class);
    }
}
