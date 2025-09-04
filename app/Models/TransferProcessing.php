<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TransferProcessing
 *
 * @property-read string|null $assigned_ts
 * @property-read string|null $delivery_start_ts
 * @property-read string|null $delivery_stop_ts
 * @property-read string|null $merged_ts
 * @property-read string|null $receive_ts
 * @property-read string|null $update_ts
 * @property-read \App\Models\MqttCommand|null $mqttCommand
 * @property-read \App\Models\ObjectPortMgmt|null $sourceObjectPortMgmt
 * @property-read \App\Models\StationMgmt|null $stationMgmt
 * @method static \Illuminate\Database\Eloquent\Builder|TransferProcessing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TransferProcessing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TransferProcessing query()
 * @mixin Eloquent
 */
class TransferProcessing extends Eloquent {
    protected $table = 'transfer_processing';
    protected $primaryKey = 'serial_num';
    protected $dates = ['receive_ts'];

    public function sourceObjectPortMgmt() {
        return $this->belongsTo(ObjectPortMgmt::class, 'source_port', 'obj_port_id');
    }

    public function getReceiveTsAttribute($date): ?string {
        if($date) {
            $carbon = new Carbon($date);
            return $carbon->format('Y-m-d H:i:s');
        } else {
            return $date;
        }
    }

    public function getUpdateTsAttribute($date): ?string {
        if($date) {
            $carbon = new Carbon($date);
            return $carbon->format('Y-m-d H:i:s');
        } else {
            return $date;
        }
    }

    public function getMergedTsAttribute($date): ?string {
        if($date) {
            $carbon = new Carbon($date);
            return $carbon->format('Y-m-d H:i:s');
        } else {
            return $date;
        }
    }

    public function getDeliveryStartTsAttribute($date): ?string {
        if($date) {
            $carbon = new Carbon($date);
            return $carbon->format('Y-m-d H:i:s');
        } else {
            return $date;
        }
    }

    public function getDeliveryStopTsAttribute($date): ?string {
        if($date) {
            $carbon = new Carbon($date);
            return $carbon->format('Y-m-d H:i:s');
        } else {
            return $date;
        }
    }

    public function getAssignedTsAttribute($date): ?string {
        if($date) {
            $carbon = new Carbon($date);
            return $carbon->format('Y-m-d H:i:s');
        } else {
            return $date;
        }
    }

    public function mqttCommand() {
        return $this->belongsTo(MqttCommand::class, 'command_id', 'command_id');
    }

    public function stationMgmt(): BelongsTo {
        return $this->belongsTo(StationMgmt::class, 'source_port', 'station_id');
    }
}
