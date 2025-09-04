<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\RegionMgmtUser;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class UserController extends Controller {
    /**
     * @api                     {get} /api/user 索取登入者的資料
     * @apiDescription          做持續判斷是否登入以及權限用，每跳轉一個頁面會執行一次
     * @apiGroup                User
     * @apiHeader               Content-Type application/json
     * @apiHeader               X-Requested-With XMLHttpRequest
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.user 登入者資料
     * @apiSuccess {Number} data.user.id 用戶編號
     * @apiSuccess {String} data.user.name 用戶姓名
     * @apiSuccess {String} data.user.account 用戶帳號
     * @apiSuccess {Object[]} data.user.roles 使用者綁定角色資料，最多只能綁一組
     * @apiSuccess {Object[]} data.user.roles.display_name 使用者綁定角色名稱
     *
     * @apiSampleRequest        off
     */
    public function user(): array {
        $user = Auth::user()->load([
            'roles.permissions'
        ]);
        $device = Device::with([
            'mirStatus.location',
            'mirStatus.missionQueue',
            'mirStatus.map',
            'mirStatus.vehicleErrorType'
        ])->orderByDesc('connected_at')->first();

        return [
            'status' => 0,
            'data'   => [
                'user'   => $user,
                'device' => $device
            ]
        ];
    }

    /**
     * @api              {get} /api/users 索取多筆用戶資料
     * @apiGroup         User
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} [account] 搜尋帳號
     * @apiParam {String} [name] 搜尋姓名
     * @apiParam {Number} [enable] 搜尋啟用狀態
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object[]} data.users 用戶列表資訊
     * @apiSuccess {Number} data.users.id 用戶編號
     * @apiSuccess {String} data.users.name 用戶姓名
     * @apiSuccess {Object[]} data.users.roles 用戶綁定的角色列表資訊，僅會有一筆
     * @apiSuccess {String} data.users.roles.display_name 用戶綁定的角色名稱
     * @apiSuccess {String} data.users.enable 用戶啟用狀態，1為啟用，0為禁用
     * @apiSuccess {Object} data.pagination 分頁資訊
     * @apiSuccess {Number} data.pagination.last_page 總共頁數
     * @apiSuccess {Number} data.pagination.current_page 目前頁數
     *
     * @apiSampleRequest off
     */
    public function index(Request $request): array {
        $users = new User();
        $account = $request->input('account');
        if($account) {
            $users = $users->where('account', 'like', "%$account%");
        }

        $name = $request->input('name');
        if($name) {
            $users = $users->where('name', 'like', "%$name%");
        }

        $enable = $request->input('enable');
        if($enable !== null) {
            $users = $users->where('enable', $enable);
        }
        $usersPaginate = $users->with([
            'readRegionMgmts',
            'writeRegionMgmts'
        ])->orderByDesc('created_at')->with('roles')->paginate(50);

        return [
            'status' => 0,
            'data'   => [
                'users'      => $usersPaginate->items(),
                'pagination' => [
                    'last_page'    => $usersPaginate->lastPage(),
                    'current_page' => $usersPaginate->currentPage()
                ]
            ]
        ];
    }

    /**
     * @api              {get} /api/users/{id} 索取單筆用戶資料
     * @apiGroup         User
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam  {Number} id 用戶編號
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.user 用戶資訊
     * @apiSuccess {Number} data.user.id 用戶編號
     * @apiSuccess {String} data.user.account 用戶帳號
     * @apiSuccess {String} data.user.name 用戶姓名
     * @apiSuccess {Object[]} data.user.roles 用戶綁定的角色列表資訊，僅會有一筆
     * @apiSuccess {Number} data.user.roles.id 用戶綁定的角色編號
     * @apiSuccess {String} data.user.roles.display_name 用戶綁定的角色名稱
     * @apiSuccess {Object[]} data.user.region_mgmts 用戶綁定的區域列表資訊，會有多筆
     * @apiSuccess {String} data.user.region_mgmts.region 用戶綁定的區域名稱
     * @apiSuccess {Object[]} data.user.vehicleMgmts 用戶綁定的AMR列表資訊，會有多筆
     * @apiSuccess {String} data.user.vehicleMgmts.vehicle_id 用戶綁定的AMR編號
     * @apiSuccess {Number} data.user.enable 用戶啟用狀態，1為啟用，0為禁用
     *
     * @apiSampleRequest off
     */
    public function show($id): array {
        $user = User::with([
            'roles',
            'readRegionMgmts',
            'writeRegionMgmts',
            'vehicleMgmts'
        ])->findOrFail($id);

        return [
            'status' => 0,
            'data'   => [
                'user' => $user
            ]
        ];
    }

    /**
     * @api              {post} /api/users 新增單筆用戶資料
     * @apiGroup         User
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} name 用戶姓名
     * @apiParam {String} account 用戶帳號
     * @apiParam {String} password 用戶密碼，8~20碼
     * @apiParam {String} password_confirmation 用戶確認密碼，8~20碼
     * @apiParam {Number} enable 用戶啟用狀態，1為啟用，0為禁用
     * @apiParam {Object[]} roles 用戶綁定的角色陣列，僅能塞一筆，也不能小於一筆
     * @apiParam {Number} roles.id 用戶綁定的角色編號
     * @apiParam {Object[]} [region_mgmts] 用戶綁定的區域列表資訊，會有多筆
     * @apiParam {String} [region_mgmts.region] 用戶綁定的區域名稱
     * @apiParam {Object[]} [vehicleMgmts] 用戶綁定的AMR列表資訊，會有多筆
     * @apiParam {String} [vehicleMgmts.vehicle_id] 用戶綁定的AMR編號
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.user 用戶資訊
     * @apiSuccess {Number} data.user.id 用戶編號
     * @apiSuccess {Object[]} data.user.roles 用戶綁定的角色列表資訊，僅會有一筆
     * @apiSuccess {Number} data.user.roles.id 用戶綁定的角色編號
     * @apiSuccess {String} data.user.roles.display_name 用戶綁定的角色名稱
     * @apiSuccess {Object[]} data.user.region_mgmts 用戶綁定的區域列表資訊，會有多筆
     * @apiSuccess {String} data.user.region_mgmts.region 用戶綁定的區域名稱
     * @apiSuccess {Object[]} data.user.vehicleMgmts 用戶綁定的AMR列表資訊，會有多筆
     * @apiSuccess {String} data.user.vehicleMgmts.vehicle_id 用戶綁定的AMR編號
     *
     * @apiSampleRequest off
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'roles'      => 'required|array',
            'roles.*.id' => 'required|integer|exists:roles,id',
            'enable'     => 'required|boolean',
            'account'    => 'required|max:255',
            'password'   => 'required|string|between:8,20,confirmed'
        ]);
        if($validator->fails()) {
            return response(null, 422);
        }

        $account = $request->input('account');
        $user = User::whereAccount($account)->first();
        if($user) {
            return [
                'status' => config('errors.repeat_account')
            ];
        }

        $user = new User();
        $user->name = $request->input('account');
        $user->enable = $request->input('enable');
        $user->account = $account;
        $user->password = Hash::make($request->input('password'));
        $user->save();

        $user = $this->syncAllM2MModels($request, $user);

        return [
            'status' => 0,
            'data'   => [
                'user' => $user
            ]
        ];
    }

    /**
     * @api              {patch} /api/users/{id} 更新單筆用戶資料
     * @apiGroup         User
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam {String} name 用戶姓名
     * @apiParam {String} account 用戶帳號
     * @apiParam {String} password 用戶密碼，8~20碼
     * @apiParam {String} password_confirmation 用戶確認密碼，8~20碼
     * @apiParam {Number} enable 用戶啟用狀態，1為啟用，0為禁用
     * @apiParam {Object[]} roles 用戶綁定的角色陣列，僅能塞一筆，也不能小於一筆
     * @apiParam {Number} roles.id 用戶綁定的角色編號
     * @apiParam {Object[]} [region_mgmts] 用戶綁定的區域列表資訊，會有多筆
     * @apiParam {String} [region_mgmts.region] 用戶綁定的區域名稱
     * @apiParam {Object[]} [vehicleMgmts] 用戶綁定的AMR列表資訊，會有多筆
     * @apiParam {String} [vehicleMgmts.vehicle_id] 用戶綁定的AMR編號
     *
     * @apiSuccess {Number} status 狀態碼<br>0：成功
     * @apiSuccess {Object} data 獲得數據
     * @apiSuccess {Object} data.user 用戶資訊
     * @apiSuccess {Number} data.user.id 用戶編號
     * @apiSuccess {Object[]} data.user.roles 用戶綁定的角色列表資訊，僅會有一筆
     * @apiSuccess {Number} data.user.roles.id 用戶綁定的角色編號
     * @apiSuccess {String} data.user.roles.display_name 用戶綁定的角色名稱
     * @apiSuccess {Object[]} data.user.region_mgmts 用戶綁定的區域列表資訊，會有多筆
     * @apiSuccess {String} data.user.region_mgmts.region 用戶綁定的區域名稱
     * @apiSuccess {Object[]} data.user.vehicleMgmts 用戶綁定的AMR列表資訊，會有多筆
     * @apiSuccess {String} data.user.vehicleMgmts.vehicle_id 用戶綁定的AMR編號
     *
     * @apiSampleRequest off
     */
    public function update(Request $request, $id): Response|array|Application|ResponseFactory {
        $validator = Validator::make($request->all(), [
            'roles'      => 'required|array',
            'roles.*.id' => 'required|integer|exists:roles,id',
            'enable'     => 'required|boolean',
            'account'    => 'required|max:255',
            'password'   => 'nullable|string|between:8,20,confirmed'
        ]);
        if($validator->fails())
            return response(null, 422);

        $account = $request->input('account');
        $user = User::findOrFail($id);
        $user->name = $request->input('account');
        $user->enable = $request->input('enable');
        $user->account = $account;
        $password = $request->input('password');
        if($password) {
            $user->password = Hash::make($password);
        }
        $user->save();

        $user = $this->syncAllM2MModels($request, $user);

        return [
            'status' => 0,
            'data'   => [
                'user' => $user
            ]
        ];
    }

    private function syncAllM2MModels(Request $request, User $user): User {
        $roles = collect($request->input('roles', []));
        $user->roles()->sync($roles->map(function($role) {
            return $role['id'];
        }));

        $readRegionMgmts = collect($request->input('read_region_mgmts', []));
        $writeRegionMgmts = collect($request->input('write_region_mgmts', []));
        $regionMgmtIds = $readRegionMgmts->merge($writeRegionMgmts)->pluck('id')->unique();
        RegionMgmtUser::whereUserId($user->id)->delete();
        foreach($regionMgmtIds as $regionMgmtId) {
            $regionMgmtUser = new RegionMgmtUser();
            $regionMgmtUser->user_id = $user->id;
            $regionMgmtUser->region_mgmt_id = $regionMgmtId;
            $readRegionMgmt = $readRegionMgmts->where('id', $regionMgmtId)->first();
            if($readRegionMgmt) {
                $regionMgmtUser->is_read = 1;
            } else {
                $regionMgmtUser->is_read = 0;
            }
            $writeRegionMgmt = $writeRegionMgmts->where('id', $regionMgmtId)->first();
            if($writeRegionMgmt) {
                $regionMgmtUser->is_write = 1;
            } else {
                $regionMgmtUser->is_write = 0;
            }
            $regionMgmtUser->save();
        }

        return $user->load([
            'roles',
            'readRegionMgmts',
            'writeRegionMgmts',
            'vehicleMgmts'
        ]);
    }

    /**
     * @api              {delete} /api/users/{id} 刪除單筆用戶資料
     * @apiGroup         User
     * @apiHeader        Content-Type application/json
     * @apiHeader        X-Requested-With XMLHttpRequest
     *
     * @apiParam  {Number} id 要刪除的用戶編號
     *
     * @apiSampleRequest off
     */
    public function destroy($id): array {
        $user = User::findOrFail($id);
        $user->delete();

        return [
            'status' => 0
        ];
    }
}
