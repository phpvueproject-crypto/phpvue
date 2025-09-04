<?php

namespace App\Http\Controllers;

use App\Models\EquipmentClass;

class EquipmentClassController extends Controller {
    /**
     * @api              {get} /api/equipmentClasses 索取設備類別列表
     * @apiGroup         EquipmentClass
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.equipmentClasses 設備類別列表
     * @apiSuccess {String} data.equipmentClasses.equipment_class 設備類別代碼
     * @apiSuccess {String} data.equipmentClasses.name 設備類別名稱
     *
     * @apiSampleRequest off
     */
    public function index() {
        $equipmentClasses = EquipmentClass::whereEnable(1)->orderBy('equipment_class')->get();

        return [
            'status' => 0,
            'data'   => [
                'equipmentClasses' => $equipmentClasses
            ]
        ];
    }
}
