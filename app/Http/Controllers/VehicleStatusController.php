<?php

namespace App\Http\Controllers;

use App\Models\VehicleStatus;

class VehicleStatusController extends Controller {
    /**
     * @api              {get} /api/vehicleStatuses 索取AMR車輛狀態列表
     * @apiGroup         VehicleStatus
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.vehicleStatuses AMR狀態列表
     * @apiSuccess {String} data.vehicleStatuses.vehicle_id 車輛ID
     * @apiSuccess {String} data.vehicleStatuses.vehicle_location 車輛位置
     * @apiSuccess {String} data.vehicleStatuses.vehicle_status 車輛狀態
     * @apiSuccess {String} data.vehicleStatuses.battery_status 車輛電力
     * @apiSuccess {String} data.vehicleStatuses.vehicle_mgmt 車輛資訊
     * @apiSuccess {String} data.vehicleStatuses.vehicle_mgmt.carrier_class 載貨物種類
     * @apiSuccess {Number} data.vehicleStatuses.vehicle_mgmt.slo_num 槽位數量
     * @apiSuccess {Number} data.vehicleStatuses.vehicle_mgmt.battery_threshold_full 電量飽
     * @apiSuccess {Number} data.vehicleStatuses.vehicle_mgmt.battery_threshold_high 高
     * @apiSuccess {Number} data.vehicleStatuses.vehicle_mgmt.battery_threshold_low 低
     * @apiSuccess {String} data.vehicleStatuses.vehicle_mgmt.macaddr 車輛MAC
     * @apiSuccess {String} data.vehicleStatuses.vehicle_mgmt.ipaddr 車輛IP
     *
     * @apiSampleRequest off
     */
    public function index() {
        $vehicleStatuses = VehicleStatus::orderBy('vehicle_id')->with('vehicleMgmt')->get();

        return [
            'status' => 0,
            'data'   => [
                'vehicleStatuses' => $vehicleStatuses
            ]
        ];
    }
}
