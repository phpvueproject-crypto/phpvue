<?php

namespace App\Http\Controllers;

use App\Models\ClearStatus;

class ClearStatusController extends Controller {
    /**
     * @api              {get} /api/clearStatuses
     * @apiGroup         ClearStatus
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.clearStatuses
     * @apiSuccess {String} data.clearStatuses.coverage_status
     * @apiSuccess {String} data.clearStatuses.turn_points
     *
     * @apiSampleRequest off
     */
    public function index() {
        $clearStatuses = ClearStatus::get();

        return [
            'status' => 0,
            'data'   => [
                'clearStatuses' => $clearStatuses
            ]
        ];
    }
}
