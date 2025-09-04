<?php

namespace App\Repositories;

use App\Models\Location;
use App\Models\Map;
use App\Models\Project;
use App\Models\RegionMgmt;
use GuzzleHttp\Exception\GuzzleException;
use function app\quadrantCoordToImgCoordX;
use function app\quadrantCoordToImgCoordY;

class MapRepository {
    private MirRepository $mir;

    public function __construct() {
        $this->mir = new MirRepository();
    }

    /**
     * @throws GuzzleException
     */
    public function getAllPositions($mapId, Project $project): void {
        $locations = Location::where('map_id', $mapId)->get();
        $positions = $this->mir->getPositions($mapId);
        /** @var Map $map */
        $map = Map::with('regionMgmt')->find($mapId);
        if(!$map) {
            return;
        }

        foreach($positions as $position) {
            $locationInsert = false;
            $location = $locations->where('device_name', $position['name'])->first();
            if(!$location) {
                $location = new Location();
                $location->device_name = $position['name'];
                $locationInsert = true;
            }
            $positionInfo = $this->mir->getPosition($position['guid']);
            $location->x = $positionInfo['pos_x'];
            $location->y = $positionInfo['pos_y'];
            $location->map_id = $mapId;
            $location->build = $project->name;
            $regionMgmt = RegionMgmt::whereProjectId($project->id)->whereRegion($map->region)->first();
            $location->x_px = quadrantCoordToImgCoordX(
                $regionMgmt->resolution, $location->x, $regionMgmt->origin_x
            );
            $location->y_px = quadrantCoordToImgCoordY(
                $regionMgmt->resolution, $location->y, $regionMgmt->img_height, $regionMgmt->origin_y
            );
            $location->room = $regionMgmt->roomEnvironment->room_name;
            $location->floors = $regionMgmt->floors;
            if(!$map->region_mgmt_id) {
                $map->region_mgmt_id = $regionMgmt->id;
                $map->save();
            }
            $location->save();
            if($locationInsert) {
                $locations = $locations->push($location);
            }
        }
    }

    public function getProject(string $source): ?string {
        $matchedRegion = null;
        if(preg_match('/^([^_]+)_((B\d+|\d+F))_(.+)$/u', $source, $matches)) {
            $matchedRegion = $matches[1];
        }

        return $matchedRegion;
    }

    public function getFloor(string $source): int {
        if(preg_match('/^([^_]+)_((B\d+|\d+F))_(.+)$/u', $source, $matches)) {
            $floorStr = $matches[2];

            // 判斷 B 開頭（地下樓層）
            if(preg_match('/^B(\d+)$/i', $floorStr, $subMatches)) {
                return -1 * (int)$subMatches[1];
            }

            // 標準樓層結尾為 F，如 3F、10F
            if(preg_match('/^(\d+)F$/i', $floorStr, $subMatches)) {
                return (int)$subMatches[1];
            }
        }

        return 1; // 無法判斷
    }

    public function getRegion(string $source): ?string {
        $matchedRegion = $source;
        if(preg_match('/^([^_]+)_((B\d+|\d+F))_(.+)$/u', $source, $matches)) {
            $matchedRegion = $matches[3];
        }

        return $matchedRegion;
    }
}
