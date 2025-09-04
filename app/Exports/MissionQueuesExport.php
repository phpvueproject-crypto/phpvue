<?php

namespace App\Exports;

use App\Models\CustomBuilder;
use App\Models\MissionQueue;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Generator;
use Maatwebsite\Excel\Events\AfterSheet;
use Str;

class MissionQueuesExport implements FromGenerator, WithHeadings, ShouldAutoSize, WithEvents {
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
        $missionQueues = $missionQueues->whereIn('state', ['Aborted', 'Done', 'Failed'])->orderByDesc('mission_queues.id');
        $this->missionQueues = $missionQueues;
    }

    public function headings(): array {
        $this->selectCols = [
            '時間'     => "TO_CHAR(mission_queues.started, 'YYYY-MM-DD HH24:MI:SS') as started",
            '名稱'     => 'missions.name',
            '結果'     => 'mission_queues.state',
            '採樣點'   => 'COUNT(remote_management_system_statuses.id) AS remote_management_system_statuses_count',
            '總計時數' => 'SUM(remote_management_system_statuses.total_time) AS remote_management_system_statuses_total_time',
            '備註'     => 'mission_queues.remark'
        ];
        return array_keys($this->selectCols);
    }

    public function generator(): Generator {
        $values = array_values($this->selectCols);
        $selectColSql = implode(',', $values);
        /** @var MissionQueue $missionQueues */
        $missionQueues = $this->missionQueues->selectRaw($selectColSql);
        $values = array_values($this->selectCols);
        foreach($values as $value) {
            if(Str::contains($value, 'remote_management_system_statuses.')) {
                $missionQueues = $missionQueues->safeLeftJoin(
                    'remote_management_system_statuses', function(JoinClause $query) {
                    $query->on('remote_management_system_statuses.mission_queue_id', '=', 'mission_queues.id');
                }
                );
            }

            if(Str::contains($value, 'missions.')) {
                $missionQueues = $missionQueues->safeJoin(
                    'missions', function(JoinClause $query) {
                    $query->on('missions.guid', '=', 'mission_queues.mission_id');
                }
                );
            }
        }
        $missionQueues = $missionQueues->groupBy(
            'mission_queues.id',
            'mission_queues.started',
            'missions.name',
            'mission_queues.state',
            'mission_queues.remark'
        );
        foreach($missionQueues->cursor() as $r) {
            yield $r;
        }
    }

    public function registerEvents(): array {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:V1000')->getAlignment()->setHorizontal('center');
                $event->sheet->getDelegate()->getStyle('A1:V1000')->getAlignment()->setVertical('center');
            },
        ];
    }
}
