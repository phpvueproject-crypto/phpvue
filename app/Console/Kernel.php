<?php

namespace App\Console;

use App\Console\Commands\CheckConnected;
use App\Console\Commands\ContinuousSyncStatus;
use App\Console\Commands\MicroorganismDataReceiver;
use App\Console\Commands\ParticleDataReceiver;
use App\Console\Commands\SubscribeToTriggers;
use App\Console\Commands\SyncMap;
use App\Console\Commands\SyncMission;
use App\Console\Commands\SyncMissionQueue;
use App\Models\MissionBooking;
use App\Repositories\MirRepository;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Log;

class Kernel extends ConsoleKernel {
    protected $commands = [
        SubscribeToTriggers::class,
        CheckConnected::class,
        ContinuousSyncStatus::class,
        MicroorganismDataReceiver::class,
        ParticleDataReceiver::class,
        SyncMap::class,
        SyncMission::class,
        SyncMissionQueue::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void {
        // 從資料庫中獲取所有的 crontab 指令
        $missionBookings = MissionBooking::all();
        foreach($missionBookings as $missionBooking) {
            // 將每個 crontab 指令添加到調度器中
            $schedule->call(function() use ($missionBooking) {
                $mir = new MirRepository();
                try {
                    $mir->postMissionQueue($missionBooking->mission_id);
                    $missionBooking->delete();
                } catch(Exception $e) {
                    // 捕獲異常並記錄錯誤
                    Log::error('Error executing mission: ' . $missionBooking->mission_id . ' - ' . $e->getMessage());
                }
            })->cron($missionBooking->schedule);
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
