<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Map;
use App\Models\MirStatus;
use Artisan;
use Illuminate\Http\Request;

class MapController extends Controller {
    public function index(Request $request): array {
        $refresh = $request->input('refresh', 0);
        if($refresh) {
            Artisan::call('map:sync');
        }
        $pagination = null;
        $isPagination = $request->input('pagination');
        if($isPagination) {
            $pagination = Map::withCount('locations')->orderBy('name')->paginate(20);
            $maps = collect($pagination->items());
        } else {
            $maps = Map::withCount('locations')->orderBy('name')->get();
        }
        /** @var Device $device */
        $device = Device::with('mirStatus')->first();
        if($device->is_connected) {
            /** @var MirStatus $mirStatus */
            $mirStatus = MirStatus::orderByDesc('id')->first();
            if($mirStatus) {
                $maps = $maps->map(function(Map $map) use ($mirStatus) {
                    $map = $map->toArray();
                    $map['is_active'] = $mirStatus->map_id == $map['guid'];
                    return $map;
                });
            } else {
                $maps = $maps->map(function(Map $map) {
                    $map = $map->toArray();
                    $map['is_active'] = 0;
                    return $map;
                });
            }
        } else {
            $maps = $maps->map(function(Map $map) {
                $map = $map->toArray();
                $map['is_active'] = 0;
                return $map;
            });
        }

        return [
            'status' => 0,
            'data'   => [
                'maps'       => $maps,
                'pagination' => [
                    'current_page' => $pagination ? $pagination->currentPage() : 1,
                    'last_page'    => $pagination ? $pagination->lastPage() : 1
                ]
            ]
        ];
    }

    public function show($id): array {
        $map = Map::with('locations')->findOrFail($id);

        return [
            'status' => 0,
            'data'   => [
                'map' => $map
            ]
        ];
    }
}
