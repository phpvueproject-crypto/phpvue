<?php

namespace App\Events;

use App\Models\MicroOrganism;
use App\Models\RemoteManagementSystemStatus;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RemoteManagementSystemStatusUpdated implements ShouldBroadcastNow {
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
