<?php

namespace App\Http\Controllers;

use App\Models\SystemEvent;
use Illuminate\Http\Request;

class SystemEventController extends Controller {
    public function index(Request $request): array {
        $systemEvents = new SystemEvent();

        $eventName = $request->input('event_name');
        if($eventName) {
            $systemEvents = $systemEvents->where('event_name', 'like', "%$eventName%");
        }

        $eventLevel = $request->input('event_level');
        if($eventLevel) {
            $systemEvents = $systemEvents->where('event_level', $eventLevel);
        }

        $systemId = $request->input('system_id');
        if($systemId) {
            $systemEvents = $systemEvents->where('system_id', 'like', "%$systemId%");
        }

        $eventCode = $request->input('event_code');
        if($eventCode) {
            $systemEvents = $systemEvents->where('event_code', 'like', "%$eventCode%");
        }

        $systemEvents = $systemEvents->orderByDesc('receive_time')->paginate(10);

        return [
            'systemEvents' => $systemEvents
        ];
    }
}
