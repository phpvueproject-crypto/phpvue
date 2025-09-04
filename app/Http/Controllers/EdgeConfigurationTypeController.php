<?php

namespace App\Http\Controllers;

use App\Models\EdgeConfigurationType;

class EdgeConfigurationTypeController extends Controller {
    public function index(): array {
        $edgeConfigurationTypes = EdgeConfigurationType::orderBy('name')->get();

        return [
            'status' => 0,
            'data'   => [
                'edgeConfigurationTypes' => $edgeConfigurationTypes
            ]
        ];
    }
}
