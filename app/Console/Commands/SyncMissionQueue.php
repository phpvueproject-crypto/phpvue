<?php

namespace App\Console\Commands;

use App\Events\MissionQueueCreated;
use App\Events\MissionQueueUpdated;
use App\Models\MissionQueue;
use App\Repositories\MirRepository;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use function app\getCarbon;

class SyncMissionQueue extends Command {
    protected $signature = 'missionQueue:sync {--continuous : 是否持續同步}';
    private MirRepository $mir;

    public function __construct() {
        parent::__construct();
        $this->mir = new MirRepository();
    }

    /**
     * @throws GuzzleException
     */
    public function handle(): void {
        if($this->option('continuous')) {
            $this->continuousSync();
        } else {
            $this->syncOnce();
        }
    }

    /**
     * @throws GuzzleException
     */
    protected function syncOnce(): void {
        $mirDomain = $this->mir->getHost();
        if(!$mirDomain) {
            sleep(5);
            return;
        }

        $device = $this->mir->getDevice();
        $mirStatus = $device->mirStatus;

        $missionQueueIds = [];
        $missionQueuesData = collect($this->mir->getMissionQueues());
        $this->call('mission:sync', ['--continuous' => false]);
        foreach($missionQueuesData as $missionQueueData) {
            $queueId = $missionQueueData['id'];
            $missionQueue = MissionQueue::withTrashed()->find($queueId);
            if(!$missionQueue) {
                $missionQueue = new MissionQueue();
                $missionQueue->id = $queueId;
                $missionQueue->state = $missionQueueData['state'];

                $detail = $this->mir->getMissionQueue($queueId);
                $missionQueue->mission_id = $detail['mission_id'];
                $missionQueue->started = getCarbon($detail['started']);
                $mirStatus = $mirStatus->refresh();
                $missionQueue->start_location_id = $mirStatus->location_id;
                $missionQueue->save();
                event(new MissionQueueCreated($missionQueue));
            } else if($missionQueueData['state'] == 'Executing' || $missionQueueData['state'] != $missionQueue->state || !$missionQueue->started) {
                $missionQueue->state = $missionQueueData['state'];
                $detail = $this->mir->getMissionQueue($queueId);
                $missionQueue->started = getCarbon($detail['started']);
                $missionQueue->finished = getCarbon($detail['finished']);
                $missionQueue->save();
                event(new MissionQueueUpdated($missionQueue));
            }

            $missionQueueIds[] = $queueId;
        }
        MissionQueue::whereNotIn('id', $missionQueueIds)->delete();
        $this->info('佇列已同步');
    }

    public function continuousSync(): void {
        while(true) {
            $this->syncOnce();
            $this->info('持續同步佇列...');
            sleep(5 * 60);
        }
    }
}
