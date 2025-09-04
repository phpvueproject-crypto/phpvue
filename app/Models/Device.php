<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Device
 *
 * @property int $id
 * @property string $name
 * @property string|null $ap
 * @property string|null $ip
 * @property int $is_connected
 * @property string|null $connected_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MirStatus|null $mirStatus
 * @method static \Illuminate\Database\Eloquent\Builder|Device newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Device newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Device query()
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereAp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereConnectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereIsConnected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Device extends Eloquent {
    protected $fillable = [
        'name',
        'ip',
        'ap'
    ];

    public function mirStatus(): HasOne {
        return $this->hasOne(MirStatus::class);
    }
}
