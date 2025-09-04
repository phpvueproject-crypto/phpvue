<?php

namespace App\Http\Controllers;

use App\Models\Sweep;

class SweepController extends Controller {
    /**
     * @api              {get} /api/sweeps
     * @apiGroup         Sweep
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.sweeps
     * @apiSuccess {String} data.sweeps.clear_left
     * @apiSuccess {String} data.sweeps.clear_right
     * @apiSuccess {String} data.sweeps.detection_points
     * @apiSuccess {Object} data.sweeps.update_ts
     *
     * @apiSampleRequest off
     */
    public function index() {
        $sweeps = Sweep::get();

        return [
            'status' => 0,
            'data'   => [
                'sweeps' => $sweeps
            ]
        ];
    }
}
