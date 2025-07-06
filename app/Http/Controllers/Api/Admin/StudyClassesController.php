<?php

namespace App\Http\Controllers\Api\Admin;

use App\BundleStudent;
use App\Exports\BatchStudentsExportV2;
use App\Http\Controllers\Admin\RequirementController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Controller;
use App\Imports\BatchStudentImport;
use App\Models\Api\Organization;
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

class StudyClassesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $classes = StudyClass::all()->map(function ($class) {
            return [
                'id' => $class->id,
                'title' => $class->title,
                'created_at' => $class->created_at,
                'register_enrollments' => $class->registerEnrollements(),
                'form_fee_enrollments' => $class->formFeeEnrollements(),
                'bundle_enrollments' => $class->bundleEnrollements(),
                'direct_register_enrollments' => $class->directRegisterEnrollements(),
                'scholarship_enrollments' => $class->scholarshipEnrollements(),
            ];
        });

        return response()->json($classes, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($url_name, Request $request)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validData = $validator->validated();
        $studyClass = StudyClass::create($validData);

        return response()->json([
            'message' => 'تم اضافة دفعة جديدة بنجاح',
            'data' => $studyClass,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($url_name, Request $request, StudyClass $class)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validData = $validator->validated();

        $class->update($validData);
        $toastData = [
            'title' => 'تعديل دفعة',
            'msg' => "تم تعديل بيانات الدفعة بنجاح",
            'status' => 'success'
        ];

        return response()->json([
            'message' => "تم تعديل بيانات الدفعة بنجاح",
            'data' => $validData,
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($url_name, StudyClass $class)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

        $class->delete();

        return response()->json([
            'message' => 'Class deleted successfully.'
        ]);
    }

    public function students($url_name, StudyClass $class, Request $request, $is_export_excel = false)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

        $query = User::whereHas('bundleSales', function ($query) use ($class) {
            $query->where("class_id", $class->id)->groupBy('buyer_id');
        });

        $salesQuery = Sale::whereNull('refund_at')
            ->whereNotNull('bundle_id')
            ->where('class_id', $class->id)
            ->whereHas('buyer')
            ->with(['buyer', 'bundle'])
            ->orderBy('buyer_id', 'desc')
            ->groupBy(['buyer_id', 'bundle_id']);

        $totalSales = $salesQuery->get()->count();

        $salesQuery = (new SaleController())->getSalesFilters($salesQuery, $request);

        if ($is_export_excel) {
            $enrollments = $salesQuery->orderBy('created_at', 'desc')
                ->orderBy('buyer_id', 'desc')
                ->get();
        } else {
            $enrollments = $salesQuery->orderBy('created_at', 'desc')
                ->orderBy('buyer_id', 'desc')
                ->get();
        }

        if ($is_export_excel) {
            return response()->json([
                'success' => true,
                'total_sales' => $totalSales,
                'data' => $enrollments
            ]);
        }

        return response()->json([
            'success' => true,
            'class' => $class,
            'total_sales' => $totalSales,
            'enrollments' => $enrollments
        ]);
    }

    private function getSalesData(StudyClass $class, Request $request)
    {
        $salesQuery = Sale::whereNull('refund_at')
            ->whereNotNull('bundle_id')
            ->where('class_id', $class->id)
            ->whereHas('buyer')
            ->with(['buyer', 'bundle'])
            ->orderBy('buyer_id', 'desc')
            ->groupBy(['buyer_id', 'bundle_id']);

        $salesQuery = (new SaleController())->getSalesFilters($salesQuery, $request);

        return $salesQuery->orderBy('created_at', 'desc')->orderBy('buyer_id', 'desc')->get();
    }

    public function exportExcelBatchStudents($url_name, StudyClass $class, Request $request)
    {
        $this->authorize('admin_users_export_excel');

        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

        $sales = $this->getSalesData($class, $request); // use the clean method

        $usersExport = new BatchStudentsExportV2($sales);

        return Excel::download($usersExport, 'طلاب ' . $class->title . '.xlsx');
    }


    public function RegisteredUsers($url_name, Request $request, StudyClass $class, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

        $query = User::where(['role_name' => Role::$registered_user])->whereDoesntHave('student')->whereBetween('created_at', [strtotime($class->start_date), strtotime($class->end_date)]);

        $query = (new UserController())->filters($query, $request);

        if ($is_export_excel) {
            $users = $query->orderBy('created_at', 'desc')->get();
        } else {
            $users = $query->orderBy('created_at', 'desc')
                ->get();
        }

        $users = (new UserController())->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $users;
        }

        return response()->json($users);
    }
    public function Users($url_name, Request $request, StudyClass $class, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

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
            ->with(['buyer', 'bundle'])
            ->orderBy('buyer_id', 'desc')
            ->groupBy(['buyer_id', 'bundle_id']);

        $query = (new SaleController())->getSalesFilters($salaQuery, $request);

        if ($is_export_excel) {
            $sales = $query->orderBy('created_at', 'desc')->get();
        } else {

            $sales = $query->orderBy('created_at', 'desc')->get();
        }

        if ($is_export_excel) {
            return $sales;
        }

        return response()->json([
            'success' => true,
            'message' => $sales
        ]);
    }
    public function Enrollers($url_name, Request $request, StudyClass $class, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

        $query = User::where(['role_name' => Role::$user])->whereHas('purchasedBundles', function ($query) use ($class) {
            $query->where('class_id', $class->id)->where("payment_method", "!=", 'scholarship');
        });

        $salaQuery = Sale::whereNull('refund_at')
            ->whereNotNull('bundle_id')
            ->whereHas('buyer')
            ->whereIn('type', ['bundle', 'installment_payment', 'bridging'])
            ->where('class_id', $class->id)->where("payment_method", "!=", 'scholarship')
            ->with(['buyer', 'bundle'])
            ->orderBy('buyer_id', 'desc')
            ->groupBy(['buyer_id', 'bundle_id']);

        $query = (new SaleController())->getSalesFilters($salaQuery, $request);

        if ($is_export_excel) {
            $sales = $query->orderBy('created_at', 'desc')->get();
        } else {

            $sales = $query->orderBy('created_at', 'desc')
                ->get();
        }

        if ($is_export_excel) {
            return $sales;
        }

        return response()->json([
            'success' => true,
            'message' => $sales
        ]);
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

    public function directRegister($url_name, Request $request, StudyClass $class, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

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


        $query = (new UserController())->directRegisterFilters($query, $request)->with(['student.user', 'bundle']);

        if ($is_export_excel) {
            $bundlstudents = $query->orderBy('created_at', 'desc')->get();
        } else {
            $bundlstudents = $query->orderBy('created_at', 'desc')->get();
        }

        if ($is_export_excel) {
            return $bundlstudents;
        }

        return response()->json([
            'success' => true,
            'message' => $bundlstudents
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
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
