<?php

namespace App\Http\Controllers;

use App\Events\ParkingLotMgmtCreated;
use App\Events\ParkingLotMgmtDeleted;
use App\Events\ParkingLotMgmtUpdated;
use App\Models\ParkingLotMgmt;
use App\Models\ParkingLotStatus;
use App\Models\Project;
use App\Models\Vertex;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class ParkingLotMgmtController extends Controller {
    public function index(Request $request): array {
        $project = Project::whereIsDeploy(1)->first();
        $parkingLotMgmts = ParkingLotMgmt::with([
            'parkingLotStatus',
            'vertex.regionMgmt'
        ])->get();

        return [
            'status' => 0,
            'data'   => [
                'parkingLotMgmts' => $parkingLotMgmts,
                'project'         => $project
            ]
        ];
    }

    public function show($parkingLotId): array {
        $parkingLotMgmt = ParkingLotMgmt::with('vertex.regionMgmt')->findOrFail($parkingLotId);

        return [
            'status' => 0,
            'data'   => [
                'parkingLotMgmt' => $parkingLotMgmt
            ]
        ];
    }

    public function store(Request $request): array {
        $parkingLotId = $request->input('parking_lot_id');
        $parkingLotMgmt = ParkingLotMgmt::find($parkingLotId);
        if($parkingLotMgmt) {
            return [
                'status' => config('errors.data_repeat')
            ];
        }

        $vertexId = $request->input('vertex_id');
        $vertex = Vertex::find($vertexId);
        if($vertex) {
            $parkingLotMgmt = ParkingLotMgmt::where('vertex_name', $vertex->name)->first();
            if($parkingLotMgmt) {
                return [
                    'status' => config('errors.data_repeat_vertex_name')
                ];
            }
        }

        $parkingLotMgmt = new ParkingLotMgmt();
        $parkingLotMgmt = $this->saveModel($request, $parkingLotMgmt);

        return [
            'status' => 0,
            'data'   => [
                'parkingLotMgmt' => $parkingLotMgmt
            ]
        ];
    }

    public function update(Request $request, $parkingLotId): array {
        $parkingLotMgmt = ParkingLotMgmt::findOrFail($parkingLotId);

        $vertexId = $request->input('vertex_id');
        $vertex = Vertex::find($vertexId);
        if($vertex) {
            $parkingLotMgmtByVertexName = ParkingLotMgmt::where('vertex_name', $vertex->name)->where('parking_lot_id', '<>', $parkingLotId)->first();
            if($parkingLotMgmtByVertexName) {
                return [
                    'status' => config('errors.data_repeat_vertex_name')
                ];
            }
        }

        $parkingLotMgmt = $this->saveModel($request, $parkingLotMgmt);

        return [
            'status' => 0,
            'data'   => [
                'parkingLotMgmt' => $parkingLotMgmt
            ]
        ];
    }

    private function saveModel(Request $request, ParkingLotMgmt $parkingLotMgmt): ParkingLotMgmt {
        $insertMode = false;
        if(!$parkingLotMgmt->parking_lot_id) {
            $insertMode = true;
        }
        $parkingLotId = $request->input('parking_lot_id');
        $vertexId = $request->input('vertex_id');
        $vertex = Vertex::find($vertexId);
        $parkingLotMgmt->parking_lot_id = $parkingLotId;
        if($vertexId) {
            $parkingLotMgmt->vertex_id = $vertexId;
            $parkingLotMgmt->vertex_name = $vertex->name;
        } else {
            $parkingLotMgmt->vertex_id = null;
            $parkingLotMgmt->vertex_name = null;
        }
        $parkingLotMgmt->prefer_vehicle = $request->input('prefer_vehicle', $parkingLotMgmt->prefer_vehicle);
        $parkingLotMgmt->attribute = $request->input('attribute');
        $parkingLotMgmt->enable = $request->input('enable');
        if($insertMode) {
            event(new ParkingLotMgmtCreated($parkingLotMgmt));
        } else {
            event(new ParkingLotMgmtUpdated($parkingLotMgmt));
        }
        $parkingLotMgmt->save();

        $parkingLotStatus = ParkingLotStatus::whereParkingLotId($parkingLotId)->first();
        if(!$parkingLotStatus) {
            $parkingLotStatus = new ParkingLotStatus();
            $parkingLotStatus->parking_lot_id = $parkingLotMgmt->parking_lot_id;
            $parkingLotStatus->save();
        }

        return $parkingLotMgmt->load([
            'vertex.regionMgmt',
            'parkingLotStatus'
        ]);
    }

    public function destroy($parkingLotId): array {
        $parkingLotMgmt = ParkingLotMgmt::findOrFail($parkingLotId);
        $parkingLotMgmt?->delete();
        $parkingLotStatus = ParkingLotStatus::whereParkingLotId($parkingLotMgmt->parking_lot_id)->first();
        $parkingLotStatus?->delete();
        if($parkingLotMgmt) {
            event(new ParkingLotMgmtDeleted($parkingLotMgmt));
        }

        return [
            'status' => 0
        ];
    }
}
