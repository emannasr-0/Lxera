<?php

namespace App\Http\Controllers\Panel;

use App\Bitwise\UserLevelOfTraining;
use App\Http\Controllers\Controller;
use App\Mixins\RegistrationPackage\UserPackage;
use App\Models\Category;
use App\Models\DeleteAccountRequest;
use App\Models\Meeting;
use App\Models\Newsletter;
use App\Models\Region;
use App\Models\ReserveMeeting;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Role;
use App\Models\UserBank;
use App\Models\UserMeta;
use App\Models\UserOccupation;
use App\Models\UserSelectedBank;
use App\Models\UserSelectedBankSpecification;
use App\Models\UserZoomApi;
use App\User;
use App\Student;
use App\StudentRequirement;
use App\BundleStudent;
use App\Models\UserReference;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use App\Models\Accounting;
use App\Models\OfflineBank;
use App\Models\OfflinePayment;
use App\Models\PaymentChannel;
use App\Http\Controllers\Web\traits\InstallmentsTrait;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\Bundle;
use App\Models\Sale;
use Illuminate\Support\Collection;

class UserController extends Controller
{
    use InstallmentsTrait;
    public function setting($step = 1)
    {
        $user = auth()->user();

        if (!empty($user->location)) {
            $user->location = \Geo::getST_AsTextFromBinary($user->location);

            $user->location = \Geo::get_geo_array($user->location);
        }

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $userMetas = $user->userMetas;

        if (!empty($userMetas)) {
            foreach ($userMetas as $meta) {
                $user->{$meta->name} = $meta->value;
            }
        }

        $occupations = $user->occupations->pluck('category_id')->toArray();


        $userLanguages = getGeneralSettings('user_languages');
        if (!empty($userLanguages) and is_array($userLanguages)) {
            $userLanguages = getLanguages($userLanguages);
        } else {
            $userLanguages = [];
        }

        $countries = null;
        $provinces = null;
        $cities = null;
        $districts = null;
        if ($step == 9) {
            $countries = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                ->where('type', Region::$country)
                ->get();

            if (!empty($user->country_id)) {
                $provinces = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                    ->where('type', Region::$province)
                    ->where('country_id', $user->country_id)
                    ->get();
            }

            if (!empty($user->province_id)) {
                $cities = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                    ->where('type', Region::$city)
                    ->where('province_id', $user->province_id)
                    ->get();
            }

            if (!empty($user->city_id)) {
                $districts = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                    ->where('type', Region::$district)
                    ->where('city_id', $user->city_id)
                    ->get();
            }
        }

        $userBanks = UserBank::query()
            ->with([
                'specifications'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'pageTitle' => trans('panel.settings'),
            'user' => $user,
            'categories' => $categories,
            'educations' => $userMetas->where('name', 'education'),
            'experiences' => $userMetas->where('name', 'experience'),
            'links' => $userMetas->where('name', 'link'),
            'references' => $user->references,
            'occupations' => $occupations,
            'userLanguages' => $userLanguages,
            'currentStep' => $step,
            'countries' => $countries,
            'provinces' => $provinces,
            'cities' => $cities,
            'districts' => $districts,
            'userBanks' => $userBanks,
        ];

        return view(getTemplate() . '.panel.setting.index', $data);
    }

    public function update(Request $request)
    {
        $data = $request->all();

        $organization = null;
        if (!empty($data['organization_id']) and !empty($data['user_id'])) {
            $organization = auth()->user();
            $user = User::where('id', $data['user_id'])
                ->where('organ_id', $organization->id)
                ->first();
        } else {
            $user = auth()->user();
        }

        $step = $data['step'] ?? 1;
        $nextStep = (!empty($data['next_step']) and $data['next_step'] == '1') ?? false;

        $rules = [
            'identity_scan' => 'required_with:account_type',
            'bio' => 'nullable|string|min:3|max:48',
        ];

        if ($step == 1) {
            $registerMethod = getGeneralSettings('register_method') ?? 'mobile';

            $rules = array_merge($rules, [
                'full_name' => 'required|string',
            ]);
            if (session()->has('impersonated')) {
                $rules['email'] = 'required|email|max:255|unique:users,email,' . $user->id;
                $rules['mobile'] ='required|unique:users,mobile,' . $user->id;
                if($user->student){
                    $rules['user_code'] = 'required|unique:users,user_code,' . $user->id;
                }
            } else {
                $data['email'] = $user->email;
                $data['mobile'] = $user->mobile;
                $data['user_code'] = $user->user_code;
            }
        }
        $this->validate($request, $rules);

        if (!empty($user)) {

            if (!empty($data['password'])) {
                $this->validate($request, [
                    'password' => 'required|confirmed|min:6',
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
                    'user_code' => $data['user_code'] ?? $user->user_code,
                    'language' => $data['language'] ?? null,
                    'timezone' => $data['timezone'] ?? null,
                    'currency' => $data['currency'] ?? null,
                    'newsletter' => $joinNewsletter,
                    'public_message' => (!empty($data['public_messages']) and $data['public_messages'] == 'on'),
                ];

                if (!session()->has('impersonated')) {

                    $updateData['email'] = $user->email;
                    $updateData['mobile'] = $user->mobile;
                    $updateData['user_code'] = $user->user_code;
                }

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
                if (!session()->has('impersonated')) {
                    $data['ar_name'] = $user->student->ar_name;
                    $data['en_name'] = $user->student->en_name;
                }
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
            return redirect($url)->with(['toast' => $toastData]);
        }
        return view('errors.404');
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

    public function createImage($user, $img)
    {
        $folderPath = "/" . $user->id . '/avatar/';

        $image_parts = explode(";base64,", $img);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file = uniqid() . '.' . $image_type;

        Storage::disk('public')->put($folderPath . $file, $image_base64);

        return Storage::disk('public')->url($folderPath . $file);
    }

    public function storeMetas(Request $request)
    {
        $data = $request->all();

        if (!empty($data['name']) and !empty($data['value'])) {

            if (!empty($data['user_id'])) {
                $organization = auth()->user();
                $user = User::where('id', $data['user_id'])
                    ->where('organ_id', $organization->id)
                    ->first();
            } else {
                $user = auth()->user();
            }

            UserMeta::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'value' => $data['value'],
            ]);

            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([], 422);
    }

    public function updateMeta(Request $request, $meta_id)
    {
        $data = $request->all();
        $user = auth()->user();

        if (!empty($data['user_id'])) {
            $checkUser = User::find($data['user_id']);

            if ((!empty($checkUser) and ($data['user_id'] == $user->id) or $checkUser->organ_id == $user->id)) {
                $meta = UserMeta::where('id', $meta_id)
                    ->where('user_id', $data['user_id'])
                    ->where('name', $data['name'])
                    ->first();

                if (!empty($meta)) {
                    $meta->update([
                        'value' => $data['value']
                    ]);

                    return response()->json([
                        'code' => 200
                    ], 200);
                }

                return response()->json([
                    'code' => 403
                ], 200);
            }
        }

        return response()->json([], 422);
    }

    public function deleteMeta(Request $request, $meta_id)
    {
        $data = $request->all();
        $user = auth()->user();

        if (!empty($data['user_id'])) {
            $checkUser = User::find($data['user_id']);

            if (!empty($checkUser) and ($data['user_id'] == $user->id or $checkUser->organ_id == $user->id)) {
                $meta = UserMeta::where('id', $meta_id)
                    ->where('user_id', $data['user_id'])
                    ->first();

                $meta->delete();

                return response()->json([
                    'code' => 200
                ], 200);
            }
        }

        return response()->json([], 422);
    }


    public function storeReference(Request $request)
    {
        $data = $request->all();

        if (!empty($data['name']) and !empty($data['email']) and !empty($data['workplace']) and !empty($data['relationship']) and !empty($data['job_title'])) {

            if (!empty($data['user_id'])) {
                $organization = auth()->user();
                $user = User::where('id', $data['user_id'])
                    ->where('organ_id', $organization->id)
                    ->first();
            } else {
                $user = auth()->user();
            }
            $data['user_id'] = $user->id;
            UserReference::create($data);

            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([], 422);
    }

    public function updateReference(Request $request, $reference_id)
    {
        $data = $request->all();
        $user = auth()->user();

        $data['user_id'] = $data['user_id'] ?? $user->id;

        $reference = UserReference::where('id', $reference_id)
            ->where('user_id', $data['user_id'])
            ->first();


        if (!empty($reference)) {
            $reference->update($data);

            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([
            'code' => 403
        ], 200);
        // return response()->json([], 422);
    }

    public function deleteReference(Request $request, $reference_id)
    {
        $data = $request->all();
        $user = auth()->user();

        if (!empty($data['user_id'])) {
            $checkUser = User::find($data['user_id']);

            if (!empty($checkUser) and ($data['user_id'] == $user->id or $checkUser->organ_id == $user->id)) {
                $reference = UserReference::where('id', $reference_id)
                    ->where('user_id', $data['user_id'])
                    ->first();

                $reference->delete();

                return response()->json([
                    'code' => 200
                ], 200);
            }
        }

        return response()->json([], 422);
    }

    public function manageUsers(Request $request, $user_type)
    {
        $valid_type = ['instructors', 'students'];
        $organization = auth()->user();

        if ($organization->isOrganization() and in_array($user_type, $valid_type)) {
            if ($user_type == 'instructors') {
                $query = $organization->getOrganizationTeachers();
            } else {
                $query = $organization->getOrganizationStudents();
            }

            $activeCount = deepClone($query)->where('status', 'active')->count();
            $verifiedCount = deepClone($query)->where('verified', true)->count();
            $inActiveCount = deepClone($query)->where('status', 'inactive')->count();

            $from = $request->get('from', null);
            $to = $request->get('to', null);
            $name = $request->get('name', null);
            $email = $request->get('email', null);
            $type = request()->get('type', null);

            if (!empty($from) and !empty($to)) {
                $from = strtotime($from);
                $to = strtotime($to);

                $query->whereBetween('created_at', [$from, $to]);
            } else {
                if (!empty($from)) {
                    $from = strtotime($from);

                    $query->where('created_at', '>=', $from);
                }

                if (!empty($to)) {
                    $to = strtotime($to);

                    $query->where('created_at', '<', $to);
                }
            }

            if (!empty($name)) {
                $query->where('full_name', 'like', "%$name%");
            }

            if (!empty($email)) {
                $query->where('email', $email);
            }

            if (!empty($type)) {
                if (in_array($type, ['active', 'inactive'])) {
                    $query->where('status', $type);
                } elseif ($type == 'verified') {
                    $query->where('verified', true);
                }
            }

            $users = $query->orderBy('created_at', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('public.' . $user_type),
                'user_type' => $user_type,
                'organization' => $organization,
                'users' => $users,
                'activeCount' => $activeCount,
                'inActiveCount' => $inActiveCount,
                'verifiedCount' => $verifiedCount,
            ];

            return view(getTemplate() . '.panel.manage.' . $user_type, $data);
        }

        return view('errors.404');
    }

    public function createUser($user_type)
    {
        $valid_type = ['instructors', 'students'];
        $organization = auth()->user();

        if ($organization->isOrganization() and in_array($user_type, $valid_type)) {

            $packageType = $user_type == 'instructors' ? 'instructors_count' : 'students_count';
            $userPackage = new UserPackage();
            $userAccountLimited = $userPackage->checkPackageLimit($packageType);

            if ($userAccountLimited) {
                session()->put('registration_package_limited', $userAccountLimited);

                return redirect()->back();
            }

            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $userLanguages = getGeneralSettings('user_languages');
            if (!empty($userLanguages) and is_array($userLanguages)) {
                $userLanguages = getLanguages($userLanguages);
            }

            $data = [
                'pageTitle' => trans('public.new') . ' ' . trans('quiz.' . $user_type),
                'new_user' => true,
                'user_type' => $user_type,
                'user' => $organization,
                'categories' => $categories,
                'organization_id' => $organization->id,
                'userLanguages' => $userLanguages,
                'currentStep' => 1,
            ];

            return view(getTemplate() . '.panel.setting.index', $data);
        }

        return view('errors.404');
    }

    public function storeUser(Request $request, $user_type)
    {
        $valid_type = ['instructors', 'students'];
        $organization = auth()->user();

        if ($organization->isOrganization() and in_array($user_type, $valid_type)) {
            $this->validate($request, [
                'email' => 'required|string|email|max:255|unique:users',
                'full_name' => 'required|string',
                'mobile' => 'required|numeric|unique:users',
                'password' => 'required|confirmed|min:6',
            ]);

            $data = $request->all();
            $role_name = ($user_type == 'instructors') ? Role::$teacher : Role::$user;
            $role_id = ($user_type == 'instructors') ? Role::getTeacherRoleId() : Role::getUserRoleId();

            $referralSettings = getReferralSettings();
            $usersAffiliateStatus = (!empty($referralSettings) and !empty($referralSettings['users_affiliate_status']));

            $user = User::create([
                'role_name' => $role_name,
                'role_id' => $role_id,
                'email' => $data['email'],
                'organ_id' => $organization->id,
                'password' => Hash::make($data['password']),
                'full_name' => $data['full_name'],
                'mobile' => $data['mobile'],
                'language' => $data['language'] ?? null,
                'timezone' => $data['timezone'] ?? null,
                'currency' => $data['currency'] ?? null,
                'affiliate' => $usersAffiliateStatus,
                'newsletter' => (!empty($data['join_newsletter']) and $data['join_newsletter'] == 'on'),
                'public_message' => (!empty($data['public_messages']) and $data['public_messages'] == 'on'),
                'created_at' => time()
            ]);


            $notifyOptions = [
                '[organization.name]' => $organization->full_name,
                '[u.name]' => $user->full_name,
                '[u.role]' => trans("update.role_{$user->role_name}"),
            ];
            sendNotification('new_organization_user', $notifyOptions, 1);


            return redirect('/panel/manage/' . $user_type . '/' . $user->id . '/edit');
        }

        return view('errors.404');
    }

    public function editUser($user_type, $user_id, $step = 1)
    {
        $valid_type = ['instructors', 'students'];
        $organization = auth()->user();

        if ($organization->isOrganization() and in_array($user_type, $valid_type)) {
            $user = User::where('id', $user_id)
                ->where('organ_id', $organization->id)
                ->first();

            if (!empty($user)) {
                $categories = Category::where('parent_id', null)
                    ->with('subCategories')
                    ->get();
                $userMetas = $user->userMetas;

                $occupations = $user->occupations->pluck('category_id')->toArray();

                $userLanguages = getGeneralSettings('user_languages');
                if (!empty($userLanguages) and is_array($userLanguages)) {
                    $userLanguages = getLanguages($userLanguages);
                }

                $data = [
                    'organization_id' => $organization->id,
                    'edit_new_user' => true,
                    'user' => $user,
                    'user_type' => $user_type,
                    'categories' => $categories,
                    'educations' => $userMetas->where('name', 'education'),
                    'experiences' => $userMetas->where('name', 'experience'),
                    'pageTitle' => trans('panel.settings'),
                    'occupations' => $occupations,
                    'userLanguages' => $userLanguages,
                    'currentStep' => $step,
                ];

                return view(getTemplate() . '.panel.setting.index', $data);
            }
        }

        return view('errors.404');
    }

    public function deleteUser($user_type, $user_id)
    {
        $valid_type = ['instructors', 'students'];
        $organization = auth()->user();

        if ($organization->isOrganization() and in_array($user_type, $valid_type)) {
            $user = User::where('id', $user_id)
                ->where('organ_id', $organization->id)
                ->first();

            if (!empty($user)) {
                $user->update([
                    'organ_id' => null
                ]);

                return response()->json([
                    'code' => 200
                ]);
            }
        }

        return response()->json([], 422);
    }

    public function search(Request $request)
    {
        $term = $request->get('term');
        $option = $request->get('option', null);
        $user = auth()->user();

        if (!empty($term)) {
            $query = User::select('id', 'full_name')
                ->where(function ($query) use ($term) {
                    $query->where('full_name', 'like', '%' . $term . '%');
                    $query->orWhere('email', 'like', '%' . $term . '%');
                    $query->orWhere('mobile', 'like', '%' . $term . '%');
                })
                ->where('id', '<>', $user->id)
                ->whereNotIn('role_name', ['admin']);

            if (!empty($option) and $option == 'just_teachers') {
                $query->where('role_name', 'teacher');
            }

            if ($option == "just_student_role") {
                $query->where('role_name', Role::$user);
            }

            $users = $query->get();

            return response()->json($users, 200);
        }

        return response('', 422);
    }

    public function contactInfo(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'user_type' => 'required|in:student,instructor',
        ]);

        $user = User::find($request->get('user_id'));

        if (!empty($user)) {
            $itemId = $request->get('item_id');
            $userType = $request->get('user_type');
            $description = null;
            $location = null;

            if (!empty($itemId)) {
                $reserve = ReserveMeeting::where('id', $itemId)
                    ->where(function ($query) use ($user) {
                        $query->where('user_id', $user->id);

                        if (!empty($user->meeting)) {
                            $query->orWhere('meeting_id', $user->meeting->id);
                        }
                    })->first();

                if (!empty($reserve)) {
                    if ($userType == 'student') {
                        $description = $reserve->description;
                    } elseif (!empty($reserve->meetingTime)) {
                        $description = $reserve->meetingTime->description;
                    }

                    if ($reserve->meeting_type == 'in_person') {
                        $userMetas = $user->userMetas;

                        if (!empty($userMetas)) {
                            foreach ($userMetas as $meta) {
                                if ($meta->name == 'address') {
                                    $location = $meta->value;
                                }
                            }
                        }
                    }
                }
            }

            return response()->json([
                'code' => 200,
                'avatar' => $user->getAvatar(),
                'name' => $user->full_name,
                'email' => !empty($user->email) ? $user->email : '-',
                'phone' => !empty($user->mobile) ? $user->mobile : '-',
                'description' => $description,
                'location' => $location,
            ], 200);
        }

        return response()->json([], 422);
    }

    public function offlineToggle(Request $request)
    {
        $user = auth()->user();

        $message = $request->get('message');
        $toggle = $request->get('toggle');
        $toggle = (!empty($toggle) and $toggle == 'true');

        $user->offline = $toggle;
        $user->offline_message = $message;

        $user->save();

        return response()->json([
            'code' => 200
        ], 200);
    }

    public function deleteAccount(Request $request)
    {
        $user = auth()->user();

        if (!empty($user)) {
            DeleteAccountRequest::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'created_at' => time()
            ]);

            return response()->json([
                'code' => 200,
                'title' => trans('public.request_success'),
                'text' => trans('update.delete_account_request_stored_msg'),
                'dont_reload' => true
            ]);
        }

        abort(403);
    }

    public function getUserInfo($id)
    {
        $user = User::query()->select('id', 'full_name', 'avatar')
            ->where('id', $id)
            ->first();

        if (!empty($user)) {
            $user->avatar = $user->getAvatar(40);

            return response()->json([
                'user' => $user
            ]);
        }

        return response()->json([], 422);
    }


    // requirement index
    public function requirementIndex($step = 1)
    {


        $user = auth()->user();

        $student = $user->Student;

        if (!$student) {
            return redirect('/apply');
        }

        // $studentBundles = BundleStudent::where('student_id', $student->id)->get()->reverse();

        $studentBundles = BundleStudent::where('student_id', $student->id)
            ->whereHas('bundle.category.categoryRequirements') // Assuming 'requirements' is the relationship or attribute in Category
            ->groupBy('bundle_id')
            ->get()
            ->reverse();


        return view(getTemplate() . '.panel.requirements.index', ['studentBundles' => $studentBundles]);
    }

    public function requirementPaymentStep()
    {
        $user = auth()->user();
        // $usersId = [2480, 2447, 2382, 2196, 2080 ];
        // if(!in_array($user->id, $usersId)){
        //     $message = " انتهاء التسجيل في البرامج الدراسيه للدفعه الثامنه";
        //     return view('web.default.pages.registration_close', compact('message'));
        // }

        $student = $user->Student;

        if (!$student) {
            return redirect('/apply');
        }

        $studentBundles = BundleStudent::where('student_id', $student->id)->groupBy('bundle_id')->get()->reverse();

        /* Installments */
        $bundleInstallments = [];
        foreach ($studentBundles as $studentBundle) {
            $hasBought = $studentBundle->bundle->checkUserHasBought($user);
            $canSale = ($studentBundle->bundle->canSale() && !$hasBought);

            // Check if the bundle meets the conditions
            if ($canSale && !empty($studentBundle->bundle->price) && $studentBundle->bundle->price > 0 && getInstallmentsSettings('status') && (empty($user) || $user->enable_installments)) {
                $installmentPlans = new InstallmentPlans($user);
                $installments = $installmentPlans->getPlans('bundles', $studentBundle->bundle->id, $studentBundle->bundle->type, $studentBundle->bundle->category_id, $studentBundle->bundle->teacher_id);

                $bundleInstallments[$studentBundle->id] = [
                    'bundle' => $studentBundle,
                    'installments' => $installments,
                ];
            } else {

                $bundleInstallments[$studentBundle->id] = [
                    'bundle' => $studentBundle,
                    'installments' => null,
                ];
            }
        }

        return view(getTemplate() . '.panel.requirements.payment_step', ['studentBundles' => $studentBundles, 'bundleInstallments' => $bundleInstallments ?? null]);
    }

    public function appliedCourses()
    {

        $user = auth()->user();

        $student = $user->Student;

        if (!$student) {
            return redirect('/apply');
        }

        $webinarSales = Sale::where('buyer_id', $user->id)->whereNotNull('webinar_id')->with('order.orderItems')->get()->sortByDesc('created_at');
        $offlinePayments = $user->offlinePayments->where('pay_for', 'webinar')->whereIn('status', ['waiting', 'reject'])->sortByDesc('created_at');

        $webinarsOrders = new Collection();

        foreach ($offlinePayments as $offlinePayment) {
            $off = $offlinePayment->order->orderItems->first();
            $off->status = $offlinePayment->status;
            $webinarsOrders->push($off);
        }

        foreach ($webinarSales as $webinarSale) {
            $web = $webinarSale->order->orderItems->first();
            $web->status = "approved";
            $webinarsOrders->push($web);
        }


        return view(getTemplate() . '.panel.requirements.courses_payment', ['webinarsOrders' => $webinarsOrders]);
    }



    // create requirement function
    public function createRequirement($studentBundleId)
    {
        $user = auth()->user();

        $student = $user->Student;

        $studentBundle = BundleStudent::find($studentBundleId);

        if (!$student || !$studentBundle) {
            return redirect('/apply');
        }

        $data = [
            "user_code" => $user->user_code,
            "program" => $studentBundle->bundle->category,
            'currentStep' => 1,
            'requirementUploaded' => false,
            'requirementStatus' => StudentRequirement::pending,
            'bundle' => $studentBundle->bundle,
            'studentBundleId' => $studentBundleId
        ];

        $studentRequirments = $studentBundle->studentRequirement;

        if ($studentRequirments) {
            if ($studentRequirments->status != "rejected") {
                return redirect('/panel/requirements');
            }
            $data["requirementUploaded"] = true;
            $data["requirementStatus"] = $studentRequirments->status;
        }

        return view(getTemplate() . '.panel.requirements.create', [...$data]);
    }


    // store requirement function
    public function storeRequirement(Request $request, $studentBundleId)
    {
        $request->validate([
            'user_code' => 'required|string',
            'program' => 'required|string',
            'specialization' => 'required|string',
            'identity_type' => 'required|string',
            'identity_attachment' => 'required|file|mimes:jpeg,jpg,png,pdf',
            // 'admission_attachment' => 'required|file|mimes:pdf|max:20480',
            'study_purpose' => 'required|string',
        ]);

        $user = auth()->user();

        $student = $user->Student;

        $studentBundle = BundleStudent::find($studentBundleId);

        if (!$student || !$studentBundle) {
            return redirect('/apply');
        }


        $studentRequirments = $studentBundle->studentRequirement;


        $identity_attachment = $request->file('identity_attachment');
        if (!in_array(strtolower($identity_attachment->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'pdf'])) {
            return back()->withInput($request->all())->withErrors(['identity_attachment' => "يجب أن يكون الهوية الوطنية/جواز السفر ملف من النوع: jpeg, jpg, png, pdf والملف المرفع بامتداد " . $identity_attachment->getClientOriginalExtension()]);
        }
        $identity_attachmentName =  $user->user_code . '_' . $request['identity_type'] . '.' . $identity_attachment->getClientOriginalExtension();
        $identity_attachmentPath = $identity_attachment->storeAs('studentRequirements', $identity_attachmentName);

        // $admission_attachment = $request->file('admission_attachment');
        // $admission_attachmentName =  $user->user_code . '_addmission.' . $admission_attachment->getClientOriginalExtension();
        // $admission_attachmentPath = $admission_attachment->storeAs('studentRequirements', $admission_attachmentName);



        $data = [
            'bundle_student_id' => $studentBundle->id,
            'identity_type' => $request['identity_type'],
            'identity_attachment' => $identity_attachmentPath,
            // 'admission_attachment' => $admission_attachmentPath,
            'study_purpose' => $request['study_purpose']
        ];

        if ($studentRequirments) {
            $data['status'] = StudentRequirement::pending;
            $studentRequirments->update($data);
        } else {
            StudentRequirement::create($data);
        }
        return redirect('/panel/requirements')->with('success', 'تم رفع متطلبات القبول بنجاح يرجي الانتظار حتي يتم مراجعتها');
    }


    // financial requirements function
    public function account($id = null, $bundleId = 10)
    {
        $userAuth = auth()->user();

        $student = $userAuth->Student;

        $studentBundle = BundleStudent::where("student_id", $student->id)->where("bundle_id", $bundleId)->latest()->first();

        if (!$student || !$studentBundle) {
            return redirect('/apply');
        }

        $studentRequirments = $studentBundle->studentRequirement;

        $data = [
            'requirementUploaded' => false,
            'requirementApproved' =>  StudentRequirement::pending
        ];

        if ($studentRequirments && $studentRequirments->status == "approved") {
            $data["requirementUploaded"] = true;
            $data["requirementApproved"] = $studentRequirments->status;
        } else {
            return redirect('/panel/requirements');
        }



        $editOfflinePayment = null;
        if (!empty($id)) {
            $editOfflinePayment = OfflinePayment::where('id', $id)
                ->where('user_id', $userAuth->id)
                ->first();
        }


        $paymentChannels = PaymentChannel::where('status', 'active')->get();
        $offlinePayments = OfflinePayment::where('user_id', $userAuth->id)->orderBy('created_at', 'desc')->get();

        $offlineBanks = OfflineBank::query()
            ->orderBy('created_at', 'desc')
            ->with([
                'specifications'
            ])
            ->get();

        $razorpay = false;
        foreach ($paymentChannels as $paymentChannel) {
            if ($paymentChannel->class_name == 'Razorpay') {
                $razorpay = true;
            }
        }

        $registrationBonusAmount = null;

        if ($userAuth->enable_registration_bonus) {
            $registrationBonusSettings = getRegistrationBonusSettings();

            $registrationBonusAccounting = Accounting::query()
                ->where('user_id', $userAuth->id)
                ->where('is_registration_bonus', true)
                ->where('system', false)
                ->first();

            $registrationBonusAmount = (empty($registrationBonusAccounting) and !empty($registrationBonusSettings['status']) and !empty($registrationBonusSettings['registration_bonus_amount'])) ? $registrationBonusSettings['registration_bonus_amount'] : null;
        }

        $data = [
            ...$data,
            'pageTitle' => trans('financial.charge_account_page_title'),
            'offlinePayments' => $offlinePayments,
            'paymentChannels' => $paymentChannels,
            'offlineBanks' => $offlineBanks,
            'accountCharge' => $userAuth->getAccountCharge(),
            'readyPayout' => $userAuth->getPayout(),
            'totalIncome' => $userAuth->getIncome(),
            'editOfflinePayment' => $editOfflinePayment,
            'razorpay' => $razorpay,
            'registrationBonusAmount' => $registrationBonusAmount,
            'currentStep' => 2,
        ];

        $bundle = Bundle::find(8);

        $data['bundle'] = $studentBundle->bundle;


        return view(getTemplate() . '.panel.requirements.index', [...$data]);
    }
}
