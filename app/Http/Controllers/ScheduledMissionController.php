<?php

namespace App\Http\Controllers;

use App\Models\ScheduledMission;
use Illuminate\Http\Request;

class ScheduledMissionController extends Controller {
    public function index(Request $request): array {
        $scheduledMissions = ScheduledMission::with('vehicleMgmt');
        $orderBy = $request->input('order_by');
        $direction = $request->input('direction');
        if($orderBy == 'system_id') {
            $scheduledMissions = $scheduledMissions->orderBy('system_id', $direction);
        } else if($orderBy == 'time') {
            $scheduledMissions = $scheduledMissions->orderBy('years', $direction)->orderBy('month', $direction)->orderBy('week', $direction)->orderBy('day', $direction)->orderBy('hours', $direction)->orderBy('minutes', $direction);
        }

        $pagination = $scheduledMissions->paginate(20);
        return [
            'status' => 0,
            'data'   => [
                'scheduledMissions' => $pagination->items(),
                'pagination'        => [
                    'current_page' => $pagination->currentPage(),
                    'last_page'    => $pagination->lastPage(),
                ]
            ]
        ];
    }
}
