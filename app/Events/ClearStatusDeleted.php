<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ClearStatusDeleted implements ShouldBroadcastNow {
    private array $clearStatus;

    public function __construct(array $clearStatus) {
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
