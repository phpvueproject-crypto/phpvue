<?php

namespace App\Events;

use App\Models\DoorMgmt;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class DoorMgmtUpdated implements ShouldBroadcastNow {
    private DoorMgmt $doorMgmt;

    public function __construct(DoorMgmt $doorMgmt) {
        $this->doorMgmt = $doorMgmt;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('doorMgmts'),
            new PrivateChannel("edges.{$this->doorMgmt->edge_id}")
        ];
    }

    public function broadcastWith(): array {
        return [
            'doorMgmt' => $this->doorMgmt
        ];
    }
}
