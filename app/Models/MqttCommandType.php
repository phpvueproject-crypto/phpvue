<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\MqttCommandType
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $typename
 * @property int $is_mission
 * @property string $sender_type
 * @property string $sender_name
 * @property string $receiver_type
 * @property string $receiver_name
 * @property string|null $mission_type
 * @property int $is_schedule
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType query()
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType whereIsMission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType whereIsSchedule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType whereMissionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType whereReceiverName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType whereReceiverType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType whereSenderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType whereSenderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType whereTypename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MqttCommandType whereUpdatedAt($value)
 * @mixin Eloquent
 */
class MqttCommandType extends Eloquent {

}
