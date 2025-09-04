<?php

namespace App\Events;

use App\Models\MissionQueue;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MissionQueueUpdated implements ShouldBroadcastNow {
    private MissionQueue $missionQueue;

    public function __construct(MissionQueue $missionQueue) {
        $this->missionQueue = $missionQueue;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('remoteManagementSystemStatuses')
        ];
    }

    public function broadcastWith(): array {
        $this->missionQueue = $this->missionQueue->load([
            'startLocation.map',
            'endLocation.map'
        ]);

        return [
            'missionQueue' => $this->missionQueue->toArray()
        ];
    }
}
