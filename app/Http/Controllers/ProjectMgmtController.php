<?php

namespace App\Http\Controllers;

use App\Models\ProjectMgmt;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Validator;

class ProjectMgmtController extends Controller {
    /**
     * @api              {get} /api/projectMgmts 索取專案列表
     * @apiGroup         ProjectMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.projectMgmts 專案列表
     * @apiSuccess {String} data.projectMgmts.project_name 專案名稱
     *
     * @apiSampleRequest off
     */
    public function index(): array {
        $user = Auth::user();
        $projectMgmts = new ProjectMgmt();
        $projectMgmts = $projectMgmts->whereHas('projectMgmts.regionMgmts.users', function(Builder $query) use ($user) {
            $query->where('id', $user->id);
        });
        $projectMgmts = $projectMgmts->with('regionMgmts.editUser')->get();

        return [
            'status' => 0,
            'data'   => [
                'projectMgmts' => $projectMgmts
            ]
        ];
    }

    /**
     * @api              {post} /api/projectMgmts 新增單筆專案資料
     * @apiGroup         ProjectMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} project_name 專案名稱
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     *
     * @apiSampleRequest off
     */
    public function store(Request $request): \Illuminate\Http\Response|array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:100'
        ]);
        if($validator->fails())
            return response(null, 422);

        $projectName = $request->input('project_name');
        $projectMgmt = ProjectMgmt::find($projectName);
        if($projectMgmt) {
            return [
                'status' => config('errors.data_repeat')
            ];
        }

        $projectMgmt = new ProjectMgmt();
        $projectMgmt->project_name = $projectName;
        $projectMgmt->profile = json_encode([
            'project_name' => $projectMgmt->project_name,
            'map'          => []
        ]);
        $projectMgmt->save();

        return [
            'status' => 0,
            'data'   => [
                'projectMgmt' => $projectMgmt
            ]
        ];
    }

    /**
     * @api              {patch} /api/projectMgmts/{region} 更新該張區域所有站點跟軌道
     * @apiGroup         ProjectMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} project_name 專案名稱
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     *
     * @apiSampleRequest off
     */
    public function update(Request $request, $projectName) {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:100'
        ]);
        if($validator->fails())
            return response(null, 422);

        $inputProjectName = $request->input('project_name');
        $projectMgmt = ProjectMgmt::find($projectName);
        if($projectMgmt) {
            return [
                'status' => config('errors.data_repeat')
            ];
        }

        $projectMgmt = ProjectMgmt::findOrFail($projectName);
        $projectMgmt->project_name = $inputProjectName;
        $projectMgmt->save();

        return [
            'status' => 0,
            'data'   => [
                'projectMgmt' => $projectMgmt
            ]
        ];
    }

    /**
     * @api              {get} /api/projectMgmts/{project_name} 獲取單筆專案資料
     * @apiGroup         ProjectMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} project_name 專案名稱
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.projectMgmt 專案資訊
     * @apiSuccess {String} data.projectMgmt.project_name 專案名稱
     *
     * @apiSampleRequest off
     */
    public function show($region) {
        $projectMgmt = ProjectMgmt::findOrFail($region);

        return [
            'status' => 0,
            'data'   => [
                'projectMgmt' => $projectMgmt
            ]
        ];
    }

    /**
     * @api              {delete} /api/projectMgmts/{id} 刪除單筆專案資料
     * @apiGroup         ProjectMgmt
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} project_name 專案名稱
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     *
     * @apiSampleRequest off
     */
    public function destroy($region) {
        $projectMgmt = ProjectMgmt::findOrFail($region);
        $projectMgmt->delete();

        return [
            'status' => 0
        ];
    }
}
