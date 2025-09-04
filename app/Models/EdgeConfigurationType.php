<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\EdgeConfigurationType
 *
 * @property int $id
 * @property string $name
 * @property string|null $validate
 * @property string $view_type
 * @property int $is_unique
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfigurationType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfigurationType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfigurationType query()
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfigurationType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfigurationType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfigurationType whereIsUnique($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfigurationType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfigurationType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfigurationType whereValidate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfigurationType whereViewType($value)
 * @mixin Eloquent
 */
class EdgeConfigurationType extends Eloquent {

}
