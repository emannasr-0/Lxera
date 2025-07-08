<?php

namespace App\Http\Controllers\Api\Admin;

use App\Bitwise\UserLevelOfTraining;
use App\BundleStudent;
use App\Exports\EnrollersExport;
use App\Exports\DirectRegisterExport;
use App\Exports\OrganizationsExport;
use App\Exports\StudentsExport;
use App\Exports\GroupStudentsExport;
use App\Http\Controllers\Controller;
use App\Imports\ExcelImport;
use App\Models\Accounting;
use App\Models\Badge;
use App\Models\BecomeInstructor;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Code;
use App\Models\Enrollment;
use App\Models\ForumTopic;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Meeting;
use App\Models\Notification;
use App\Models\Region;
use App\Models\ReserveMeeting;
use App\Models\Role;
use App\Models\Sale;
use App\Models\UserBadge;
use App\Models\UserBank;
use App\Models\UserMeta;
use App\Models\UserOccupation;
use App\Models\UserRegistrationPackage;
use App\Models\UserSelectedBank;
use App\Models\UserSelectedBankSpecification;
use App\Models\Webinar;
use App\Models\StudyClass;
use App\Student;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNotifications;
use App\Imports\StudentImport;
use App\Exports\ProgramCodeExport;
use App\Http\Controllers\Admin\StudyClassController;
use App\Imports\SendUserMail;
use App\Models\Api\Organization;
use App\Models\Api\Plan;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function transform(Request $request, $user_id)
    {
        $student = Student::where('user_id', $user_id)->first();
        $toDiploma = Bundle::where('id', $request->toDiploma ?? null)->first();
        $fromDiploma = Bundle::where('id', $request->fromDiploma ?? null)->first();
        try {
            $validatedData = $request->validate([
                'category_id' => 'required',
                'fromDiploma' => [
                    'required',
                    function ($attribute, $value, $fail) use ($student) {
                        if (!$student && $student->bundles()->where('bundles.id', $value)->exists()) {
                            $fail('لم يسجل الطالب في هذه الدبلومة.');
                        }
                    },
                ],
                'toDiploma' => [
                    'required',
                    function ($attribute, $value, $fail) use ($student) {
                        if ($student && $student->bundles()->where('bundles.id', $value)->exists()) {
                            $fail('لقد سجل الطالب في هذة الدبلومة مسبقا.');
                        }
                    },
                ],
                'certificate' => $toDiploma->has_certificate ? 'required|boolean' : '',
            ]);
            $user = User::where('id', $user_id)->first();
            $accounting = Accounting::where('form_fee', 1)->where('user_id', $user_id)
                ->where('bundle_id', $validatedData['fromDiploma'])
                ->first();
            $sale = $user->purchasedFormBundle()->where('bundle_id', $validatedData['fromDiploma'])->first();
            $orderItem = null;
            if (!empty($sale)) {
                $sale->update([
                    'bundle_id' => $validatedData['toDiploma'],
                ]);
                $orderItem = $sale->order->orderItems[0];
            }
            if (!empty($orderItem)) {
                $orderItem->update([
                    'bundle_id' => $validatedData['toDiploma'],
                ]);
            }
            if (!empty($accounting)) {
                $accounting->update([
                    'bundle_id' => $validatedData['toDiploma'],
                ]);
            }
            $certificate = !empty($validatedData['certificate']) ? $validatedData['certificate'] : null;
            // $student->bundles()->detach($validatedData['fromDiploma']);
            // $student->bundles()->attach($validatedData['toDiploma'], ['certificate' => (!empty($validatedData['certificate'])) ? $validatedData['certificate'] : null]);
            BundleStudent::where(['student_id' => $student->id, 'bundle_id' => $validatedData['fromDiploma']])->update(['bundle_id' => $validatedData['toDiploma'], 'certificate' => (!empty($validatedData['certificate'])) ? $validatedData['certificate'] : null]);


            $toastData = [
                'title' => '',
                'msg' => 'تم التحويل بنجاح',
                'status' => 'success',
            ];

            $data['user_id'] = $user->id;
            $data['name'] = $user->full_name;
            $data['receiver'] = $user->email;
            $data['fromEmail'] = env('MAIL_FROM_ADDRESS');
            $data['fromName'] = env('MAIL_FROM_NAME');
            $data['subject'] = 'تحويل الدبلومة';
            $data['body'] = 'نود اعلامك علي انه تم الموافقة علي تحويل الدبلومه المسجلة ' . $fromDiploma->title . '  الي ' . $toDiploma->title . '  يرجي الذهاب للموقع الخاص بنا للمتابعه باقي الخطوات';

            $this->sendNotification($data);

            return redirect()->back()->with(['toast' => $toastData]);
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            $toastData = [
                'title' => '',
                'msg' => $th->getMessage(),
                'status' => 'error',
            ];

            return redirect()->back()->with(['toast' => $toastData]);
        }
    }

    protected function sendNotification($data)
    {
        // $this->authorize('admin_notifications_send');

        Notification::create([
            'user_id' => !empty($data['user_id']) ? $data['user_id'] : null,
            'sender_id' => auth()->id(),
            'title' => "تحويل الدبلومة",
            'message' => $data['body'],
            'sender' => Notification::$AdminSender,
            'type' => "single",
            'created_at' => time()
        ]);

        if (!empty($data['user_id']) and env('APP_ENV') == 'production') {
            $user = User::where('id', $data['user_id'])->first();
            if (!empty($user) and !empty($user->email)) {
                Mail::to($user->email)->send(new SendNotifications(['title' => $data['subject'], 'message' => $data['body'], 'name' => $data['name']]));
            }
        }

        return true;
    }


    public function staffs(Request $request)
    {
        $this->authorize('admin_staffs_list');

        $staffsRoles = Role::where('is_admin', true)->get();
        $staffsRoleIds = $staffsRoles->pluck('id')->toArray();

        $query = User::whereIn('role_id', $staffsRoleIds)->with(['role']);
        $query = $this->filters($query, $request);

        $users = $query->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'users' => $users,
            'staffsRoles' => $staffsRoles,
        ];

        return response()->json($data);
    }

    public function organizations(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_organizations_list');

        $query = User::where('role_name', Role::$organization);

        $totalOrganizations = deepClone($query)->count();
        $verifiedOrganizations = deepClone($query)->where('verified', true)
            ->count();
        $totalOrganizationsTeachers = User::where('role_name', Role::$teacher)
            ->whereNotNull('organ_id')
            ->count();
        $totalOrganizationsStudents = User::where('role_name', Role::$user)
            ->whereNotNull('organ_id')
            ->count();
        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $query = $this->filters($query, $request);

        if ($is_export_excel) {
            $users = $query->orderBy('created_at', 'desc')->get();
        } else {
            $users = $query->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        $users = $this->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $users;
        }

        $data = [
            'pageTitle' => trans('admin/main.organizations'),
            'users' => $users,
            'totalOrganizations' => $totalOrganizations,
            'verifiedOrganizations' => $verifiedOrganizations,
            'totalOrganizationsTeachers' => $totalOrganizationsTeachers,
            'totalOrganizationsStudents' => $totalOrganizationsStudents,
            'userGroups' => $userGroups,
        ];

        return view('admin.users.organizations', $data);
    }

    public function students(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $query = User::whereIn('role_name', [Role::$user, Role::$registered_user]);

        $totalStudents = deepClone($query)->count();
        $inactiveStudents = deepClone($query)->where('status', 'inactive')
            ->count();
        $banStudents = deepClone($query)->where('ban', true)
            ->whereNotNull('ban_end_at')
            ->where('ban_end_at', '>', time())
            ->count();

        $totalOrganizationsStudents = User::where('role_name', Role::$user)
            ->whereNotNull('organ_id')
            ->count();
        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $organizations = User::select('id', 'full_name', 'created_at')
            ->where('role_name', Role::$organization)
            ->orderBy('created_at', 'desc')
            ->get();

        $t1 = $query->get();
        $query = $this->filters($query, $request);
        $t2 = $query->get();

        // dd($t1, $t2);

        if ($is_export_excel) {
            $users = $query->orderBy('created_at', 'desc')->get();
        } else {
            $users = $query->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        $users = $this->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $users;
        }

        // $purchasedFormBundle=null;
        // $purchasedUserFormBundle=Sale::where('type', 'form_fee')
        //         ->where('buyer_id', $user->id)
        //         ->first();


        $category = Category::where('parent_id', '!=', null)->get();

        $data = [
            'pageTitle' => trans('public.students'),
            'users' => $users,
            'category' => $category,
            'totalStudents' => $totalStudents,
            'inactiveStudents' => $inactiveStudents,
            'banStudents' => $banStudents,
            'totalOrganizationsStudents' => $totalOrganizationsStudents,
            'userGroups' => $userGroups,
            'organizations' => $organizations,
        ];

        return view('admin.users.students', $data);
    }

    public function instructors(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_instructors_list');

        $query = User::where('role_name', Role::$teacher);

        $totalInstructors = deepClone($query)->count();
        $inactiveInstructors = deepClone($query)->where('status', 'inactive')
            ->count();
        $banInstructors = deepClone($query)->where('ban', true)
            ->whereNotNull('ban_end_at')
            ->where('ban_end_at', '>', time())
            ->count();

        $totalOrganizationsInstructors = User::where('role_name', Role::$teacher)
            ->whereNotNull('organ_id')
            ->count();
        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $organizations = User::select('id', 'full_name', 'created_at')
            ->where('role_name', Role::$organization)
            ->orderBy('created_at', 'desc')
            ->get();

        $query = $this->filters($query, $request);

        if ($is_export_excel) {
            $users = $query->orderBy('created_at', 'desc')->get();
        } else {
            $users = $query->orderBy('created_at', 'desc')
                ->get();
        }

        $users = $this->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $users;
        }

        $data = [
            'pageTitle' => trans('admin/main.instructors'),
            'users' => $users,
            'totalInstructors' => $totalInstructors,
            'inactiveInstructors' => $inactiveInstructors,
            'banInstructors' => $banInstructors,
            'totalOrganizationsInstructors' => $totalOrganizationsInstructors,
            'userGroups' => $userGroups,
            'organizations' => $organizations,
        ];

        return response()->json($data);
    }

    public function addUsersExtraInfo($users)
    {
        foreach ($users as $user) {
            $salesQuery = Sale::where('seller_id', $user->id)
                ->whereNull('refund_at');

            $classesSaleQuery = deepClone($salesQuery)->whereNotNull('webinar_id')
                ->whereNull('meeting_id')
                ->whereNull('promotion_id')
                ->whereNull('subscribe_id');

            $user->classesSalesCount = $classesSaleQuery->count();
            $user->classesSalesSum = $classesSaleQuery->sum('total_amount');

            $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');
            $reserveMeetingsQuery = ReserveMeeting::whereIn('meeting_id', $meetingIds)
                ->where(function ($query) {
                    $query->whereHas('sale', function ($query) {
                        $query->whereNull('refund_at');
                    });

                    $query->orWhere(function ($query) {
                        $query->whereIn('status', ['canceled']);
                        $query->whereHas('sale');
                    });
                });

            $user->meetingsSalesCount = deepClone($reserveMeetingsQuery)->count();
            $user->meetingsSalesSum = deepClone($reserveMeetingsQuery)->sum('paid_amount');

            $purchasedQuery = Sale::where('buyer_id', $user->id)
                ->whereNull('refund_at');

            $classesPurchasedQuery = deepClone($purchasedQuery)->whereNotNull('webinar_id')
                ->whereNull('meeting_id')
                ->whereNull('promotion_id')
                ->whereNull('subscribe_id');

            $user->classesPurchasedsCount = $classesPurchasedQuery->count();
            $user->classesPurchasedsSum = $classesPurchasedQuery->sum('total_amount');

            $meetingsPurchasedQuery = deepClone($purchasedQuery)->whereNotNull('meeting_id')
                ->whereNull('webinar_id')
                ->whereNull('promotion_id')
                ->whereNull('subscribe_id');

            $user->meetingsPurchasedsCount = $meetingsPurchasedQuery->count();
            $user->meetingsPurchasedsSum = $meetingsPurchasedQuery->sum('total_amount');
        }

        return $users;
    }

    public function filters($query, $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $user_name = $request->get('user_name');
        $full_name = $request->get('full_name');
        $user_code = $request->get('user_code');
        $ar_name = $request->get('ar_name');
        $email = $request->get('email');
        $mobile = $request->get('mobile');
        $sort = $request->get('sort');
        $group_id = $request->get('group_id');
        $status = $request->get('status');
        $role_id = $request->get('role_id');
        $organization_id = $request->get('organization_id');
        $program = $request->get('program', null);

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($full_name)) {
            $query->where('full_name', 'like', "%$full_name%");
        }

        if (!empty($user_name)) {
            $query->where(function ($q) use ($user_name) {
                $q->where('full_name', 'like', "%$user_name%")
                    ->orWhereHas('student', function ($q) use ($user_name) {
                        $q->where('ar_name', 'like', "%$user_name%")
                            ->orWhere('en_name', 'like', "%$user_name%");
                    });
            });
        }

        if (!empty($user_code)) {
            $query->where('user_code', 'like', "%$user_code%");
        }
        if (!empty($ar_name)) {
            $query->whereHas('student', function ($q) use ($ar_name) {
                $q->where('ar_name', 'like', "%$ar_name%");
                $q->orWhere('en_name', 'like', "%$ar_name%");
            });
        }
        if (!empty($email)) {
            $query->where('email', 'like', "%$email%");
        }
        if (!empty($mobile)) {
            $query->where('mobile', 'like', "%$mobile%");
        }
        if (!empty($program)) {
            $query->where(function ($q1) use ($program) {
                $q1->whereHas('purchases', function ($query2) use ($program) {
                    $query2->whereHas(
                        'bundle',
                        function ($q) use ($program) {
                            $q->whereTranslationLike('title', '%' . $program . '%')
                                ->orWhere('slug', 'like', "%$program%");
                        }
                    )
                        ->orWhereHas(
                            'webinar',
                            function ($q) use ($program) {
                                $q->whereTranslationLike('title', '%' . $program . '%')
                                    ->orWhere('slug', 'like', "%$program%");
                            }
                        );
                })
                    ->orWhereHas('appliedProgram', function ($q) use ($program) {
                        $q->whereTranslationLike('title', '%' . $program . '%')
                            ->orWhere('slug', 'like', "%$program%");
                    });
            });
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'sales_classes_asc':
                    $query->join('sales', 'users.id', '=', 'sales.seller_id')
                        ->select('users.*', 'sales.seller_id', 'sales.webinar_id', 'sales.refund_at', DB::raw('count(sales.seller_id) as sales_count'))
                        ->whereNotNull('sales.webinar_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.seller_id')
                        ->orderBy('sales_count', 'asc');
                    break;
                case 'sales_classes_desc':
                    $query->join('sales', 'users.id', '=', 'sales.seller_id')
                        ->select('users.*', 'sales.seller_id', 'sales.webinar_id', 'sales.refund_at', DB::raw('count(sales.seller_id) as sales_count'))
                        ->whereNotNull('sales.webinar_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.seller_id')
                        ->orderBy('sales_count', 'desc');
                    break;
                case 'purchased_classes_asc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.refund_at', DB::raw('count(sales.buyer_id) as purchased_count'))
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_count', 'asc');
                    break;
                case 'purchased_classes_desc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.refund_at', DB::raw('count(sales.buyer_id) as purchased_count'))
                        ->groupBy('sales.buyer_id')
                        ->whereNull('sales.refund_at')
                        ->orderBy('purchased_count', 'desc');
                    break;
                case 'purchased_classes_amount_asc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.amount', 'sales.refund_at', DB::raw('sum(sales.amount) as purchased_amount'))
                        ->groupBy('sales.buyer_id')
                        ->whereNull('sales.refund_at')
                        ->orderBy('purchased_amount', 'asc');
                    break;
                case 'purchased_classes_amount_desc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.amount', 'sales.refund_at', DB::raw('sum(sales.amount) as purchased_amount'))
                        ->groupBy('sales.buyer_id')
                        ->whereNull('sales.refund_at')
                        ->orderBy('purchased_amount', 'desc');
                    break;
                case 'sales_appointments_asc':
                    $query->join('sales', 'users.id', '=', 'sales.seller_id')
                        ->select('users.*', 'sales.seller_id', 'sales.meeting_id', 'sales.refund_at', DB::raw('count(sales.seller_id) as sales_count'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.seller_id')
                        ->orderBy('sales_count', 'asc');
                    break;
                case 'sales_appointments_desc':
                    $query->join('sales', 'users.id', '=', 'sales.seller_id')
                        ->select('users.*', 'sales.seller_id', 'sales.meeting_id', 'sales.refund_at', DB::raw('count(sales.seller_id) as sales_count'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.seller_id')
                        ->orderBy('sales_count', 'desc');
                    break;
                case 'purchased_appointments_asc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.meeting_id', 'sales.refund_at', DB::raw('count(sales.buyer_id) as purchased_count'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_count', 'asc');
                    break;
                case 'purchased_appointments_desc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.meeting_id', 'sales.refund_at', DB::raw('count(sales.buyer_id) as purchased_count'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_count', 'desc');
                    break;
                case 'purchased_appointments_amount_asc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.amount', 'sales.meeting_id', 'sales.refund_at', DB::raw('sum(sales.amount) as purchased_amount'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_amount', 'asc');
                    break;
                case 'purchased_appointments_amount_desc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.amount', 'sales.meeting_id', 'sales.refund_at', DB::raw('sum(sales.amount) as purchased_amount'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_amount', 'desc');
                    break;
                case 'register_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'register_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        }

        if (!empty($group_id)) {
            // $userIds = GroupUser::where('group_id', $group_id)->pluck('user_id')->toArray();
            $userIds = Enrollment::where('group_id', $group_id)->pluck('user_id')->toArray();

            $query->whereIn('id', $userIds);
        }

        if (!empty($status)) {
            switch ($status) {
                case 'active_verified':
                    $query->where('status', 'active')
                        ->where('verified', true);
                    break;
                case 'active_notVerified':
                    $query->where('status', 'active')
                        ->where('verified', false);
                    break;
                case 'inactive':
                    $query->where('status', 'inactive');
                    break;
                case 'ban':
                    $query->where('ban', true)
                        ->whereNotNull('ban_end_at')
                        ->where('ban_end_at', '>', time());
                    break;
            }
        }

        if (!empty($role_id)) {
            $query->where('role_id', $role_id);
        }

        if (!empty($organization_id)) {
            $query->where('organ_id', $organization_id);
        }

        //dd($query->get());
        return $query;
    }

    public function create()
    {
        $this->authorize('admin_users_create');

        $roles = Role::orderBy('created_at', 'desc')->get();
        $userGroups = Group::orderBy('created_at', 'desc')->where('status', 'active')->get();

        $data = [
            'pageTitle' => trans('admin/main.user_new_page_title'),
            'roles' => $roles,
            'userGroups' => $userGroups,
        ];

        return view('admin.users.create', $data);
    }

    public function username($data)
    {
        $email_regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        $username = 'mobile';
        if (preg_match($email_regex, request('username', null))) {
            $username = 'email';
        }

        return $username;
    }

    public function store(Request $request)
    {

        $usersCount = User::count();

        $plan = Plan::first();

        if ($usersCount >= $plan->max_users) {
            return response()->json([
                'msg' => 'Sorry, you have reached the maximum number of users allowed for your subscription plan.'
            ], 403);
        }

        $this->authorize('admin_users_create');
        $data = $request->all();

        $username = $this->username($data);
        $data[$username] = $data['username'];
        $request->merge([$username => $data['username']]);
        unset($data['username']);

        $this->validate($request, [
            $username => ($username == 'mobile') ? 'required|numeric|unique:users' : 'required|string|email|max:255|unique:users',
            'full_name' => 'required|min:3|max:128',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|string|min:6',
            'status' => 'required',
        ]);

        $role = Role::find($data['role_id']);

        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found!',
            ], 404);
        }

        $referralSettings = getReferralSettings();
        $usersAffiliateStatus = (!empty($referralSettings) and !empty($referralSettings['users_affiliate_status']));

        $lastCode = Code::latest()->first();
        $userCode = null;

        if (!empty($lastCode)) {
            if ($role->name == 'user') {
                if (empty($lastCode->lst_sd_code)) {
                    $lastCode->lst_sd_code = $lastCode->student_code;
                }
                $lastCodeAsInt = intval(substr($lastCode->lst_sd_code, 2));
                do {
                    $nextCodeAsInt = $lastCodeAsInt + 1;
                    $userCode = 'SD' . str_pad($nextCodeAsInt, 5, '0', STR_PAD_LEFT);
                    $lastCodeAsInt = $nextCodeAsInt;
                } while (User::where('user_code', $userCode)->exists());
                $lastCode->update(['lst_sd_code' => $userCode]);
            } elseif ($role->name == 'teacher') {
                if (empty($lastCode->lst_tr_code)) {
                    $lastCode->lst_tr_code = $lastCode->instructor_code;
                }
                $lastCodeAsInt = intval(substr($lastCode->lst_tr_code, 2));
                do {
                    $nextCodeAsInt = $lastCodeAsInt + 1;
                    $userCode = 'TR' . str_pad($nextCodeAsInt, 5, '0', STR_PAD_LEFT);
                    $lastCodeAsInt = $nextCodeAsInt;
                } while (User::where('user_code', $userCode)->exists());
                $lastCode->update(['lst_tr_code' => $userCode]);
            }
        }

        $user = User::create([
            'full_name' => $data['full_name'],
            'role_name' => $role->name,
            'role_id' => $data['role_id'],
            $username => $data[$username],
            'user_code' => $userCode,
            'password' => User::generatePassword($data['password']),
            'status' => $data['status'],
            'affiliate' => $usersAffiliateStatus,
            'verified' => true,
            'created_at' => time(),
        ]);

        if (!empty($data['group_id'])) {
            $group = Group::find($data['group_id']);
            if ($group) {
                GroupUser::create([
                    'group_id' => $group->id,
                    'user_id' => $user->id,
                    'created_at' => time(),
                ]);
                sendNotification('add_to_user_group', ['[u.g.title]' => $group->name], $user->id);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }


    public function storeexcel(Request $request)
    {
        $file = $request->file('excel_file');

        Excel::import(new ExcelImport, $file);

        return redirect()->back()->with('success', 'Data imported successfully.');
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_users_edit');
        // $student= Student::where('user_id', $id)->get();

        $user = User::where('id', $id)
            ->with([
                'customBadges' => function ($query) {
                    $query->with('badge');
                },
                'occupations' => function ($query) {
                    $query->with('category');
                },
                'organization' => function ($query) {
                    $query->select('id', 'full_name');
                },
                'userRegistrationPackage',
            ])
            ->first();

        if (empty($user)) {
            abort(404);
        }

        if (!empty($user->location)) {
            $user->location = \Geo::getST_AsTextFromBinary($user->location);

            $user->location = \Geo::get_geo_array($user->location);
        }

        $userMetas = $user->userMetas;

        if (!empty($userMetas)) {
            foreach ($userMetas as $meta) {
                $user->{$meta->name} = $meta->value;
            }
        }

        $becomeInstructor = null;
        if (!empty($request->get('type')) and $request->get('type') == 'check_instructor_request') {
            $becomeInstructor = BecomeInstructor::where('user_id', $user->id)
                ->first();
        }

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $occupations = $user->occupations->pluck('category_id')->toArray();

        $userBadges = $user->getBadges(false);

        $roles = Role::all();
        $badges = Badge::all();

        $userLanguages = getGeneralSettings('user_languages');
        if (!empty($userLanguages) and is_array($userLanguages)) {
            $userLanguages = getLanguages($userLanguages);
        } else {
            $userLanguages = [];
        }

        $provinces = null;
        $cities = null;
        $districts = null;

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

        $userBanks = UserBank::query()
            ->with([
                'specifications',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'pageTitle' => trans('admin/pages/users.edit_page_title'),
            'user' => $user,
            'userBadges' => $userBadges,
            'roles' => $roles,
            'badges' => $badges,
            'categories' => $categories,
            'occupations' => $occupations,
            'becomeInstructor' => $becomeInstructor,
            'userLanguages' => $userLanguages,
            'userRegistrationPackage' => $user->userRegistrationPackage,
            'countries' => $countries,
            'provinces' => $provinces,
            'cities' => $cities,
            'districts' => $districts,
            'userBanks' => $userBanks,
            //'student'=>$student,
        ];

        // Purchased Classes Data
        $data = array_merge($data, $this->getPurchasedClassesData($user));

        // Purchased Bundles Data
        $data = array_merge($data, $this->getPurchasedBundlesData($user));

        // Purchased Product Data
        $data = array_merge($data, $this->getPurchasedProductsData($user));

        if (auth()->user()->can('admin_forum_topics_lists')) {
            $data['topics'] = ForumTopic::where('creator_id', $user->id)
                ->with([
                    'posts' => function ($query) {
                        $query->orderBy('created_at', 'desc');
                    },
                    'forum',
                ])
                ->withCount('posts')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('admin.users.edit', $data);
    }

    public function getPurchasedClassesData($user)
    {
        $manualAddedClasses = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('webinar_id')
            ->where('sales.manual_added', true)
            ->where('sales.access_to_purchased_item', true)
            ->whereHas('webinar')
            ->with([
                'webinar',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $manualDisabledClasses = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('webinar_id')
            ->where('sales.access_to_purchased_item', false)
            ->whereHas('webinar')
            ->with([
                'webinar',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $purchasedClasses = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('webinar_id')
            ->where('sales.access_to_purchased_item', true)
            ->where('sales.manual_added', false)
            ->whereHas('webinar')
            ->with([
                'webinar',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'manualAddedClasses' => $manualAddedClasses,
            'purchasedClasses' => $purchasedClasses,
            'manualDisabledClasses' => $manualDisabledClasses,
        ];
    }

    public function getPurchasedBundlesData($user)
    {
        $manualAddedBundles = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('bundle_id')
            ->where('sales.manual_added', true)
            ->where('sales.access_to_purchased_item', true)
            ->whereHas('bundle')
            ->with([
                'bundle',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $manualDisabledBundles = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('bundle_id')
            ->where('sales.access_to_purchased_item', false)
            ->whereHas('bundle')
            ->with([
                'bundle',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $purchasedBundles = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('bundle_id')
            ->where('sales.access_to_purchased_item', true)
            ->where('sales.manual_added', false)
            ->whereHas('bundle')
            ->with([
                'bundle',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'manualAddedBundles' => $manualAddedBundles,
            'purchasedBundles' => $purchasedBundles,
            'manualDisabledBundles' => $manualDisabledBundles,
        ];
    }

    public function getPurchasedProductsData($user)
    {
        $manualAddedProducts = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('product_order_id')
            ->where('sales.manual_added', true)
            ->where('sales.access_to_purchased_item', true)
            ->whereHas('productOrder')
            ->with([
                'productOrder' => function ($query) {
                    $query->with([
                        'product',
                    ]);
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $manualDisabledProducts = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('product_order_id')
            ->where('sales.access_to_purchased_item', false)
            ->whereHas('productOrder')
            ->with([
                'productOrder' => function ($query) {
                    $query->with([
                        'product',
                    ]);
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $purchasedProducts = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('product_order_id')
            ->where('sales.access_to_purchased_item', true)
            ->where('sales.manual_added', false)
            ->whereHas('productOrder')
            ->with([
                'productOrder' => function ($query) {
                    $query->with([
                        'product',
                    ]);
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'manualAddedProducts' => $manualAddedProducts,
            'purchasedProducts' => $purchasedProducts,
            'manualDisabledProducts' => $manualDisabledProducts,
        ];
    }

    public function update($url_name, Request $request, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($id);

        $rules = [
            'full_name' => 'sometimes|min:3|max:128',
            'role_id' => 'sometimes|exists:roles,id',
            'email' => (!empty($user->email)) ? 'sometimes|email|unique:users,email,' . $user->id . ',id,deleted_at,NULL' : 'nullable|email|unique:users',
            'mobile' => (!empty($user->mobile)) ? 'sometimes|unique:users,mobile,' . $user->id . ',id,deleted_at,NULL' : 'nullable|numeric|unique:users',
            'password' => 'nullable|string',
            'bio' => 'nullable|string|min:3|max:48',
            'about' => 'nullable|string|min:3',
            'certificate_additional' => 'nullable|string|max:255',
            'status' => ['sometimes', Rule::in(User::$statuses)],
            'ban_start_at' => 'sometimes|required_if:ban,1',
            'ban_end_at' => 'sometimes|required_if:ban,1',
            'en_name' => 'nullable|string|max:255',
        ];

        if (!empty($user->student)) {
            $rules['user_code'] = 'sometimes|unique:users,user_code,' . $user->id;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $role = Role::find($data['role_id'] ?? $user->role_id);
        if (!$role) {
            return response()->json([
                'message' => 'Selected role does not exist.'
            ], 404);
        }

        $userOldRoleId = $user->role_id;

        if ($user->role_id != $role->id && $role->name == Role::$teacher) {
            $becomeInstructor = BecomeInstructor::where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!empty($becomeInstructor)) {
                $becomeInstructor->update(['status' => 'accept']);
                $becomeInstructor->sendNotificationToUser('accept');
            }
        }

        $user->fill([
            'full_name' => $data['full_name'] ?? $user->full_name,
            'role_id' => $role->id,
            'role_name' => $role->name,
            'user_code' => $data['user_code'] ?? $user->user_code,
            'timezone' => $data['timezone'] ?? $user->timezone,
            'currency' => $data['currency'] ?? $user->currency,
            'organ_id' => $data['organ_id'] ?? $user->organ_id,
            'email' => $data['email'] ?? $user->email,
            'mobile' => $data['mobile'] ?? $user->mobile,
            'bio' => $data['bio'] ?? $user->bio,
            'about' => $data['about'] ?? $user->about,
            'status' => $data['status'] ?? $user->status,
            'language' => $data['language'] ?? $user->language,
        ]);

        if ($role->id == 1 && isset($data['en_name']) && !empty($user->student)) {
            $user->student->en_name = $data['en_name'];
            $user->student->save();
        }

        if (!empty($data['password'])) {
            $user->password = User::generatePassword($data['password']);
            $this->sendEmail($user, [
                'title' => 'تغيير كلمة المرور',
                'body' => "تم تغيير كلمة المرور الخاصة بك، البريد: $user->email، كلمة المرور: {$data['password']}"
            ]);
            $this->sendNotificationToUser($user, ['body' => 'تم تغيير كلمة المرور الخاصة بك']);
        }

        if (!empty($data['ban']) && $data['ban'] == '1') {
            $user->ban = true;
            $user->ban_start_at = strtotime($data['ban_start_at']);
            $user->ban_end_at = strtotime($data['ban_end_at']);
        } else {
            $user->ban = false;
            $user->ban_start_at = null;
            $user->ban_end_at = null;
        }

        $user->verified = isset($data['verified']) && $data['verified'] == '1';
        $user->affiliate = isset($data['affiliate']) && $data['affiliate'] == '1';
        $user->can_create_store = isset($data['can_create_store']) && $data['can_create_store'] == '1';
        $user->access_content = isset($data['access_content']) && $data['access_content'] == '1';

        $user->save();

        $this->handleUserCertificateAdditional($user->id, $data['certificate_additional'] ?? '');

        if ($userOldRoleId != $role->id) {
            sendNotification('user_role_change', ['[u.role]' => $role->caption], $user->id);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    public function handleUserCertificateAdditional($userId, $value)
    {
        $name = 'certificate_additional';

        if (empty($value)) {
            $checkMeta = UserMeta::where('user_id', $userId)
                ->where('name', $name)
                ->first();

            if (!empty($checkMeta)) {
                $checkMeta->delete();
            }
        } else {
            UserMeta::updateOrCreate([
                'user_id' => $userId,
                'name' => $name,
            ], [
                'value' => $value,
            ]);
        }
    }

    public function updateImage(Request $request, $id)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($id);

        $user->avatar = $request->get('avatar', null);

        if (!empty($request->get('cover_img', null))) {
            $user->cover_img = $request->get('cover_img', null);
        }

        $user->save();

        return redirect()->back()->with('msg', 'تم تعديل الصورة بنجاح');
    }

    public function financialUpdate(Request $request, $id)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($id);
        $data = $request->all();

        $user->update([
            'identity_scan' => $data['identity_scan'],
            'address' => $data['address'],
            'commission' => $data['commission'] ?? null,
            'financial_approval' => (!empty($data['financial_approval']) and $data['financial_approval'] == 'on'),
            'installment_approval' => (!empty($data['installment_approval']) and $data['installment_approval'] == 'on'),
            'enable_installments' => (!empty($data['enable_installments']) and $data['enable_installments'] == 'on'),
            'disable_cashback' => (!empty($data['disable_cashback']) and $data['disable_cashback'] == 'on'),
            'enable_registration_bonus' => (!empty($data['enable_registration_bonus']) and $data['enable_registration_bonus'] == 'on'),
            'registration_bonus_amount' => !empty($data['registration_bonus_amount']) ? $data['registration_bonus_amount'] : null,
        ]);

        if (!empty($data['bank_id'])) {
            UserSelectedBank::query()->where('user_id', $user->id)->delete();

            $userSelectedBank = UserSelectedBank::query()->create([
                'user_id' => $user->id,
                'user_bank_id' => $data['bank_id'],
            ]);

            if (!empty($data['bank_specifications'])) {
                $specificationInsert = [];

                foreach ($data['bank_specifications'] as $specificationId => $specificationValue) {
                    if (!empty($specificationValue)) {
                        $specificationInsert[] = [
                            'user_selected_bank_id' => $userSelectedBank->id,
                            'user_bank_specification_id' => $specificationId,
                            'value' => $specificationValue,
                        ];
                    }
                }

                UserSelectedBankSpecification::query()->insert($specificationInsert);
            }
        }

        return redirect()->back()->with('msg', 'تم تعديل بيانات المستخدم بنجاح');
    }

    public function occupationsUpdate(Request $request, $id)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($id);
        $data = $request->all();

        UserOccupation::where('user_id', $user->id)->delete();
        if (!empty($data['occupations'])) {

            foreach ($data['occupations'] as $category_id) {
                UserOccupation::create([
                    'user_id' => $user->id,
                    'category_id' => $category_id,
                ]);
            }
        }

        return redirect()->back();
    }

    public function badgesUpdate(Request $request, $id)
    {
        $this->authorize('admin_users_edit');

        $this->validate($request, [
            'badge_id' => 'required',
        ]);

        $data = $request->all();
        $user = User::findOrFail($id);
        $badge = Badge::findOrFail($data['badge_id']);

        UserBadge::create([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'created_at' => time(),
        ]);

        sendNotification('new_badge', ['[u.b.title]' => $badge->title], $user->id);

        return redirect()->back();
    }

    public function deleteBadge(Request $request, $id, $badge_id)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($id);

        $badge = UserBadge::where('id', $badge_id)
            ->where('user_id', $user->id)
            ->first();

        if (!empty($badge)) {
            $badge->delete();
        }

        return redirect()->back();
    }

    public function deleteDirectRegister(Request $request, BundleStudent $bundleStudent)
    {
        $this->authorize('admin_users_delete');
        $bundleStudent->delete();
        $toastData = [
            'title' => 'حذف طالب من برنامج',
            'msg' => "تم حذف الطالب بنجاح",
            'status' => 'success'
        ];
        return redirect()->back()->with(['toast' => $toastData]);
    }

    public function destroy($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_users_delete');

        $user = User::find($id);

        if ($user) {
            $user->delete();
        }

        return response()->json([
            'status' => 'success',
            'msg' => 'User Deleted Successfully'
        ]);
    }

    public function acceptRequestToInstructor($id)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($id);

        $becomeInstructors = BecomeInstructor::where('user_id', $user->id)->first();

        if (!empty($becomeInstructors)) {
            $role = Role::where('name', $becomeInstructors->role)->first();

            if (!empty($role)) {
                $user->update([
                    'role_id' => $role->id,
                    'role_name' => $role->name,
                ]);

                $becomeInstructors->update([
                    'status' => 'accept',
                ]);

                // Send Notification
                $becomeInstructors->sendNotificationToUser('accept');
            }

            return redirect(getAdminPanelUrl() . '/users/' . $user->id . '/edit')->with(['msg' => trans('admin/pages/users.user_role_updated')]);
        }

        abort(404);
    }

    public function search(Request $request)
    {
        $term = $request->get('term');
        $option = $request->get('option');

        $users = User::select('id', 'full_name as name')
            //->where('role_name', Role::$user)
            ->where(function ($query) use ($term) {
                $query->where('full_name', 'like', '%' . $term . '%');
            });

        if ($option === 'for_user_group') {
            $users->whereNotIn('id', GroupUser::all()->pluck('user_id'));
        }

        if ($option === 'just_teacher_role') {
            $users->where('role_name', Role::$teacher);
        }

        if ($option === 'just_student_role') {
            $users->where('role_name', Role::$user);
        }

        if ($option === 'just_organization_role') {
            $users->where('role_name', Role::$organization);
        }

        if ($option === 'just_organization_and_teacher_role') {
            $users->whereIn('role_name', [Role::$organization, Role::$teacher]);
        }

        if ($option === 'except_user') {
            $users->where('role_name', '!=', Role::$user);
        }

        if ($option === 'consultants') {
            $users->whereHas('meeting', function ($query) {
                $query->where('disabled', false)
                    ->whereHas('meetingTimes');
            });
        }

        return response()->json($users->get(), 200);
    }
    public function searchByCode(Request $request)
    {
        $term = $request->get('term');
        $option = $request->get('option');

        $users = User::select(
            'id',
            DB::raw('CONCAT(full_name, " - ", user_code) as name')
        )
            //->where('role_name', Role::$user)
            ->where(function ($query) use ($term) {
                $query->where('full_name', 'like', '%' . $term . '%')
                    ->orWhere('user_code', 'like', '%' . $term . '%');
            });

        if ($option === 'for_user_group') {
            $users->whereNotIn('id', GroupUser::all()->pluck('user_id'));
        }

        if ($option === 'just_teacher_role') {
            $users->where('role_name', Role::$teacher);
        }

        if ($option === 'just_student_role') {
            $users->where('role_name', Role::$user);
        }

        if ($option === 'just_organization_role') {
            $users->where('role_name', Role::$organization);
        }

        if ($option === 'just_organization_and_teacher_role') {
            $users->whereIn('role_name', [Role::$organization, Role::$teacher]);
        }

        if ($option === 'except_user') {
            $users->where('role_name', '!=', Role::$user);
        }

        if ($option === 'consultants') {
            $users->whereHas('meeting', function ($query) {
                $query->where('disabled', false)
                    ->whereHas('meetingTimes');
            });
        }

        return response()->json($users->get(), 200);
    }


    public function impersonate($user_id)
    {
        $this->authorize('admin_users_impersonate');

        $user = User::findOrFail($user_id);

        if ($user->isAdmin()) {
            return redirect(getAdminPanelUrl() . '');
        }

        session()->put(['impersonated' => $user->id]);
        if (!$user->isUser()) {
            return redirect('/');
        }

        return redirect('/panel');
    }

    public function exportExcelOrganizations(Request $request)
    {
        $this->authorize('admin_users_export_excel');

        $users = $this->organizations($request, true);

        $usersExport = new OrganizationsExport($users);

        return Excel::download($usersExport, 'organizations.xlsx');
    }

    public function exportExcelInstructors(Request $request)
    {
        $this->authorize('admin_users_export_excel');

        $users = $this->instructors($request, true);

        $usersExport = new OrganizationsExport($users);

        return Excel::download($usersExport, 'instructors.xlsx');
    }

    public function importExcelStudents(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
            ]);

            $file = $request->file('file');

            $rows = Excel::toArray([], $file);

            // Check if the total number of rows is less than 40
            $totalRows = 0;
            foreach ($rows as $sheet) {
                $totalRows += count($sheet); // Count rows in each sheet
            }
            $limit = 40;

            if ($totalRows > $limit) {
                $toastData = [
                    'title' => 'استرداد طلبة',
                    'msg' => "يجيب ان يكون عدد الصفوف المضافة اقل من $limit",
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            $import = new StudentImport();

            Excel::import($import, $file);

            $errors = $import->getErrors();

            if (!empty($errors)) {
                $toastData = [
                    'title' => 'استرداد طلبة',
                    'msg' => implode('<br>', $errors),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            $toastData = [
                'title' => 'استرداد طلبة',
                'msg' => 'تم اضافه الطلبة بنجاح.',
                'status' => 'success'
            ];

            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            $toastData = [
                'title' => 'استرداد طلبة',
                'msg' => $e->getMessage(),
                'status' => 'error'
            ];
            dd($toastData);
            return back()->with(['toast' => $toastData]);
        }
    }
    public function importExcelScholarshipStudents(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
            ]);

            $file = $request->file('file');

            $rows = Excel::toArray([], $file);

            // Check if the total number of rows is less than 40
            $totalRows = 0;
            foreach ($rows as $sheet) {
                $totalRows += count($sheet); // Count rows in each sheet
            }
            $limit = 40;

            if ($totalRows > $limit) {
                $toastData = [
                    'title' => 'استرداد طلبة',
                    'msg' => "يجيب ان يكون عدد الصفوف المضافة اقل من $limit",
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            $import = new StudentImport(true);

            Excel::import($import, $file);

            $errors = $import->getErrors();

            if (!empty($errors)) {
                $toastData = [
                    'title' => 'استرداد طلبة',
                    'msg' => implode('<br>', $errors),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            $toastData = [
                'title' => 'استرداد طلبة',
                'msg' => 'تم اضافه الطلبة بنجاح.',
                'status' => 'success'
            ];

            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            $toastData = [
                'title' => 'استرداد طلبة',
                'msg' => $e->getMessage(),
                'status' => 'error'
            ];
            dd($toastData);
            return back()->with(['toast' => $toastData]);
        }
    }
    public function importExcelCourseStudents(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
            ]);

            $file = $request->file('file');

            $rows = Excel::toArray([], $file);

            // Check if the total number of rows is less than 40
            $totalRows = 0;
            foreach ($rows as $sheet) {
                $totalRows += count($sheet); // Count rows in each sheet
            }
            $limit = 40;

            if ($totalRows > $limit) {
                $toastData = [
                    'title' => 'استرداد طلبة',
                    'msg' => "يجيب ان يكون عدد الصفوف المضافة اقل من $limit",
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            $import = new StudentImport(false, true);

            Excel::import($import, $file);

            $errors = $import->getErrors();

            if (!empty($errors)) {
                $toastData = [
                    'title' => 'استرداد طلبة',
                    'msg' => implode('<br>', $errors),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            $toastData = [
                'title' => 'استرداد طلبة',
                'msg' => 'تم اضافه الطلبة بنجاح.',
                'status' => 'success'
            ];

            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            $toastData = [
                'title' => 'استرداد طلبة',
                'msg' => $e->getMessage(),
                'status' => 'error'
            ];
            dd($toastData);
            return back()->with(['toast' => $toastData]);
        }
    }

    public function sendStudentMail(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
            ]);

            $file = $request->file('file');

            $rows = Excel::toArray([], $file);

            // Check if the total number of rows is less than 40
            $totalRows = 0;
            foreach ($rows as $sheet) {
                $totalRows += count($sheet); // Count rows in each sheet
            }
            $limit = 40;

            if ($totalRows > $limit) {
                $toastData = [
                    'title' => 'ارسال ميل تسجيل لطلبة ',
                    'msg' => "يجيب ان يكون عدد الصفوف المضافة اقل من $limit",
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }



            $import = new SendUserMail();

            Excel::import($import, $file);

            $errors = $import->getErrors();

            if (!empty($errors)) {
                $toastData = [
                    'title' => 'ارسال ميل تسجيل لطلبة ',
                    'msg' => implode('<br>', $errors),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            $toastData = [
                'title' => 'استرداد  ميل تسجيل لطلبة',
                'msg' => 'تم الارسال بنجاح.',
                'status' => 'success'
            ];

            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            $toastData = [
                'title' => 'ارسال ميل تسجيل لطلبة ',
                'msg' => $e->getMessage(),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }
    }

    public function exportBundles()
    {
        $bundles = Bundle::get();
        $bundlesExport = new ProgramCodeExport($bundles);

        return Excel::download($bundlesExport, ' اكواد الدبلومات.xlsx');
    }
    public function exportCourses()
    {
        $courses = Webinar::where(['unattached' => 1, 'hasGroup' => 1])->get();
        $coursesExport = new ProgramCodeExport($courses);

        return Excel::download($coursesExport, ' اكواد الدورات.xlsx');
    }
    public function exportExcelEnrollers(Request $request)
    {
        $this->authorize('admin_users_export_excel');

        // $users = User::where(['role_name' => Role::$user])->whereHas('student')->whereHas('purchasedBundles')->orderBy('created_at', 'desc')->get();


        $sales = $this->Enrollers($request, true);
        if (!empty($request->class_id)) {
            $studyClass = StudyClass::find($request->class_id);
            if (!empty($studyClass)) {
                $sales = (new StudyClassController())->Enrollers($request, $studyClass, true);
            }
        }
        $usersExport = new EnrollersExport($sales, $request->class_id ?? null);

        return Excel::download($usersExport, ' تسجيل الدبلومات.xlsx');
    }
    public function exportExcelScholarship(Request $request)
    {
        $this->authorize('admin_users_export_excel');

        // $users = User::where(['role_name' => Role::$user])->whereHas('student')->whereHas('purchasedBundles')->orderBy('created_at', 'desc')->get();


        $sales = $this->ScholarshipStudent($request, true);
        if (!empty($request->class_id)) {
            $studyClass = StudyClass::find($request->class_id);
            if (!empty($studyClass)) {
                $sales = (new StudyClassController())->ScholarshipStudent($request, $studyClass, true);
            }
        }
        $usersExport = new EnrollersExport($sales, $request->class_id ?? null);

        return Excel::download($usersExport, ' تسجيل المنح الدراسية.xlsx');
    }
    public function exportExcelDirectRegister(Request $request)
    {
        $this->authorize('admin_users_export_excel');

        $bundlstudents = $this->directRegister($request, true);
        if (!empty($request->class_id)) {
            $studyClass = StudyClass::find($request->class_id);
            if (!empty($studyClass)) {
                $bundlstudents = (new StudyClassController())->directRegister($request, $studyClass, true);
            }
        }
        $usersExport = new DirectRegisterExport($bundlstudents, $request->class_id ?? null);

        return Excel::download($usersExport, ' تسجيل مباشر.xlsx');
    }

    public function exportExcelUsers(Request $request)
    {
        $this->authorize('admin_users_export_excel');

        $users = $this->RegisteredUsers($request, true);

        if (!empty($request->class_id)) {
            $studyClass = StudyClass::find($request->class_id);
            if (!empty($studyClass)) {
                $users = (new StudyClassController())->RegisteredUsers($request, $studyClass, true);
            }
        }

        $usersExport = new StudentsExport($users, $request->class_id ?? null);

        return Excel::download($usersExport, 'نموذج انشاء حساب.xlsx');
    }
    public function exportExcelAll(Request $request)
    {
        $this->authorize('admin_users_export_excel');

        $users = $this->students($request, true);

        $usersExport = new StudentsExport($users);

        return Excel::download($usersExport, 'الطلاب.xlsx');
    }

    public function userRegistrationPackage(Request $request, $id)
    {
        $this->authorize('admin_update_user_registration_package');

        $this->validate($request, [
            'instructors_count' => 'nullable|numeric',
            'students_count' => 'nullable|numeric',
            'courses_capacity' => 'nullable|numeric',
            'courses_count' => 'nullable|numeric',
            'meeting_count' => 'nullable|numeric',
        ]);

        $user = User::findOrFail($id);

        if ($user->isOrganization() or $user->isTeacher()) {
            $data = $request->all();

            UserRegistrationPackage::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'instructors_count' => $data['instructors_count'] ?? null,
                'students_count' => $data['students_count'] ?? null,
                'courses_capacity' => $data['courses_capacity'] ?? null,
                'courses_count' => $data['courses_count'] ?? null,
                'meeting_count' => $data['meeting_count'] ?? null,
                'status' => $data['status'],
                'created_at' => time(),
            ]);

            return redirect()->back();
        }

        abort(404);
    }

    public function meetingSettings(Request $request, $id)
    {
        $this->authorize('admin_update_user_meeting_settings');

        $user = User::findOrFail($id);

        if ($user->isOrganization() or $user->isTeacher()) {
            $data = $request->all();

            $user->update([
                'level_of_training' => !empty($data['level_of_training']) ? (new UserLevelOfTraining())->getValue($data['level_of_training']) : null,
                'meeting_type' => $data['meeting_type'] ?? null,
                'group_meeting' => (!empty($data['group_meeting']) and $data['group_meeting'] == 'on'),
                'country_id' => $data['country_id'] ?? null,
                'province_id' => $data['province_id'] ?? null,
                'city_id' => $data['city_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'location' => (!empty($data['latitude']) and !empty($data['longitude'])) ? DB::raw('POINT(' . $data['latitude'] . ',' . $data['longitude'] . ')') : null,
            ]);

            $updateUserMeta = [
                'gender' => $data['gender'] ?? null,
                'age' => $data['age'] ?? null,
                'address' => $data['address'] ?? null,
            ];

            foreach ($updateUserMeta as $name => $value) {
                $checkMeta = UserMeta::where('user_id', $user->id)
                    ->where('name', $name)
                    ->first();

                if (!empty($checkMeta)) {
                    if (!empty($value)) {
                        $checkMeta->update([
                            'value' => $value,
                        ]);
                    } else {
                        $checkMeta->delete();
                    }
                } elseif (!empty($value)) {
                    UserMeta::create([
                        'user_id' => $user->id,
                        'name' => $name,
                        'value' => $value,
                    ]);
                }
            }

            return redirect()->back();
        }

        abort(404);
    }

    public function disableCashbackToggle($id)
    {
        $this->authorize('admin_users_edit');

        $user = User::query()->findOrFail($id);

        $user->update([
            'disable_cashback' => !$user->disable_cashback,
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.cashback_was_disabled_for_the_user'),
            'status' => 'success',
        ];

        return back()->with(['toast' => $toastData]);
    }

    public function disableRegitrationBonusStatus($id)
    {
        $this->authorize('admin_users_edit');

        $user = User::query()->findOrFail($id);

        $user->update([
            'enable_registration_bonus' => false,
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.registration_bonus_was_disabled_for_the_user'),
            'status' => 'success',
        ];

        return back()->with(['toast' => $toastData]);
    }

    public function disableInstallmentApproval($id)
    {
        $this->authorize('admin_users_edit');

        $user = User::query()->findOrFail($id);

        $user->update([
            'installment_approval' => false,
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.installment_was_disabled_for_the_user'),
            'status' => 'success',
        ];

        return back()->with(['toast' => $toastData]);
    }

    public function RegisteredUsers(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $query = User::where(['role_name' => Role::$registered_user])->whereDoesntHave('student');



        $totalStudents = deepClone($query)->count();
        $inactiveStudents = deepClone($query)->where('status', 'inactive')
            ->count();
        $banStudents = deepClone($query)->where('ban', true)
            ->whereNotNull('ban_end_at')
            ->where('ban_end_at', '>', time())
            ->count();

        $totalOrganizationsStudents = User::where('role_name', Role::$user)
            ->whereNotNull('organ_id')
            ->count();
        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $organizations = User::select('id', 'full_name', 'created_at')
            ->where('role_name', Role::$organization)
            ->orderBy('created_at', 'desc')
            ->get();

        $query = $this->filters($query, $request);

        if ($is_export_excel) {
            $users = $query->orderBy('created_at', 'desc')->get();
        } else {
            $users = $query->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        $users = $this->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $users;
        }

        // $purchasedFormBundle=null;
        // $purchasedUserFormBundle=Sale::where('type', 'form_fee')
        //         ->where('buyer_id', $user->id)
        //         ->first();

        $category = Category::where('parent_id', '!=', null)->get();

        $data = [
            'pageTitle' => trans('public.students'),
            'users' => $users,
            'category' => $category,
            'totalStudents' => $totalStudents,
            'inactiveStudents' => $inactiveStudents,
            'banStudents' => $banStudents,
            'totalOrganizationsStudents' => $totalOrganizationsStudents,
            'userGroups' => $userGroups,
            'organizations' => $organizations,
        ];

        return view('admin.students.index', $data);
    }
    public function Users(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        //    $query= User::where(['role_name'=> Role::$registered_user])->where('user_code', "!=", null)->whereHas('orderItems', function($item){
        //         $item->where('form_fee', true);
        //     });
        $query = User::whereHas('student')->whereHas('purchasedFormBundleUnique');

        $salaQuery = Sale::whereNull('refund_at')
            ->whereHas('buyer')
            ->where('type', 'form_fee')
            ->whereNotNull('bundle_id')
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('sales as s2')
                    ->whereRaw('s2.bundle_id = sales.bundle_id')
                    ->where(function ($query) {
                        $query->where('s2.type', 'bundle')
                            ->orWhere('s2.type', 'installment_payment')
                            ->orWhere('s2.type', 'bridging');
                    })
                    ->whereRaw('s2.buyer_id = sales.buyer_id');
            })
            ->where("payment_method", "!=", 'scholarship')
            ->orderBy('buyer_id', 'desc')
            ->groupBy(['buyer_id', 'bundle_id']);


        $totalStudents = deepClone($query)->count();
        $totalSales = deepClone($salaQuery->get())->count();
        $inactiveStudents = deepClone($query)->where('status', 'inactive')
            ->count();
        $banStudents = deepClone($query)->where('ban', true)
            ->whereNotNull('ban_end_at')
            ->where('ban_end_at', '>', time())
            ->count();

        $totalOrganizationsStudents = User::where('role_name', Role::$user)
            ->whereNotNull('organ_id')
            ->count();
        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $organizations = User::select('id', 'full_name', 'created_at')
            ->where('role_name', Role::$organization)
            ->orderBy('created_at', 'desc')
            ->get();

        $query = (new SaleController())->getSalesFilters($salaQuery, $request);

        if ($is_export_excel) {
            $sales = $query->orderBy('created_at', 'desc')->get();
        } else {
            $sales = $query->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        // $users = $this->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $sales;
        }

        // $purchasedFormBundle=null;
        // $purchasedUserFormBundle=Sale::where('type', 'form_fee')
        //         ->where('buyer_id', $user->id)
        //         ->first();

        $category = Category::where('parent_id', '!=', null)->get();
        // $requirement=$users[3]->student;
        // dd($requirement);
        $data = [
            'pageTitle' => trans('public.students'),
            'sales' => $sales,
            'category' => $category,
            'totalStudents' => $totalStudents,
            'totalSales' => $totalSales,
            'inactiveStudents' => $inactiveStudents,
            'banStudents' => $banStudents,
            'totalOrganizationsStudents' => $totalOrganizationsStudents,
            'userGroups' => $userGroups,
            'organizations' => $organizations,
        ];

        return view('admin.students.enrollers', $data);
    }

    public function Enrollers(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');
        $userQuery = User::where(['role_name' => Role::$user])->whereHas(
            'purchasedBundles',
            function ($query) {
                $query->where("payment_method", "!=", 'scholarship');
            }
        );
        $salaQuery = Sale::whereNull('refund_at')
            ->whereNotNull(['bundle_id', 'buyer_id'])
            ->whereHas('buyer')
            ->whereIn('type', ['bundle', 'installment_payment', 'bridging'])
            ->where("payment_method", "!=", 'scholarship')
            ->orderBy('buyer_id', 'desc')
            ->groupBy(['buyer_id', 'bundle_id']);

        $totalStudents = deepClone($userQuery)->count();
        $totalSales = deepClone($salaQuery->get())->count();
        $inactiveStudents = deepClone($userQuery)->where('status', 'inactive')
            ->count();
        $banStudents = deepClone($userQuery)->where('ban', true)
            ->whereNotNull('ban_end_at')
            ->where('ban_end_at', '>', time())
            ->count();

        $totalOrganizationsStudents = User::where('role_name', Role::$user)
            ->whereNotNull('organ_id')
            ->count();
        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $organizations = User::select('id', 'full_name', 'created_at')
            ->where('role_name', Role::$organization)
            ->orderBy('created_at', 'desc')
            ->get();

        $query = (new SaleController())->getSalesFilters($salaQuery, $request);

        if ($is_export_excel) {
            $sales = $query->orderBy('created_at', 'desc')->get();
        } else {
            $sales = $query->orderBy('created_at', 'desc')
                ->paginate(20);
        }



        // $users = $this->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $sales;
        }

        // $purchasedFormBundle=null;
        // $purchasedUserFormBundle=Sale::where('type', 'form_fee')
        //         ->where('buyer_id', $user->id)
        //         ->first();

        $category = Category::where('parent_id', '!=', null)->get();
        // $requirement=$users[3]->student;
        // dd($requirement);
        $data = [
            'pageTitle' => trans('public.students'),
            'sales' => $sales,
            'category' => $category,
            'totalStudents' => $totalStudents,
            'totalSales' => $totalSales,
            // 'inactiveStudents' => $inactiveStudents,
            // 'banStudents' => $banStudents,
            'totalOrganizationsStudents' => $totalOrganizationsStudents,
            'userGroups' => $userGroups,
            'organizations' => $organizations,
        ];

        return view('admin.students.enrollers', $data);
    }


    public function directRegister(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');
        $usersQuery = User::whereHas('student.bundleStudent', function ($query) {
            $query->whereNull('class_id');
        });

        $query = BundleStudent::whereHas('student')->whereNull('class_id');


        $totalStudents = deepClone($usersQuery)->count();
        $totalRegistration = deepClone($query)->count();


        $query = $this->directRegisterFilters($query, $request);

        if ($is_export_excel) {
            $bundlstudents = $query->orderBy('student_id', 'desc')->get();
        } else {
            $bundlstudents = $query->orderBy('student_id', 'desc')->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        // $users = $this->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $bundlstudents;
        }

        // $purchasedFormBundle=null;
        // $purchasedUserFormBundle=Sale::where('type', 'form_fee')
        //         ->where('buyer_id', $user->id)
        //         ->first();

        $category = Category::where('parent_id', '!=', null)->get();
        // $requirement=$users[3]->student;
        // dd($requirement);
        $data = [
            'pageTitle' => trans('public.students'),
            'bundlstudents' => $bundlstudents,
            'category' => $category,
            'totalStudents' => $totalStudents,
            'totalRegistration' => $totalRegistration,

        ];

        return view('admin.students.direct_register', $data);
    }

    public function directRegisterFilters($query, Request $request)
    {
        // Filter by user code
        if ($request->filled('user_code')) {
            $query->whereHas('student.registeredUser', function ($query) use ($request) {
                $query->where('user_code', 'like', '%' . $request->get('user_code') . '%');
            });
        }

        // Filter by email
        if ($request->filled('email')) {
            $query->whereHas('student.registeredUser', function ($query) use ($request) {
                $query->where('email', 'like', '%' . $request->get('email') . '%');
            });
        }

        // Filter by student name (Arabic or full name)
        if ($request->filled('ar_name')) {
            $query->whereHas('student', function ($query) use ($request) {
                $query->where('ar_name', 'like', '%' . $request->get('ar_name') . '%');
            });
        }

        if ($request->filled('full_name')) {
            $query->whereHas('student.registeredUser', function ($query) use ($request) {
                $query->where('full_name', 'like', '%' . $request->get('full_name') . '%');
            });
        }
        if ($request->filled('user_name')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('student.registeredUser', function ($query) use ($request) {
                    $query->where('full_name', 'like', '%' . $request->get('user_name') . '%');
                })->orWhereHas('student', function ($query) use ($request) {
                    $query->where('ar_name', 'like', '%' . $request->get('user_name') . '%')
                        ->orWhere('en_name', 'like', '%' . $request->get('user_name') . '%');
                });
            });
        }

        // Filter by mobile number
        if ($request->filled('mobile')) {
            $query->whereHas('student.registeredUser', function ($query) use ($request) {
                $query->where('mobile', 'like', '%' . $request->get('mobile') . '%');
            });
        }

        return $query;
    }


    public function ScholarshipStudent(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');
        $query = User::where(['role_name' => Role::$user])->whereHas('purchasedBundles', function ($query) {
            $query->where("payment_method", 'scholarship');
        });

        $salaQuery = Sale::whereNull('refund_at')
            ->whereNotNull('bundle_id')
            ->whereHas('buyer')
            ->whereIn('type', ['bundle', 'installment_payment', 'bridging'])
            ->where("payment_method", "=", 'scholarship')
            ->orderBy('buyer_id', 'desc')
            ->groupBy(['buyer_id', 'bundle_id']);


        $totalStudents = deepClone($query)->count();
        $totalSales = deepClone($salaQuery)->count();
        $inactiveStudents = deepClone($query)->where('status', 'inactive')
            ->count();
        $banStudents = deepClone($query)->where('ban', true)
            ->whereNotNull('ban_end_at')
            ->where('ban_end_at', '>', time())
            ->count();

        $totalOrganizationsStudents = User::where('role_name', Role::$user)
            ->whereNotNull('organ_id')
            ->count();
        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $organizations = User::select('id', 'full_name', 'created_at')
            ->where('role_name', Role::$organization)
            ->orderBy('created_at', 'desc')
            ->get();

        $query = (new SaleController())->getSalesFilters($salaQuery, $request);

        if ($is_export_excel) {
            $sales = $query->orderBy('created_at', 'desc')->get();
        } else {
            $sales = $query->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        // $users = $this->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $sales;
        }

        $category = Category::where('parent_id', '!=', null)->get();

        $data = [
            'pageTitle' => trans('public.students'),
            'sales' => $sales,
            'category' => $category,
            'totalStudents' => $totalStudents,
            'totalSales' => $totalSales,
            'inactiveStudents' => $inactiveStudents,
            'banStudents' => $banStudents,
            'totalOrganizationsStudents' => $totalOrganizationsStudents,
            'userGroups' => $userGroups,
            'organizations' => $organizations,
        ];
        return view('admin.students.enrollers', $data);
    }

    public function coursesList(Request $request)
    {
        $query = Webinar::where(['unattached' => 1, 'hasGroup' => 1])
            ->withCount('groups')
            ->withCount('sales')
            ->with([
                'sales.buyer'
            ]);

        $webinars = $this->coursesListFilter($query, $request)->get();

        $cleanWebinars = $webinars->map(function ($webinar) {
            $array = $webinar->toArray();

            // Sanitize all string values to ensure UTF-8
            array_walk_recursive($array, function (&$value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                }
            });

            return $array;
        });

        return response()->json($cleanWebinars);
    }



    public function Courses(Request $request, $id, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');
        $webinar = Webinar::find($id);
        $query = $webinar->groups->unique();
        $totalGroups = deepClone($query)->count();


        $query = $this->filters($query, $request);

        if ($is_export_excel) {
            $groups = $query->orderBy('created_at', 'desc')->get();
        } else {
            $groups = $query;
        }

        if ($is_export_excel) {
            return $groups;
        }


        $category = Category::where('parent_id', '!=', null)->get();

        $data = [
            'pageTitle' => trans('public.students'),
            'groups' => $groups,
            'item' => $webinar,
            'category' => $category,
            'totalGroups' => $totalGroups,

        ];

        return view('admin.students.courses', $data);
    }


    public function coursesListFilter($query, $request)
    {
        $title = $request->input('title', null);
        $from = $request->input('from', null);
        $to = $request->input('to', null);

        $query = fromAndToDateFilter($from, $to, $query, 'start_date');
        if ($title) {
            $query->whereTranslationLike('title', '%' . $title . '%')
                ->orWhere('slug', 'like', '%' . $title . '%');
        }
        return $query;
    }


    public function groupInfo($group_id)
    {
        // dd($course_id.','.$group_id);
        $group = Group::find($group_id);
        $item = $group->webinar ?? $group->bundle;
        $enrollments = $group->enrollments;
        // dd($enrollments);
        $data = [
            'pageTitle' => trans('public.students'),
            'totalStudents' => $enrollments->count(),
            'enrollments' => $enrollments,
            'item' => $item,
            'group' => $group,

        ];

        return view('admin.students.groups', $data);
    }

    public function groupEdit(Group $group)
    {

        return view('admin.students.groups.edit', compact('group'));
    }

    public function groupUpdate(Request $request, Group $group)
    {
        $validData = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'required|in:inactive,active'
        ]);
        $toastData = [
            'title' => 'تعديل بيانات المجموعة ',
            'msg' => 'تم التعديل بنجاح',
            'status' => 'success',
        ];
        $group->update($validData);
        return back()->with('toast', $toastData);
        // return redirect('/admin/courses/' . $group->webinar_id)->with('toast', $toastData);
    }

    public function sendEmail($user, $data)
    {
        if (!empty($user) and !empty($user->email)) {
            Mail::to($user->email)->send(new SendNotifications(['title' => $data['title'] ?? '', 'message' => $data['body'] ?? '']));
        }
    }

    public function sendNotificationToUser($user, $data)
    {
        Notification::create([
            'user_id' => $user->id ?? 0,
            'sender_id' => auth()->id(),
            'title' => $data['title'] ?? '',
            'message' => $data['body'] ?? '',
            'sender' => Notification::$AdminSender,
            'type' => "single",
            'created_at' => time()
        ]);
    }

    function changeGroup(Request $request, Group $group)
    {
        try {
            $request->validate([
                'from' => 'required|exists:groups,id',
                'to' => 'required|exists:groups,id',
                'user_id' => 'required|exists:users,id'
            ]);

            $user =  auth()->user();
            Enrollment::where(['user_id' => $request->user_id, 'group_id' => $request->from])->update(["group_id" => $request->to]);

            $toastData = [
                'title' => 'تحويل من جروب لأخر',
                'msg' => 'تم التحويل بنجاح',
                'status' => 'success',
            ];
            return back()->with('toast', $toastData);
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            $toastData = [
                'title' => '',
                'msg' => $th->getMessage(),
                'status' => 'error',
            ];

            return back()->with(['toast' => $toastData]);
        }
    }


    function groupExportExcel(Request $request, Group $group)
    {
        $enrollments = $group->enrollments;
        $export = new GroupStudentsExport($enrollments);
        return Excel::download($export, "group_$group->name _students.xlsx");
    }
}
