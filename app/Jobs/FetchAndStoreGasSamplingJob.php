<?php

namespace App\Jobs;

use Artisan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchAndStoreGasSamplingJob implements ShouldQueue, ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 300; // 秒數，這期間內都不能重複 dispatch
    private int $remoteManagementSystemStatusId;

    public function __construct(int $remoteManagementSystemStatusId) {
        $this->remoteManagementSystemStatusId = $remoteManagementSystemStatusId;
    }

    public function uniqueId(): string {
        return 'fetch_gas_sampling_job';
    }

    public function handle(): void {
        Artisan::call('gas:sampling-fetch', [
            'remote_management_system_status_id' => $this->remoteManagementSystemStatusId
        ]);
    }
}
