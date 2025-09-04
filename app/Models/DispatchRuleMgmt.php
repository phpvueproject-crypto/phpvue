<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\DispatchRuleMgmt
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchRuleMgmt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchRuleMgmt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchRuleMgmt query()
 * @mixin Eloquent
 */
class DispatchRuleMgmt extends Eloquent {
    protected $table = 'dispatch_rule_mgmt';
    protected $primaryKey = 'source_port';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}
