<?php

namespace App\Events;

use App\Models\ParkingLotMgmt;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ParkingLotMgmtDeleted implements ShouldBroadcastNow {
    private ParkingLotMgmt $parkingLotMgmt;

    public function __construct(ParkingLotMgmt $parkingLotMgmt) {
        $this->parkingLotMgmt = $parkingLotMgmt;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel("vertices.{$this->parkingLotMgmt->vertex_id}"),
            new PrivateChannel('parkingLotMgmts')
        ];
    }

    public function broadcastWith(): array {
        return [
            'parkingLotMgmt' => $this->parkingLotMgmt
        ];
    }
}
