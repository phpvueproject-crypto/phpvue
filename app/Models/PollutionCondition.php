<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\PollutionCondition
 *
 * @property int $id
 * @property string $name
 * @property string $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $display_name
 * @method static \Illuminate\Database\Eloquent\Builder|PollutionCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PollutionCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PollutionCondition query()
 * @method static \Illuminate\Database\Eloquent\Builder|PollutionCondition whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PollutionCondition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PollutionCondition whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PollutionCondition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PollutionCondition whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PollutionCondition whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PollutionCondition extends Eloquent {

}
