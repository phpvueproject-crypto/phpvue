<?php

namespace App\Events;

use App\Models\ParkingLotStatus;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ParkingLotStatusUpdated implements ShouldBroadcastNow {
    private ParkingLotStatus $parkingLotStatus;

    public function __construct(ParkingLotStatus $parkingLotStatus) {
        $this->parkingLotStatus = $parkingLotStatus;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('parkingLotStatuses'),
            new PrivateChannel("vertices.{$this->parkingLotStatus->parkingLotMgmt->vertex_id}")
        ];
    }

    public function broadcastWith(): array {
        return [
            'parkingLotStatus' => $this->parkingLotStatus->toArray()
        ];
    }
}
