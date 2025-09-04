<?php

namespace App\Exports;

use App\Models\CustomBuilder;
use App\Models\MissionQueue;
use App\Models\RemoteManagementSystemStatus;
use Illuminate\Database\Query\JoinClause;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Generator;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Str;

class RemoteManagementSystemStatusesExport implements FromGenerator, WithHeadings, WithTitle, ShouldAutoSize, WithEvents {
    use Exportable;

    private RemoteManagementSystemStatus|CustomBuilder $remoteManagementSystemStatuses;
    private array $selectCols = [];
    private ?int $missionQueueId;

    public function __construct(?int $missionQueueId) {
        $remoteManagementSystemStatuses = RemoteManagementSystemStatus::query();
        $remoteManagementSystemStatuses = $remoteManagementSystemStatuses->orderBy(
            'remote_management_system_statuses.start_time'
        );
        $remoteManagementSystemStatuses = $remoteManagementSystemStatuses->where('mission_queue_id', $missionQueueId);
        $this->remoteManagementSystemStatuses = $remoteManagementSystemStatuses;
        $this->missionQueueId = $missionQueueId;
    }

    public function headings(): array {
        $this->selectCols = [
            '時間'              => "TO_CHAR(remote_management_system_statuses.start_time, 'YYYY/MM/DD HH24:MI:SS') as start_time",
            '動作'              => 'location.device_name',
            '結果'              => "case remote_management_system_statuses.result when -1 then '失敗' when 0 then '進行中' else '成功' end as result",
            '房間名稱'          => 'location.room',
            '地圖'              => 'maps.name',
            '座標 (x, y)'       => "CONCAT('(', location.x, ',', location.y, ')')  AS position",
            '條碼'              => 'location.bar_code',
            '總計時數'          => 'remote_management_system_statuses.total_time',
            'AMR到達點(A)'      => 'remote_management_system_statuses."status_A_time"',
            '培養皿掃碼(B)'     => 'remote_management_system_statuses."status_B_time"',
            '置入空氣採樣口(C)' => 'remote_management_system_statuses."status_C_time"',
            '空氣採樣開始(D)'   => 'remote_management_system_statuses."status_D_time"',
            '培養皿歸位(E)'     => 'remote_management_system_statuses."status_E_time"',
            'AMR歸位(F)'        => 'remote_management_system_statuses."status_F_time"'
        ];
        return array_keys($this->selectCols);
    }

    public function generator(): Generator {
        $values = array_values($this->selectCols);
        $selectColSql = implode(',', $values);
        /** @var RemoteManagementSystemStatus $remoteManagementSystemStatuses */
        $remoteManagementSystemStatuses = $this->remoteManagementSystemStatuses->selectRaw($selectColSql);
        $values = array_values($this->selectCols);
        foreach($values as $value) {
            if(Str::contains($value, 'location.')) {
                $remoteManagementSystemStatuses = $remoteManagementSystemStatuses->safeLeftJoin(
                    'location', function(JoinClause $query) {
                    $query->on('location.id', '=', 'remote_management_system_statuses.location_id');
                }
                );
            }

            if(Str::contains($value, 'missions.')) {
                $remoteManagementSystemStatuses = $remoteManagementSystemStatuses->safeJoin(
                    'mission_queues', function(JoinClause $query) {
                    $query->on('mission_queues.id', '=', 'remote_management_system_statuses.mission_queue_id');
                }
                );

                $remoteManagementSystemStatuses = $remoteManagementSystemStatuses->safeJoin(
                    'missions', function(JoinClause $query) {
                    $query->on('missions.guid', '=', 'mission_queues.mission_id');
                }
                );
            }

            if(Str::contains($value, 'maps.')) {
                $remoteManagementSystemStatuses = $remoteManagementSystemStatuses->safeLeftJoin(
                    'maps', function(JoinClause $query) {
                    $query->on('maps.guid', '=', 'location.map_id');
                }
                );
            }
        }
        foreach($remoteManagementSystemStatuses->cursor() as $r) {
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

    public function title(): string {
        if($this->missionQueueId) {
            $missionQueue = MissionQueue::find($this->missionQueueId);
            return $missionQueue->mission?->name . '_' . $missionQueue->started?->format('YmdHis');
        } else {
            return '查無資料';
        }
    }
}
