<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\DoorStatus
 *
 * @property string $update_ts
 * @property string $door_id
 * @property string|null $door_status
 * @property-read \App\Models\DoorMgmt|null $doorMgmt
 * @method static \Illuminate\Database\Eloquent\Builder|DoorStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DoorStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DoorStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|DoorStatus whereDoorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DoorStatus whereDoorStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DoorStatus whereUpdateTs($value)
 * @mixin Eloquent
 */
class DoorStatus extends Eloquent {
    protected $table = 'door_status';
    protected $primaryKey = 'door_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function doorMgmt(): BelongsTo {
        return $this->belongsTo(DoorMgmt::class, 'door_id', 'door_id');
    }
}
