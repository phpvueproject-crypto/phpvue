<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\ClearStatus
 *
 * @property float|null $converage_status
 * @property int|null $turn_points
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClearStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClearStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClearStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClearStatus whereConverageStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClearStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClearStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClearStatus whereTurnPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClearStatus whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ClearStatus extends Eloquent {
    protected $table = 'clear_status';
}
