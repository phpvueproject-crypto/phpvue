<?php

namespace App\Http\Controllers;

use App\Models\VertexConfiguration;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Validator;

class VertexConfigurationController extends Controller {
    /**
     * @api              {get} /api/vertexConfigurations 索取站點自定義屬性列表
     * @apiGroup         VertexConfiguration
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} [region] 區域，必填
     * @apiParam {String} [type] 站點自定義屬性名字，必填
     * @apiParam {Number} [vertex_type_id] 站點類型編號，選填
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.vertexConfigurations 站點自定義屬性列表
     * @apiSuccess {String} data.vertexConfigurations.type 類型
     * @apiSuccess {String} data.vertexConfigurations.data 資料
     *
     * @apiSampleRequest off
     */
    public function index(Request $request): array {
        $validator = Validator::make($request->all(), [
            'vertex_region_mgmt_id' => 'nullable|string|exists:region_mgmt,id',
            'type'                  => 'nullable|string',
            'vertex_type_id'        => 'nullable|exists:vertex_types,id',
            'vertex_is_deploy'      => 'nullable|boolean'
        ]);
        if($validator->fails()) {
            return [
                'status' => config('errors.account_already_been_taken')
            ];
        }

        $regionMgmtId = $request->input('vertex_region_mgmt_id');
        $vertexConfigurations = new VertexConfiguration();
        if($regionMgmtId) {
            $vertexConfigurations = $vertexConfigurations->whereRelation('vertex', 'region_mgmt_id', $regionMgmtId);
        }
        $type = $request->input('type');
        if($type) {
            $vertexConfigurations = $vertexConfigurations->where('type', $type);
            if($type == 'charger') {
                $vertexConfigurations = $vertexConfigurations->whereHas('vertex.vertexConfiguration', function(Builder $query) {
                    $query->where('type', 'device_name');
                })->with([
                    'vertex.vertexConfiguration' => function(HasOne $query) {
                        $query->where('type', 'device_name');
                    }
                ]);
            }
        }

        $vertexTypeId = $request->input('vertex_type_id');
        if($vertexTypeId) {
            $vertexConfigurations = $vertexConfigurations->whereRelation('vertex', 'vertex_type_id', $vertexTypeId);
        }

        $vertexIsDeploy = $request->input('vertex_is_deploy', 1);
        if($vertexIsDeploy !== null) {
            $vertexConfigurations = $vertexConfigurations->whereRelation('vertex', 'is_deploy', $vertexIsDeploy);
        }

        $vertexConfigurations = $vertexConfigurations->with('vertex')->get();

        return [
            'status' => 0,
            'data'   => [
                'vertexConfigurations' => $vertexConfigurations
            ]
        ];
    }
}
