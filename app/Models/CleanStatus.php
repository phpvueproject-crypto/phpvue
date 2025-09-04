<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\CleanStatus
 *
 * @property string $cleanstation_ID
 * @property string|null $cleanstation_status
 * @property string|null $door_status
 * @property string|null $cylinder_status
 * @property int|null $temperature
 * @property int|null $humidity
 * @property int|null $pressure_difference
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CleanStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CleanStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CleanStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|CleanStatus whereCleanstationID($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanStatus whereCleanstationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanStatus whereCylinderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanStatus whereDoorStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanStatus whereHumidity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanStatus wherePressureDifference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanStatus whereTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CleanStatus whereUpdatedAt($value)
 * @mixin Eloquent
 */
class CleanStatus extends Eloquent {
    protected $primaryKey = 'cleanstation_ID';
    public $incrementing = false;
    protected $keyType = 'string';
}
