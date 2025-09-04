<?php

namespace App\Repositories;

use App\Models\GasSampling;
use DB;
use Illuminate\Support\Collection;

class GasSamplingRepository {
    /**
     * 取得指定地點、指定 second_mark 下 id 最大的 gas sampling（每個 second_mark 只回傳一筆）
     *
     * @param int $locationId
     *
     * @return Collection
     */
    public function getLatestIdsByLocationAndSecondMarks(int $locationId): Collection {
        $secondMarks = GasSampling::where('location_id', $locationId)->pluck('second_mark')->unique()->values()->all();
        return GasSampling::where('location_id', $locationId)->whereIn('second_mark', $secondMarks)->select([
            'second_mark',
            DB::raw('MAX(id) as id')
        ])->groupBy('second_mark')->pluck('id');
    }
}
