<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\ChartColor
 *
 * @property int $id
 * @property string $label
 * @property string $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ChartColor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChartColor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChartColor query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChartColor whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartColor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartColor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartColor whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartColor whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ChartColor extends Eloquent {

}
