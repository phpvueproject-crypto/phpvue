<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\VertexConfigurationColumn
 *
 * @property int $id
 * @property string $name
 * @property string|null $validate
 * @property string $view_type
 * @property int $is_unique
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationColumn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationColumn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationColumn query()
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationColumn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationColumn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationColumn whereIsUnique($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationColumn whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationColumn whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationColumn whereValidate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationColumn whereViewType($value)
 * @mixin Eloquent
 */
class VertexConfigurationColumn extends Eloquent {

}
