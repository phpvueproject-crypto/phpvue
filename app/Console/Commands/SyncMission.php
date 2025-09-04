<?php

namespace App\Console\Commands;

use App\Models\Mission;
use App\Repositories\MirRepository;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class SyncMission extends Command {
    protected $signature = 'mission:sync {--continuous : 是否持續同步}';

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

        // 一次性同步任務的邏輯
        $missionIds = [];
        $missionsData = collect($this->mir->getMissions());
        foreach($missionsData as $missionData) {
            Mission::updateOrCreate(
                ['guid' => $missionData['guid']], ['name' => $missionData['name']]
            );
            $missionIds[] = $missionData['guid'];
        }
        Mission::whereNotIn('guid', $missionIds)->delete();
        $this->info('任務已同步');
    }

    public function continuousSync(): void {
        while(true) {
            $this->syncOnce();
            $this->info('持續同步任務...');
            sleep(5);
        }
    }
}
