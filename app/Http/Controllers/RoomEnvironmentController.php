<?php

namespace App\Http\Controllers;

use App\Models\RoomEnvironment;

class RoomEnvironmentController extends Controller {
    public function index(): array {
        $roomEnvironments = RoomEnvironment::orderBy('room_name')->get();

        return [
            'status' => 0,
            'data'   => [
                'roomEnvironments' => $roomEnvironments
            ]
        ];
    }
}
