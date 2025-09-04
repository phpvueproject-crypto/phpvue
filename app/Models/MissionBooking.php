<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MissionBooking
 *
 * @property int $id
 * @property string $mission_id
 * @property string $schedule
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string $day
 * @property-read string $hours
 * @property-read string $minutes
 * @property-read string $month
 * @property-read string $week
 * @property-read \App\Models\Mission $mission
 * @method static \Illuminate\Database\Eloquent\Builder|MissionBooking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionBooking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionBooking onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionBooking query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionBooking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionBooking whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionBooking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionBooking whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionBooking whereSchedule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionBooking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionBooking withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionBooking withoutTrashed()
 * @mixin Eloquent
 */
class MissionBooking extends Eloquent {
    use SoftDeletes;

    protected $fillable = [
        'mission_id',
        'schedule',
    ];

    protected $appends = [
        'minutes',
        'hours',
        'day',
        'month',
        'week'
    ];

    public function getMinutesAttribute(): string {
        return explode(' ', $this->schedule)[0];
    }

    public function getHoursAttribute(): string {
        return explode(' ', $this->schedule)[1];
    }

    public function getDayAttribute(): string {
        return explode(' ', $this->schedule)[2];
    }

    public function getMonthAttribute(): string {
        return explode(' ', $this->schedule)[3];
    }

    public function getWeekAttribute(): string {
        return explode(' ', $this->schedule)[4];
    }

    public function mission(): BelongsTo {
        return $this->belongsTo(Mission::class, 'mission_id', 'guid');
    }
}
