<?php

namespace App\Console\Commands;

use App\Events\RemoteManagementSystemStatusUpdated;
use App\Models\GasSampling;
use App\Models\RemoteManagementSystemStatus;
use App\Repositories\MirRepository;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class GasSamplingReceiver extends Command {
    protected $signature = 'gas:sampling-fetch {remote_management_system_status_id}';

    /**
     * @throws GuzzleException
     */
    public function handle(): void {
        $mir = new MirRepository();
//        $oldValue = Cache::get('R2');
//        $value = $mir->getRegister(2);
//        Cache::put('R2', $value);
//        if($value != $oldValue) {
        GasSampling::whereIsLatest(1)->update([
            'is_latest' => 0
        ]);
        $device = $mir->getDevice();
        $mirStatus = $device->mirStatus;
        if(!$mirStatus || !$mirStatus->location_id) {
            return;
        }
        $remoteManagementSystemStatusId = $this->argument('remote_management_system_status_id');
        for($i = 1; $i <= 5; $i++) {
            sleep(60);
            GasSampling::updateOrCreate([
                'remote_management_system_status_id' => $remoteManagementSystemStatusId,
                'second_mark'                        => 60 * $i
            ], [
                'location_id'       => $mirStatus->location_id,
                'average_volume'    => $mir->getRegister(101),
                'cumulative_volume' => $mir->getRegister(102),
                'is_latest'         => 1
            ]);
            $remoteManagementSystemStatus = RemoteManagementSystemStatus::find($remoteManagementSystemStatusId);
            event(new RemoteManagementSystemStatusUpdated($remoteManagementSystemStatus));
        }
//        }
    }
}
