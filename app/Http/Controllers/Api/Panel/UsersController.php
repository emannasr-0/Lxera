<?php

namespace App\Http\Controllers\Api\Panel;

use App\Bitwise\UserLevelOfTraining;
use App\Http\Controllers\Api\Controller;
use App\Http\Controllers\Api\Objects\UserObj;
use App\Http\Resources\InstallmentResource;
use App\Models\Category;
use App\Models\Newsletter;

use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\UserMeta;
use App\Models\Follow;

use App\Models\UserZoomApi;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

use App\Http\Controllers\Api\UploadFileManager;
use App\Mixins\Installment\InstallmentPlans;

use App\Mixins\RegistrationPackage\UserPackage;
use App\Models\DeleteAccountRequest;
use App\Models\Meeting;
use App\Models\Region;
use App\Models\ReserveMeeting;
use App\Models\Role;
use App\Models\UserBank;
use App\Models\UserOccupation;
use App\Models\UserSelectedBank;
use App\Models\UserSelectedBankSpecification;
use App\Student;
use App\StudentRequirement;
use App\BundleStudent;
use App\Models\Accounting;
use App\Models\OfflineBank;
use App\Models\OfflinePayment;
use App\Models\PaymentChannel;
use App\Http\Controllers\Web\traits\InstallmentsTrait;
use App\Http\Resources\ActiveBundleResource;
use App\Models\Bundle;
use App\Models\UserReference;

class UsersController extends Controller

{

    public function setting()
    {
        $user = apiAuth();
        return apiResponse2(
            1,
            'retrieved',
            trans('api.public.retrieved'),
            [
                'user' => $user->details
            ]
        );
    }

    public function updateImages(Request $request)
    {
        $user = apiAuth();
        if ($request->file('profile_image')) {
            validateParam($request->all(), [
                'profile_image' => 'file|mimes:jpeg,jpg,png',

            ]);
            $profileImage = $this->createImage($user, $request->file('profile_image'));
            $user->update([
                'avatar' => $profileImage
            ]);
        }

        if ($request->file('identity_scan')) {

            $storage = new UploadFileManager($request->file('identity_scan'));

            $user->update([
                'identity_scan' => $storage->storage_path,
            ]);
        }

        if ($request->file('certificate')) {

            $storage = new UploadFileManager($request->file('certificate'));

            $user->update([
                'certificate' => $storage->storage_path,
            ]);
        }

        return sendResponse([],  trans('api.public.updated'));
    }


    public function update1(Request $request)
    {
        $available_inputs = [
            'full_name',
            'language',
            'email',
            'mobile',
            'newsletter',
            'public_message',
            'timezone',
            'password',
            'about',
            'bio',
            'account_type',
            'iban',
            'account_id',
            'level_of_training',
            'meeting_type',
            'country_id',
            'province_id',
            'city_id',
            'district_id',
            'location'
        ];
        $meta = ['address', 'gender', 'age'];

        $user = apiAuth();

        validateParam($request->all(), [
            'full_name' => 'string',
            'language' => 'string',
            'email' => 'email|unique:users,email,' . $user->id,
            'mobile' => 'numeric|unique:users,mobile,' . $user->id,
            'timezone' => ['string', Rule::in(getListOfTimezones())],
            'public_message' => 'boolean',
            'newsletter' => 'boolean',
            // 'password' => 'required|string|min:6',

            'account_type' => Rule::in(getOfflineBanksTitle()),
            'iban' => 'required_with:account_type',
            'account_id' => 'required_with:account_type',
            // 'identity_scan' => 'required_with:account_type',

            'bio' => 'nullable|string|min:3|max:48',
            'level_of_training' => 'array|in:beginner,middle,expert',
            'meeting_type' => 'in:in_person,all,online',

            'gender' => 'nullable|in:man,woman',
            'location' => 'array|size:2',
            'location.latitude' => 'required_with:location',
            'location.longitude' => 'required_with:location',
            'address' => 'string',
            'country_id' => 'exists:regions,id',
            'province_id' => 'exists:regions,id',
            'city_id' => 'exists:regions,id',
            'district_id' => 'exists:regions,id',
        ]);

        $user = User::find($user->id);

        foreach ($available_inputs as $input) {
            if ($request->has($input)) {
                $value = $request->input($input);
                if ($input == 'level_of_training') {
                    $value = (new UserLevelOfTraining())->getValue($value);
                }
                if ($input == 'location') {
                    $value = DB::raw("POINT(" . $value['latitude'] . "," . $value['longitude'] . ")");
                }
                if ($input == 'password') {
                    $value = User::generatePassword($value);
                }

                $user->update([
                    $input => $value
                ]);
            }
        }


        if (!$user->isUser()) {
            if ($request->has('zoom_jwt_token') and !empty($request->input('zoom_jwt_token'))) {

                UserZoomApi::updateOrCreate(
                    [
                        'user_id' => $user->id,
                    ],
                    [
                        'jwt_token' => $request->input('zoom_jwt_token'),
                        'created_at' => time()
                    ]
                );
            } else {
                UserZoomApi::where('user_id', $user->id)->delete();
            }
        }

        if ($request->has('newsletter')) {
            $this->handleNewsletter($user->email, $user->id, $user->newsletter);
        }

        $this->updateMetas($meta);


        return apiResponse2(1, 'updated', trans('api.public.updated'));
    }

    public function update(Request $request)
    {
        $data = $request->all();
        $user = auth('api')->user();
        $organization = null;
        // if (!empty($data['organization_id']) and !empty($data['user_id'])) {
        //     $organization = auth('api')->user();
        //     $user = User::where('id', $data['user_id'])
        //     ->where('organ_id', $organization->id)
        //         ->first();
        // }
        $step = $data['step'] ?? 1;
        $nextStep = (!empty($data['next_step']) and $data['next_step'] == '1') ?? false;

        $rules = [
            'identity_scan' => 'required_with:account_type',
            'bio' => 'nullable|string|min:3|max:48',
        ];

        if ($step == 1) {
            $rules = array_merge($rules, [
                'full_name' => 'required|string',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'mobile' => 'required|unique:users,mobile,' . $user->id,
            ]);

            if ($user->student) {
                $rules['user_code'] = 'required|unique:users,user_code,' . $user->id;
            }
        }
        validateParam($request->all(), $rules);

        if (!empty($user)) {

            if (!empty($data['password'])) {
                validateParam($request->all(), [
                    'password' => 'required|string|confirmed|min:6',
                ]);

                $user->update([
                    'password' => User::generatePassword($data['password'])
                ]);
            }



            $updateData = [];

            if ($step == 1) {
                $joinNewsletter = (!empty($data['join_newsletter']) and $data['join_newsletter'] == 'on');

                $updateData = [
                    'email' => $data['email'],
                    'full_name' => $data['full_name'],
                    'mobile' => $data['mobile'],
                    // 'user_code' => $data['user_code'] ?? $user->user_code,
                    'language' => $data['language'] ?? null,
                    'timezone' => $data['timezone'] ?? null,
                    'currency' => $data['currency'] ?? null,
                    'newsletter' => $joinNewsletter,
                    'public_message' => (!empty($data['public_messages']) and $data['public_messages'] == 'on'),
                ];

                // if (!session()->has('impersonated')) {

                //     $updateData['email'] = $user->email;
                //     $updateData['mobile'] = $user->mobile;
                //     $updateData['user_code'] = $user->user_code;
                // }

                $this->handleNewsletter($data['email'], $user->id, $joinNewsletter);
            } elseif ($step == 2) {

                // $updateData = [
                //     'cover_img' => $data['cover_img'] ?? null,
                // ];

                if (!empty($data['profile_image'])) {
                    $profileImage = $this->createImage($user, $data['profile_image']);
                    $updateData['avatar'] = $profileImage;
                }
            } elseif ($step == 3) {
                $data = $request->except(['step', '_token', 'next_step']);
                if (!empty($request['identity_img'])) {
                    $request->validate([
                        'identity_img' => 'file|mimes:jpeg,jpg,png'
                    ]);
                    $identityImg = $request->file('identity_img');
                    if (!in_array(strtolower($identityImg->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'])) {
                        return back()->withInput($request->all())->withErrors(['identity_img' => "يجب أن تكون صورة الهوية الوطنية/جواز السفر صورة بإمتداد : jpeg, jpg, png والصورة المرفعة بامتداد " . $identityImg->getClientOriginalExtension()]);
                    }
                    $identityImgName =  $user->user_code . '_identity.' . $identityImg->getClientOriginalExtension();
                    $identityImgPath = $identityImg->storeAs('userIdentityImages', $identityImgName);
                    $data['identity_img'] = $identityImgPath;
                }
                // if (!session()->has('impersonated')) {
                //     $data['ar_name'] = $user->student->ar_name;
                //     $data['en_name'] = $user->student->en_name;
                // }
                $user->student->update($data);
            } elseif ($step == 4) {
                $data = $request->except(['step', '_token', 'next_step']);
                if (!empty($request['high_certificate_img'])) {
                    $request->validate([
                        'high_certificate_img' => 'file|mimes:jpeg,jpg,png'
                    ]);
                    $highCertificateImg = $request->file('high_certificate_img');
                    if (!in_array(strtolower($highCertificateImg->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'])) {
                        return back()->withInput($request->all())->withErrors(['high_certificate_img' => "يجب أن تكون صورة شهادة التخرج  بإمتداد : jpeg, jpg, png والصورة المرفعة بامتداد " . $highCertificateImg->getClientOriginalExtension()]);
                    }
                    $highCertificateImgName =  $user->user_code . '_highCertificate.' . $highCertificateImg->getClientOriginalExtension();
                    $highCertificateImgPath = $highCertificateImg->storeAs('userCertificateImages', $highCertificateImgName);
                    $data['high_certificate_img'] = $highCertificateImgPath;
                }

                if (!empty($request['secondary_certificate_img'])) {
                    $request->validate([
                        'secondary_certificate_img' => 'file|mimes:jpeg,jpg,png'
                    ]);
                    $secondaryCertificateImg = $request->file('secondary_certificate_img');
                    if (!in_array(strtolower($secondaryCertificateImg->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'])) {
                        return back()->withInput($request->all())->withErrors(['secondary_certificate_img' => "يجب أن تكون صورة شهادة الثانوية العامة بإمتداد : jpeg, jpg, png والصورة المرفعة بامتداد " . $secondaryCertificateImg->getClientOriginalExtension()]);
                    }
                    $secondaryCertificateImgName =  $user->user_code . '_secondaryCertificate.' . $secondaryCertificateImg->getClientOriginalExtension();
                    $secondaryCertificateImgPath = $secondaryCertificateImg->storeAs('userCertificateImages', $secondaryCertificateImgName);
                    $data['secondary_certificate_img'] = $secondaryCertificateImgPath;
                }

                $user->student->update($data);
            } elseif ($step == 5) {
                $data = [
                    "job" => $request->workStatus == 1 ? $request->job : null,
                    "job_type" => $request->workStatus == 1 ? $request->job : null,
                    "healthy_problem" => $request->healthy == 1 ? $request->healthy_problem : null,
                    "disabled_type" => $request->disabled == 1 ? $request->disabled_type : null,
                    "deaf" => $request->deaf,
                ];
                $user->student->update($data);
            } elseif ($step == 6) {
                $user->student->update($request->except(['step', '_token', 'next_step']));
            } elseif ($step == 10) {
                UserOccupation::where('user_id', $user->id)->delete();
                if (!empty($data['occupations'])) {

                    foreach ($data['occupations'] as $category_id) {
                        UserOccupation::create([
                            'user_id' => $user->id,
                            'category_id' => $category_id
                        ]);
                    }
                }
            } elseif ($step == 11) {
                $updateData = [
                    'identity_scan' => $data['identity_scan'] ?? '',
                    'certificate' => $data['certificate'] ?? '',
                    'address' => $data['address'] ?? '',
                ];

                if (!empty($data['bank_id'])) {
                    UserSelectedBank::query()->where('user_id', $user->id)->delete();

                    $userSelectedBank = UserSelectedBank::query()->create([
                        'user_id' => $user->id,
                        'user_bank_id' => $data['bank_id']
                    ]);

                    if (!empty($data['bank_specifications'])) {
                        $specificationInsert = [];

                        foreach ($data['bank_specifications'] as $specificationId => $specificationValue) {
                            if (!empty($specificationValue)) {
                                $specificationInsert[] = [
                                    'user_selected_bank_id' => $userSelectedBank->id,
                                    'user_bank_specification_id' => $specificationId,
                                    'value' => $specificationValue
                                ];
                            }
                        }

                        UserSelectedBankSpecification::query()->insert($specificationInsert);
                    }
                }
            } elseif ($step == 12) {
                if (!$user->isUser()) {
                    if (!empty($data['zoom_api_key']) and !empty($data['zoom_api_secret'])) {
                        UserZoomApi::updateOrCreate(
                            [
                                'user_id' => $user->id,
                            ],
                            [
                                'api_key' => $data['zoom_api_key'] ?? null,
                                'api_secret' => $data['zoom_api_secret'] ?? null,
                                'created_at' => time()
                            ]
                        );
                    } else {
                        UserZoomApi::where('user_id', $user->id)->delete();
                    }
                }
            } elseif ($step == 13) {
                $updateData = [
                    'about' => $data['about'],
                    'bio' => $data['bio'],
                ];
            }

            if (!empty($updateData)) {
                $user->update($updateData);
            }

            $url = '/panel/setting';
            if (!empty($organization)) {
                $userType = $user->isTeacher() ? 'instructors' : 'students';
                $url = "/panel/manage/{$userType}/{$user->id}/edit";
            }

            if ($step <= 10) {
                if ($nextStep) {
                    $step = $step + 1;
                }

                $url .= '/step/' . (($step <= 9) ? $step : 10);
            }

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('panel.user_setting_success'),
                'status' => 'success'
            ];
            return sendResponse([], trans('panel.user_setting_success'));
        }
        return $this->forbidden();
    }

    public function updateUserBasicInformation(Request $request)
    {
        $user = auth('api')->user();
        $data = $request->all();

        $rules = [
            'full_name' => 'required|string|min:15',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile' => 'required|unique:users,mobile,',
            'password' => 'nullable|string|min:6',
            'timezone' => ['string', Rule::in(getListOfTimezones())],
            'language' => 'string',
            'public_message' => 'boolean',
            'newsletter' => 'boolean',
        ];

        $validData = validateParam($request->all(), $rules);

        $joinNewsletter = !empty($data['newsletter']);

        $this->handleNewsletter($user->email, $user->id, $joinNewsletter);


        if (!empty($validData['password'])) {
            $user->update([
                'password' => User::generatePassword($validData['password'])
            ]);
        }

        $updateData = [
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            // 'user_code' => $data['user_code'] ?? $user->user_code,
            'language' => $data['language'] ?? null,
            'timezone' => $data['timezone'] ?? null,
            'currency' => $data['currency'] ?? null,
            'newsletter' => $joinNewsletter,
            'public_message' => !empty($data['public_message']),
        ];

        $user->update($updateData);

        return sendResponse([], trans('panel.user_setting_success'));
    }

    public function updatePersonalDetails(Request $request)
    {
        $user = auth('api')->user();
        $data = $request->all();

        $rules = [
            'ar_name' => 'required|string|regex:/^[\p{Arabic} ]+$/u|max:255|min:15',
            'en_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|max:255|min:15',
            'birthdate' => 'required|date',
            'identifier_num' => 'required|numeric|min:6',
            'nationality' => 'required|string|min:3|max:25',
            'gender' => 'required|in:male,female',
            'country' => 'required|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
            'town' => 'required|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
            'area' => 'nullable|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
            'city' => 'nullable|string|max:255|min:3|regex:/^(?=.*[\p{Arabic}\p{L}])[0-9\p{Arabic}\p{L}\s]+$/u',
            'identity_img' => 'nullable|file|mimes:jpeg,jpg,png',
        ];
        $validData = validateParam($request->all(), $rules);

        if (!empty($request['identity_img'])) {
            $identityImg = $request->file('identity_img');
            if (!in_array(strtolower($identityImg->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'])) {
                return sendError([
                    'identity_img' => ["يجب أن تكون صورة الهوية الوطنية/جواز السفر صورة بإمتداد : jpeg, jpg, png والصورة المرفعة بامتداد " . $identityImg->getClientOriginalExtension()]
                ], "صيغة الهوية الوطنية غير صحيحة");
            }
            $identityImgName =  $user->user_code . '_identity.' . $identityImg->getClientOriginalExtension();
            $identityImgPath = $identityImg->storeAs('userIdentityImages', $identityImgName);
            $validData['identity_img'] = $identityImgPath;
        }

        $user->student->update($validData);
        return sendResponse([], trans('panel.user_setting_success'));
    }

    public function updateUserEducation(Request $request)
    {
        $user = auth('api')->user();
        $data = $request->all();

        $rules = [
            'certificate_type' => 'required|in:diploma,bachelor,master,PhD',
            'educational_qualification_country' => 'nullable|string',
            'university' => 'nullable|string',
            'faculty' => 'nullable|string',
            'education_specialization' => 'nullable|string',
            'graduation_year' => 'nullable|numeric',
            'gpa' => 'nullable|numeric',
            'high_certificate_img' => 'nullable|file|mimes:jpeg,jpg,png',

            'secondary_educational_qualification_country' => 'nullable|string',
            'educational_area' => 'nullable|string',
            'school' => 'nullable|string',
            'secondary_graduation_year' => 'nullable|numeric',
            'secondary_school_gpa' => 'nullable|numeric',
            'secondary_certificate_img' => 'nullable|file|mimes:jpeg,jpg,png',
            'experiences' => 'nullable|array',
            'experiences.' => 'string',
        ];
        $validData = validateParam($request->all(), $rules);

        if (!empty($request['high_certificate_img'])) {

            $highCertificateImg = $request->file('high_certificate_img');
            if (!in_array(strtolower($highCertificateImg->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'])) {
                return sendError([
                    'high_certificate_img' => ["يجب أن تكون صورة شهادة التخرج  بإمتداد : jpeg, jpg, png والصورة المرفعة بامتداد " . $highCertificateImg->getClientOriginalExtension()]
                ], "صيغة صورة شهادة التخرج غير صحيحة");
            }
            $highCertificateImgName =  $user->user_code . '_highCertificate.' . $highCertificateImg->getClientOriginalExtension();
            $highCertificateImgPath = $highCertificateImg->storeAs('userCertificateImages', $highCertificateImgName);
            $validData['high_certificate_img'] = $highCertificateImgPath;
        }

        if (!empty($request['secondary_certificate_img'])) {

            $secondaryCertificateImg = $request->file('secondary_certificate_img');
            if (!in_array(strtolower($secondaryCertificateImg->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'])) {
                return back()->withInput($request->all())->withErrors(['secondary_certificate_img' => "يجب أن تكون صورة شهادة الثانوية العامة بإمتداد : jpeg, jpg, png والصورة المرفعة بامتداد " . $secondaryCertificateImg->getClientOriginalExtension()]);
            }
            $secondaryCertificateImgName =  $user->user_code . '_secondaryCertificate.' . $secondaryCertificateImg->getClientOriginalExtension();
            $secondaryCertificateImgPath = $secondaryCertificateImg->storeAs('userCertificateImages', $secondaryCertificateImgName);
            $validData['secondary_certificate_img'] = $secondaryCertificateImgPath;
        }


        $user->student->update($validData);
        return sendResponse([], trans('panel.user_setting_success'));
    }

    public function updateAddtionalDetails(Request $request)
    {
        $user = auth('api')->user();
        $data = $request->all();

        $rules = [
            'work_status' => 'required|boolean',
            'job_type' => 'required_if:workStatus,1',
            'job' => 'required_if:workStatus,1',
            'healthy' => 'required|boolean',
            'healthy_problem' => 'required_if:healthy,1',
            'disabled' => 'required|boolean',
            'disabled_type' => 'required_if:disabled,1',
            'deaf' => 'required|boolean',
            'referral_person' => 'nullable|string',
            'relation' => 'nullable|string',
            'referral_email' => 'nullable|email',
            'referral_phone' => 'nullable|string',
        ];

        $validData = validateParam($request->all(), $rules);

        $data = [
            "job" => $request->work_status == 1 ? $request->job : null,
            "job_type" => $request->work_status == 1 ? $request->job_type : null,
            "healthy_problem" => $request->healthy == 1 ? $request->healthy_problem : null,
            "disabled_type" => $request->disabled == 1 ? $request->disabled_type : null,
            "deaf" => $request->deaf,
            "referral_person" => $request->referral_person ?? null,
            "relation" => $request->relation ?? null,
            "referral_email" => $request->referral_email ?? null,
            "referral_phone" => $request->referral_phone ?? null,
        ];

        $user->student->update($data);
        return sendResponse([], trans('panel.user_setting_success'));
    }
    private function handleNewsletter($email, $user_id, $joinNewsletter)

    {
        $check = Newsletter::where('email', $email)->first();
        if ($joinNewsletter) {
            if (empty($check)) {
                Newsletter::create([
                    'user_id' => $user_id,
                    'email' => $email,
                    'created_at' => time()
                ]);
            } else {
                $check->update([
                    'user_id' => $user_id,
                ]);
            }

            $newsletterReward = RewardAccounting::calculateScore(Reward::NEWSLETTERS);
            RewardAccounting::makeRewardAccounting($user_id, $newsletterReward, Reward::NEWSLETTERS, $user_id, true);
        } elseif (!empty($check)) {
            $reward = RewardAccounting::where('user_id', $user_id)
                ->where('item_id', $user_id)
                ->where('type', Reward::NEWSLETTERS)
                ->where('status', RewardAccounting::ADDICTION)
                ->first();

            if (!empty($reward)) {
                $reward->delete();
            }

            $check->delete();
        }
    }

    public function updatePassword(Request $request)
    {
        validateParam($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        $user = apiAuth();
        if (Hash::check($request->input('current_password'), $user->password)) {
            $user->update([
                'password' => User::generatePassword($request->input('new_password'))
            ]);
            $token = auth('api')->refresh();

            return sendResponse([
                'token' => $token
            ], trans('api.public.status', ['item' => 'الباسورد', 'status' => 'تم تغيير']),);
        }
        return sendError([
            'current_password' => [trans('update.invalid_current_password')]
        ],  trans('update.profile_setting_incorrect'));
    }

    private function updateMetas($updateUserMeta)
    {
        $user = apiAuth();
        foreach ($updateUserMeta as $name) {
            $value = request()->input($name);
            $checkMeta = UserMeta::where('user_id', operator: $user->id)
                ->where('name', $name)
                ->first();

            if (!empty($checkMeta)) {
                if (!empty($value)) {
                    $checkMeta->update([
                        'value' => $value
                    ]);
                } else {
                    $checkMeta->delete();
                }
            } else if (!empty($value)) {
                UserMeta::create([
                    'user_id' => $user->id,
                    'name' => $name,
                    'value' => $value
                ]);
            }
        }
    }

    public function indexMetas(){
        $user = apiAuth();
        $metas = UserMeta::where('user_id', $user->id)->get();

        return response()->json($metas);
    }

    public function followToggle(Request $request, $id)
    {
        // dd('ff') ;
        $authUser = apiAuth();
        validateParam($request->all(), [
            'status' => 'required|boolean'
        ]);

        $status = $request->input('status');

        $user = User::where('id', $id)->first();
        if (!$user) {
            abort(404);
        }
        $followStatus = false;
        $follow = Follow::where('follower', $authUser->id)
            ->where('user_id', $user->id)
            ->first();

        if ($status) {

            if (empty($follow)) {
                Follow::create([
                    'follower' => $authUser->id,
                    'user_id' => $user->id,
                    'status' => Follow::$accepted,
                ]);

                $followStatus = true;
            }
            return apiResponse2(1, 'followed', trans('api.user.followed'));
        }

        if (!empty($follow)) {

            $follow->delete();
            return apiResponse2(1, 'unfollowed', trans('api.user.unfollowed'));
        }

        return apiResponse2(0, 'not_followed', trans('api.user.not_followed'));
    }

    public function createImage($user, $img)
    {
        $folderPath = "/" . $user->id . '/avatar';

        //     $image_parts = explode(";base64,", $img);
        //   $image_type_aux = explode("image/", $image_parts[0]);
        //   $image_type = $image_type_aux[1];
        //  $image_base64 = base64_decode($image_parts[1]);
        // $file = uniqid() . '.' . $image_type;

        $file = uniqid() . '.' . $img->getClientOriginalExtension();
        $storage_path = $img->storeAs($folderPath, $file);
        return 'store/' . $storage_path;

        //    Storage::disk('public')->put($folderPath . $file, $img);

        //  return Storage::disk('public')->url($folderPath . $file);
    }

    // requirement index
    public function requirementIndex($step = 1)
    {


        $user = auth("api")->user();

        $student = $user->Student;

        if (!$student) {
            return apiResponse2(0, 'not_student', "you need to apply to diploma first");
        }

        $studentBundles = BundleStudent::where('student_id', $student->id)->get()->reverse();

        /* Installments */
        $bundleInstallments = [];

        foreach ($studentBundles as $studentBundle) {
            $hasBought = $studentBundle->bundle->checkUserHasBought($user);
            $boughtInstallment = $studentBundle->bundle->getInstallmentOrder();
            $canSale = ($studentBundle->bundle->canSale() && !$hasBought);
            $studentBundle->bundle->title = $studentBundle->bundle->title;
            $studentBundle->requirement = [
                "status" => !empty($studentBundle->studentRequirement) ? $studentBundle->studentRequirement->status : null,
                "upload_link" => "/panel/bundles/$studentBundle->id/requirements"
            ];


            // Check if the bundle meets the conditions
            if ($canSale && !empty($studentBundle->bundle->price) && $studentBundle->bundle->price > 0 && getInstallmentsSettings('status') && (empty($user) || $user->enable_installments)) {
                $installmentPlans = new InstallmentPlans($user);
                $installment = $installmentPlans->getPlans('bundles', $studentBundle->bundle->id, $studentBundle->bundle->type, $studentBundle->bundle->category_id, $studentBundle->bundle->teacher_id)->last() ?? null;

                $bundleInstallments[] = [
                    'studentBundle' => $studentBundle,
                    'installment' => InstallmentResource::make($installment),
                    'hasBought' => $hasBought,
                    'boughtInstallment' => $boughtInstallment,
                    "cache_payment_url" => "/panel/bundles/purchase",
                    "installment_payment_url" => "/panel/bundles/purchase/$installment->id"
                ];
            } else {

                $bundleInstallments[] = [
                    'studentBundle' => $studentBundle,
                    'installment' => null,
                    'hasBought' => $hasBought,
                    'boughtInstallment' => $boughtInstallment,
                    "cache_payment_url" => "/panel/bundles/purchase",
                    "installment_payment_url" => null
                ];
            }
        }
        return apiResponse2(1, 'requirements_details', "all data retireved successfully", $bundleInstallments ?? null);
    }

    // create requirement function
    public function createRequirement($studentBundleId)
    {
        $user = auth("api")->user();

        $student = $user->Student;

        $studentBundle = BundleStudent::find($studentBundleId);

        if (!$student) {
            return apiResponse2(0, 'not_student', "you need to apply to diploma first");
        }

        if (!$studentBundle) {
            return apiResponse2(0, "doesn't match", "this is not your bundle url");
        }

        $data = [
            "user_code" => $user->user_code,
            'requirementUploaded' => false,
            'requirementStatus' => StudentRequirement::pending,
            'bundle' => ActiveBundleResource::make($studentBundle->bundle),
            'studentBundleId' => $studentBundleId,
            "requirmentsFile" => "https://anasacademy.uk/wp-content/uploads/2023/12/نموذج-عقد-اتفاقية-التحاق-متدربـ-النسخة-الاخيرة.pdf"
        ];

        $studentRequirments = $studentBundle->studentRequirement;

        if ($studentRequirments) {

            $data["requirementUploaded"] = true;
            $data["requirementStatus"] = $studentRequirments->status;
        }

        return apiResponse2(0, 'create_requirement', "retieve what you need to upload requirements", $data);
    }


    // store requirements
    public function storeRequirement(Request $request, $studentBundleId)
    {
        $rules = [
            'user_code' => 'required|string',
            'program' => 'required|string',
            'specialization' => 'required|string',
            'identity_type' => 'required|string',
            'identity_attachment' => 'required|file|mimes:jpeg,jpg,png,pdf',
            'admission_attachment' => 'required|file|mimes:pdf|max:20480',
        ];
        validateParam($request->all(), $rules);
        $user = auth("api")->user();

        $student = $user->Student;

        $studentBundle = BundleStudent::find($studentBundleId);

        if (!$student) {
            return apiResponse2(0, 'not_student', "you need to apply to diploma first");
        }

        if (!$studentBundle) {
            return apiResponse2(0, "doesn't match", "this is not your bundle url");
        }


        $studentRequirments = $studentBundle->studentRequirement;


        $identity_attachment = $request->file('identity_attachment');
        $identity_attachmentName =  $user->user_code . '_' . $request['identity_type'] . '.' . $identity_attachment->getClientOriginalExtension();
        $identity_attachmentPath = $identity_attachment->storeAs('studentRequirements', $identity_attachmentName);

        $admission_attachment = $request->file('admission_attachment');
        $admission_attachmentName =  $user->user_code . '_addmission.' . $admission_attachment->getClientOriginalExtension();
        $admission_attachmentPath = $admission_attachment->storeAs('studentRequirements', $admission_attachmentName);



        $data = [
            'bundle_student_id' => $studentBundle->id,
            'identity_type' => $request['identity_type'],
            'identity_attachment' => $identity_attachmentPath,
            'admission_attachment' => $admission_attachmentPath,
        ];

        if ($studentRequirments) {
            if ($studentRequirments->status != StudentRequirement::rejected) {
                return apiResponse2(1, 'already_upload', "You upload requirements before successfully, go to requirements section to view its status");
            }
            $data['status'] = StudentRequirement::pending;
            $studentRequirments->update($data);
        } else {
            StudentRequirement::create($data);
        }
        return apiResponse2(1, 'success', "requirements uploaded successfully, wait to be reviewed");
    }


    public function storeMetas(Request $request)
    {
        $data = $request->all();
        $rules = [
            'name' => 'required|string',
            'value' => 'required|string',
        ];

        validateParam($request->all(), $rules);

        $user = auth("api")->user();

        $meta = UserMeta::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'value' => $data['value'],
        ]);

        return sendResponse( $meta , 'data stored successfully');
    }

    public function updateMeta(Request $request, $meta_id)
    {
        $data = $request->all();
        $user = auth('api')->user();
        $rules = [
            'name' => 'required|string',
            'value' => 'required|string',
        ];
        validateParam($request->all(), $rules);

        $meta = UserMeta::where('id', $meta_id)
            ->where('user_id', $user->id)
            ->where('name', $data['name'])
            ->first();

        if (!empty($meta)) {
            $meta->update([
                'value' => $data['value']
            ]);

            return sendResponse($meta, "meta is updated successfully");
        }

        return sendError([], "reference not found or not belong to you", 404);
    }

    public function deleteMeta(Request $request, $meta_id)
    {
        $data = $request->all();
        $user = auth('api')->user();

        UserMeta::where('id', $meta_id)
            ->where('user_id', $user->id)
            ->delete();

        return sendResponse([], "meta is deleted successfully");
    }

    public function indexReferences(){
        $user = apiAuth();
        $reference = UserReference::where('user_id', $user->id)->get();
        return response()->json($reference);
    }

    public function indexbusinesslinks(){
        $user = apiAuth();
        $reference = UserMeta::where('user_id', $user->id)->where('name','link')->get();
        return response()->json($reference);
    }

    
    public function storeReference(Request $request)
    {
        $user = auth('api')->user();
        $data = $request->all();

        $rules = [
            'name' => 'required|string',
            'email' => 'required|email',
            'workplace' => 'required|string',
            'relationship' => 'required|string',
            'job_title' => 'required|string',
        ];

        $validData = validateParam($request->all(), $rules);
        $validData['user_id'] = $user->id;
        $reference = UserReference::create($validData);

        return sendResponse($reference, "reference is created successfully");
    }

    public function updateReference(Request $request, $reference_id)
    {
        $data = $request->all();
        $user = auth('api')->user();

        $rules = [
            'name' => 'required|string',
            'email' => 'required|email',
            'workplace' => 'required|string',
            'relationship' => 'required|string',
            'job_title' => 'required|string',
        ];

        $validData = validateParam($request->all(), $rules);
        $reference = UserReference::where('id', $reference_id)
            ->where('user_id', $user->id)
            ->first();


        if (!empty($reference)) {
            $reference->update($validData);

            return sendResponse($reference, "reference is updated successfully");
        }

        return sendError([], "reference not found or not belong to you", 404);
    }

    public function deleteReference(Request $request, $reference_id)
    {
        $data = $request->all();
        $user = auth('api')->user();

        UserReference::where('id', $reference_id)
            ->where('user_id', $user->id)
            ->delete();



        return sendResponse([], "reference is deleted successfully");
    }
}
