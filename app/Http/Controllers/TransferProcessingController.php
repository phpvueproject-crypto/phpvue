<?php

namespace App\Http\Controllers;

use App\Models\TransferProcessing;
use Auth;
use Carbon\Carbon;
use Exception;
use Generator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;

class TransferProcessingController extends Controller {
    /**
     * @api              {get} /api/transferPros 索取待辦任務列表
     * @apiGroup         Transfer
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} region 區域
     * @apiParam {String} [format] 匯出格式
     * @apiParam {String} [receive_start_ts] 搜尋開始時間
     * @apiParam {String} [receive_stop_ts] 搜尋結束時間
     * @apiParam {String} [transfer_state] 搜尋搬運狀態
     * @apiParam {String} [command_id] 搜尋命令編號
     * @apiParam {String} [query] 搜尋關鍵字
     *
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.transfers 待辦列表
     * @apiSuccess {Number} data.transfers.serial_num 任務編號
     * @apiSuccess {String} data.transfers.receive_ts 任務收到時間
     * @apiSuccess {String} data.transfers.command_id 任務編號
     * @apiSuccess {String} data.transfers.source_port 起點
     * @apiSuccess {String} data.transfers.dest_port 終點
     * @apiSuccess {Number} data.transfers.priority 任務優先
     * @apiSuccess {String} data.transfers.operator_id 派任務人員
     * @apiSuccess {String} data.transfers.carrier_id 載貨ID
     * @apiSuccess {Object} data.pagination 分頁資訊
     * @apiSuccess {Number} data.pagination.last_page 總共頁數
     * @apiSuccess {Number} data.pagination.current_page 目前頁數
     *
     * @apiSampleRequest off
     */
    public function index(Request $request) {
        $transferProcessings = new TransferProcessing();
        $transferState = $request->input('transfer_state');
        if($transferState) {
            $transferProcessings = $transferProcessings->where('transfer_state', $transferState);
        }
        $commandId = $request->input('command_id');
        if($commandId) {
            $transferProcessings = $transferProcessings->where('command_id', 'like', "%$commandId%");
        }

        $receiveStartTs = $request->date('receive_start_ts');
        if($receiveStartTs) {
            $transferProcessings = $transferProcessings->where(function(Builder $query) use ($receiveStartTs) {
                $query->where('receive_ts', '>=', $receiveStartTs);
            });
        }

        $receiveStopTs = $request->date('receive_stop_ts');
        if($receiveStopTs) {
            $receiveStopTs = $receiveStopTs->addSeconds(59);
            $transferProcessings = $transferProcessings->where(function(Builder $query) use ($receiveStopTs) {
                $query->where('receive_ts', '<=', $receiveStopTs);
            });
        }

        $searchQuery = $request->input('query');
        if($searchQuery) {
            $transferProcessings = $transferProcessings->where(function(Builder $query) use ($searchQuery) {
                $query->where('serial_num', 'like', "%$searchQuery%");
                $query->orWhere('receive_ts', 'like', "%$searchQuery%");
                $query->orWhere('command_id', 'like', "%$searchQuery%");
                $query->orWhere('source_port', 'like', "%$searchQuery%");
                $query->orWhere('dest_port', 'like', "%$searchQuery%");
                $query->orWhere('priority', 'like', "%$searchQuery%");
                $query->orWhere('operator_id', 'like', "%$searchQuery%");
                $query->orWhere('carrier_id', 'like', "%$searchQuery%");
                $query->orWhere('mission_type', 'like', "%$searchQuery%");
                $query->orWhere('merged_command_id', 'like', "%$searchQuery%");
                $query->orWhere('vehicle_id', 'like', "%$searchQuery%");
                $query->orWhere('transfer_state', 'like', "%$searchQuery%");
                $query->orWhere('comment', 'like', "%$searchQuery%");
                $query->orWhere('update_ts', 'like', "%$searchQuery%");
                $query->orWhere('merged_ts', 'like', "%$searchQuery%");
                $query->orWhere('assigned_ts', 'like', "%$searchQuery%");
                $query->orWhere('delivery_start_ts', 'like', "%$searchQuery%");
                $query->orWhere('delivery_stop_ts', 'like', "%$searchQuery%");
            });
        }

        $format = $request->input('format');
        if($format != 'csv') {
            $transferProcessings = $transferProcessings->with('mqttCommand');
            $orderBy = $request->input('order_by', 'receive_ts');
            if($orderBy == 'receive_ts') {
                $transferProcessingsPagination = $transferProcessings->orderByDesc($orderBy)->paginate(10);

                return [
                    'status' => 0,
                    'data'   => [
                        'transferProcessings' => $transferProcessingsPagination->items(),
                        'pagination'          => [
                            'last_page'    => $transferProcessingsPagination->lastPage(),
                            'current_page' => $transferProcessingsPagination->currentPage()
                        ]
                    ]
                ];
            } else {
                $vehicleGroup = $request->input('vehicle_group');
                if($vehicleGroup) {
                    $transferProcessings->where('vehicle_group', $vehicleGroup);
                }

                $transferProcessings = $transferProcessings->orderByDesc('priority_1')->orderBy('receive_ts')->selectRaw("
                    *,
                    case when transfer_state = 'WAITING' then 101
                         when transfer_state = 'TRANSFERRING' then 101
                         when transfer_state = 'PAUSED' then 100
                         when transfer_state = 'PENDING' then 99
                         ELSE priority
                         END priority_1
                ")->whereIn('transfer_state', [
                    'QUEUED',
                    'WAITING',
                    'TRANSFERRING',
                    'PAUSED',
                    'PENDING'
                ])->with([
                    'stationMgmt.users' => function(BelongsToMany $query) {
                        $query->where('id', Auth::id());
                    }
                ])->get();

                return [
                    'status' => 0,
                    'data'   => [
                        'transferProcessings' => $transferProcessings
                    ]
                ];
            }
        } else {
            $transferProcessings = $transferProcessings->orderByDesc('receive_ts');
            $receiveStartTs = $request->input('receive_start_ts');
            $receiveStopTs = $request->input('receive_stop_ts');
            $filename = 'transferProcessings.csv';
            if($receiveStartTs && $receiveStopTs) {
                $receiveStartTs = new Carbon($receiveStartTs);
                $receiveStartTs = $receiveStartTs->format('YmdHis');
                $receiveStopTs = new Carbon($receiveStopTs);
                $receiveStopTs = $receiveStopTs->format('YmdHis');
                $filename = "transferProcessings_{$receiveStartTs}_{$receiveStopTs}.csv";
            } else if($receiveStartTs && !$receiveStopTs) {
                $receiveStartTs = new Carbon($receiveStartTs);
                $receiveStartTs = $receiveStartTs->format('YmdHis');
                $filename = "transferProcessings_start_{$receiveStartTs}.csv";
            } else if(!$receiveStartTs && $receiveStopTs) {
                $receiveStopTs = new Carbon($receiveStopTs);
                $receiveStopTs = $receiveStopTs->format('YmdHis');
                $filename = "transferProcessings_end_{$receiveStopTs}.csv";
            }
            try {
                return (new FastExcel(self::transferProcessingsGenerator($transferProcessings)))->download($filename);
            } catch(Exception) {
                return response(null, 404);
            }
        }
    }

    private static function transferProcessingsGenerator($transferProcessings): Generator {
        if($transferProcessings->count() == 0) {
            yield [
                'serial_num'  => null,
                'receive_ts'  => null,
                'command_id'  => null,
                'source_port' => null,
                'dest_port'   => null,
                'priority'    => null,
                'operator_id' => null,
                'carrier_id'  => null
            ];
        } else {
            foreach($transferProcessings->cursor() as $transferProcessing) {
                yield $transferProcessing;
            }
        }
    }
}
