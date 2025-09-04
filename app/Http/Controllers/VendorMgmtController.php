<?php

namespace App\Http\Controllers;

use App\Models\VendorMgmt;
use Illuminate\Http\Request;
use Validator;

class VendorMgmtController extends Controller {
    /**
     * @api              {get} /api/vendorMgmts 索取供應商列表
     * @apiGroup         VendorMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.vehicleMgmts 供應商列表
     * @apiSuccess {String} data.vehicleMgmts.vendor 供應商名稱
     * @apiSuccess {String} data.vehicleMgmts.vendor_vat 供應商統編
     * @apiSuccess {String} data.vehicleMgmts.vendor_support 供應商電話
     *
     * @apiSampleRequest off
     */
    public function index() {
        $vendorMgmts = VendorMgmt::orderBy('vendor')->get();

        return [
            'status' => 0,
            'data'   => [
                'vendorMgmts' => $vendorMgmts
            ]
        ];
    }

    /**
     * @api              {get} /api/vendorMgmt/{vendor} 索取單筆供應商列表
     * @apiGroup         VendorMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.vehicleMgmt 供應商列表
     * @apiSuccess {String} data.vehicleMgmt.vendor 供應商名稱
     * @apiSuccess {String} data.vehicleMgmt.vendor_vat 供應商統編
     * @apiSuccess {String} data.vehicleMgmt.vendor_support 供應商電話
     *
     * @apiSampleRequest off
     */
    public function show($vendor) {
        $vendorMgmt = VendorMgmt::whereVendor($vendor)->firstOrFail();

        return [
            'status' => 0,
            'data'   => [
                'vendorMgmt' => $vendorMgmt
            ]
        ];
    }

    /**
     * @api              {post} /api/vendorMgmts 新增單筆供應商資料
     * @apiGroup         VendorMgmts
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} vendor 供應商
     * @apiParam {String} vendor_vat 供應商統編
     * @apiParam {String} vendor_support 供應商電話和地址
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功<br>-7：該供應商已存在
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.vendorMgmt 供應商資訊
     * @apiSuccess {String} data.vendorMgmt.vendor 供應商名稱
     *
     * @apiSampleRequest off
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'vendor'         => 'required|max:255',
            'vendor_vat'     => 'required|max:255',
            'vendor_support' => 'required|max:65535'
        ]);
        if($validator->fails())
            return response(null, 422);

        $vendor = $request->input('vendor');
        $vendorMgmt = VendorMgmt::withTrashed()->whereVendor($vendor)->first();
        if($vendorMgmt) {
            if($vendorMgmt->deleted_at)
                $vendorMgmt->restore();
        } else {
            $vendorMgmt = new VendorMgmt();
        }

        $vendorMgmt->vendor = $request->input('vendor');
        $vendorMgmt->vendor_vat = $request->input('vendor_vat');
        $vendorMgmt->vendor_support = $request->input('vendor_support');
        $vendorMgmt->save();

        return [
            'status' => 0,
            'data'   => [
                'vendorMgmt' => $vendorMgmt
            ]
        ];
    }

    /**
     * @api              {patch} /api/vendorMgmts/{vendor} 更新單筆供應商資料
     * @apiGroup         VendorMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} vendor 供應商
     * @apiParam {String} vendor_vat 供應商統編
     * @apiParam {String} vendor_support 供應商電話和地址
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     *
     * @apiSampleRequest off
     */
    public function update(Request $request, $vendor) {
        $validator = Validator::make($request->all(), [
            'vendor'         => 'required|max:255',
            'vendor_vat'     => 'required|max:255',
            'vendor_support' => 'required|max:65535'
        ]);
        if($validator->fails())
            return response(null, 422);

        $vendorMgmt = VendorMgmt::whereVendor($vendor)->firstOrFail();
        $vendorMgmt->vendor = $request->input('vendor');
        $vendorMgmt->vendor_vat = $request->input('vendor_vat');
        $vendorMgmt->vendor_support = $request->input('vendor_support');
        $vendorMgmt->save();

        return [
            'status' => 0,
            'data'   => [
                'vendorMgmt' => $vendorMgmt
            ]
        ];
    }

    /**
     * @api              {delete} /api/vendorMgmts/{vendor} 刪除單筆供應商
     * @apiGroup         VendorMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam  {Number} vendor 要刪除的供應商名稱
     *
     * @apiSampleRequest off
     */
    public function destroy($vendor): array {
        $vendorMgmt = VendorMgmt::whereVendor($vendor)->firstOrFail();
        $vendorMgmt->delete();

        return [
            'status' => 0
        ];
    }
}
