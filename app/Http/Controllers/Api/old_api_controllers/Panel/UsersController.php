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

        return apiResponse2(1, 'updated', trans('api.public.updated'));
    }


    public function update(Request $request)
    {
        $available_inputs = [
            'full_name', 'language', 'email', 'mobile', 'newsletter', 'public_message', 'timezone', 'password',
            'about', 'bio',
            'account_type', 'iban', 'account_id',
            'level_of_training', 'meeting_type',
            'country_id', 'province_id', 'city_id', 'district_id',
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

        $this->updateMeta($meta);


        return apiResponse2(1, 'updated', trans('api.public.updated'));
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
            'current_password' => 'required',
            'new_password' => 'required|string|min:6',
        ]);

        $user = apiAuth();
        if (Hash::check($request->input('current_password'), $user->password)) {
            $user->update([
                'password' => User::generatePassword($request->input('new_password'))
            ]);
            $token = auth('api')->refresh();

            return apiResponse2(1, 'updated', trans('api.public.updated'), [
                'token' => $token
            ]);
        }
        return apiResponse2(0, 'incorrect', trans('api.public.profile_setting.incorrect'));
    }

    private function updateMeta($updateUserMeta)
    {
        $user = apiAuth();
        foreach ($updateUserMeta as $name) {
            $value = request()->input($name);
            $checkMeta = UserMeta::where('user_id', $user->id)
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
            return view('errors.404');
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
     public function createRequirement( $studentBundleId)
     {
         $user = auth("api")->user();

         $student = $user->Student;

         $studentBundle = BundleStudent::find($studentBundleId);

         if (!$student) {
            return apiResponse2(0, 'not_student', "you need to apply to diploma first");
         }

         if (!$studentBundle ) {
            return apiResponse2(0, "doesn't match", "this is not your bundle url");
         }

         $data = [
             "user_code" => $user->user_code,
             'requirementUploaded' => false,
             'requirementStatus' => StudentRequirement::pending,
             'bundle' => ActiveBundleResource::make($studentBundle->bundle),
             'studentBundleId' =>$studentBundleId,
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
        $rules =[
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
            if($studentRequirments->status != StudentRequirement::rejected){
                return apiResponse2(1, 'already_upload', "You upload requirements before successfully, go to requirements section to view its status");
            }
            $data['status'] = StudentRequirement::pending;
            $studentRequirments->update($data);
        } else {
            StudentRequirement::create($data);
        }
        return apiResponse2(1, 'success', "requirements uploaded successfully, wait to be reviewed");
    }


}
