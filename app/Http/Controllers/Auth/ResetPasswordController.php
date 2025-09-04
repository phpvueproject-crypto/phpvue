<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Laravel\Passport\ApiTokenCookieFactory;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/admin';
    protected $cookieFactory;

    /**
     * Create a new controller instance.
     *
     * @param ApiTokenCookieFactory $cookieFactory
     *
     * @return void
     */
    public function __construct(ApiTokenCookieFactory $cookieFactory) {
        $this->cookieFactory = $cookieFactory;
        $this->middleware('guest')->except('updatePassword');
    }

    public function reset(Request $request) {
        $this->validate($request, $this->rules(), $this->validationErrorMessages());

        $response = $this->broker()->reset($this->credentials($request), function(User $user, $password) {
            $this->resetPassword($user, $password);
            $user->save();
        });

        if($response == Password::PASSWORD_RESET) {
            $user = Auth::user();
            return response()->json([
                'status' => 0
            ])->withCookie($this->cookieFactory->make($user->getKey(), $request->session()->token()));
        } else if($response == Password::INVALID_TOKEN) {
            return response()->json([
                'status' => config('errors.invalid_password_token')
            ]);
        } else {
            return response()->json([
                'status' => config('errors.invalid_user')
            ]);
        }
    }

}
