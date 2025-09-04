<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\ObjectPortMgmt
 *
 * @property string $obj_id
 * @property string $obj_port_id
 * @property int|null $obj_port_index
 * @property string|null $prefer_obj_port_id
 * @property-read \App\Models\DispatchRuleMgmt|null $dispatchRuleMgmt
 * @property-read \App\Models\MqttCommand|null $mqttCommand
 * @property-read \App\Models\ObjectMgmt $objectMgmt
 * @property-read \App\Models\ObjectPortStatus|null $objectPortStatus
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectPortMgmt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectPortMgmt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectPortMgmt query()
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectPortMgmt whereObjId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectPortMgmt whereObjPortId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectPortMgmt whereObjPortIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ObjectPortMgmt wherePreferObjPortId($value)
 * @mixin Eloquent
 */
class ObjectPortMgmt extends Eloquent {
    protected $table = 'object_port_mgmt';
    protected $primaryKey = 'obj_port_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function dispatchRuleMgmt() {
        return $this->hasOne(DispatchRuleMgmt::class, 'source_port', 'obj_port_id');
    }

    public function objectPortStatus() {
        return $this->hasOne(ObjectPortStatus::class, 'obj_port_id', 'obj_port_id');
    }

    public function objectMgmt() {
        return $this->belongsTo(ObjectMgmt::class, 'obj_id', 'obj_id');
    }

    public function mqttCommand() {
        return $this->hasOne(MqttCommand::class, 'obj_port_id', 'obj_port_id')->orderByDesc('created_at');
    }
}
