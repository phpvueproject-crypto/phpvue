<?php

namespace App\Http\Controllers;

use App\Models\PollutionCondition;

class PollutionConditionController extends Controller {
    public function index(): array {
        $pollutionConditions = PollutionCondition::all();

        return [
            'status' => 0,
            'data'   => [
                'pollutionConditions' => $pollutionConditions
            ]
        ];
    }
}
