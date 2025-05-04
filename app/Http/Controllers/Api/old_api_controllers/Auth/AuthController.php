<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Api\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Laravel\Socialite\Facades\Socialite;
use App\Models\Role;
use Exception;


class AuthController extends Controller
{
    //

    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['login','register']]);
    // }
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
            return response()->json(['error' => $validator->errors()], 400);
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
        ]);

        // Generate JWT token for the user after registration
        $token = JWTAuth::fromUser($user);

        // Return response with user data and token
        return response()->json([
            'message' => 'User registered successfully.',
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'role_name' => $user->role_name,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Attempt to verify the user's credentials
        if (!$token = JWTAuth::attempt($request->only(['email', 'password']))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Return token on successful login
        return response()->json(compact('token'));
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
        return response()->json(auth()->user());
    }

    // Logout method
    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

}
