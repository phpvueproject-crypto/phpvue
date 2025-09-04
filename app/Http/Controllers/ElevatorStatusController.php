<?php

namespace App\Http\Controllers;

use App\Models\ElevatorStatus;

class ElevatorStatusController extends Controller {
    /**
     * @api              {get} /api/elevatorStatuses
     * @apiGroup         ElevatorStatus
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.elevatorStatuses
     * @apiSuccess {String} data.elevatorStatuses.elevator_id
     * @apiSuccess {String} data.elevatorStatuses.booking
     * @apiSuccess {String} data.elevatorStatuses.booking_owner
     * @apiSuccess {String} data.elevatorStatuses.elevator_door_mgmt
     * @apiSuccess {String} data.elevatorStatuses.electric_cylinder_mgmt
     * @apiSuccess {String} data.elevatorStatuses.inject_mgmt
     * @apiSuccess {String} data.elevatorStatuses.update_ts
     *
     * @apiSampleRequest off
     */
    public function index() {
        $elevatorStatuses = ElevatorStatus::get();

        return [
            'status' => 0,
            'data'   => [
                'elevatorStatuses' => $elevatorStatuses
            ]
        ];
    }
}
