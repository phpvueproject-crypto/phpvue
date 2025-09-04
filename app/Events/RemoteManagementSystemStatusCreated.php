<?php

namespace App\Events;

use App\Models\RemoteManagementSystemStatus;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class RemoteManagementSystemStatusCreated implements ShouldBroadcastNow {
    private RemoteManagementSystemStatus $remoteManagementSystemStatus;

    public function __construct(RemoteManagementSystemStatus $remoteManagementSystemStatus) {
        $this->remoteManagementSystemStatus = $remoteManagementSystemStatus;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('remoteManagementSystemStatuses')
        ];
    }

    public function broadcastWith(): array {
        $this->remoteManagementSystemStatus = $this->remoteManagementSystemStatus->load([
            'location.map',
            'microOrganisms',
            'particles',
            'gasSamplings'
        ]);

        return [
            'remoteManagementSystemStatus' => $this->remoteManagementSystemStatus->toArray()
        ];
    }
}
