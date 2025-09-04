<?php

namespace App\Repositories;

use App\Events\MicroOrganismCreated;
use App\Events\MicroOrganismUpdated;
use App\Models\MicroOrganism;
use App\Models\RegionMgmt;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MicroOrganismRepository {
    public function query(Request $request) {
        $microOrganisms = new MicroOrganism();
        $startTime = $request->date('start_time');
        if($startTime) {
            $time = new Carbon($startTime);
            $microOrganisms = $microOrganisms->where('Time', '>=', $time->format('Y-m-d H:i:s'));
        }
        $endTime = $request->date('end_time');
        if($endTime) {
            $time = new Carbon($endTime);
            $microOrganisms = $microOrganisms->where('Time', '<=', $time->format('Y-m-d H:i:s'));
        }
        $organismKinds = $request->input('organism_kinds', []);
        if(count($organismKinds) > 0) {
            $microOrganisms = $microOrganisms->whereIn('organism_kind', $organismKinds);
        }
        $regionMgmtId = $request->input('region_mgmt_id');
        if($regionMgmtId) {
            $microOrganisms = $microOrganisms->whereRelation('regionMgmt', 'region_mgmt.id', $regionMgmtId);
        }

        $orderBy = $request->input('order_by');
        $direction = $request->input('direction', 'desc');
        if($orderBy) {
            $microOrganisms = $microOrganisms->orderBy($orderBy, $direction);
        } else {
            $microOrganisms = $microOrganisms->orderByDesc('id');
        }

        if($request->has('source')) {
            $microOrganisms = $microOrganisms->where('source', $request->input('source'));
        }

        return $microOrganisms->with('location.roomEnvironment');
    }

    public function getOrganismKindIdByName($name): string {
        if($name == '微粒子(0.5µm)') {
            return 'microparticle_dot_5';
        } else if($name == '微粒子(5µm)') {
            return 'microparticle_5';
        } else if($name == '懸浮微生物') {
            return 'suspended';
        } else if($name == '落下微生物') {
            return 'falling';
        } else {
            return 'contact';
        }
    }

    public function refreshSeriousData(): array {
        $changeIds = [];
        $regionMgmts = RegionMgmt::whereHas('microOrganisms')->with([
            'microOrganism',
            'seriousMicroOrganism'
        ])->get();
        /** @var RegionMgmt $regionMgmt */
        foreach($regionMgmts as $regionMgmt) {
            if($regionMgmt->seriousMicroOrganism && $regionMgmt->seriousMicroOrganism->is_serious == 0) {
                if($regionMgmt->microOrganism && $regionMgmt->microOrganism->is_serious == 1) {
                    $microOrganism = $regionMgmt->microOrganism;
                    $microOrganism->is_serious = 0;
                    $microOrganism->save();

                    event(new MicroOrganismUpdated($microOrganism));
                    $changeIds[] = $microOrganism->id;
                }

                $seriousMicroOrganism = $regionMgmt->seriousMicroOrganism;
                $seriousMicroOrganism->is_serious = 1;
                $seriousMicroOrganism->save();

                event(new MicroOrganismUpdated($seriousMicroOrganism));
                $changeIds[] = $seriousMicroOrganism->id;
            }
        }

        return $changeIds;
    }

    public function pushEvents(Collection $microOrganisms, Collection $insertMicroOrganisms, Collection $updateMicroOrganisms): void {
        $changeMicroOrganismIds = $this->refreshSeriousData();
        $insertMicroOrganismIds = $insertMicroOrganisms->pluck('id')->all();
        $updateMicroOrganismIds = $updateMicroOrganisms->pluck('id')->all();
        /** @var MicroOrganism $microOrganism */
        foreach($microOrganisms as $microOrganism) {
            if(in_array($microOrganism->id, $changeMicroOrganismIds)) {
                continue;
            }

            if(in_array($microOrganism->id, $insertMicroOrganismIds)) {
                event(new MicroOrganismCreated($microOrganism));
            } else if(in_array($microOrganism->id, $updateMicroOrganismIds)) {
                $updateMicroOrganism = $updateMicroOrganisms->where('id', $microOrganism->id)->first();
                if($updateMicroOrganism['old_organism_value'] != $microOrganism->organism_value) {
                    event(new MicroOrganismUpdated($microOrganism));
                }
            }
        }
    }

    public function filterWithLocation(array $filters) {
        $query = MicroOrganism::whereNotNull('location_id');

        if(!empty($filters['organism_kinds'])) {
            $query->whereIn('organism_kind', $filters['organism_kinds']);
        }

        if(!empty($filters['room_names'])) {
            $query->whereIn('room_name', $filters['room_names']);
        }

        $startDate = isset($filters['start_date']) ? new Carbon($filters['start_date']) : Carbon::now()->startOfDay();
        $endDate = isset($filters['end_date']) ? new Carbon($filters['end_date']) : Carbon::now()->endOfDay();

        return $query->whereDate('Time', '>=', $startDate)->whereDate('Time', '<=', $endDate)->with('location')->orderBy('Time')->get();
    }
}
