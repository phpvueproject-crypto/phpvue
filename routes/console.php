<?php

use App\Models\Location;
use App\Models\MicroOrganism;
use App\Models\MirStatus;
use App\Models\RemoteManagementSystemStatus;
use App\Models\User;
use App\Repositories\MirRepository;
use App\Services\MicroOrganismService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function() {
    echo getProject('11C08B_20250611_V1');
})->purpose('Display an inspiring quote');

Artisan::command('create-account', function() {
    $user = new User();
    $user->name = '管理員';
    $user->account = 'admin';
    $user->password = Hash::make('peRHt?F#');
    $user->save();
    $user->roles()->sync([1]);

    $user = new User();
    $user->name = '一般使用者';
    $user->account = 'user';
    $user->password = Hash::make('Msdq24Na');
    $user->save();
    $user->roles()->sync([2]);
})->purpose('Display an inspiring quote');


function getRegion(string $source): ?string {
    $matchedRegion = $source;
    if(preg_match('/^([^_]+)_((B\d+|\d+F))_(.+)$/u', $source, $matches)) {
        $matchedRegion = $matches[3];
    }

    return $matchedRegion;
}

function getFloor(string $source): ?int {
    if(preg_match('/^([^_]+)_((B\d+|\d+F))_(.+)$/u', $source, $matches)) {
        $floorStr = $matches[2];

        // 判斷 B 開頭（地下樓層）
        if(preg_match('/^B(\d+)$/i', $floorStr, $subMatches)) {
            return -1 * (int)$subMatches[1];
        }

        // 標準樓層結尾為 F，如 3F、10F
        if(preg_match('/^(\d+)F$/i', $floorStr, $subMatches)) {
            return (int)$subMatches[1];
        }
    }

    return null; // 無法判斷
}

function getProject(string $source): ?string {
    $matchedRegion = null;
    if(preg_match('/^([^_]+)_((B\d+|\d+F))_(.+)$/u', $source, $matches)) {
        $matchedRegion = $matches[1];
    }

    return $matchedRegion;
}
