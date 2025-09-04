<?php

namespace App\Events;

use App\Models\VehicleMgmt;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class VehicleMgmtCreated implements ShouldBroadcastNow {
    private VehicleMgmt $vehicleMgmt;

    public function __construct(VehicleMgmt $vehicleMgmt) {
        $this->vehicleMgmt = $vehicleMgmt;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('vehicleMgmts')
        ];
    }

    public function broadcastWith(): array {
        $this->vehicleMgmt = $this->vehicleMgmt->load('vehicleStatus');
        return [
            'vehicleMgmt' => $this->vehicleMgmt
        ];
    }
}
