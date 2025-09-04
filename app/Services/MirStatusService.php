<?php

namespace App\Services;

use Carbon\Carbon;

class MirStatusService {
    public function toCarbon($rocDate, $time, $timezone = 'Asia/Taipei'): bool|Carbon|null {
        if(strlen($rocDate) != 7 || strlen($time) != 6) {
            return null; // 或丟 Exception
        }
        $rocYear = substr($rocDate, 0, 3);
        $monthDay = substr($rocDate, 3);
        $year = (int)$rocYear + 1911;
        $adDate = $year . $monthDay;

        return Carbon::createFromFormat('Ymd His', $adDate . ' ' . $time, $timezone);
    }
}
