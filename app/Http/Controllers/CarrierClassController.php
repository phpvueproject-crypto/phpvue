<?php

namespace App\Http\Controllers;

use App\Models\CarrierClass;

class CarrierClassController extends Controller {
    /**
     * @api              {get} /api/carrierClasses 索取貨物類別列表
     * @apiGroup         CarrierClass
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.carrierClasses 貨物類別列表
     * @apiSuccess {String} data.carrierClasses.carrier_class 貨物類別名稱
     *
     * @apiSampleRequest off
     */
    public function index() {
        $carrierClasses = CarrierClass::orderBy('carrier_class')->get();

        return [
            'status' => 0,
            'data'   => [
                'carrierClasses' => $carrierClasses
            ]
        ];
    }
}
