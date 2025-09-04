<?php

namespace App\Events;

use App\Models\VehicleStatus;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class VehicleStatusUpdated implements ShouldBroadcastNow {
    private VehicleStatus $vehicleStatus;

    public function __construct(VehicleStatus $vehicleStatus) {
        $this->vehicleStatus = $vehicleStatus;
    }


    public function broadcastOn(): array {
        return [
            new PrivateChannel("vehicleMgmts.{$this->vehicleStatus->vehicle_id}"),
            new PrivateChannel('vehicleMgmts')
        ];
    }

    public function broadcastWith(): array {
        return [
            'vehicleStatus' => $this->vehicleStatus
        ];
    }
}
