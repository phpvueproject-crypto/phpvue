<?php

namespace App\Events;

use App\Models\DoorMgmt;
use App\Models\StationMgmt;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class StationMgmtDeleted implements ShouldBroadcastNow {
    private StationMgmt $stationMgmt;

    public function __construct(StationMgmt $stationMgmt) {
        $this->stationMgmt = $stationMgmt;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel("vertices.{$this->stationMgmt->vertex_id}"),
            new PrivateChannel('stationMgmts')
        ];
    }

    public function broadcastWith(): array {
        return [
            'stationMgmt' => $this->stationMgmt
        ];
    }
}
