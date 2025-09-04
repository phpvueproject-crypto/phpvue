<?php

namespace App\Http\Controllers;

use App\Models\Location;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LocationController extends Controller {
    public function index(Request $request): array|Application|ResponseFactory|Response {
        $locations = new Location();
        $regionMgmtId = $request->input('region_mgmt_id');
        if($regionMgmtId) {
            $locations = $locations->whereRelation('roomEnvironment.regionMgmt', 'id', $regionMgmtId);
        }
        $mapId = $request->input('map_id');
        if($mapId) {
            $locations = $locations->where('map_id', $mapId);
        }
        $locations = $locations->with([
            'microOrganism',
            'map'
        ])->orderBy('device_name')->get()->map(function(Location $location) {
            $microOrganisms = $location->microOrganisms;
            $location = $location->toArray();
            $location['micro_organisms'] = $microOrganisms->sortByDesc('id')->unique('organism_kind')->values();
            return $location;
        });

        return [
            'status' => 0,
            'data'   => [
                'locations' => $locations
            ]
        ];
    }

    /**
     * @throws \Throwable
     */
    public function updateBatch(Request $request): array {
        $inputLocations = collect($request->input('locations'));
        $locations = Location::whereIn('id', $inputLocations->pluck('id'))->with('regionMgmt')->get();
        DB::transaction(function() use ($inputLocations, &$locations) {
            foreach($inputLocations as $inputLocation) {
                $location = $locations->where('id', $inputLocation['id'])->first();
                if(!$location) {
                    $location = new Location();
                }
                $location->build = $inputLocation['build'];
                $location->room = $inputLocation['room'];
                $location->device_name = $inputLocation['device_name'];
                $location->x_px = $inputLocation['x_px'];
                $location->y_px = $inputLocation['y_px'];
                $location->save();
                if($location->regionMgmt) {
                    $location->floors = $location->regionMgmt->floors;
                    $location->save();
                }
                $locationIdx = $locations->search(function(Location $existLocation) use ($location) {
                    return $existLocation->id == $location->id;
                });
                if($locationIdx === false) {
                    $locations = $locations->push($location);
                }
            }
        }, 3);

        return [
            'status' => 0,
            'data'   => [
                'locations' => $locations
            ]
        ];
    }

    public function store(Request $request): array {
        $deviceName = $request->input('device_name');
        if($this->checkLocationDeviceNameDuplicate($request, null, $deviceName)) {
            return [
                'status' => config('errors.data_repeat')
            ];
        }

        return [
            'status' => 0
        ];
    }

    public function update(Request $request, $id): array|Response|Application|ResponseFactory {
        $location = Location::findOrFail($id);
        $name = $request->input('name');
        if($this->checkLocationDeviceNameDuplicate($request, $location->id, $name)) {
            return [
                'status' => config('errors.data_repeat')
            ];
        }

        return [
            'status' => 0
        ];
    }

    private function checkLocationDeviceNameDuplicate(Request $request, $id, $deviceName): bool {
        $room = $request->input('room');
        $locations = Location::where('room', $room);
        if($id) {
            $locations = $locations->where('id', '<>', $id);
        }
        $existLocation = $locations->whereDeviceName($deviceName)->first();
        if($existLocation) {
            return true;
        } else {
            return false;
        }
    }
}
