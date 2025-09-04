<?php

namespace App\Events;

use App\Models\GasSampling;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class GasSamplingCreated implements ShouldBroadcastNow {
    private GasSampling $gasSampling;

    public function __construct(GasSampling $gasSampling) {
        $this->gasSampling = $gasSampling;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('locations')
        ];
    }

    public function broadcastWith(): array {
        return [
            'gasSampling' => $this->gasSampling->toArray()
        ];
    }
}
