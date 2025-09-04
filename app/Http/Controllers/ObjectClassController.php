<?php

namespace App\Http\Controllers;

use App\Models\ObjectClass;

class ObjectClassController extends Controller {
    /**
     * @api              {get} /api/objectClasses 索取設備類別列表
     * @apiGroup         ObjectClass
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.objectClasses 設備類別列表
     * @apiSuccess {String} data.objectClasses.object_class 設備類別代碼
     * @apiSuccess {String} data.objectClasses.name 設備類別名稱
     *
     * @apiSampleRequest off
     */
    public function index(): array {
        $objectClasses = ObjectClass::whereEnable(1)->orderBy('object_class')->get();

        return [
            'status' => 0,
            'data'   => [
                'objectClasses' => $objectClasses
            ]
        ];
    }
}
