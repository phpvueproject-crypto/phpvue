<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\ObjectClass
 *
 * @property string $object_class
 * @property int $enable
 * @property string|null $name
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectClass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectClass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectClass query()
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectClass whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectClass whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectClass whereObjectClass($value)
 * @mixin Eloquent
 */
class ObjectClass extends Eloquent {
    protected $table = 'object_class';
}
