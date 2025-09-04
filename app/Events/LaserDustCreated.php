<?php

namespace App\Events;

use App\Models\LaserDust;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class LaserDustCreated implements ShouldBroadcastNow {
    private LaserDust $laserDust;

    public function __construct(LaserDust $laserDust) {
        $this->laserDust = $laserDust;
    }

    public function broadcastOn(): PrivateChannel {
        return new PrivateChannel('laserDusts');
    }

    public function broadcastWith(): array {
        return [
            'laserDust' => $this->laserDust
        ];
    }
}
