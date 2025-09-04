<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\UniversalEventLog
 *
 * @property-read \App\Models\EventClass|null $eventClass
 * @property-read \App\Models\ObjectMgmt|null $objectMgmt
 * @property-read \App\Models\Vertex|null $vertex
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalEventLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalEventLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalEventLog query()
 * @mixin Eloquent
 */
class UniversalEventLog extends Eloquent {
    protected $table = 'universal_event_log';

    public function vertex() {
        return $this->belongsTo(Vertex::class, 'obj_location', 'name');
    }

    public function objectMgmt() {
        return $this->belongsTo(ObjectMgmt::class, 'obj_id', 'obj_id');
    }

    public function eventClass() {
        return $this->belongsTo(EventClass::class, 'event_class', 'event_class');
    }
}
