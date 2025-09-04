<?php

namespace App\Http\Controllers;

use App\Models\ChartColor;
use Illuminate\Http\Request;

class ChartColorController extends Controller {
    public function update(Request $request, $id): array {
        $chartColor = ChartColor::findOrFail($id);
        $chartColor->color = $request->input('color');
        $chartColor->save();

        return [
            'status' => 0,
            'data'   => [
                'chartColor' => $chartColor
            ]
        ];
    }
}
