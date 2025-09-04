<?php

namespace App\Events;

use App\Models\MirStatus;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MirStatusUpdated implements ShouldBroadcastNow {
    private MirStatus $mirStatus;

    public function __construct(MirStatus $mirStatus) {
        $this->mirStatus = $mirStatus;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel("devices.{$this->mirStatus->device_id}"),
            new PrivateChannel('mirStatuses'),
            new PrivateChannel('remoteManagementSystemStatuses')
        ];
    }

    public function broadcastWith(): array {
        $this->mirStatus = $this->mirStatus->refresh();
        $this->mirStatus = $this->mirStatus->load([
            'location',
            'missionQueue.mission',
            'map',
            'vehicleErrorType'
        ]);

        return [
            'mirStatus'  => $this->mirStatus->toArray(),
            'pagination' => [
                'last_page' => ceil(MirStatus::whereDeviceId($this->mirStatus->device_id)->count() / 10)
            ]
        ];
    }
}
