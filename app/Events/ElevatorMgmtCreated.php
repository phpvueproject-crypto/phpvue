<?php

namespace App\Events;

use App\Models\ElevatorMgmt;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ElevatorMgmtCreated implements ShouldBroadcastNow {
    private ElevatorMgmt $elevatorMgmt;

    public function __construct(ElevatorMgmt $elevatorMgmt) {
        $this->elevatorMgmt = $elevatorMgmt;
        $this->elevatorMgmt = $this->elevatorMgmt->load('elevatorStatus');
    }

    public function broadcastOn(): array {
        $this->elevatorMgmt = $this->elevatorMgmt->load('vertices');
        $channels = [];
        foreach($this->elevatorMgmt->vertices as $vertex) {
            $channels[] = new PrivateChannel("vertices.{$vertex->id}");
        }
        $channels[] = new PrivateChannel('elevatorMgmts');

        return $channels;
    }

    public function broadcastWith(): array {
        return [
            'elevatorMgmt' => $this->elevatorMgmt
        ];
    }
}
