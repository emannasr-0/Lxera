<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Exports\WebinarStudents;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\WebinarController;
use App\Http\Resources\BundleResource;
use App\Models\Bundle;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Certificate;
use App\Models\CourseNoticeboard;
use App\Models\Webinar;

class BundleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = apiAuth();

        $query = Bundle::where(function ($query) use ($user) {
            $query->where('bundles.teacher_id', $user->id);
            $query->orWhere('bundles.creator_id', $user->id);
        });

        $bundlesHours = deepClone($query)->join('bundle_webinars', 'bundle_webinars.bundle_id', 'bundles.id')
            ->join('webinars', 'webinars.id', 'bundle_webinars.webinar_id')
            ->select('bundles.*', DB::raw('sum(webinars.duration) as duration'))
            ->sum('duration');

        $query->with([
            /*'reviews' => function ($query) {
                $query->where('status', 'active');
            },*/
            'bundleWebinars',
            'category',
            'teacher',
            'sales' => function ($query) {
                $query->where('type', 'bundle')
                    ->whereNull('refund_at');
            }
        ])->orderBy('updated_at', 'desc');

        $bundlesCount = $query->count();

        $bundles = $query->get();

        $bundleSales = Sale::where('seller_id', $user->id)
            ->where('type', 'bundle')
            ->whereNotNull('bundle_id')
            ->whereNull('refund_at')
            ->get();

        return apiResponse2(
            1,
            'retrieved',
            trans('api.public.retrieved'),
            [
                'bundles' => BundleResource::collection($bundles),
                'bundles_count' => $bundlesCount,
                'bundle_sales_amount' => $bundleSales->sum('amount'),
                'bundle_sales_count' => $bundleSales->count(),
                'bundles_hours' => $bundlesHours,

            ]
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bundle $bundle)
    {
        $bundle = $bundle->where('creator_id', apiAuth()->id)
            ->first();
        if (!$bundle) {
            abort(404);
        }
        $bundle->delete();
        return apiResponse2(1, 'deleted', trans('api.public.deleted'));
    }


    public function export($id)
    {
        $user = apiAuth();
        $bundle = Bundle::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id);
            })->first();

        if (!$bundle) {
            abort(404);
        }

        $sales = Sale::where('type', 'bundle')
            ->where('bundle_id', $bundle->id)
            ->whereNull('refund_at')
            ->whereHas('buyer')
            ->with([
                'buyer' => function ($query) {
                    $query->select('id', 'full_name', 'email', 'mobile');
                }
            ])->get();

        if (!empty($sales) and !$sales->isEmpty()) {
            $export = new WebinarStudents($sales);
            return Excel::download($export, trans('panel.users') . '.xlsx');
            //     $ee = Excel::store($export, trans('panel.users') . '.xlsx');

            //  return response($ees);
        }

        return apiResponse2(0, 'failed', trans('api.bundles.exported'));
    }


    public function courses(Request $request)
    {
        $user = apiAuth();

        $bundle = Bundle::findOrFail($request->id);

        if (!$user->isTeacher() and !$user->isOrganization() and !$bundle->isPartnerTeacher($user->id)) {
            abort(404);
        }

        $bundle = Bundle::where('id', $request->id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id)
                    ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                        $q->where('teacher_id', $user->id);
                    });
            })
            ->with([
                'bundleWebinars' => function ($query) use ($user) {
                    $query->whereHas('webinar', function ($query) use ($user) {
                        $query->where('teacher_id', $user->id)
                            ->orWhere('creator_id', $user->id)
                            ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                                $q->Where('teacher_id', $user->id);
                            });
                    })
                        ->with([
                            'webinar'
                        ]);
                    $query->orderBy('order', 'asc');
                }
            ])
            ->first();

        if (!empty($bundle)) {

            $data = [
                'pageTitle' => trans('product.courses'),
                'bundle' => $bundle
            ];

            return apiResponse2(
                1,
                'retrieved',
                trans('api.public.retrieved'),
                [
                    'data' => $data,

                ]
            );
        }

        abort(404);
    }

    public function course_learning_page(Request $request, Bundle $bundle = null, $id)
      {
          return response()->json([
            'test'=>$bundle
          ]);
          $requestData = $request->all();
          $webinarController = new WebinarController();
   
          // Find the webinar by its ID
          $webinar = Webinar::findOrFail($id);
   
          // Get course data using the WebinarController's course method
          $data = $webinarController->course2($id, true);
          $course = $data['course'];
          $user = $data['user'];
   
          // Set default itemId and itemName
          $itemId = $course->id;
          $itemName = 'webinar_id';
   
          // If the course is not unattached and there's a bundle, switch itemId to bundle's ID
          if (empty($course->unattached) && !empty($bundle)) {
              $itemId = $bundle->id;
              $itemName = "bundle_id";
          }
   
          // Check installment content limitation
          $installmentLimitation = $webinarController->installmentContentLimitation($user, $itemId, $itemName);
          if ($installmentLimitation != "ok") {
              return response()->json([
                  'success' => false,
                  'message' => $installmentLimitation
              ], 403);
          }
   
          // Check if the user has bought the course or if they are eligible
          if (!$data || (!$data['hasBought'] && empty($course->getInstallmentOrder()))) {
              return response()->json([
                  'success' => false,
                  'message' => 'You are not authorized to access this course.'
              ], 403);
          }
   
          // Check if type is 'assignment' and add assignment data to response if necessary
          if (!empty($requestData['type']) && $requestData['type'] == 'assignment' && !empty($requestData['item'])) {
              $assignmentData = $this->getAssignmentData($course, $requestData);
              $data = array_merge($data, $assignmentData);
          }
   
          // If the user is not the creator, teacher, admin, or partner, check for unread noticeboards
          if ($course->creator_id != $user->id && $course->teacher_id != $user->id && !$user->isAdmin() && !$course->isPartnerTeacher($user->id)) {
              $unReadCourseNoticeboards = CourseNoticeboard::where('webinar_id', $course->id)
                  ->whereDoesntHave('noticeboardStatus', function ($query) use ($user) {
                      $query->where('user_id', $user->id);
                  })
                  ->count();
   
              if ($unReadCourseNoticeboards) {
                  $url = $course->getNoticeboardsPageUrl();
                  return response()->json([
                      'success' => false,
                      'redirect_url' => $url,
                      'message' => 'You have unread noticeboards. Redirecting to the noticeboard page.'
                  ], 403);
              }
          }
   
          // Add course certificate data if available
          if ($course->certificate) {
              $data["courseCertificate"] = Certificate::where('type', 'course')
                  ->where('student_id', $user->id)
                  ->where('webinar_id', $course->id)
                  ->first();
          }
   
          // Return data as JSON
          return response()->json([
              'success' => true,
              'message' => 'Course learning data retrieved successfully.',
              'data' => $data
          ], 200);
      }
}
