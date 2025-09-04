<?php

namespace App\Events;

use App\Models\ElevatorMgmt;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ElevatorMgmtVertexCreated implements ShouldBroadcastNow {
    private array $elevatorMgmtVertex;

    public function __construct(array $elevatorMgmtVertex) {
        $this->elevatorMgmtVertex = $elevatorMgmtVertex;
    }

    public function broadcastOn(): PrivateChannel {
        return new PrivateChannel("vertices.{$this->elevatorMgmtVertex['vertex_id']}");
    }

    public function broadcastWith(): array {
        $elevatorMgmt = ElevatorMgmt::with('elevatorStatus')->find($this->elevatorMgmtVertex['elevator_mgmt_elevator_id']);
        return [
            'elevatorMgmtVertex' => [
                'vertex_id'                 => $this->elevatorMgmtVertex['vertex_id'],
                'elevator_mgmt_elevator_id' => $this->elevatorMgmtVertex['elevator_mgmt_elevator_id'],
                'elevator_mgmt'             => $elevatorMgmt
            ]
        ];
    }
}
