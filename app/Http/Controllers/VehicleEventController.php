<?php

namespace App\Http\Controllers;

use App\Models\VehicleEvent;
use Illuminate\Http\Request;

class VehicleEventController extends Controller {
    public function index(Request $request): array {
        $vehicleEvents = new VehicleEvent();
        $eventName = $request->input('event_name');
        if($eventName) {
            $vehicleEvents = $vehicleEvents->where('event_name', 'like', "%$eventName%");
        }

        $eventLevel = $request->input('event_level');
        if($eventLevel) {
            $vehicleEvents = $vehicleEvents->where('event_level', $eventLevel);
        }

        $systemId = $request->input('system_id');
        if($systemId) {
            $vehicleEvents = $vehicleEvents->where('system_id', 'like', "%$systemId%");
        }

        $eventCode = $request->input('event_code');
        if($eventCode) {
            $vehicleEvents = $vehicleEvents->where('event_code', 'like', "%$eventCode%");
        }

        $vehicleEvents = $vehicleEvents->orderByDesc('receive_time')->paginate(10);

        return [
            'vehicleEvents' => $vehicleEvents
        ];
    }
}
