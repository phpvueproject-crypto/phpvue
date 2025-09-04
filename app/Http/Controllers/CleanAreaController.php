<?php

namespace App\Http\Controllers;

use App\Models\CleanArea;

class CleanAreaController extends Controller {
    public function index(): array {
        $cleanAreas = CleanArea::whereEnable(1)->get();

        return [
            'status' => 0,
            'data'   => [
                'cleanAreas' => $cleanAreas
            ]
        ];
    }
}
