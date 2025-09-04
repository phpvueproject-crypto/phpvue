<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use function app\quadrantCoordToImgCoordX;
use function app\quadrantCoordToImgCoordY;

/**
 * App\Models\CleanArea
 *
 * @property int $id
 * @property float $start_goal_x
 * @property float $start_goal_y
 * @property float $end_goal_x
 * @property float $end_goal_y
 * @property int $enable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $region_mgmt_id
 * @property string|null $vehicle_mgmt_id
 * @property-read int|float $end_goal_x_px
 * @property-read int|float $end_goal_y_px
 * @property-read int|float $start_goal_x_px
 * @property-read int|float $start_goal_y_px
 * @property-read \App\Models\RegionMgmt|null $regionMgmt
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TurningPoint> $turningPoints
 * @property-read int|null $turning_points_count
 * @method static \Illuminate\Database\Eloquent\Builder|CleanArea newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CleanArea newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CleanArea query()
 * @method static \Illuminate\Database\Eloquent\Builder|CleanArea whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanArea whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanArea whereEndGoalX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanArea whereEndGoalY($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanArea whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanArea whereRegionMgmtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanArea whereStartGoalX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanArea whereStartGoalY($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanArea whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanArea whereVehicleMgmtId($value)
 * @mixin Eloquent
 */
class CleanArea extends Eloquent {
    protected $appends = ['start_goal_x_px', 'start_goal_y_px', 'end_goal_x_px', 'end_goal_y_px'];

    public function turningPoints(): HasMany {
        return $this->hasMany(TurningPoint::class);
    }

    public function regionMgmt(): BelongsTo {
        return $this->belongsTo(RegionMgmt::class);
    }

    public function getStartGoalXPxAttribute(): float|int {
        if($this->regionMgmt) {
            $regionMgmt = $this->regionMgmt;
            return quadrantCoordToImgCoordX($regionMgmt->resolution, $this->start_goal_x, $regionMgmt->origin_x);
        } else {
            return 0;
        }
    }

    public function getStartGoalYPxAttribute(): float|int {
        if($this->regionMgmt) {
            $regionMgmt = $this->regionMgmt;
            return quadrantCoordToImgCoordY($regionMgmt->resolution, $this->start_goal_y, $regionMgmt->img_height, $regionMgmt->origin_y);
        } else {
            return 0;
        }
    }

    public function getEndGoalXPxAttribute(): float|int {
        if($this->regionMgmt) {
            $regionMgmt = $this->regionMgmt;
            return quadrantCoordToImgCoordX($regionMgmt->resolution, $this->end_goal_x, $regionMgmt->origin_x);
        } else {
            return 0;
        }
    }

    public function getEndGoalYPxAttribute(): float|int {
        if($this->regionMgmt) {
            $regionMgmt = $this->regionMgmt;
            return quadrantCoordToImgCoordY($regionMgmt->resolution, $this->end_goal_y, $regionMgmt->img_height, $regionMgmt->origin_y);
        } else {
            return 0;
        }
    }
}
