<?php

namespace App\Http\Controllers;

use App\Models\CleanStatus;

class CleanStatusController extends Controller {
    public function show($id): array {
        $cleanStatus = CleanStatus::findOrFail($id);

        return [
            'status' => 0,
            'data'   => [
                'cleanStatus' => $cleanStatus
            ]
        ];
    }
}
