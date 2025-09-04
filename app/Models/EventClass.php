<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\EventClass
 *
 * @property string $event_class
 * @property int $event_level
 * @method static \Illuminate\Database\Eloquent\Builder|EventClass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventClass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventClass query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventClass whereEventClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventClass whereEventLevel($value)
 * @mixin Eloquent
 */
class EventClass extends Eloquent {
    protected $table = 'event_class';
    protected $keyType = 'string';
}
