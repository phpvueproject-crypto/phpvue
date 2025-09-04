<?php

namespace App\Http\Controllers;

use App\Events\ElevatorMgmtDeleted;
use App\Models\ElevatorMgmt;
use App\Models\ElevatorStatus;
use Illuminate\Http\Request;

class ElevatorMgmtController extends Controller {
    /**
     * @api              {get} /api/elevatorMgmts 索取電梯列表
     * @apiGroup         ElevatorMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} [region] 區域編號
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.elevatorMgmts 電梯列表
     * @apiSuccess {String} data.elevatorMgmts.id 電梯編號
     * @apiSuccess {String} data.elevatorMgmts.location 站點名稱
     * @apiSuccess {String} data.elevatorMgmts.prefer_vehicle 首選車輛
     * @apiSuccess {Object} data.elevatorMgmts.elevator_status 電梯即時狀態
     * @apiSuccess {String} data.elevatorMgmts.elevator_status.update_ts 更新時間
     * @apiSuccess {String} data.elevatorMgmts.elevator_status.booking 預定
     * @apiSuccess {String} data.elevatorMgmts.elevator_status.booking_owner 預定者
     * @apiSuccess {Object} data.elevatorMgmts.vertex 站點資訊
     * @apiSuccess {Object} data.elevatorMgmts.vertex.region_mgmt 區域
     * @apiSuccess {String} data.elevatorMgmts.vertex.region_mgmt.name 區域名稱
     *
     * @apiSampleRequest off
     */
    public function index(): array {
        $elevatorMgmts = ElevatorMgmt::with([
            'elevatorStatus',
            'vertex.regionMgmt'
        ])->get();

        return [
            'status' => 0,
            'data'   => [
                'elevatorMgmts' => $elevatorMgmts
            ]
        ];
    }

    /**
     * @api              {get} /api/elevatorMgmts/{id} 索取單筆電梯列表
     * @apiGroup         ElevatorMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} id 電梯編號
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.elevatorMgmt 電梯列表
     * @apiSuccess {String} data.elevatorMgmt.id 電梯編號
     * @apiSuccess {String} data.elevatorMgmt.location 站點名稱
     * @apiSuccess {String} data.elevatorMgmt.prefer_vehicle 首選車輛
     * @apiSuccess {Object} data.elevatorMgmt.elevator_status 電梯即時狀態
     * @apiSuccess {String} data.elevatorMgmt.elevator_status.update_ts 更新時間
     * @apiSuccess {String} data.elevatorMgmt.elevator_status.booking 預定
     * @apiSuccess {String} data.elevatorMgmt.elevator_status.booking_owner 預定者
     * @apiSuccess {Object} data.elevatorMgmt.vertex 站點資訊
     * @apiSuccess {Object} data.elevatorMgmt.vertex.region_mgmt 區域
     * @apiSuccess {String} data.elevatorMgmt.vertex.region_mgmt.name 區域名稱
     *
     * @apiSampleRequest off
     */
    public function show($elevatorId): array {
        $elevatorMgmt = ElevatorMgmt::with('vertices')->findOrFail($elevatorId);

        return [
            'status' => 0,
            'data'   => [
                'elevatorMgmt' => $elevatorMgmt
            ]
        ];
    }

    /**
     * @api              {post} /api/elevatorMgmts 新增單筆電梯資料
     * @apiGroup         ElevatorMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} id 電梯編號
     * @apiParam {String} [location] 停車格位在的站點名稱
     * @apiParam {String} prefer_vehicle 首選車輛
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功<br>-7：該電梯編號已存在。<br>-9：站點名稱重複。
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.elevatorMgmt 電梯列表
     * @apiSuccess {String} data.elevatorMgmt.id 電梯編號
     * @apiSuccess {String} data.elevatorMgmt.location 站點名稱
     * @apiSuccess {String} data.elevatorMgmt.prefer_vehicle 首選車輛
     * @apiSuccess {Object} data.elevatorMgmt.elevator_status 電梯即時狀態
     * @apiSuccess {String} data.elevatorMgmt.elevator_status.update_ts 更新時間
     * @apiSuccess {String} data.elevatorMgmt.elevator_status.booking 預定
     * @apiSuccess {String} data.elevatorMgmt.elevator_status.booking_owner 預定者
     * @apiSuccess {Object} data.elevatorMgmt.vertex 站點資訊
     * @apiSuccess {Object} data.elevatorMgmt.vertex.region_mgmt 區域
     * @apiSuccess {String} data.elevatorMgmt.vertex.region_mgmt.name 區域名稱
     *
     * @apiSampleRequest off
     */
    public function store(Request $request): array {
        $elevatorId = $request->input('elevator_id');
        $elevatorMgmt = ElevatorMgmt::find($elevatorId);
        if($elevatorMgmt) {
            return [
                'status' => config('errors.data_repeat')
            ];
        }

        $vertexName = $request->input('vertex_name');
        if($vertexName) {
            $elevatorMgmt = ElevatorMgmt::whereVertexName($vertexName)->first();
            if($elevatorMgmt) {
                return [
                    'status' => config('errors.data_repeat_vertex_name')
                ];
            }
        }

        $elevatorMgmt = new ElevatorMgmt();
        $elevatorMgmt->elevator_id = $elevatorId;
        $elevatorMgmt->vertex_name = $vertexName;
        $elevatorMgmt->prefer_vehicle = $request->input('prefer_vehicle');
        $elevatorMgmt->ipaddr = $request->input('ipaddr');
        $elevatorMgmt->macaddr = $request->input('macaddr');
        $elevatorMgmt->save();
        $elevatorMgmt = $elevatorMgmt->load('vertex.regionMgmt');

        return [
            'status' => 0,
            'data'   => [
                'elevatorMgmt' => $elevatorMgmt
            ]
        ];
    }

    /**
     * @api              {patch} /api/elevatorMgmts/{chargingStationId} 更新單筆電梯資料
     * @apiGroup         ElevatorMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} chargingStationId 網址，電梯編號
     * @apiParam {String} [location] 站點名稱
     * @apiParam {String} prefer_vehicle 首選車輛
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功<br>-9：站點名稱重複。
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.elevatorMgmt 電梯列表
     * @apiSuccess {String} data.elevatorMgmt.id 電梯編號
     * @apiSuccess {String} data.elevatorMgmt.location 站點名稱
     * @apiSuccess {String} data.elevatorMgmt.prefer_vehicle 首選車輛
     * @apiSuccess {Object} data.elevatorMgmt.elevator_status 電梯即時狀態
     * @apiSuccess {String} data.elevatorMgmt.elevator_status.update_ts 更新時間
     * @apiSuccess {String} data.elevatorMgmt.elevator_status.booking 預定
     * @apiSuccess {String} data.elevatorMgmt.elevator_status.booking_owner 預定者
     * @apiSuccess {Object} data.elevatorMgmt.vertex 站點資訊
     * @apiSuccess {Object} data.elevatorMgmt.vertex.region_mgmt 區域
     * @apiSuccess {String} data.elevatorMgmt.vertex.region_mgmt.name 區域名稱
     *
     * @apiSampleRequest off
     */
    public function update(Request $request, $elevatorId) {
        $elevatorMgmt = ElevatorMgmt::findOrFail($elevatorId);
        $vertexName = $request->input('vertex_name');
        if($vertexName) {
            $elevatorMgmtByStationLocation = ElevatorMgmt::whereVertexName($vertexName)->where('elevator_id', '<>', $elevatorId)->first();
            if($elevatorMgmtByStationLocation) {
                return [
                    'status' => config('errors.data_repeat_vertex_name')
                ];
            }
        }

        $elevatorMgmt->elevator_id = $elevatorId;
        $elevatorMgmt->vertex_name = $vertexName;
        $elevatorMgmt->prefer_vehicle = $request->input('prefer_vehicle', $elevatorMgmt->prefer_vehicle);
        $elevatorMgmt->ipaddr = $request->input('ipaddr');
        $elevatorMgmt->macaddr = $request->input('macaddr');
        $elevatorMgmt->save();
        $elevatorMgmt = $elevatorMgmt->load([
            'vertex.regionMgmt',
            'elevatorStatus'
        ]);

        return [
            'status' => 0,
            'data'   => [
                'elevatorMgmt' => $elevatorMgmt
            ]
        ];
    }

    /**
     * @api              {delete} /api/elevatorMgmts/{chargingStationId} 刪除單筆電梯資料
     * @apiGroup         ElevatorMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} chargingStationId 電梯編號
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     *
     * @apiSampleRequest off
     */
    public function destroy($elevatorId): array {
        $elevatorMgmt = ElevatorMgmt::findOrFail($elevatorId);
        $elevatorMgmt->delete();
        $elevatorStatus = ElevatorStatus::whereElevatorId($elevatorMgmt->elevator_id)->first();
        $elevatorStatus?->delete();

        event(new ElevatorMgmtDeleted($elevatorMgmt));

        return [
            'status' => 0
        ];
    }
}
