<?php

namespace App\Events;

use App\Models\StationMgmt;
use App\Models\VehicleMgmt;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class StationMgmtUpdated implements ShouldBroadcastNow {
    private StationMgmt $stationMgmt;

    public function __construct(StationMgmt $stationMgmt) {
        $this->stationMgmt = $stationMgmt;
        $this->stationMgmt = $this->stationMgmt->load('stationStatus');
        $vehicleMgmts = VehicleMgmt::get();
        if($this->stationMgmt->stationStatus) {
            $this->stationMgmt->stationStatus->setVehicleMgmts($vehicleMgmts);
        }
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel("vertices.{$this->stationMgmt->vertex_id}"),
            new PrivateChannel('stationMgmts')
        ];
    }

    public function broadcastWith(): array {
        return [
            'stationMgmt' => $this->stationMgmt
        ];
    }
}
