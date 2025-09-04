<?php

namespace App\Http\Controllers;

use App\Exports\RemoteManagementSystemStatusesExport;
use App\Models\Map;
use App\Models\MicroOrganism;
use App\Models\MirStatus;
use App\Models\MissionQueue;
use App\Models\RemoteManagementSystemStatus;
use Carbon\Carbon;
use Excel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RemoteManagementSystemStatusController extends Controller {
    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function index(Request $request): Response|BinaryFileResponse|array|Application|ResponseFactory {
        $remoteManagementSystemStatuses = new RemoteManagementSystemStatus();
        $mapId = $request->input('map_id');
        if($mapId) {
            $missionQueueId = RemoteManagementSystemStatus::whereRelation('location', 'map_id', $mapId)->max('mission_queue_id');
        } else {
            $mirStatus = MirStatus::first();
            $mapId = $mirStatus->map_id;
            $missionQueueId = $mirStatus->mission_queue_id;
            if(!$missionQueueId) {
                $missionQueueId = RemoteManagementSystemStatus::max('mission_queue_id');
            }
        }
        $remoteManagementSystemStatuses = $remoteManagementSystemStatuses->where('mission_queue_id', $missionQueueId);

        $format = null;
        if($request->path() == 'remoteManagementSystemStatuses.xlsx') {
            $format = 'xlsx';
        }
        if($format != 'xlsx') {
            $remoteManagementSystemStatuses = $remoteManagementSystemStatuses->with([
                'location.map',
                'microOrganisms',
                'particles',
                'gasSamplings'
            ])->orderByDesc('remote_management_system_statuses.created_at')->take(10)->get()->reverse()->values();

            return [
                'status' => 0,
                'data'   => [
                    'remoteManagementSystemStatuses' => $remoteManagementSystemStatuses,
                    'missionQueue'                   => MissionQueue::with([
                        'startLocation.map',
                        'endLocation.map'
                    ])->find($missionQueueId),
                    'remoteManagementSystemStatus'   => RemoteManagementSystemStatus::with([
                        'location.map',
                        'microOrganisms',
                        'particles',
                        'gasSamplings'
                    ])->orderByDesc('id')->first()
                ]
            ];
        } else {
            $map = Map::find($mapId);
            if($map) {
                $title = str_replace('/', '', $map->region ?? '其他');
            } else {
                $title = '全部房間';
            }
            return Excel::download(
                new RemoteManagementSystemStatusesExport($missionQueueId), "$title.xlsx"
            );
        }
    }
}
