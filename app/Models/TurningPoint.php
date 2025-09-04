<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use function app\quadrantCoordToImgCoordX;
use function app\quadrantCoordToImgCoordY;

/**
 * App\Models\TurningPoint
 *
 * @property int $id
 * @property int $clean_area_id
 * @property float $x
 * @property float $y
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CleanArea $cleanArea
 * @property-read mixed $x_px
 * @property-read mixed $y_px
 * @method static \Illuminate\Database\Eloquent\Builder|TurningPoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TurningPoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TurningPoint query()
 * @method static \Illuminate\Database\Eloquent\Builder|TurningPoint whereCleanAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TurningPoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TurningPoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TurningPoint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TurningPoint whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TurningPoint whereY($value)
 * @mixin Eloquent
 */
class TurningPoint extends Eloquent {
    protected $appends = ['x_px', 'y_px'];

    public function cleanArea(): BelongsTo {
        return $this->belongsTo(CleanArea::class);
    }

    public function getXPxAttribute() {
        if($this->cleanArea && $this->cleanArea->regionMgmt) {
            $regionMgmt = $this->cleanArea->regionMgmt;
            return quadrantCoordToImgCoordX($regionMgmt->resolution, $this->x, $regionMgmt->origin_x);
        } else {
            return 0;
        }
    }

    public function getYPxAttribute() {
        if($this->cleanArea && $this->cleanArea->regionMgmt) {
            $regionMgmt = $this->cleanArea->regionMgmt;
            return quadrantCoordToImgCoordY($regionMgmt->resolution, $this->y, $regionMgmt->img_height, $regionMgmt->origin_y);
        } else {
            return 0;
        }
    }
}
