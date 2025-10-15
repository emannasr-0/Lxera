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
        $classes = StudyClass::paginate(10);

        $classes->getCollection()->transform(function ($class) {
            return [
                'id' => $class->id,
                'title' => $class->title,
                'register_enrollments' => $class->registerEnrollements()->count(),
                'form_fee_enrollments' => $class->formFeeEnrollements()->count(),
                'bundle_enrollments' => $class->bundleEnrollements()->count(),
                'direct_register_enrollments' => $class->directRegisterEnrollements()->count(),
                'scholarship_enrollments' => $class->scholarshipEnrollements()->count(),
                'start_date' => $class->start_date ?? '--',
                'end_date' => $class->end_date ?? '--',
                'created_at' => $class->created_at,
            ];
        });

        return response()->json($classes, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function getTargetOptions()
    {
        $targetOptions = [];
        $targets = ['all', 'bundles', 'specific_bundles', 'webinars', 'specific_webinars'];

        foreach ($targets as $target) {
            $targetOptions[] = [
            'label_en' => $target,
            'label_ar' => trans('panel.' . $target, [], 'ar'),
            ];
        }
        $data = [
            'targetOptions' => $targetOptions
        ];

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($url_name, Request $request)
    {
        try {
            $organization = Organization::where('url_name', $url_name)->first();

            if (!$organization) {
                return response()->json(['message' => 'This Organization not found'], 404);
            }

            $request->validate([
                'title' => 'required|min:3|max:128',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date'
            ]);


            $validData = $request->all();
            $studyClass = StudyClass::create($validData);

            return response()->json([
                'message' => 'تم اضافة دفعة جديدة بنجاح',
                'data' => $studyClass,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $formattedErrors = [];

            $currentLocale = app()->getLocale();

            foreach ($e->errors() as $field => $messages) {
                $data = $e->validator->getData();
                $rules = [$field => $e->validator->getRules()[$field]];

                app()->setLocale('ar');
                $arValidator = Validator::make($data, $rules);
                $arMessage = $arValidator->errors()->first($field);

                app()->setLocale('en');
                $enValidator = Validator::make($data, $rules);
                $enMessage = $enValidator->errors()->first($field);

                $formattedErrors[$field] = [
                    'ar' => $arMessage,
                    'en' => $enMessage
                ];
            }

            app()->setLocale($currentLocale);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $formattedErrors
            ], 422);
        }
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
        try {
            $organization = Organization::where('url_name', $url_name)->first();

            if (!$organization) {
                return response()->json(['message' => 'This Organization not found'], 404);
            }

            $request->validate([
                'title' => 'sometimes|min:3|max:128',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after:start_date'
            ]);

            $class->update($request->all());

            return response()->json([
                'message' => "تم تعديل بيانات الدفعة بنجاح",
                'data' => $class,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $formattedErrors = [];

            $currentLocale = app()->getLocale();

            foreach ($e->errors() as $field => $messages) {
                $data = $e->validator->getData();
                $rules = [$field => $e->validator->getRules()[$field]];

                app()->setLocale('ar');
                $arValidator = Validator::make($data, $rules);
                $arMessage = $arValidator->errors()->first($field);

                app()->setLocale('en');
                $enValidator = Validator::make($data, $rules);
                $enMessage = $enValidator->errors()->first($field);

                $formattedErrors[$field] = [
                    'ar' => $arMessage,
                    'en' => $enMessage
                ];
            }

            app()->setLocale($currentLocale);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $formattedErrors
            ], 422);
        }
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

        $filters = $request->only(['user_code', 'email', 'full_name', 'mobile']);

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
                ->whereHas('buyer', function ($query) use ($filters) {
                    $query->filterBySearch($filters);
                })
                ->paginate(10);
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

        $filters = $request->only(['email', 'full_name', 'mobile', 'status']);

        $query = User::where(['role_name' => Role::$registered_user])->whereDoesntHave('student')->whereBetween('created_at', [strtotime($class->start_date), strtotime($class->end_date)]);

        $query = (new UserController())->filters($query, $request);

        if ($is_export_excel) {
            $users = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'data' => $users,
            ]);
        } else {
            $users = $query->filterBySearch($filters)
                ->with(['appliedProgram'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
                'data' => $users->items(),
            ]);
        }

        $users = (new UserController())->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $users;
        }
        $cleanUsers = $users->map(function ($user) {
            $array = $user->toArray();

            // Sanitize all string values to ensure UTF-8
            array_walk_recursive($array, function (&$value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                }
            });

            return $array;
        });

        return response()->json($cleanUsers);
    }

    public function Users($url_name, Request $request, StudyClass $class, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

        $filters = $request->only(['user_code', 'email', 'full_name', 'mobile']);

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
            ->whereHas('buyer', function ($query) use ($filters) {
                $query->filterBySearch($filters);
            })
            ->orderBy('buyer_id', 'desc')
            ->groupBy(['buyer_id', 'bundle_id']);

        $query = (new SaleController())->getSalesFilters($salaQuery, $request);

        if ($is_export_excel) {
            $sales = $query->orderBy('created_at', 'desc')->get();
        } else {

            $sales = $query->orderBy('created_at', 'desc')->paginate(10);
        }

        if ($is_export_excel) {
            return $sales;
        }

        return response()->json([
            'pagination' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
            ],
            'data' => $sales->items(),
        ]);
    }

    public function Enrollers($url_name, Request $request, StudyClass $class, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

        $filters = $request->only(['user_code', 'email', 'full_name', 'mobile']);

        $query = User::where(['role_name' => Role::$user])->whereHas('purchasedBundles', function ($query) use ($class) {
            $query->where('class_id', $class->id)->where("payment_method", "!=", 'scholarship');
        });

        $salaQuery = Sale::whereNull('refund_at')
            ->whereNotNull('bundle_id')
            ->whereHas('buyer')
            ->whereIn('type', ['bundle', 'installment_payment', 'bridging'])
            ->where('class_id', $class->id)->where("payment_method", "!=", 'scholarship')
            ->with(['buyer', 'bundle'])
            ->whereHas('buyer', function ($query) use ($filters) {
                $query->filterBySearch($filters);
            })
            ->orderBy('buyer_id', 'desc')
            ->groupBy(['buyer_id', 'bundle_id']);

        $query = (new SaleController())->getSalesFilters($salaQuery, $request);

        if ($is_export_excel) {
            $sales = $query->orderBy('created_at', 'desc')->get();
        } else {

            $sales = $query->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        if ($is_export_excel) {
            return $sales;
        }

        return response()->json([
            'success' => true,
            'message' => $sales
        ]);
    }

    public function ScholarshipStudent($url_name, Request $request, StudyClass $class, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

        $filters = $request->only(['user_code', 'email', 'full_name', 'mobile']);

        $query = User::where(['role_name' => Role::$user])
            ->whereHas('purchasedBundles', function ($query) use ($class) {
                $query->where("payment_method", 'scholarship')
                    ->where('class_id', $class->id);
            });

        $salaQuery = Sale::whereNull('refund_at')
            ->whereNotNull('bundle_id')
            ->whereHas('buyer')
            ->whereIn('type', ['bundle', 'installment_payment', 'bridging'])
            ->where("payment_method", "=", 'scholarship')
            ->where('class_id', $class->id)
            ->with(['buyer', 'bundle'])
            ->whereHas('buyer', function ($query) use ($filters) {
                $query->filterBySearch($filters);
            })
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

            $sales = $query->orderBy('created_at', 'desc')->paginate(10);
        }

        // $users = (new UserController())->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $sales;
        }

        $category = Category::where('parent_id', '!=', null)->get();

        $data = [
            'pageTitle' => trans('public.students'),
            'sales' => $sales,
            // 'category' => $category,
            'totalStudents' => $totalStudents,
            'totalSales' => $totalSales,
            'inactiveStudents' => $inactiveStudents,
            'banStudents' => $banStudents,
            'totalOrganizationsStudents' => $totalOrganizationsStudents,
            // 'userGroups' => $userGroups,
            // 'organizations' => $organizations,
        ];

        return response()->json($data);
    }

    public function directRegister($url_name, Request $request, StudyClass $class, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

        $filters = $request->only(['user_code', 'email', 'full_name', 'mobile']);

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

        $query = (new UserController())->directRegisterFilters($query, $request)
            ->with(['student.user', 'bundle'])
            ->whereHas('student.user', function ($query) use ($filters) {
                $query->filterBySearch($filters);
            });

        if ($is_export_excel) {
            $bundlstudents = $query->orderBy('created_at', 'desc')->get();
        } else {
            $bundlstudents = $query->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        if ($is_export_excel) {
            return $bundlstudents;
        }

        return response()->json([
            'pagination' => [
                'current_page' => $bundlstudents->currentPage(),
                'last_page' => $bundlstudents->lastPage(),
                'per_page' => $bundlstudents->perPage(),
                'total' => $bundlstudents->total(),
            ],
            'data' => $bundlstudents->items(),
        ]);
    }

    public function requirements($url_name, Request $request, StudyClass $class)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This Organization not found'], 404);
        }

        $filters = $request->only(['user_code', 'email', 'full_name', 'mobile']);

        $query = StudentRequirement::with([
            'bundleStudent.bundle',
            'bundleStudent.student',
            'bundleStudent.student.user',
        ])
            ->whereHas('bundleStudent', function ($query) use ($class) {
                $query->where('class_id', $class->id);
            })
            ->whereHas('bundleStudent.student.user', function ($query) use ($filters) {
                $query->filterBySearch($filters);
            })
            ->orderByDesc('created_at');
        $query = (new RequirementController())->filters($query, $request);
        $requirements = $query->paginate(10);

        return response()->json($requirements);
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
