<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ElevatorStatus
 *
 * @property string $update_ts
 * @property string $elevator_id
 * @property string|null $elevator_door_status open, closed
 * @property string|null $elevator_status idle, busy, disabled
 * @property string|null $elevator_position 1F, 2F, 3F
 * @property bool|null $elevator_authorization_state
 * @property-read \App\Models\ElevatorMgmt|null $elevatorMgmt
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorStatus whereElevatorAuthorizationState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorStatus whereElevatorDoorStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorStatus whereElevatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorStatus whereElevatorPosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorStatus whereElevatorStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ElevatorStatus whereUpdateTs($value)
 * @mixin Eloquent
 */
class ElevatorStatus extends Eloquent {
    protected $table = 'elevator_status';
    protected $primaryKey = 'elevator_id';
    protected $keyType = 'string';

    public function elevatorMgmt(): BelongsTo {
        return $this->belongsTo(ElevatorMgmt::class, 'elevator_id', 'elevator_id');
    }
}
