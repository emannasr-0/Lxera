<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebinarAssignmentHistoryResource;
use App\Http\Resources\WebinarAssignmentResource;
use App\Models\Api\Organization;
use App\Models\Sale;
use App\Models\Webinar;
use App\Models\Api\WebinarAssignment;
use App\Models\Api\WebinarAssignmentHistory;
use App\Models\WebinarAssignmentHistoryMessage;
use App\Models\WebinarChapter;
use App\User;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        if (!getFeaturesSettings('webinar_assignment_status')) {
            abort(403);
        }

        $user = apiAuth();

        $purchasedCoursesIds = Sale::where('buyer_id', $user->id)
            ->whereNotNull('webinar_id')
            ->whereNull('refund_at')
            ->pluck('webinar_id')
            ->toArray();


        $query = WebinarAssignment::whereIn('webinar_id', $purchasedCoursesIds)
            ->where('status', 'active')
            ->with(['assignmentHistory' => function ($d) use ($user) {
                $d->where('student_id', $user->id);
            }]);


        $assignments = $query->handleFilters()->orderBy('created_at', 'desc')
            ->get()->map(function ($assignment) use ($user) {
                //  dd($assignment->assignmentHistory->where('student_id', $user->id)->get()) ;
                return $assignment->assignmentHistory;
            });
        //dd($assignments);

        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
            [

                'assignments' => WebinarAssignmentHistoryResource::collection($assignments),

            ]);

    }

    public function show($id)
    {
        $user = apiAuth();
        $assignmnet = WebinarAssignment::where('id', $id)
            /*  ->where(function ($q) use ($user) {
                  $q->whereHas('assignmentHistory', function ($q) use ($user) {
                      $q->where('student_id', $user->id);
                  });
              })*/
            ->with(['assignmentHistory' => function ($d) use ($user) {
                $d->where('student_id', $user->id);
            }])
            ->where('status', WebinarChapter::$chapterActive)->first();
        abort_unless($assignmnet, 404);

        $purchasedCoursesIds = Sale::where('buyer_id', $user->id)
            ->whereNotNull('webinar_id')
            ->whereNull('refund_at')
            ->pluck('webinar_id')
            ->toArray();
        if (!in_array($assignmnet->webinar->id,$purchasedCoursesIds)){
            abort(404);
    }

        if ($error = $assignmnet->canViewError()) {
            //       return $this->failure($error, 403, 403);
        }
        $resource = new WebinarAssignmentHistoryResource($assignmnet->assignmentHistory);
        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $resource);
    }

    public function myCoursesAssignments(Request $request)
    {
        if (!getFeaturesSettings('webinar_assignment_status')) {
            abort(403);
        }

        $user = apiAuth();

        if (!$user->isOrganization() && !$user->isTeacher() && !$user->isAdmin()) {
            abort(404);
        }

        $query = WebinarAssignment::where('creator_id', $user->id)
        ->orWhereHas('webinar', function ($query) use ($user) {
            $query->where('teacher_id', $user->id)
                ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                    $q->where('teacher_id', $user->id);
                });
        });

        $courseAssignmentsCount = deepClone($query)->count();

        $pendingReviewCount = deepClone($query)->whereHas('instructorAssignmentHistories', function ($query) use ($user) {
            $query->where('instructor_id', $user->id);
            $query->where('status', WebinarAssignmentHistory::$pending);
        })->count();

        $passedCount = deepClone($query)->whereHas('instructorAssignmentHistories', function ($query) use ($user) {
            // $query->where('instructor_id', $user->id);
            $query->where('status', WebinarAssignmentHistory::$passed);
        })->count();

        $failedCount = deepClone($query)->whereHas('instructorAssignmentHistories', function ($query) use ($user) {
            // $query->where('instructor_id', $user->id);
            $query->where('status', WebinarAssignmentHistory::$notPassed);
        })->count();

        $assignments = $query->with([
            'webinar',
            // 'instructorAssignmentHistories' => function ($query) use ($user) {
            //     $query->where('instructor_id', $user->id);
            // },
        ])->orderBy('created_at', 'desc')
            ->paginate(10);

        foreach ($assignments as &$assignment) {
            $grades = $assignment->instructorAssignmentHistories->filter(function ($item) {
                return !is_null($item->grade);
            });

            $historyIds = $assignment->instructorAssignmentHistories->pluck('id')->toArray();

            $assignment->min_grade = count($grades) ? $grades->min('grade') : null;
            $assignment->average_grade = count($grades) ? $grades->avg('grade') : null;
            $assignment->submissions = WebinarAssignmentHistoryMessage::whereIn('assignment_history_id', $historyIds)
                ->where('sender_id', '!=', $user->id)
                ->count();

            $assignment->pendingCount = $assignment->instructorAssignmentHistories->where('status', WebinarAssignmentHistory::$pending)->count();
            $assignment->passedCount = $assignment->instructorAssignmentHistories->where('status', WebinarAssignmentHistory::$passed)->count();
            $assignment->failedCount = $assignment->instructorAssignmentHistories->where('status', WebinarAssignmentHistory::$notPassed)->count();
        }

        $data = [
            'pageTitle' => trans('update.my_courses_assignments'),
            'assignments' => $assignments,
            'courseAssignmentsCount' => $courseAssignmentsCount,
            'pendingReviewCount' => $pendingReviewCount,
            'passedCount' => $passedCount,
            'failedCount' => $failedCount,
        ];

        return response()->json([
            'data'=>$data
        ]);
    }
    private function handleAssignmentStudentsFilters(Request $request, $query)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $studentId = $request->get('student_id');
        $status = $request->get('status');

        // $from and $to
        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($studentId)) {
            $query->where('student_id', $studentId);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        return $query;
    }
    public function students(Request $request, $url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name);
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        if (!getFeaturesSettings('webinar_assignment_status')) {
            abort(403);
        }

        $user = apiAuth();

        if (!$user->isOrganization() and !$user->isTeacher()) {
            abort(404);
        }

        $assignment = WebinarAssignment::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhereHas('webinar', function ($query) use ($user) {
                        $query->where('teacher_id', $user->id)
                            ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                                $q->where('teacher_id', $user->id);
                            });
                    });
            })
            ->with([
                'webinar',
            ])
            ->first();
        if (!empty($assignment)) {
            $webinar = $assignment->webinar;

            $query = $assignment->instructorAssignmentHistories()
                // ->where('instructor_id', $user->id)
                ->where('student_id', '!=', $user->id)
                ->with([
                    'student'
                ]);

            // $courseAssignmentsCount = WebinarAssignment::where('creator_id', $user->id)
            //     ->orWhereHas('webinar', function ($query) use ($user) {
            //         $query->where('teacher_id', $user->id)
            //             ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
            //                 $q->where('teacher_id', $user->id);
            //             });
            //     })
            //     ->where('webinar_id', $webinar->id)
            //     ->count();

            $courseAssignmentsCount = deepClone($query)->count();

            $pendingReviewCount = deepClone($query)->where('status', WebinarAssignmentHistory::$pending)->count();
            $passedCount = deepClone($query)->where('status', WebinarAssignmentHistory::$passed)->count();
            $failedCount = deepClone($query)->where('status', WebinarAssignmentHistory::$notPassed)->count();

            $query = $this->handleAssignmentStudentsFilters($request, $query);

            $histories = $query->orderBy('created_at', 'desc')
                ->paginate(10);

            foreach ($histories as &$history) {
                $history->usedAttemptsCount = 0;

                $sale = Sale::where('buyer_id', $history->student_id)
                    ->where('webinar_id', $assignment->webinar_id)
                    ->whereNull('refund_at')
                    ->first();

                if (!empty($sale)) {
                    $history->purchase_date = $sale->created_at;
                }

                if (!empty($history) and count($history->messages)) {
                    try {
                        $history->last_submission = $history->messages->first()->created_at;
                        $history->first_submission = $history->messages->last()->created_at;
                        $history->usedAttemptsCount = $history->messages->count();
                    } catch (\Exception $exception) {
                    }
                }
            }

            $studentsIds = Sale::where('webinar_id', $webinar->id)
                ->whereNull('refund_at')
                ->pluck('buyer_id')
                ->toArray();

            $students = User::select('id', 'full_name')
                ->whereIn('id', $studentsIds)
                ->get();

            $data = [
                'pageTitle' => trans('update.students_assignments'),
                'assignment' => $assignment,
                'histories' => $histories,
                'students' => $students,
                'webinar' => $webinar,
                'courseAssignmentsCount' => $courseAssignmentsCount,
                'pendingReviewCount' => $pendingReviewCount,
                'passedCount' => $passedCount,
                'failedCount' => $failedCount,
            ];

            return response()->json([
                'data'=>$data
            ]);
        }

        abort(404);
    }


}
