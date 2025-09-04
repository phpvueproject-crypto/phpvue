<?php

namespace App\Events;

use App\Models\ClearStatus;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ClearStatusCreated implements ShouldBroadcastNow {
    private ClearStatus $clearStatus;

    public function __construct(ClearStatus $clearStatus) {
        $this->clearStatus = $clearStatus;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('clearStatuses')
        ];
    }

    public function broadcastWith(): array {
        return [
            'clearStatus' => $this->clearStatus
        ];
    }
}
