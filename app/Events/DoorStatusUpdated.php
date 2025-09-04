<?php

namespace App\Events;

use App\Models\DoorStatus;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class DoorStatusUpdated implements ShouldBroadcastNow {
    private DoorStatus $doorStatus;

    public function __construct(DoorStatus $doorStatus) {
        $this->doorStatus = $doorStatus;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('doorStatuses'),
            new PrivateChannel("edges.{$this->doorStatus->doorMgmt->edge_id}")
        ];
    }

    public function broadcastWith(): array {
        return [
            'doorStatus' => $this->doorStatus->toArray()
        ];
    }
}
