<?php

namespace App\Http\Controllers;

use App\Models\StationMgmt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StationMgmtController extends Controller {
    public function index(Request $request): array {
        $stationMgmts = new StationMgmt();
        $stationGroup = $request->input('station_group');
        if($stationGroup) {
            $stationMgmts = $stationMgmts->where('station_group', 'like', "%$stationGroup%");
        }

        $stationMgmts = $stationMgmts->whereHas('vertex', function(Builder $query) {
            $query->where('enable', 1);
        })->with([
            'stationStatus',
            'vertex'
        ])->get();

        return [
            'status' => 0,
            'data'   => [
                'stationMgmts' => $stationMgmts
            ]
        ];
    }
}
