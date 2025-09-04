<?php

namespace App\Console\Commands;

use App\Models\Map;
use App\Models\Project;
use App\Models\RegionMgmt;
use App\Models\RoomEnvironment;
use App\Repositories\MapRepository;
use App\Repositories\MirRepository;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class SyncMap extends Command {
    protected $signature = 'map:sync {--continuous : 是否持續同步}';

    private MirRepository $mir;

    public function __construct() {
        parent::__construct();
        $this->mir = new MirRepository();
    }

    /**
     * @throws GuzzleException
     */
    public function handle(): void {
        if($this->option('continuous')) {
            $this->continuousSync();
        } else {
            $this->syncOnce();
        }
    }

    /**
     * @throws GuzzleException
     */
    protected function syncOnce(): void {
        $mirDomain = $this->mir->getHost();
        if(!$mirDomain) {
            sleep(5);
            return;
        }
        // 一次性同步地圖的邏輯
        $mapIds = [];
        $mapRepository = new MapRepository();
        $mapsData = collect($this->mir->getMaps());
        $project = Project::whereIsDeploy(1)->first();
        foreach($mapsData as $mapData) {
            $mapId = $mapData['guid'];
            $map = Map::find($mapId);
            if(!$map) {
                $map = new Map();
                $map->guid = $mapId;
                $map->name = $mapData['name'];
                $mapDetail = $this->mir->getMap($mapId);
                $map->origin_x = $mapDetail['origin_x'];
                $map->origin_y = $mapDetail['origin_y'];
                $map->resolution = $mapDetail['resolution'];
                $map->save();
            }
            $regionMgmt = RegionMgmt::whereProjectId($project->id)->whereRegion($map->region)->first();
            if($regionMgmt) {
                $regionMgmt->resolution = $map->resolution;
                $regionMgmt->origin_x = $map->origin_x;
                $regionMgmt->origin_y = $map->origin_y;
                $regionMgmt->save();
            } else {
                $regionMgmt = new RegionMgmt();
                $regionMgmt->project_id = $project->id;
                $regionMgmt->project_name = $project->name;
                $regionMgmt->region = $map->region;
                $regionMgmt->floors = $mapRepository->getFloor($map->name);
                $regionMgmt->resolution = $map->resolution;
                $regionMgmt->origin_x = $map->origin_x;
                $regionMgmt->origin_y = $map->origin_y;
                $regionMgmt->save();
                $roomEnvironment = new RoomEnvironment();
                $roomEnvironment->room_name = $map->region;
                $roomEnvironment->region_mgmt_id = $regionMgmt->id;
                $roomEnvironment->save();
            }
            $mapRepository->getAllPositions($map->guid, $project);
            $mapIds[] = $mapId;
        }
        Map::whereNotIn('guid', $mapIds)->delete();
        $this->info('地圖已同步');
    }

    public function continuousSync(): void {
        while(true) {
            $this->syncOnce();
            $this->info('持續同步地圖...');
            sleep(5 * 60);
        }
    }
}
