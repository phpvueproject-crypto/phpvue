<?php

namespace App\Http\Controllers;

use App\Models\VehicleMgmt;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class VehicleMgmtController extends Controller {
    /**
     * @api              {get} /api/vehicleMgmts 索取AMR車輛列表
     * @apiGroup         VehicleMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.vehicleMgmts AMR列表
     * @apiSuccess {String} data.vehicleMgmts.vehicle_id AMR編號
     * @apiSuccess {Object} data.vehicleMgmts.vehicle_color 車輛顏色資訊
     * @apiSuccess {String} data.vehicleMgmts.vehicle_color.color 車輛顏色
     * @apiSuccess {String} data.vehicleMgmts.vehicle_status 即時狀態資訊
     * @apiSuccess {String} data.vehicleMgmts.vehicle_status.update_ts 更新時間
     * @apiSuccess {String} data.vehicleMgmts.vehicle_status.vehicle_id 車輛ID
     * @apiSuccess {String} data.vehicleMgmts.vehicle_status.vehicle_location 車輛位置
     * @apiSuccess {String} data.vehicleMgmts.vehicle_status.vehicle_status 車輛狀態
     * @apiSuccess {String} data.vehicleMgmts.vehicle_status.battery_status 車輛電力
     * @apiSuccess {Object} data.vehicleMgmts.vehicle_status.vertex 車輛所在的站點資訊
     * @apiSuccess {Number} data.vehicleMgmts.vehicle_status.vertex.vertex_type_id 站點類型編號
     * @apiSuccess {Object} data.vehicleMgmts.object_mgmt 載具資訊
     * @apiSuccess {String} data.vehicleMgmts.object_mgmt.obj_uid 載具系統編號
     * @apiSuccess {String} data.vehicleMgmts.object_mgmt.obj_id 載具編號
     *
     * @apiSampleRequest off
     */
    public function index(Request $request): array {
        $vehicleMgmts = new VehicleMgmt();
        $regionMgmtId = $request->input('region_mgmt_id');
        if($regionMgmtId) {
            $vehicleMgmts = $vehicleMgmts->whereRelation('vehicleStatus.vertex', 'region_mgmt_id', $regionMgmtId);
        }

        $vehicleStatus = $request->input('vehicle_status');
        if($vehicleStatus) {
            $vehicleStatusOperator = $request->input('vehicle_status_operator', '=');
            if($vehicleStatusOperator == '=') {
                $vehicleMgmts = $vehicleMgmts->where(function(Builder $query) use ($vehicleStatus) {
                    $query->whereHas('vehicleStatus', function(Builder $query) use ($vehicleStatus) {
                        $query->whereIn('vehicle_status', $vehicleStatus);
                    });
                    $query->orWhereDoesntHave('vehicleStatus');
                });
            } else {
                $vehicleMgmts = $vehicleMgmts->where(function(Builder $query) use ($vehicleStatus) {
                    $query->whereHas('vehicleStatus', function(Builder $query) use ($vehicleStatus) {
                        $query->whereNotIn('vehicle_status', $vehicleStatus);
                    });
                    $query->orWhereDoesntHave('vehicleStatus');
                });
            }
        }

        $vehicleMgmtsQuery = $vehicleMgmts->with([
            'vehicleStatus.vertex',
            'vehicleStatus.regionMgmt',
            'objectMgmt',
            'mqttCommand.mqttCommandType',
            'cleanArea.turningPoints'
        ]);

        $vehicleMgmts = $vehicleMgmtsQuery->orderBy('vehicle_id')->get();

        return [
            'status' => 0,
            'data'   => [
                'vehicleMgmts' => $vehicleMgmts
            ]
        ];
    }

    public function update(Request $request, $id): array {
        $vehicleMgmt = VehicleMgmt::find($id);
        $vehicleMgmt->color = $request->input('color', $vehicleMgmt->color);
        $vehicleMgmt->save();

        return [
            'status' => 0
        ];
    }
}
