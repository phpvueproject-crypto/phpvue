<?php

namespace App\Console\Commands;

use App\Events\RemoteManagementSystemStatusUpdated;
use App\Models\Location;
use App\Models\MicroOrganism;
use App\Models\RemoteManagementSystemStatus;
use App\Repositories\MirRepository;
use Cache;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class ParticleDataReceiver extends Command {
    protected $signature = 'sampling:check-particle {remote_management_system_status_id}';

    /**
     * @throws GuzzleException
     */
    public function handle(): void {
        $mir = new MirRepository();
//        $oldValue = Cache::get('R3');
//        $value = $mir->getRegister(3);
//        if($value != $oldValue) {
        $r105Data = $mir->getRegister(105);
        $r106Data = $mir->getRegister(106);

        $device = $mir->getDevice();
        $mirStatus = $device->mirStatus;

        if(!$mirStatus || !$mirStatus->location) {
            return;
        }
        $location = $mirStatus->location;
        $location->save();

        $datetime = Carbon::now();
        $remoteManagementSystemStatusId = $this->argument('remote_management_system_status_id');
        $this->createMicroOrganism($mirStatus->roomEnvironment?->room_name, $location, 'microparticle_dot_5', $r105Data, $datetime, $remoteManagementSystemStatusId);
        $this->createMicroOrganism($mirStatus->roomEnvironment?->room_name, $location, 'microparticle_5', $r106Data, $datetime, $remoteManagementSystemStatusId);
        $remoteManagementSystemStatus = RemoteManagementSystemStatus::find($remoteManagementSystemStatusId);
        event(new RemoteManagementSystemStatusUpdated($remoteManagementSystemStatus));
//        }
//        Cache::put('R3', $value);
    }

    public function createMicroOrganism(?string $roomName, Location $location, string $organismKind, $data, $datetime, $remoteManagementSystemStatusId): void {
        MicroOrganism::updateOrCreate([
            'source'                             => 2,
            'organism_kind'                      => $organismKind,
            'remote_management_system_status_id' => $remoteManagementSystemStatusId
        ], [
            'room_name'      => $roomName,
            'device_name'    => $location->device_name,
            'location_id'    => $location->id,
            'Time'           => $datetime,
            'x'              => $location->x,
            'y'              => $location->y,
            'organism_value' => $data
        ]);
    }
}
