<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Api\Quiz;
use App\Models\Api\QuizzesResult;
use App\Models\Api\Webinar;
use App\Models\Bundle;
use App\Models\InstallmentOrder;
use App\Models\StudentExceptionCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CertificatesController extends Controller
{
    public function index(Request $request)
    {
        //dd($request);
        $user = auth('api')->user();

        if (!$user->isUser()) {
            $query = Quiz::where('creator_id', $user->id)
                ->where('status', Quiz::ACTIVE);

            $activeQuizzes = $query->count();

            $quizzesIds = $query->pluck('id')->toArray();

            $achievementsCount = Certificate::whereIn('quiz_id', $quizzesIds)->count();

            $quizResultQuery = QuizzesResult::whereIn('quiz_id', $quizzesIds);
            $failedResults = deepClone($quizResultQuery)->where('status', QuizzesResult::$failed)->count();
            $avgGrade = deepClone($quizResultQuery)->where('status', QuizzesResult::$passed)->avg('user_grade');

            $userAllQuizzes = deepClone($query)->get();

            $query = $this->quizFilters(deepClone($query), $request);

            $quizzes = $query->with([
                'webinar',
                'certificates',
                'quizResults' => function ($query) {
                    $query->orderBy('id', 'desc');
                },
            ])->paginate(10);

            foreach ($quizzes as $quiz) {
                $quizResults = $quiz->quizResults;

                $quiz->avg_grade = $quizResults->where('status', QuizzesResult::$passed)->avg('user_grade');
            }

            $userWebinars = Webinar::select('id')
                ->where(function ($query) use ($user) {
                    $query->where('creator_id', $user->id)
                        ->orWhere('teacher_id', $user->id);
                })
                ->where('status', 'active')
                ->get();

            $data = [
                'pageTitle' => trans('quiz.certificates_lists'),
                'quizzes' => $quizzes,
                'activeQuizzes' => $activeQuizzes,
                'achievementsCount' => $achievementsCount,
                'avgGrade' => round($avgGrade, 2),
                'failedResults' => $failedResults,
                // 'userWebinars' => $userWebinars,
                'userAllQuizzes' => $userAllQuizzes,
            ];

            return response()->json([
                'data'=>$data
            ]);
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

    public function checkValidate(Request $request)
    {
        
        $data = $request->all();

        $validator = Validator::make($data, [
            'certificate_code' => 'required',
            // 'captcha' => 'required|captcha',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $certificateCode = $data['certificate_code'];

        $certificate = Certificate::where('certificate_code', $certificateCode)->first();

        if (!empty($certificate)) {
            $webinarTitle = "-";

            if ($certificate->type == 'quiz' and !empty($certificate->quiz) and !empty($certificate->quiz->webinar)) {
                $webinarTitle = $certificate->quiz->webinar->title;
            } else if ($certificate->type == "course" and !empty($certificate->webinar)) {
                $webinarTitle = $certificate->webinar->title;
            }

            $result = [
                'student' => $certificate->student->full_name,
                'webinar_title' => $webinarTitle,
                'date' => dateTimeFormat($certificate->created_at, 'j F Y'),
            ];

            return response()->json([
                'code' => 200,
                'certificate' => $result
            ]);
        }

        return response()->json([
            'code' => 404,
        ]);
    }

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
        ])->paginate(10);


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



        // $bundlesIds = $user->purchasedBundles->pluck('bundle_id');
        // $userbundles = Bundle::whereIn('id', $bundlesIds)->with('bundleWebinars.webinar')->get();
        // foreach ($userbundles as $bundle) {
        //     //dd($bundle);
        //     $template = $bundle->certificate_template()->where('status', 'publish')
        //         ->where('type', 'bundle')->latest()->first();

        //         $test = $bundle->bundleWebinars()->whereHas('webinar', function($query){
        //             $query->whereHas('assignments')->where('type', 'graduation_project');
        //         })->get();

        //     if ($bundle && !empty($bundle->end_date) && $bundle->end_date < time() && !empty($template) && count($test)>0) {
        //         $this->makeBundleCertificate($bundle->id);
        //     }
        // }


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
            'pageTitle' => trans('quiz.my_achievements_lists'),
            'quizzes' => $quizzes,
            'failedQuizzes' => $failedQuizzes,
            'avgGrades' => round($avgGrades, 2),
            'certificatesCount' => $certificatesCount,
            'userWebinars' => $userWebinars,
            'userAllQuizzes' => $userAllQuizzes,
            'courseCertificates' => $courseCertificates,
            'bundleCertificates' => $bundleCertificates,
        ];
        
        return response()->json([
            'data'=>$data
        ]);
    }

}
