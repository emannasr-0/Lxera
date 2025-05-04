<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CourseResource;
use App\Models\Affiliate;
use App\Models\Role;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNotifications;
use App\Models\Category;
use App\Models\Webinar;

class RegisterController extends Controller
{
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

    public function showRegistrationForm()
    {
        $categories = Category::whereNull('parent_id')->where('status', 'active')
        ->where(function ($query) {
            $query->whereHas('activeBundles')
            ->orWhereHas('activeSubCategories', function ($query) {
                $query->whereHas('activeBundles')->with('activeBundles');
            });
        })->with(['activeBundles', 'activeSubCategories'])
        ->get();

        $courses = Webinar::where('unattached', 1)->where('status', 'active')->get();
        $data = [
            'categories' => CategoryResource::collection($categories),
            'courses'  => CourseResource::collection($courses),
            'types' => ['programs', 'courses'],

        ];

        return apiResponse2('1', 200, null, $data);
    }
    public function stepRegister(Request $request, $step=1)
    {
        return response()->json(['status' => 'success', 'data' => 1]);
        if ($step == 1) {
            return $this->stepOne($request);
        } elseif ($step == 2) {
            return $this->stepTwo($request);
        } elseif ($step == 3) {
            return $this->stepThree($request);
        }
        return view('errors.404');
    }

    public function registerAPI(Request $request)
    {
        return $this->stepOne($request);
    }

    private function stepOne(Request $request)
    {
        // $registerMethod = getGeneralSettings('register_method') ?? 'email';
        // $data = $request->all();
        // $username = $this->username();

        // if ($registerMethod !== $username && $username) {
        //     // return apiResponse2(0, 'invalid_register_method', trans('api.auth.invalid_register_method'));
        //     return apiResponse2(0, 'invalid_register_method', "invalid register method, you can only register through email or mobile");
        // }

        $data = $request->all();
        if (!empty($data['mobile']) and !empty($data['country_code'])) {
            $data['mobile'] = $data['country_code'] . ' ' . ltrim($data['mobile'], '0');
        }

        $type = $data['type'] ?? null;

        $rules = [
            'country_code' => 'required',
            'mobile' => 'required|unique:users',
            'email' => 'required|email|max:255|unique:users',
            // 'full_name' => 'required|string|regex:/^[\p{Arabic} ]+$/u|max:255|min:5',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|same:password',
            'email_confirmation' => 'required|same:email',
            'referral_code' => 'nullable|exists:affiliates_codes,code',
            'type' => 'required|in:courses,programs',
            'bundle_id' => (($type == 'programs') ? 'required' : ""),
            'webinar_id' => (($type == 'courses') ? 'required' : ""),
        ];

        validateParam($data, $rules);
        // if (!empty($data['mobile'])) {
        //    $data['mobile'] = ltrim($data['country_code'], '+') . ltrim($data['mobile'], '0');
        // }
        // $userCase = User::where('email',$data['email'])->first();
        // if ($userCase) {
        //     //  $userCase->update(['password' => Hash::make($data['password'])]);
        //     $verificationController = new VerificationController();
        //     $checkConfirmed = $verificationController->checkConfirmed($userCase, $username, $data[$username]);

        //     if ($checkConfirmed['status'] == 'verified') {
        //         if ($userCase->full_name) {
        //             // return apiResponse2(0, 'already_registered', trans('api.auth.already_registered'));
        //             return apiResponse2(0, 'already_registered', "this user is already registered");
        //         } else {
        //             $userCase->update(['password' => Hash::make($data['password'])]);
        //             return apiResponse2(0, 'go_step_3', trans('api.auth.go_step_3'), [
        //                 'user_id' => $userCase->id
        //             ]);
        //         }
        //     } else {
        //         $userCase->update(['password' => Hash::make($data['password'])]);
        //         // return apiResponse2(0, 'go_step_2', trans('api.auth.go_step_2'), [
        //         //     'user_id' => $userCase->id
        //         // ]);
        //         return apiResponse2(0, 'go_step_2', "you are already registed, Go to Step 2 to Varify your account through sending the varification code", [
        //             'user_id' => $userCase->id
        //         ]);
        //     }
        // }


        $referralSettings = getReferralSettings();
        $usersAffiliateStatus = (!empty($referralSettings) and !empty($referralSettings['users_affiliate_status']));

        $user = User::create([
            'role_name' => 'registered_user',
            'role_id' => 13,
            'mobile' => $data['mobile'] ?? null,
            'email' => $data['email'] ?? null,
            'full_name' => $data['full_name'],
            'status' => User::$active,
            'access_content' => 1,
            'password' => Hash::make($data['password']),
            'affiliate' => $usersAffiliateStatus,
            'timezone' => $data['timezone'] ?? null,
            'created_at' => time(),
            'verified' => 1
        ]);

        // $verificationController = new VerificationController();
        // $verificationController->checkConfirmed($user, $username, $data[$username]);

        $notifyOptions = [
            '[u.name]' => $user->full_name,
            '[u.role]' => trans("update.role_{$user->role_name}"),
            '[time.date]' => dateTimeFormat($user->created_at, 'j M Y H:i'),
        ];

        event(new Registered($user));
        sendNotification("new_registration", $notifyOptions, 1);
        // $data['title'] = "انشاء حساب جديد";
        // $data['body'] = " تهانينا تم انشاء حساب لكم في اكاديمية انس للفنون
        //                     <br>
        //                     <br>
        //                     يمكن تسجيل الدخول من خلال هذا الرابط
        //                     <a href='https://lms.anasacademy.uk/login' class='btn btn-danger'>اضغط هنا للدخول</a>
        //                     <br>
        //                     بإستخدام هذا البريد الإلكتروني وكلمة المرور
        //                     <br>
        //                     <span style='font-weight:bold;'>البريد الالكتروني: </span> $user->email
        //                     <br>
        //                      <span style='font-weight:bold;'>كلمة المرور: </span>" . $request['password'] .
        //     "<br>
        //         ";

        // if (!empty($user) and !empty($user->email)) {
        //     Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'] ?? '', 'message' => $data['body'] ?? '']));
        // }

        return apiResponse2('1', 'stored', "user created successfully, Go to login page", [
            'user' => $user
        ]);
    }

    private function stepTwo(Request $request)
    {
        $data = $request->all();
        validateParam($data, [
            'user_id' => 'required|exists:users,id',

        ]);

        $user = User::find($data['user_id']);
        $verificationController = new VerificationController();
        $ee = $user->email ?? $user->mobile;
        return $verificationController->confirmCode($request, $ee);
    }

    private function stepThree(Request $request)
    {
        $data = $request->all();
        validateParam($request->all(), [
            'user_id' => 'required|exists:users,id',
            // 'full_name' => 'required|min:3',
            'referral_code' => 'nullable|exists:affiliates_codes,code'

        ]);

        $user = User::find($request->input('user_id'));
        $user->update([
            'full_name' => $data['full_name']
        ]);
        $referralCode = $request->input('referral_code', null);
        if (!empty($referralCode)) {
            Affiliate::storeReferral($user, $referralCode);
        }
        event(new Registered($user));
        $token = auth('api')->tokenById($user->id);
        $data['token'] = $token;
        $data['user_id'] = $user->id;
        return apiResponse2(1, 'login', trans('api.auth.login'), $data);
    }

    public function username()
    {
        $email_regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        $data = request()->all();

        if (empty($this->username)) {
            if (in_array('email', array_keys($data))) {
                $this->username = 'email';
            } else if (in_array('mobile', array_keys($data))) {
                $this->username = 'mobile';
            }
        }

        return $this->username ?? '';
    }
}
