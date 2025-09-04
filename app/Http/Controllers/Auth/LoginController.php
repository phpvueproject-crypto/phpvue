<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Laravel\Passport\ApiTokenCookieFactory;

class LoginController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected string $redirectTo = '/';
    protected ApiTokenCookieFactory $cookieFactory;

    /**
     * Create a new controller instance.
     *
     * @param ApiTokenCookieFactory $cookieFactory
     *
     * @return void
     */
    public function __construct(ApiTokenCookieFactory $cookieFactory) {
        $this->cookieFactory = $cookieFactory;
        $this->middleware('guest', ['except' => ['logout', 'refreshCsrfToken']]);
    }

    /**
     * @api                     {post} /login 登入
     * @apiDescription          登入時效為一天，一天後會自動登出
     * @apiGroup                User
     * @apiHeader               Content-Type application/json
     *
     * @apiParam {String} account 帳號
     * @apiParam {String} password 密碼，為8~20碼
     *
     * @apiSuccess {Number} status 狀態碼<br>0：登入<br>-1：目前此帳號沒綁定任何身份，請聯繫管理者！<br>-2：該帳號權限未開通或已被禁止登入！
     *
     * @apiSampleRequest        off
     */
    protected function sendLoginResponse(Request $request) {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);
        /** @var User $user */
        $user = $this->guard()->user();
        if($user->enable == 0) {
            Auth::logout();
            return [
                'status' => config('errors.disable_login')
            ];
        }

        $user = $user->load('roles');
        if($user->roles->count() == 0) {
            Auth::logout();
            return [
                'status' => config('errors.no_organization_is_bound')
            ];
        }

        return $this->authenticated($request, $user) ?: response()->json([
            'status' => 0
        ])->withCookie($this->cookieFactory->make($user->getKey(), $request->session()->token()));
    }

    public function refreshCsrfToken(): array {
        return [
            'status' => 0,
            'data'   => [
                'csrfToken' => csrf_token()
            ]
        ];
    }

    public function logout(Request $request): array {
        $this->guard()->logout();
        $request->session()->invalidate();

        return [
            'status' => 0
        ];
    }

    protected function attemptLogin(Request $request): bool {
        return $this->guard()->attempt([
            $this->username() => $request->input($this->username()),
            'password'        => $request->input('password')
        ], $request->filled('remember'));
    }

    public function username(): string {
        return 'account';
    }
}
