<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Laravel\Passport\ApiTokenCookieFactory;

class ForgotPasswordController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    protected $cookieFactory;

    /**
     * Create a new controller instance.
     *
     * @param ApiTokenCookieFactory $cookieFactory
     */
    public function __construct(ApiTokenCookieFactory $cookieFactory) {
        $this->cookieFactory = $cookieFactory;
        $this->middleware('guest');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request) {
        $this->validateEmail($request);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink($request->only('email'));

        if($response == Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => 0
            ]);
        } else {
            return response()->json([
                'status' => config('errors.invalid_user')
            ]);
        }
    }

}
