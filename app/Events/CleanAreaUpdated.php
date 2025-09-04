<?php

namespace App\Events;

use App\Models\CleanArea;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class CleanAreaUpdated implements ShouldBroadcastNow {
    private CleanArea $cleanArea;

    public function __construct(CleanArea $cleanArea) {
        $this->cleanArea = $cleanArea;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('cleanAreas')
        ];
    }

    public function broadcastWith(): array {
        return [
            'cleanArea' => $this->cleanArea
        ];
    }
}
