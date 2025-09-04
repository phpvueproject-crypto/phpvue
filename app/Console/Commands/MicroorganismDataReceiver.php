<?php

namespace App\Console\Commands;

use App\Events\RemoteManagementSystemStatusUpdated;
use App\Models\MicroOrganism;
use App\Models\RemoteManagementSystemStatus;
use App\Repositories\MirRepository;
use App\Services\MicroOrganismService;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class MicroorganismDataReceiver extends Command {
    protected $signature = 'sampling:check-microorganism {remote_management_system_status_id}';

    public function __construct(protected MicroOrganismService $microOrganismService) {
        parent::__construct();
    }

    /**
     * @throws GuzzleException
     */
    public function handle(): void {
        $mir = new MirRepository();
        $binaryString = '';
        for($i = 17; $i >= 11; $i--) {
            $binaryString .= $this->getQrCodeStrArr($mir->getRegister($i));
        }

        $device = $mir->getDevice();
        $mirStatus = $device->mirStatus;

        if(!$mirStatus || !$mirStatus->location) {
            return;
        }
        $location = $mirStatus->location;
        $location->bar_code = $binaryString ?: null;
        $location->save();
        $microOrganismKinds = ['falling', 'suspended', 'contact'];
        $now = Carbon::now();
        $remoteManagementSystemStatusId = $this->argument('remote_management_system_status_id');
        foreach($microOrganismKinds as $microOrganismKind) {
            MicroOrganism::updateOrCreate([
                'source'                             => 2,
                'organism_kind'                      => $microOrganismKind,
                'remote_management_system_status_id' => $remoteManagementSystemStatusId
            ], [
                'room_name'   => $mirStatus->roomEnvironment?->room_name,
                'device_name' => $location->device_name,
                'location_id' => $location->id,
                'Time'        => $now,
                'x'           => $location->x,
                'y'           => $location->y,
                'bar_code'    => $binaryString ?: null
            ]);
        }
        $remoteManagementSystemStatus = RemoteManagementSystemStatus::find($remoteManagementSystemStatusId);
        event(new RemoteManagementSystemStatusUpdated($remoteManagementSystemStatus));
    }

    private function getQrCodeStrArr($number): string {
        $reversed_number = strrev($number);

        // 初始化結果陣列
        $result = [];

        // 每三碼切割
        for($i = 0; $i < strlen($reversed_number); $i += 3) {
            $chunk = substr($reversed_number, $i, 3);
            $chunk = strrev(str_pad($chunk, 3, '0', STR_PAD_RIGHT)); // 反轉並補足三位
            $result[] = $chunk;
        }

        // 最後反轉結果陣列以符合所需的順序
        $result = array_reverse($result);
        $binaryString = '';
        foreach($result as $r) {
            $asciiValue = (int)$r; // 將數字 65 轉換為 ASCII 字符
            $binaryString .= chr($asciiValue);
        }

        return $binaryString;
    }
}
