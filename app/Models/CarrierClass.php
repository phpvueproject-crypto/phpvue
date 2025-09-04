<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\CarrierClass
 *
 * @property string $carrier_class
 * @method static \Illuminate\Database\Eloquent\Builder|CarrierClass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CarrierClass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CarrierClass query()
 * @method static \Illuminate\Database\Eloquent\Builder|CarrierClass whereCarrierClass($value)
 * @mixin Eloquent
 */
class CarrierClass extends Eloquent {
    protected $table = 'carrier_class';
}
