<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Role;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\User;

class SocialiteController extends Controller
{

    public function redirectToGoogle()
    {
        // Generate the Google OAuth redirection URL
        $redirectUrl = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        // dd($redirectUrl);
        // Return the URL to the frontend
        return response()->json(['redirect_url' => $redirectUrl]);
    }


  

    public function handleGoogleCallback(Request $request)
    {
        try {
            // Retrieve the "code" sent by Google
            $code = $request->query('code');
            
            if (!$code) {
                return redirect(env('FRONT_APP_URL') . "/auth/google/callback?error=missing_code");
            }
    
            // Exchange the authorization code for user details
            $googleUser = Socialite::driver('google')->stateless()->user();
            // Find or create the user in your database
            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();
            if (!$user) {
                $user = User::create([
                    'full_name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'role_id' => Role::getUserRoleId(),
                    'role_name' => Role::$user,
                    'status' => User::$active,
                    'verified' => true,
                    'password' => null,
                ]);
               
            }
            
            // Ensure the Google ID is stored
            $user->update(['google_id' => $googleUser->id]);
    
            // Generate JWT token for authentication
            $token = auth('api')->tokenById($user->id);
    
            // Redirect to the React frontend with token
            return redirect(env('FRONT_APP_URL') . "/auth/google/callback?token=" . $token);
        } catch (\Exception $e) {
            return redirect(env('FRONT_APP_URL') . "/auth/google/callback?error=authentication_failed");
        }
    }
    



    public function redirectToFacebook()
    {
        $redirectUrl =  Socialite::driver('facebook')->stateless()->redirect();
        return response()->json(['redirect_url' => $redirectUrl]);
    }

    public function handleFacebookCallback(Request $request)
    {
        validateParam($request->all(), [
            'email' => 'required|email',
            'name' => 'required',
            'id' => 'required'
        ]);
        $data = $request->all();
        $user = User::where('facebook_id', $data['id'])->orWhere('email', $data['email'])->first();
        $registered = true;
        if (empty($user)) {
            $registered = false;
            $user = User::create([
                'full_name' => $data['name'],
                'email' => $data['email'],
                'facebook_id' => $data['id'],
                'role_id' => Role::getUserRoleId(),
                'role_name' => Role::$user,
                'status' => User::$active,
                'verified' => true,
                'password' => null
            ]);
        }
        $data = [];
        $data['user_id'] = $user->id;
        $data['already_registered'] = $registered;
        if ($registered) {

            $token = auth('api')->tokenById($user->id);
            $data['token'] = $token;
            return apiResponse2(1, 'login', trans('api.auth.login'), $data);
        }
        return apiResponse2(1, 'registered', trans('api.auth.registered'), $data);
    }

    public function redirectToApple() {}

    public function handleAppleCallback(Request $request) {}
}
