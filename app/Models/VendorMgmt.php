<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\VendorMgmt
 *
 * @property string $vendor
 * @property string $vendor_vat
 * @property string|null $vendor_support
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|VendorMgmt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorMgmt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorMgmt onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorMgmt query()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorMgmt whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorMgmt whereVendor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorMgmt whereVendorSupport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorMgmt whereVendorVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VendorMgmt withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|VendorMgmt withoutTrashed()
 * @mixin Eloquent
 */
class VendorMgmt extends Eloquent {
    use SoftDeletes;

    protected $table = 'vendor_mgmt';
    protected $primaryKey = 'vendor';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}
