<?php

namespace App\Console\Commands;

use App\Events\DeviceUpdated;
use App\Models\Device;
use App\Repositories\DeviceRepository;
use App\Repositories\MirRepository;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class CheckConnected extends Command {
    protected $signature = 'app:check-vehicle-connection';

    public function handle(): void {
        $mir = new MirRepository();
        $deviceRepository = new DeviceRepository();
        $device = Device::first();
        $init = false;
        while(true) {
            $isConnected = $device->is_connected;
            try {
                $success = $mir->getWifiConnections();
                $device->is_connected = $success ? 1 : 0;
                if($device->is_connected) {
                    $device->connected_at = Carbon::now();
                }
                $device->save();
            } catch(GuzzleException) {
                $device->is_connected = 0;
                $device->save();
            }
            if($isConnected != $device->is_connected || !$init) {
                if($device->is_connected) {
                    $deviceRepository->startAllProcess();
                } else {
                    $deviceRepository->stopAllProcess();
                }
                $init = true;
            }
            event(new DeviceUpdated($device));
            sleep(5);
        }
    }
}
