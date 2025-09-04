<?php

namespace App\Events;

use App\Models\ElevatorMgmt;
use App\Models\VehicleMgmt;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class VehicleMgmtDeleted implements ShouldBroadcastNow {
    private VehicleMgmt $vehicleMgmt;

    public function __construct(VehicleMgmt $vehicleMgmt) {
        $this->vehicleMgmt = $vehicleMgmt;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel("vehicleMgmts.{$this->vehicleMgmt->vehicle_id}"),
            new PrivateChannel('vehicleMgmts')
        ];
    }

    public function broadcastWith(): array {
        return [
            'vehicleMgmt' => $this->vehicleMgmt
        ];
    }
}
