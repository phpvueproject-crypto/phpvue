<?php

namespace App\Console\Commands;

use App\Events\MirStatusUpdated;
use App\Events\MissionQueueUpdated;
use App\Events\RemoteManagementSystemStatusCreated;
use App\Events\RemoteManagementSystemStatusUpdated;
use App\Jobs\FetchAndStoreGasSamplingJob;
use App\Models\Device;
use App\Models\MirStatus;
use App\Models\Project;
use App\Models\RemoteManagementSystemStatus;
use App\Repositories\MapRepository;
use App\Repositories\MirRepository;
use App\Services\MissionService;
use Cache;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Log;
use function app\getClosestLocation;

class ProcessStatusChangeListener extends Command {
    use DispatchesJobs;

    protected $signature = 'process:monitor-status';
    private MissionService $missionService;

    public function __construct() {
        $this->missionService = new MissionService();
        parent::__construct();
    }

    /**
     * @throws GuzzleException
     */
    public function handle(): void {
        $mir = new MirRepository();
        $mapRepository = new MapRepository();
        $mirDomain = $mir->getHost();
        if(!$mirDomain) {
            sleep(5);
            return;
        }

        while(true) {
            sleep(1);

            $date = Carbon::now();
            $r110 = (int)(($date->format('Y') - 1911) . $date->format('md'));
            $mir->postRegister(110, $r110);
            $r111 = (int)$date->format('His');
            $mir->postRegister(111, $r111);

            $r4Value = $mir->getRegister(4);
            $r5Value = $mir->getRegister(5);
            $oldR5Value = Cache::get('R5');
            $r6Value = $mir->getRegister(6);
            $oldR6Value = Cache::get('R6');
            /** @var Device $device */
            $device = $mir->getDevice();
            $mirStatus = $device->mirStatus;
            if(!$mirStatus) {
                continue;
            }
            $mirStatus = $mirStatus->refresh();
            if($mirStatus->state_id != 5 && $mirStatus->state_id != 11) {
                continue;
            }

            $project = Project::whereIsDeploy(1)->first();
            $mapRepository->getAllPositions($mirStatus->map_id, $project);
            $position = json_decode($mirStatus->position, true);
            $location = getClosestLocation($position['x'], $position['y'], $mirStatus->map_id);
            $mirStatus->location_id = $location?->id;
            $mirStatus->save();

            $startTime = $this->getStartTime($mir, $r4Value);
            if(!$startTime) {
                continue;
            }

            /** @var RemoteManagementSystemStatus $remoteManagementSystemStatus */
            $remoteManagementSystemStatus = RemoteManagementSystemStatus::where([
                'sampling_point'   => $r4Value,
                'mission_queue_id' => $mirStatus->mission_queue_id
            ])->first();
            if(!$remoteManagementSystemStatus) {
                $remoteManagementSystemStatus = $this->initRemoteManagementSystemStatus(
                    $mirStatus, $startTime, $r4Value
                );
            }

            $remoteManagementSystemStatus->procedure_step = $r5Value;
            if($r5Value == 1 || $r5Value == 2) {
                $remoteManagementSystemStatus->status_A_time = $mir->getRegister(30 + $r5Value);
                if($r5Value == 1) {
                    $remoteManagementSystemStatus->location_id = null;
                } else {
                    $remoteManagementSystemStatus->location_id = $mirStatus->location_id;
                }
                $remoteManagementSystemStatus->save();
                $mirStatus->current_status = 'A';
                $mirStatus->save();
            } else if($r5Value == 3 || $r5Value == 4) {
                if($oldR5Value != $r5Value) {
                    $this->call('sampling:check-microorganism', [
                        'remote_management_system_status_id' => $remoteManagementSystemStatus->id
                    ]);
                }
                $remoteManagementSystemStatus->status_B_time = $mir->getRegister(30 + $r5Value);
                $remoteManagementSystemStatus->location_id = $mirStatus->location_id;
                $remoteManagementSystemStatus->save();
                $mirStatus->current_status = 'B';
                $mirStatus->save();
            } else if($r5Value == 5 || $r5Value == 6) {
                $remoteManagementSystemStatus->status_C_time = $mir->getRegister(30 + $r5Value);
                $remoteManagementSystemStatus->location_id = $mirStatus->location_id;
                $remoteManagementSystemStatus->save();
                $mirStatus->current_status = 'C';
                $mirStatus->save();
            } else if($r5Value == 7 || $r5Value == 8) {
                $remoteManagementSystemStatus->status_D_time = $mir->getRegister(30 + $r5Value);
                $remoteManagementSystemStatus->location_id = $mirStatus->location_id;
                $remoteManagementSystemStatus->save();
                $mirStatus->current_status = 'D';
                $mirStatus->save();
                if($r5Value == 7) {
                    $r2Value = $mir->getRegister(2);
                    if($r2Value == 12) {
                        $this->dispatch(new FetchAndStoreGasSamplingJob($remoteManagementSystemStatus->id));
                    }
                    $remoteManagementSystemStatus->r2_value = $r2Value;
                    $remoteManagementSystemStatus->save();
                }
            } else if($r5Value == 9 || $r5Value == 10) {
                $remoteManagementSystemStatus->status_E_time = $mir->getRegister(30 + $r5Value);
                $remoteManagementSystemStatus->location_id = $mirStatus->location_id;
                $remoteManagementSystemStatus->save();
                $mirStatus->current_status = 'E';
                $mirStatus->save();
            } else if($r5Value == 11 || $r5Value == 12) {
                $remoteManagementSystemStatus->status_F_time = $mir->getRegister(30 + $r5Value);
                if($r5Value == 12) {
                    $missionQueue = $remoteManagementSystemStatus->missionQueue;
                    if($missionQueue) {
                        $oldEndLocationId = $missionQueue->end_location_id;
                        $missionQueue->end_location_id = $mirStatus->location_id;
                        $missionQueue->save();
                        if(!$oldEndLocationId && $mirStatus->location_id) {
                            event(new MissionQueueUpdated($missionQueue));
                        }
                        $missionQueue->end_location_id = $mirStatus->location_id;
                    }
                }
                $remoteManagementSystemStatus->save();
                $mirStatus->current_status = 'F';
                $mirStatus->save();
            }

            $r3Value = $mir->getRegister(3);
            Log::info('$r3Value:' . $r3Value);
            if($r3Value == 20) {
                $this->call('sampling:check-particle', [
                    'remote_management_system_status_id' => $remoteManagementSystemStatus->id
                ]);
            }
            $remoteManagementSystemStatus->r3_value = $r3Value;
            $remoteManagementSystemStatus->save();

            // ✅ 就在這裡插入：
            $this->updateSamplingResult($mir, $remoteManagementSystemStatus, $r4Value);

            event(new MirStatusUpdated($mirStatus));
            event(new RemoteManagementSystemStatusUpdated($remoteManagementSystemStatus));
            if($oldR6Value != $r6Value) {
                $mir->postRegister(7);
            }
            Cache::put('R4', $r4Value);
            Cache::put('R5', $r5Value);
            Cache::put('R6', $r6Value);
        }
    }

    /**
     * @throws GuzzleException
     */
    private function getStartTime(MirRepository $mir, int $r4Value): Carbon|null {
        $currentPoint = $r4Value; // 取得目前點位編號
        if($currentPoint < 1 || $currentPoint > 10) {
            return null;
        }

        $registerAddress = 49 + $currentPoint; // 對應的寄存器地址：R50 ~ R59
        $startTime = $mir->getRegister($registerAddress); // 取得對應的起始時間
        return $startTime ? Carbon::createFromFormat(
            'His', $startTime
        ) : Carbon::now();
    }

    /**
     * @throws GuzzleException
     */
    private function initRemoteManagementSystemStatus(
        MirStatus $mirStatus, Carbon $startTime, int $r4Value
    ): ?RemoteManagementSystemStatus {
        RemoteManagementSystemStatus::whereIsLatest(1)->update([
            'is_latest' => 0
        ]);

        $remoteManagementSystemStatus = new RemoteManagementSystemStatus();
        $remoteManagementSystemStatus->mission_queue_id = $mirStatus->mission_queue_id;
        $remoteManagementSystemStatus->sampling_point = $r4Value;
        $remoteManagementSystemStatus->start_time = $startTime;
        $remoteManagementSystemStatus->is_latest = 1;
        $remoteManagementSystemStatus->save();
        event(new RemoteManagementSystemStatusCreated($remoteManagementSystemStatus));

        return $remoteManagementSystemStatus;
    }


    /**
     * @throws GuzzleException
     */
    private function updateSamplingResult(
        MirRepository $mir, RemoteManagementSystemStatus $status, int $r4Value
    ): void {
        $currentPoint = $r4Value; // R4：目前正在處理的採樣點

        if(!is_numeric($currentPoint) || $currentPoint < 1 || $currentPoint > 10) {
            return; // 非合法點位
        }

        $register = 59 + $currentPoint; // 對應 R60 ~ R69
        $result = $mir->getRegister($register); // 取得結果：-1, 0, 1
        if(!in_array($result, [-1, 0, 1], true)) {
            return; // 非有效狀態
        }

        // 寫入 result 欄位：0=進行中, 1=成功, -1=失敗
        $status->result = $result;
        $status->save();
    }
}
