<?php

namespace App\Http\Controllers\Admin;

use App\BundleStudent;
use App\Exports\BatchStudentsExportV2;
use App\Http\Controllers\Controller;
use App\Imports\BatchStudentImport;
use App\Models\Category;
use App\Models\Group;
use App\Models\Role;
use App\Models\Sale;
use Illuminate\Http\Request;
use App\Models\StudyClass;
use App\StudentRequirement;
use App\User;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class StudyClassController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $classes = StudyClass::paginate(10);
        $pageTitle = 'الدفعات الدراسية';
        return view('admin.study_classes.lists', compact('classes', 'pageTitle'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);


        if ($validator->fails()) {
            $toastData = [
                'title' => 'إضافة دفعة',
                'msg' => implode(
                    ', ',
                    $validator->errors()->all()
                ),
                'status' => 'error'
            ];
            return back()->with(['sweetalert' => $toastData]);
        }

        $validData = $validator->validated();
        StudyClass::create($validData);
        $toastData = [
            'title' => 'إضافة دفعة',
            'msg' => "تم اضافة دفعة جديدة بنجاح",
            'status' => 'success'
        ];
        return back()->with(['toast' => $toastData]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StudyClass $class)
    {
        //

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);

        if ($validator->fails()) {
            $toastData = [
                'title' => 'إضافة دفعة',
                'msg' => implode(
                    ', ',
                    $validator->errors()->all()
                ),
                'status' => 'error'
            ];

            return back()->with(['sweetalert' => $toastData]);
        }

        $validData = $validator->validated();

        $class->update($validData);
        $toastData = [
            'title' => 'تعديل دفعة',
            'msg' => "تم تعديل بيانات الدفعة بنجاح",
            'status' => 'success'
        ];
        return back()->with(['toast' => $toastData]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudyClass $class)
    {
        //
        $class->delete();
        $toastData = [
            'title' => 'حذف دفعة',
            'msg' => "تم حذف الدفعة بنجاح",
            'status' => 'success'
        ];
        return back()->with(['toast' => $toastData]);
    }

    public function students(StudyClass $class, Request $request, $is_export_excel = false)
    {

        $pageTitle = trans('public.students');
        $query = User::whereHas(
            'bundleSales',
            function ($query) use ($class) {
                $query->where("class_id", $class->id)->groupBy('buyer_id');
            }
        );

        $salesQuery = Sale::whereNull('refund_at')
            ->whereNotNull('bundle_id')
            ->where('class_id', $class->id)
            ->whereHas('buyer')
            ->orderBy('buyer_id', 'desc')->groupBy(['buyer_id', 'bundle_id']);

        $totalSales = $salesQuery->get()->count();
        $salesQuery = (new SaleController())->getSalesFilters($salesQuery, $request);

        if ($is_export_excel) {
            $enrollments = $salesQuery->orderBy('created_at', 'desc')->orderBy('buyer_id', 'desc')->get();
        } else {

            $enrollments = $salesQuery->orderBy('created_at', 'desc')
                ->orderBy('buyer_id', 'desc')
                ->paginate(20);
        }

        // $enrollments = (new UserController())->addUsersExtraInfo($enrollments);

        if ($is_export_excel) {
            return $enrollments;
        }
        return view('admin.study_classes.student', compact('enrollments', "class", 'pageTitle', 'totalSales'));
    }

    public function exportExcelBatchStudents(StudyClass $class, Request $request)
    {
        $this->authorize('admin_users_export_excel');
        $sales = $this->students($class, $request, true);

        // $usersExport = new BatchStudentsExport($users, $class->id);

        $usersExport = new BatchStudentsExportV2($sales);

        return Excel::download($usersExport, 'طلاب ' . $class->title . '.xlsx');
    }

    public function RegisteredUsers(Request $request, StudyClass $class, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $query = User::where(['role_name' => Role::$registered_user])->whereDoesntHave('student')->whereBetween('created_at', [strtotime($class->start_date), strtotime($class->end_date)]);

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

        $query = (new UserController())->filters($query, $request);

        if ($is_export_excel) {
            $users = $query->orderBy('created_at', 'desc')->get();
        } else {
            $users = $query->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        $users = (new UserController())->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $users;
        }


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
            'class' => $class
        ];

        return view('admin.students.index', $data);
    }

    public function Users(Request $request, StudyClass $class, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        // $usersQuery = User::whereHas('student.bundleStudent', function ($query) use ($class) {
        //     $query->where('class_id', $class->id);
        // });
        $query = User::whereHas('student')->whereHas('purchasedFormBundleUnique', function ($query) use ($class) {
            $query->where('class_id', $class->id);
        });

        $salaQuery = Sale::whereNull('refund_at')
            ->whereHas('buyer')
            ->where('type', 'form_fee')
            ->whereNotNull('bundle_id')
            ->where('class_id', $class->id)
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('sales as s2')
                    ->whereRaw('s2.bundle_id = sales.bundle_id')
                    ->where(function ($query) {
                        $query->where('s2.type', 'bundle')
                            ->orWhere('s2.type', 'installment_payment');
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

            $sales = $query->orderBy('created_at', 'desc')->paginate(20);
        }

        // $users = (new UserController())->addUsersExtraInfo($users);

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
            'class' => $class
        ];

        return view('admin.students.enrollers', $data);
    }

    public function Enrollers(Request $request, StudyClass $class, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');


        // $usersQuery = User::whereHas('student.bundleStudent', function ($query) use ($class) {
        //         $query->where('class_id', $class->id);
        //     });

        $query = User::where(['role_name' => Role::$user])->whereHas('purchasedBundles', function ($query) use ($class) {
            $query->where('class_id', $class->id)->where("payment_method", "!=", 'scholarship');
        });

        $salaQuery = Sale::whereNull('refund_at')
            ->whereNotNull('bundle_id')
            ->whereHas('buyer')
            ->whereIn('type', ['bundle', 'installment_payment', 'bridging'])
            ->where('class_id', $class->id)->where("payment_method", "!=", 'scholarship')
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

        // $users =(new UserController())->addUsersExtraInfo($users);


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
            'class' => $class
        ];

        return view('admin.students.enrollers', $data);
    }

    public function ScholarshipStudent(Request $request, StudyClass $class, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');
        $query = User::where(['role_name' => Role::$user])->whereHas('purchasedBundles', function ($query) use ($class) {
            $query->where("payment_method", 'scholarship')->where('class_id', $class->id);
        });

        $salaQuery = Sale::whereNull('refund_at')
            ->whereNotNull('bundle_id')
            ->whereHas('buyer')
            ->whereIn('type', ['bundle', 'installment_payment', 'bridging'])
            ->where("payment_method", "=", 'scholarship')
            ->where('class_id', $class->id)
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

            $sales = $query->orderBy('created_at', 'desc')->paginate(20);
        }

        // $users = (new UserController())->addUsersExtraInfo($users);

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


    public function directRegister(Request $request, StudyClass $class, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $studentQuery = User::whereHas('student.bundleStudent', function ($query) use ($class) {
            $query->whereNull('class_id')->whereHas('bundle', function ($query)  use ($class) {
                $query->where('batch_id', $class->id);
            });
        });

        $query = BundleStudent::whereNull('class_id')
            ->whereHas('student')
            ->whereHas(
                'bundle',
                function ($query)  use ($class) {
                    $query->where('batch_id', $class->id);
                }
            );

        $totalStudents = deepClone($studentQuery)->count();
        $totalRegistration = deepClone($query)->count();


        $query = (new UserController())->directRegisterFilters($query, $request);

        if ($is_export_excel) {
            $bundlstudents = $query->orderBy('created_at', 'desc')->get();
        } else {
            $bundlstudents = $query->orderBy('created_at', 'desc')->paginate(20);
        }

        // $users = (new UserController())->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $bundlstudents;
        }

        $category = Category::where('parent_id', '!=', null)->get();

        $data = [
            'pageTitle' => trans('public.students'),
            'bundlstudents' => $bundlstudents,
            'category' => $category,
            'totalStudents' => $totalStudents,
            'totalRegistration' => $totalRegistration,
            'class' => $class,

        ];

        return view('admin.students.direct_register', $data);
    }

    public function requirements(Request $request, StudyClass $class)
    {
        $query = StudentRequirement::whereHas('bundleStudent', function ($query) use ($class) {
            $query->where('class_id', $class->id); // Filter by class_id
        })->orderByDesc('created_at');
        $query = (new RequirementController())->filters($query, $request);
        $requirements = $query->paginate(20);

        return view('admin.requirements.index', ['requirements' => $requirements]);
    }


    function importExcelBatchStudents(Request $request, StudyClass $class)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
            ]);

            $file = $request->file('file');

            $import = new BatchStudentImport($class->id);

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
            return back()->with(['toast' => $toastData]);
        }
    }
}
