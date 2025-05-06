<?php

namespace App\Http\Controllers\Api\Auth;

use App\BundleStudent;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfile;
use App\Http\Resources\UserResource;
use App\Models\Api\User;
use App\Models\Bundle;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Laravel\Socialite\Facades\Socialite;
use App\Models\Role;
use App\Student;
use Exception;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //

    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['login','register']]);
    // }

    public function country_code()
    {
        return response()->json(getCountriesMobileCode());
    }
    public function register_bundle($id)
    {
        $user = auth('api')->user();

        if ($user) {
            //if user loggedin
            $bundle = Bundle::where('id', $id)->where('status', 'active')->first();

            if ($bundle) {
                $alreadyJoined = BundleStudent::where('bundle_id', $id)
                    ->where('student_id', $user->student->id)
                    ->exists();

                if (!$alreadyJoined) {
                    BundleStudent::create([
                        'student_id' => $user->student->id,
                        'bundle_id' => $id,
                        'status' => 'approved'
                    ]);
                }else{
                    //if user already applied to bundle
                    return redirect('/'); 
                }
            }
        }else{
            return redirect('register');
        }
    }

    public function register(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'mobile' => 'required|string|min:10|max:15|unique:users,mobile',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Create new user
        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile' => $request->mobile,
            'role_name' => 'registered_user',
            'role_id' => 13,
            'created_at' => now()->timestamp,  // Use Unix timestamp for created_at
            'updated_at' => now()->timestamp,
            'verified' => 1
        ]);


        //create student 
        $student = Student::create([
            'user_id' => $user->id,
            'en_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->mobile,
            'mobile' => $request->mobile,
            // 'status' => 'pending'
        ]);


        //create bundle student 
        $bundle_id = $request->bundle_id ?? 0;
        $studentBundle = BundleStudent::create(['student_id' => $student->id, 'bundle_id' => $bundle_id, 'status' => 'applying']);


        // Generate JWT token for the user after registration
        $token = JWTAuth::fromUser($user);

        // Return response with user data and token
        $data = [
            'user' => UserResource::make($user),
            'token' => $token,
        ];
        return apiResponse2(1, 'register', "User registered successfully.", $data);
    }

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ];

        validateParam($request->all(), $rules);

        return $this->attemptLogin($request);
    }

    protected function attemptLogin(Request $request)
    {
        $credentials = [
            'email' => $request->get('email'),
            'password' => $request->get('password')
        ];


        if (!$token = auth('api')->attempt($credentials)) {
            $errors = ['email', ["invalid email or password"]];
            // return sendError($errors, "invalid email or password" );
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
                return sendError([], "your account has been banned", 403);
                // return apiResponse2(0, 'banned_account', "your account has been banned");
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
            return sendError([], trans('auth.inactive_account'), 401);
            // return apiResponse2(0, 'inactive_account', trans('auth.inactive_account'));
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
            return sendError([], trans('auth.inactive_account'), 401);
            // return apiResponse2(0, 'inactive_account', trans('auth.inactive_account'));
        }

        $profile_completion = [];
        $data['token'] = $token;
        $data['user'] = UserResource::make($user);
        if (!$user->full_name) {
            $profile_completion[] = 'full_name';
            $data['profile_completion'] = $profile_completion;
        }

        // return sendResponse2($data, 'user login successfully' );
        return response()->json(['data' => $data]);
    }

    public function redirectToGoogle()
    {
        return response()->json([
            'message' => 'Redirecting to Google...',
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl()
        ]);
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            // Get the user data from Google
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Find the user in the database by Google ID or Email
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if (!$user) {
                // If the user doesn't exist, create a new user
                $user = User::create([
                    'full_name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'role_id' => 13, // Default role
                    'role_name' => Role::$registered_user, // Role for new users
                    'status' => User::$active,
                    'verified' => true,
                    'password' => null, // Random password for new users
                ]);
            } else {
                // If the user exists, update the Google ID if necessary
                $user->update([
                    'google_id' => $googleUser->getId(),
                ]);
            }

            // Generate JWT token for the authenticated user
            $token = JWTAuth::fromUser($user);

            // Return a successful response with user data and JWT token
            return response()->json([
                'message' => 'User successfully logged in via Google.',
                'user' => $user,
                'token' => $token
            ]);
        } catch (Exception $e) {
            // Handle errors and return response
            return response()->json([
                'message' => 'Failed to authenticate with Google.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function myProfile()
    {
        $user = auth('api')->user();

        return sendResponse(UserProfile::make($user), 'user profile data is returned successfully');
    }
    public function BriefProfile()
    {
        $user = auth('api')->user();
        $data = [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'user_code' => $user->user_code,
            'email' => $user->email,
            'avatar' => url($user->getAvatar(150)),
        ];
        return sendResponse($data, 'user profile data is returned successfully');
    }

    // Logout method
    public function logout()
    {
        auth('api')->logout();
        return response()->json(['success' => 'Successfully logged out']);
    }
}
