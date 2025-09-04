<?php

namespace App\Services;

use App\Models\Mission;
use Artisan;

class MissionService {
    public function getCurrentMissionId(): ?string {
        Artisan::call('mission:sync');
        Artisan::call('missionQueue:sync');

        /** @var Mission $mission */
        $mission = Mission::orderByDesc('created_at')->first();
        return $mission?->guid;
    }
}
