<?php

namespace App\Http\Controllers;

use App\Models\LaserDust;
use Illuminate\Http\Request;

class LaserDustController extends Controller {
    /**
     * @api              {post} /api/laserDusts 雷射落塵資訊
     * @apiGroup         LaserDusts
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} vehicle_id 車輛編號
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.laserDusts 指令資訊
     * @apiSuccess {Number} data.laserDusts.val_1 0.3um
     * @apiSuccess {Number} data.laserDusts.val_2 0.5um
     * @apiSuccess {Number} data.laserDusts.val_3 1.0um
     * @apiSuccess {Number} data.laserDusts.val_4 2.5um
     * @apiSuccess {Number} data.laserDusts.val_5 5.0um
     * @apiSuccess {Number} data.laserDusts.val_6 10.0um
     *
     * @apiSampleRequest off
     */
    public function index(Request $request) {
        $vehicleId = $request->input('vehicle_id');
        $laserDusts = LaserDust::orderByDesc('id')->where('vehicle_id', $vehicleId)->skip(0)->take(1)->get();

        return [
            'status' => 0,
            'data'   => [
                'laserDusts' => $laserDusts
            ]
        ];
    }
}
