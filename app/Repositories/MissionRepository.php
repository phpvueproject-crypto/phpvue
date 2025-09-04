<?php

namespace App\Repositories;

class MissionRepository {
    public function getRegion(string $source): ?string {
        $matchedRegion = null;
        if(preg_match('/\d+F_(.+?)V/', $source, $matches)) {
            $matchedRegion = $matches[1];
        }

        return $matchedRegion;
    }
}
