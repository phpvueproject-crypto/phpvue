<?php

namespace App\Http\Controllers;

use App\Models\UniversalEventLog;
use Illuminate\Http\Request;

class UniversalEventLogController extends Controller {
    /**
     * @api              {get} /api/universalEventLogs 索取日誌列表
     * @apiGroup         UniversalEventLog
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} region 區域，必填
     * @apiParam {Object} [event_class] 選填
     * @apiParam {Number} [event_class.event_level] 事件層級
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.universalEventLogs 操作紀錄列表
     * @apiSuccess {String} data.universalEventLogs.event_ts 事件時間
     * @apiSuccess {String} data.universalEventLogs.event_class 事件類別
     * @apiSuccess {String} data.universalEventLogs.obj_class 設備類別
     * @apiSuccess {String} data.universalEventLogs.obj_id 設備ID
     * @apiSuccess {String} data.universalEventLogs.obj_port_id  設備槽ID
     * @apiSuccess {String} data.universalEventLogs.obj_location 設備位置
     * @apiSuccess {String} data.universalEventLogs.carrier_id 貨物ID
     * @apiSuccess {String} data.universalEventLogs.comment
     * @apiSuccess {String} data.universalEventLogs.msg_uuid 訊息ID
     * @apiSuccess {Object} data.universalEventLogs.event_class
     * @apiSuccess {Number} data.universalEventLogs.event_class.event_level
     *
     * @apiSampleRequest off
     */
    public function index(Request $request) {
        $universalEventLogs = new UniversalEventLog();
        $eventLevel = $request->input('event_level');
        if($eventLevel !== null)
            $universalEventLogs = $universalEventLogs->whereRelation('eventClass', 'event_level', $eventLevel);

        $universalEventLogsPagination = $universalEventLogs->with('eventClass')->paginate(50);

        return [
            'status' => 0,
            'data'   => [
                'universalEventLogs' => $universalEventLogsPagination->items(),
                'pagination'         => [
                    'last_page'    => $universalEventLogsPagination->lastPage(),
                    'current_page' => $universalEventLogsPagination->currentPage()
                ]
            ]
        ];
    }
}
