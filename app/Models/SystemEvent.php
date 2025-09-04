<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\SystemEvent
 *
 * @property string $receive_time
 * @property string|null $system_type
 * @property string|null $system_id
 * @property string|null $event_code
 * @property string|null $event_name
 * @property string $event_level
 * @property string|null $comment
 * @method static \Illuminate\Database\Eloquent\Builder|SystemEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemEvent whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemEvent whereEventCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemEvent whereEventLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemEvent whereEventName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemEvent whereReceiveTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemEvent whereSystemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemEvent whereSystemType($value)
 * @mixin Eloquent
 */
class SystemEvent extends Eloquent {
    protected $table = 'system_event';
}
