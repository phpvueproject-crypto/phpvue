<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use Artisan;
use Illuminate\Http\Request;

class MissionController extends Controller {
    public function index(Request $request): array {
        $refresh = $request->input('refresh', 0);
        if($refresh) {
            Artisan::call('mission:sync');
        }
        $missions = new Mission();
        $name = $request->input('name');
        if($name) {
            $missions = $missions->where('name', 'like', "%$name%");
        }
        $missions = $missions->orderByDesc('created_at')->get()->sortBy('region')->values();

        return [
            'status' => 0,
            'data'   => [
                'missions' => $missions
            ]
        ];
    }
}
