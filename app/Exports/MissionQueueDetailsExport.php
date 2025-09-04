<?php

namespace App\Exports;

use App\Models\CustomBuilder;
use App\Models\MissionQueue;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MissionQueueDetailsExport implements ShouldAutoSize, WithMultipleSheets {
    use Exportable;

    private MissionQueue|CustomBuilder $missionQueues;
    private array $selectCols = [];

    public function __construct(Carbon $startDate = null, Carbon $endDate = null) {
        $missionQueues = MissionQueue::query();
        $missionQueues = $missionQueues->orderBy(
            'mission_queues.started'
        );
        if($startDate && !$endDate) {
            $missionQueues = $missionQueues->where('mission_queues.started', '>=', $startDate);
        } else if(!$startDate && $endDate) {
            $missionQueues = $missionQueues->where('mission_queues.started', '<=', $endDate);
        } else {
            $missionQueues = $missionQueues->whereBetween('mission_queues.started', [$startDate, $endDate]);
        }
        $this->missionQueues = $missionQueues;
    }

    public function sheets(): array {
        $sheets = [];
        /** @var MissionQueue $missionQueue */
        $missionQueues = $this->missionQueues->get();
        foreach($missionQueues as $missionQueue) {
            $sheets[] = new RemoteManagementSystemStatusesExport($missionQueue->id);
        }

        if($missionQueues->count() <= 0) {
            $sheets[] = new RemoteManagementSystemStatusesExport(null);
        }
        return $sheets;
    }
}
