<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class LoginController extends Controller
{
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

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ];

        validateParam($request->all(), $rules);

        return $this->attemptLogin($request);

    }

    public function username()
    {
        $email_regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        if (empty($this->username)) {
            $this->username = 'mobile';
            if (preg_match($email_regex, request('username', null))) {
                $this->username = 'email';
            }
        }
        return $this->username;
    }

    protected function attemptLogin(Request $request)
    {
        $credentials = [
            'email' => $request->get('email'),
            'password' => $request->get('password')
        ];


        if (!$token = auth('api')->attempt($credentials)) {
            return apiResponse2(0, 'invalid', "invalid email or password");
        }
        return $this->afterLogged($request, $token);
    }

    public function afterLogged(Request $request, $token, $verify = false)
    {
        $user = auth('api')->user();

        if ($user->ban) {
            $time = time();
            $endBan = $user->ban_end_at;
            if (!empty($endBan) and $endBan > $time) {
                auth('api')->logout();
                return apiResponse2(0, 'banned_account', "your account has been banned");
            } elseif (!empty($endBan) and $endBan < $time) {
                $user->update([
                    'ban' => false,
                    'ban_start_at' => null,
                    'ban_end_at' => null,
                ]);
            }

        }

        if ($user->status != User::$active and !$verify) {
            // auth('api')->logout();
            auth('api')->logout();
            return apiResponse2(0, 'inactive_account', trans('auth.inactive_account'));
            //  dd(apiAuth());
            // $verificationController = new VerificationController();
            // $checkConfirmed = $verificationController->checkConfirmed($user, 'email', $request->input('email'));

            // if ($checkConfirmed['status'] == 'send') {

            //     return apiResponse2(0, 'not_verified', "can't login before verify your acount");

            // } elseif ($checkConfirmed['status'] == 'verified') {
            //     $user->update([
            //         'status' => User::$active,
            //     ]);
            // }
        } elseif ($verify) {
            $user->update([
                'status' => User::$active,
            ]);

        }

        if ($user->status != User::$active) {
            \auth('api')->logout();
            return apiResponse2(0, 'inactive_account', trans('auth.inactive_account'));
        }

        $profile_completion = [];
        $data  ['token'] = $token;
        $data['user'] = [
            "id" => $user->id,
            "full_name" => $user->full_name,
            "role_name" => $user->role_name,
            "user_code" => $user->user_code,
            "mobile" => $user->mobile,
            "email" => $user->email,
            "status" => $user->status,
            "as_student" => $user->student
        ];
        if (!$user->full_name) {
            $profile_completion[] = 'full_name';
            $data['profile_completion'] = $profile_completion;
        }

        return apiResponse2(1, 'login', "user login successfully", $data);


    }

    public function logout()
    {
        auth('api')->logout();
        if (!apiAuth()) {
            return apiResponse2(1, 'logout', trans('auth.logout'));
        }
        return apiResponse2(0, 'failed', trans('auth.logout.failed'));
    }


}
