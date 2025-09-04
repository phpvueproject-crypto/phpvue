<?php

namespace App\Events;

use App\Models\CleanStatus;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class CleanStatusUpdated implements ShouldBroadcastNow {
    private CleanStatus $cleanStatus;

    public function __construct(CleanStatus $cleanStatus) {
        $this->cleanStatus = $cleanStatus;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('cleanStatuses')
        ];
    }

    public function broadcastWith(): array {
        return [
            'cleanStatus' => $this->cleanStatus
        ];
    }
}
