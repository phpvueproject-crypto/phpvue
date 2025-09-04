<?php

namespace App\Events;

use App\Models\ParkingLotMgmt;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ParkingLotMgmtUpdated implements ShouldBroadcastNow {
    private ParkingLotMgmt $parkingLotMgmt;

    public function __construct(ParkingLotMgmt $parkingLotMgmt) {
        $this->parkingLotMgmt = $parkingLotMgmt;
        $this->parkingLotMgmt = $this->parkingLotMgmt->load([
            'parkingLotStatus',
            'vertex'
        ]);
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('parkingLotMgmts'),
            new PrivateChannel("vertices.{$this->parkingLotMgmt->vertex_id}")
        ];
    }

    public function broadcastWith(): array {
        return [
            'parkingLotMgmt' => $this->parkingLotMgmt->toArray()
        ];
    }
}
