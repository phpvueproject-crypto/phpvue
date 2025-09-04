<?php

namespace App\Events;

use App\Models\ElevatorStatus;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ElevatorStatusUpdated implements ShouldBroadcastNow {
    private ElevatorStatus $elevatorStatus;

    public function __construct(ElevatorStatus $elevatorStatus) {
        $this->elevatorStatus = $elevatorStatus;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('elevatorStatuses'),
            new PrivateChannel("vertices.{$this->elevatorStatus->elevatorMgmt->vertices[0]->id}")
        ];
    }

    public function broadcastWith(): array {
        return [
            'elevatorStatus' => $this->elevatorStatus->toArray()
        ];
    }
}
