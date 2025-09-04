<?php

namespace App\Http\Controllers;

use App\Models\DoorMgmt;

class DoorMgmtController extends Controller {
    public function index(): array {
        $doorMgmts = DoorMgmt::with([
            'doorStatus',
            'edge'
        ])->get();

        return [
            'status' => 0,
            'data'   => [
                'doorMgmts' => $doorMgmts
            ]
        ];
    }
}
