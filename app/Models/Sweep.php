<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\Sweep
 *
 * @property string|null $update_ts
 * @property float|null $clear_left
 * @property float|null $clear_right
 * @property float|null $detection_points
 * @property int $id
 * @method static \Illuminate\Database\Eloquent\Builder|Sweep newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sweep newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sweep query()
 * @method static \Illuminate\Database\Eloquent\Builder|Sweep whereClearLeft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sweep whereClearRight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sweep whereDetectionPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sweep whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sweep whereUpdateTs($value)
 * @mixin Eloquent
 */
class Sweep extends Eloquent {
    protected $table = 'sweep';
}
