<?php

namespace App\Events;

use App\Models\Sweep;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class SweepCreated implements ShouldBroadcastNow {
    private Sweep $sweep;

    public function __construct(Sweep $sweep) {
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
