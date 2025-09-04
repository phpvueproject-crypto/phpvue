<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class SweepDeleted implements ShouldBroadcastNow {
    private array $sweep;

    public function __construct(array $sweep) {
        $this->sweep = $sweep;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('sweeps')
        ];
    }

    public function broadcastWith(): array {
        return [
            'sweep' => $this->sweep
        ];
    }
}
