<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\EquipmentMgmt
 *
 * @property string $equipment_id
 * @property bool|null $enable
 * @property string|null $macaddr
 * @property string|null $ipaddr
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentMgmt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentMgmt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentMgmt query()
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentMgmt whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentMgmt whereEquipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentMgmt whereIpaddr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentMgmt whereMacaddr($value)
 * @mixin Eloquent
 */
class EquipmentMgmt extends Eloquent {
    protected $table = 'equipment_mgmt';
    protected $primaryKey = 'equipment_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}
