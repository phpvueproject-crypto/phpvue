<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\RegionMgmtUser
 *
 * @property int $user_id
 * @property int $region_mgmt_id
 * @property int $is_write
 * @property int $is_read
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmtUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmtUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmtUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmtUser whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmtUser whereIsWrite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmtUser whereRegionMgmtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmtUser whereUserId($value)
 * @mixin \Eloquent
 */
class RegionMgmtUser extends Pivot {
    public $timestamps = false;
}
