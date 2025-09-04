<?php

namespace App\Events;

use App\Models\Device;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class DeviceUpdated implements ShouldBroadcastNow {
    private Device $device;

    public function __construct(Device $device) {
        $this->device = $device;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('devices'),
            new PrivateChannel('mirStatuses')
        ];
    }

    public function broadcastWith(): array {
        $this->device = $this->device->refresh();
        return [
            'device' => $this->device->load([
                'mirStatus.location',
                'mirStatus.map',
                'mirStatus.missionQueue.mission',
                'mirStatus.vehicleErrorType'
            ])->toArray()
        ];
    }
}
