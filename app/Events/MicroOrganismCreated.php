<?php

namespace App\Events;

use App\Models\MicroOrganism;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MicroOrganismCreated implements ShouldBroadcastNow {
    private MicroOrganism $microOrganism;

    public function __construct(MicroOrganism $microOrganism) {
        $this->microOrganism = $microOrganism;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('microOrganisms')
        ];
    }

    public function broadcastWith(): array {
        $this->microOrganism = $this->microOrganism->load([
            'regionMgmt',
            'location.roomEnvironment'
        ]);

        return [
            'microOrganism' => $this->microOrganism->toArray()
        ];
    }
}
