<?php

namespace App\Events;

use App\Models\RegionMgmt;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class RegionMgmtUpdated implements ShouldBroadcastNow {
    private RegionMgmt $regionMgmt;

    public function __construct(RegionMgmt $regionMgmt) {
        $this->regionMgmt = $regionMgmt;
        $this->regionMgmt = $this->regionMgmt->load([
            'project.projectDeploy',
            'editUser'
        ]);
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel("projects.{$this->regionMgmt->project_id}"),
            new PrivateChannel("regionMgmts.{$this->regionMgmt->id}")
        ];
    }

    public function broadcastWith(): array {
        return [
            'regionMgmt' => $this->regionMgmt->toArray()
        ];
    }
}
