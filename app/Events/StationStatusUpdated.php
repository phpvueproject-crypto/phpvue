<?php

namespace App\Events;

use App\Models\StationStatus;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class StationStatusUpdated implements ShouldBroadcastNow {
    private StationStatus $stationStatus;

    public function __construct(StationStatus $stationStatus) {
        $this->stationStatus = $stationStatus;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel("vertices.{$this->stationStatus->stationMgmt->vertex->id}"),
            new PrivateChannel("stationMgmts")
        ];
    }

    public function broadcastWith(): array {
        return [
            'stationStatus' => $this->stationStatus
        ];
    }
}
