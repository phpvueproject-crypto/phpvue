<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\ObjectPortStatus
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectPortStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectPortStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectPortStatus query()
 * @mixin Eloquent
 */
class ObjectPortStatus extends Eloquent {
    protected $table = 'object_port_status';
    protected $primaryKey = 'obj_port_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}
