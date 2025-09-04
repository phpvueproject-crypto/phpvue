<?php

namespace App\Events;

use App\Models\Location;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class LocationUpdated implements ShouldBroadcastNow {
    private Location $location;

    public function __construct(Location $location) {
        $this->location = $location;
    }

    public function broadcastOn(): PrivateChannel {
        return new PrivateChannel('locations');
    }

    public function broadcastWith(): array {
        return [
            'location' => $this->location
        ];
    }
}
