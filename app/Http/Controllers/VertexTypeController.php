<?php

namespace App\Http\Controllers;

use App\Models\VertexType;
use Illuminate\Http\Request;

class VertexTypeController extends Controller {
    /**
     * @api              {get} /api/vertexTypes 顯示多筆站點類型列表
     * @apiGroup         VertexType
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiSuccess {Number} status 狀態碼
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.vertexTypes 站點類型資料
     * @apiSuccess {Number} data.vertexTypes.id 站點類型編號
     * @apiSuccess {String} data.vertexTypes.name 站點類型名稱
     *
     * @apiSampleRequest off
     */
    public function index(Request $request): array {
        $vertexTypes = new VertexType();
        $id = $request->input('id');
        if($id) {
            $vertexTypes = $vertexTypes->where('id', $id);
        }
        $vertexTypes = $vertexTypes->orderBy('id')->with('vertexConfigurationTypes.vertexConfigurationColumn')->get();

        return [
            'status' => 0,
            'data'   => [
                'vertexTypes' => $vertexTypes
            ]
        ];
    }
}
