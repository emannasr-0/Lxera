<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\CertificateResource;
use App\Mixins\Certificate\MakeCertificate;
use App\Models\Api\Bundle;
use App\Models\Api\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Api\Quiz;
use App\Models\Api\QuizzesResult;
use App\Models\Api\WebinarAssignment;
use App\Models\InstallmentOrder;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\StudentExceptionCertificate;
use App\Models\Webinar;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class CertificatesController extends Controller
{
    public function created(Request $request)
    {
        $user = apiauth('api');

        $quizzes = Quiz::where('creator_id', $user->id)
            ->where('status', Quiz::ACTIVE)->handleFilters()->get();

        return apiResponse2(1, 'retrieved', trans('public.retrieved'), [
            'certificates' => CertificateResource::collection($quizzes),
        ]);


    }

    public function students()
    {
        $user = apiauth('api');

        $quizzes = Quiz::where('creator_id', $user->id)
            ->pluck('id')->toArray();


        $ee = Certificate::whereIn('quiz_id', $quizzes)
            ->get()
            ->map(function ($certificate) {

                return $certificate->details;

            });

        return apiResponse2(1, 'retrieved', trans('public.retrieved'), $ee);
    }

    // public function achievements(Request $request)
    // {
    //     $user = apiauth('api');
    //     $results = QuizzesResult::where('user_id', $user->id)->where('status', QuizzesResult::$passed)
    //         ->whereHas('quiz', function ($query) {
    //             $query->where('status', Quiz::ACTIVE);
    //         })
    //         ->get()->map(function ($result) {

    //             return array_merge($result->details,
    //                 ['certificate' => $result->certificate->brief ?? null]
    //             );

    //         });


    //     return sendResponse( $results, trans('public.retrieved'));

    // }

    public function achievements(Request $request)
    {
        $user = auth('api')->user();

        $results = QuizzesResult::where('user_id', $user->id);

        $failedQuizzes = deepClone($results)->where('status', QuizzesResult::$failed)->count();
        $avgGrades = deepClone($results)->where('status', QuizzesResult::$passed)->avg('user_grade');

        if (!empty($request->get('grade'))) {
            $results->where('user_grade', $request->get('grade'));
        }

        $quizzesIds = $results->where('status', QuizzesResult::$passed)
            ->pluck('quiz_id')
            ->toArray();
        $quizzesIds = array_unique($quizzesIds);

        $query = Quiz::whereIn('id', $quizzesIds)
            ->where('status', Quiz::ACTIVE);

        $certificatesCount = deepClone($query)->count();

        $userAllQuizzes = deepClone($query)->get();

        $query = $this->quizFilters(deepClone($query), $request);

        $quizzes = $query->with([
            'webinar',
            'quizResults' => function ($query) {
                $query->orderBy('id', 'desc');
            },
        ])->get();


        $canDownloadCertificate = false;
        foreach ($quizzes as $quiz) {
            $userQuizDone = $quiz->quizResults;

            if (count($userQuizDone)) {
                $quiz->result = $userQuizDone->first();

                if ($quiz->result->status == 'passed') {
                    $canDownloadCertificate = true;
                }
            }

            $quiz->can_download_certificate = $canDownloadCertificate;
        }

        $student = $user->student;
        $webinarsIds =  $user->getAllPurchasedWebinarsIds();
        $userWebinars = Webinar::select('id')
            ->whereIn('id', $webinarsIds)
            ->get();

        foreach ($userWebinars as $webinar) {
            $group = $webinar->groups()->whereHas('enrollments', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();
            $template = $webinar->certificate_template()->where('status', 'publish')
            ->where('type', 'course')->latest()->first();

            $isStudentExceptionWebinar = StudentExceptionCertificate::where('student_id', $student->id) // use the student's ID
                ->where('webinar_id', $webinar->id)
                ->exists();


            if ($group && !empty($group->end_date) && $group->end_date < now() && !empty($template) && !$isStudentExceptionWebinar) {
                $this->makeCourseCertificate($webinar->id);
            }
        }



        $bundlesIds = $user->purchasedBundles->pluck('bundle_id');
        $userbundles = Bundle::whereIn('id', $bundlesIds)->with('bundleWebinars.webinar')->get();

        foreach ($userbundles as $bundle) {
            // Check if the student has any overdue installments for the current bundle
            $order = InstallmentOrder::where('bundle_id', $bundle->id)
                ->where('user_id', $user->id)
                ->where('status', '!=', 'paying')
                ->first();
            //
            $hasOverdue = $order ? $order->checkOrderHasOverdue() : false;
            // dd( $hasOverdue);
            $template = $bundle->certificate_template()->where('status', 'publish')
            ->where('type', 'bundle')->latest()->first();

            $bundlesHasGraduationProject = $bundle->bundleWebinars()->whereHas('webinar', function ($query) {
                $query->whereHas('assignments')->where('type', 'graduation_project');
            })->get();

            $isStudentException = StudentExceptionCertificate::where('student_id', $student->id) // use the student's ID
                ->where('bundle_id', $bundle->id)
                ->exists();


            if ($bundle && !empty($bundle->end_date) && $bundle->end_date < time() && !empty($template) && count($bundlesHasGraduationProject) > 0 && !$hasOverdue && !$isStudentException) {
                $this->makeBundleCertificate($bundle->id);
            }
        }

        $certificates = Certificate::where('student_id', $user->id)
            ->with(['webinar', 'bundle']); // Eager load webinars and bundles

        $bundleCertificates = $certificates->clone()->whereNotNull('bundle_id')->whereHas('bundle', function ($query) use ($student) {
            $query->whereDoesntHave('studentsExcluded', function ($q) use ($student) {
                $q->where('student_id', $student->id); // Assuming the `student_id` is the correct column
            });
        })->get();

        $courseCertificates = $certificates->clone()->whereNotNull('webinar_id')->whereHas('webinar', function ($query) use ($student) {
            $query->whereDoesntHave('studentsExcluded', function ($q) use ($student) {
                $q->where('student_id', $student->id); // Assuming the `student_id` is the correct column
            });
        })->get();


        $data = [
            // 'certificatesCount' => $certificatesCount,
            // 'courseCertificates' => $courseCertificates,
            'bundleCertificates' => $bundleCertificates->map(function ($certificate){
                return [
                    'id' => $certificate->id,
                    'code' => $certificate->certificate_code,
                    'created_at' => dateTimeFormat($certificate->created_at, 'j M Y - H:i'),
                    'program' => [
                        'id' => $certificate->bundle? $certificate->bundle->id : null,
                        'title' => $certificate->bundle? $certificate->bundle->title : null,
                    ]
                    ];
            }),
        ];

        return sendResponse( $data,trans('quiz.my_achievements_lists'));
    }
    public function makeCertificate($quizResultId)
    {
        $user = apiauth('api');

        $makeCertificate = new MakeCertificate();

        $quizResult = QuizzesResult::where('id', $quizResultId)
            //->where('user_id', $user->id)
            ->where('status', QuizzesResult::$passed)
            ->first();

        if (!empty($quizResult)) {
            return $makeCertificate->makeQuizCertificate($quizResult);
        }

        abort(404);
    }

    private function quizFilters($query, $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $webinar_id = $request->get('webinar_id');
        $quiz_id = $request->get('quiz_id');
        $grade = $request->get('grade');


        fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($webinar_id)) {
            $query->where('webinar_id', $webinar_id);
        }

        if (!empty($quiz_id)) {
            $query->where('id', $quiz_id);
        }

        return $query;
    }


    public function makeBundleCertificate($bundleId, $format = 'png')
    {
        $user = auth('api')->user();
        $bundleStudent = $user->student->bundleStudent->where('bundle_id', $bundleId)->first();
        //dd($bundleStudent);
        $gpa = $bundleStudent->gpa;


        $makeCertificate = new MakeCertificate();
        $bundle = Bundle::where('id', $bundleId)->first();

        if (!empty($bundle)) {
            // Check if all assignments are passed
            $allAssignmentsPassed = $this->checkAssignmentsStatus($bundle, $bundleStudent);

            // Create the certificate with the appropriate image/template
            return $makeCertificate->makeBundleCertificate($bundle, $format, $bundleStudent->gpa, $allAssignmentsPassed);
        }

        abort(404);
    }


    public function checkAssignmentsStatus(Bundle $bundle, $bundleStudent)
    {
        $graduationProjectWebinars = $bundle->bundleWebinars()
            ->whereHas('webinar', function ($query) {
                $query->where('type', 'graduation_project');
            })
            ->with('webinar')
            ->get()
            ->pluck('webinar.id');

        $assignments = WebinarAssignment::whereIn('webinar_id', $graduationProjectWebinars)
            ->with('assignmentHistory')
            ->get();

        $assignmentsHistories = WebinarAssignment::whereIn('webinar_id', $graduationProjectWebinars)->whereHas('assignmentHistory')
        ->with('assignmentHistory')
        ->get();

        // foreach ($assignments as $assignment) {
        //     if ( $assignment->assignmentHistory && $assignment->assignmentHistory->grade > 60) {
        //         return true; // If any assignment is not passed
        //     }
        // }
        // dd(($assignmentsHistories));
        if (count($assignments) > 0 && count($assignmentsHistories) == 0) {

            $bundleStudent->update(['gpa' => 0]);
        }

        if (!isset($bundleStudent->gpa)) {
            return true;
        }

        if (!empty($bundleStudent->gpa) && $bundleStudent->gpa >= 2) {
            return true;
        }
        return false; // All assignments are passed
    }
}

