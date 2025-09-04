<?php

namespace App\Console\Commands;

use App\Events\MirStatusCreated;
use App\Events\MirStatusUpdated;
use App\Models\Map;
use App\Models\MirStatus;
use App\Models\MissionQueue;
use App\Repositories\MirRepository;
use App\Services\MirStatusService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use function app\getCarbon;

class ContinuousSyncStatus extends Command {
    protected $signature = 'status:sync';

    public function __construct(protected MirStatusService $mirStatusService) {
        parent::__construct();
    }

    /**
     * @throws GuzzleException
     */
    public function handle(): void {
        $mir = new MirRepository();
        $mirDomain = $mir->getHost();
        if(!$mirDomain) {
            sleep(5);
            return;
        }

        while(true) {
            sleep(1);
            $device = $mir->getDevice();
            $device->unsetRelation('mirStatus');
            $device->load('mirStatus');
            $data = $mir->getStatus();
            $map = Map::find($data['map_id']);
            if(!$map || !$map->region_mgmt_id) {
                $this->call('map:sync');
            }
            $missionQueue = MissionQueue::find($data['mission_queue_id']);
            if(!$missionQueue) {
                $this->call('missionQueue:sync');
                $missionQueue = MissionQueue::find($data['mission_queue_id']);
            }

            if($missionQueue) {
                $missionQueueData = $mir->getMissionQueue($missionQueue->id);
                $missionQueue = MissionQueue::where('id', $data['mission_queue_id'])->first();
                $missionQueue->state = $missionQueueData['state'];
                $missionQueue->started = getCarbon($missionQueueData['started']);
                $missionQueue->finished = getCarbon($missionQueueData['finished']);
                $missionQueue->save();
            }

            $mirStatus = $device->mirStatus;
            $insertMode = false;
            if(!$mirStatus) {
                $mirStatus = new MirStatus();
                $insertMode = true;
            }
            $mirStatus->device_id = $device->id;
            $mirStatus->position = json_encode($data['position']);
            $mirStatus->robot_model = $data['robot_model'];
            $mirStatus->mission_text = $data['mission_text'];
            $mirStatus->velocity = json_encode($data['velocity']);
            $mirStatus->battery_percentage = $data['battery_percentage'];
            $mirStatus->map_id = $data['map_id'];
            $mirStatus->state_text = $data['state_text'];
            $mirStatus->state_id = $data['state_id'];
            $mirStatus->mission_queue_id = $missionQueue?->id;
            if(!$mirStatus->mission_queue_id) {
                $missionQueue = MissionQueue::where('state', 'Pending')->orderByDesc('id')->first();
                $mirStatus->mission_queue_id = $missionQueue?->id;
            }
            $mirStatus->vehicle_error_type_id = $mir->getRegister(8);
//            $mirStatus->remaining_petri_count = $mir->getRegister(113);
            $mirStatus->device_time = $this->mirStatusService->toCarbon($mir->getRegister(110), $mir->getRegister(111));
            if(!$mirStatus->vehicle_error_type_id) {
                $mirStatus->vehicle_error_type_id = null;
                $mirStatus->vehicle_error_message = null;
            } else {
                try {
                    $mirStatus->vehicle_error_message = $mir->getErrorReports($mirStatus->vehicle_error_type_id);
                } catch(GuzzleException) {
                    $mirStatus->vehicle_error_message = null;
                }
            }
            $mirStatus->save();
            if($insertMode) {
                event(new MirStatusCreated($mirStatus));
            } else {
                event(new MirStatusUpdated($mirStatus));
            }
        }
    }
}
