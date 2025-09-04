<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ScheduledMission
 *
 * @property string $create_ts
 * @property string $mission_id
 * @property string $mission_type
 * @property string $system_id
 * @property string|null $years
 * @property string|null $month
 * @property string|null $week
 * @property string|null $day
 * @property string|null $hours
 * @property string|null $minutes
 * @property string|null $parameter
 * @property-read \App\Models\VehicleMgmt|null $vehicleMgmt
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledMission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledMission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledMission query()
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledMission whereCreateTs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledMission whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledMission whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledMission whereMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledMission whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledMission whereMissionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledMission whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledMission whereParameter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledMission whereSystemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledMission whereWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledMission whereYears($value)
 * @mixin Eloquent
 */
class ScheduledMission extends Eloquent {
    protected $table = 'scheduled_mission';

    public function vehicleMgmt(): BelongsTo {
        return $this->belongsTo(VehicleMgmt::class, 'system_id', 'vehicle_id');
    }
}
