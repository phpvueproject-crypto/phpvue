<?php

namespace App\Events;

use App\Models\ProjectDeploy;
use App\Models\VehicleMgmt;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Collection;

class ProjectDeployUpdated implements ShouldBroadcastNow {
    private Collection $vehicleMgmts;
    private ProjectDeploy $projectDeploy;

    public function __construct(ProjectDeploy $projectDeploy) {
        $this->vehicleMgmts = VehicleMgmt::with('vehicleStatus')->orderBy('vehicle_id')->get();
        $this->projectDeploy = $projectDeploy;
    }

    public function broadcastOn(): array {
        $projectName = str_replace('-', '', $this->projectDeploy->project_name);
        return [
            new PrivateChannel("projectDeploys.{$projectName}")
        ];
    }

    public function broadcastWith(): array {
        return [
            'projectDeploy' => $this->projectDeploy,
            'vehicleMgmts'  => $this->vehicleMgmts->toArray()
        ];
    }
}
