<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Laravel\Passport\ApiTokenCookieFactory;

class RegisterController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/admin';
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
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data): \Illuminate\Contracts\Validation\Validator {
        return Validator::make($data, [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|string|between:8,20|confirmed'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return \App\Models\User
     */
    protected function create(array $data): User {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    }

    public function register(Request $request) {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email|max:255|unique:users'
        ]);
        if($validator->fails()) {
            return response()->json([
                'status' => config('errors.account_already_been_taken')
            ]);
        }

        $validator = $this->validator($request->all());
        if($validator->fails())
            return response(null, 422);

        event(new Registered($user = $this->create($request->all())));

        $user->attachRole(2);

        return $this->registered($request, $user) ?: response()->json([
            'status' => 0
        ])->withCookie($this->cookieFactory->make($user->getKey(), $request->session()->token()));
    }

}
