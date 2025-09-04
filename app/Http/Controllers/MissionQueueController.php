<?php

namespace App\Http\Controllers;

use App\Exports\MissionQueueDetailsExport;
use App\Exports\MissionQueuesExport;
use App\Exports\RemoteManagementSystemStatusesExport;
use App\Http\Requests\MissionQueueRequest;
use App\Models\Map;
use App\Models\MissionQueue;
use App\Models\Project;
use App\Repositories\MirRepository;
use Artisan;
use Excel;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MissionQueueController extends Controller {
    private MirRepository $mir;

    public function __construct(MirRepository $mir) {
        $this->mir = $mir;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function index(Request $request): BinaryFileResponse|array {
        $position = null;
        $status = 0;
        $missionQueues = new MissionQueue();
        $name = $request->input('name');
        if($name) {
            $missionQueues = $missionQueues->whereRelation('mission', 'name', 'like', "%$name%");
        }

        try {
            $history = $request->input('history', 1);
            if($history) {
                $states = ['Aborted', 'Done', 'Failed'];
                $refresh = $request->input('refresh', 0);
                if($refresh) {
                    Artisan::call('missionQueue:sync');
                }
            } else {
                $states = ['Executing', 'Pending'];
                Artisan::call('missionQueue:sync');
                $position = $this->mir->getStatus();
            }
        } catch(GuzzleException) {
            $status = config('errors.mir_vehicle_disconnected');
            return [
                'status' => $status
            ];
        }

        $startDate = $request->date('start_date');
        if($startDate) {
            $missionQueues = $missionQueues->whereDate('started', '>=', $startDate);
        }
        $endDate = $request->date('end_date');
        if($endDate) {
            $missionQueues = $missionQueues->whereDate('started', '<=', $endDate);
        }

        $missionQueues = $missionQueues->whereIn('state', $states)->orderByDesc('id')->with([
            'mission',
            'startLocation',
            'endLocation'
        ])->withCount([
            'locations',
            'remoteManagementSystemStatuses'
        ])->withSum(
            'remoteManagementSystemStatuses', 'total_time'
        );
        if($history) {
            if($request->path() != 'missionQueues.xlsx') {
                $pagination = $missionQueues->paginate(
                    20
                );
                $missionQueues = $pagination->items();
                return [
                    'status' => $status,
                    'data'   => [
                        'states'        => $states,
                        'missionQueues' => $missionQueues,
                        'pagination'    => $pagination,
                        'position'      => $position
                    ]
                ];
            } else {
                $title = '歷史採樣任務總覽';
                if($startDate) {
                    $title .= '_' . $startDate->format('Ymd');
                }
                $title .= '_' . $endDate->format('Ymd');
                return Excel::download(
                    new MissionQueuesExport($startDate, $endDate), "$title.xlsx"
                );
            }
        } else {
            $missionQueues = $missionQueues->get();

            return [
                'status' => $status,
                'data'   => [
                    'states'        => $states,
                    'missionQueues' => $missionQueues,
                    'position'      => $position
                ]
            ];
        }
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportDetails(Request $request): BinaryFileResponse|array {
        $title = '歷史採樣任務進度含明細';
        $startDate = $request->date('start_date');
        $endDate = $request->date('end_date');
        if($startDate) {
            $title .= '_' . $startDate->format('Ymd');
        }
        $title .= '_' . $endDate->format('Ymd');
        return Excel::download(
            new MissionQueueDetailsExport($startDate, $endDate), "$title.xlsx"
        );
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function show(Request $request, $id): BinaryFileResponse|array {
        if(!$request->is('missionQueueDetails/*.xlsx')) {
            $missionQueue = MissionQueue::with([
                'locations.map',
                'remoteManagementSystemStatuses.location.map',
                'remoteManagementSystemStatuses',
                'startLocation.map',
                'endLocation.map'
            ])->findOrFail($id);

            return [
                'status' => 0,
                'data'   => [
                    'missionQueue' => $missionQueue
                ]
            ];
        } else {
            $missionQueue = MissionQueue::find($id);
            $region = $missionQueue->mission->region;
            $project = Project::where('is_deploy', 1)->first();
            $map = Map::whereRelation('regionMgmt', 'region', $region)->whereRelation(
                'regionMgmt', 'project_id', $project->id
            )->first();
            if($map) {
                $title = str_replace('/', '', $map->region ?? '其他');
            } else {
                $title = '全部房間';
            }
            return Excel::download(
                new RemoteManagementSystemStatusesExport($id), "$title.xlsx"
            );
        }
    }

    public function store(Request $request): array {
        $missionId = $request->input('mission_id');
        try {
            $success = $this->mir->postMissionQueue($missionId);
            if(!$success) {
                return [
                    'status' => config('errors.fail')
                ];
            }
        } catch(GuzzleException) {
            return [
                'status' => config('errors.mir_vehicle_disconnected')
            ];
        }

        return [
            'status' => 0
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function update(MissionQueueRequest $request, $id): array {
        $stateId = $request->input('state_id');
        if($stateId) {
            $success = $this->mir->putStatus($stateId);
            if(!$success) {
                return [
                    'status' => config('errors.fail')
                ];
            }
        }

        $missionQueue = MissionQueue::find($id);
        $missionQueue->update([
            'remark' => $request->validated('remark')
        ]);

        return [
            'status' => 0
        ];
    }

    /**
     * @throws GuzzleException
     * @throws FileNotFoundException
     */
    public function destroy($id): array {
        $missionQueue = MissionQueue::find($id);
        $missionQueue?->delete();
        $success = $this->mir->deleteMissionQueue($id);
        if(!$success) {
            $code = $this->mir->getStatusCode();
            if($code == 404) {
                return [
                    'status' => config('errors.data_not_found')
                ];
            } else {
                return [
                    'status' => config('errors.fail')
                ];
            }
        }

        return [
            'status' => 0
        ];
    }
}
