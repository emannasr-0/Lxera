<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Mixins\Certificate\MakeCertificate;
use App\Models\Bundle;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\InstallmentOrder;
use App\Models\Quiz;
use App\Models\QuizzesResult;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Sale;
use App\Models\StudentExceptionCertificate;
use App\Models\Webinar;
use App\Models\WebinarAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class CertificateController extends Controller
{

    public function lists(Request $request)
    {
        //dd($request);
        $user = auth()->user();

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
                'userWebinars' => $userWebinars,
                'userAllQuizzes' => $userAllQuizzes,
            ];

            return view('web.default.panel.certificates.list', $data);
        }

        return view('errors.404');
    }


    public function certificateLists()
    {
        $salesWithCertificate = Sale::where('buyer_id', auth()->user()->id)
            ->whereNotNull('certificate_template_id')
            ->get();
        $certificateTemplatesArray = [];
        $titlesArray = [];

        foreach ($salesWithCertificate as $sale) {
            $certificateTemplate = $sale->certificate_template;

            if ($certificateTemplate) {
                $certificateTemplatesArray[] = $certificateTemplate;

                if ($certificateTemplate->translations->isNotEmpty()) {
                    $titlesArray[] = $certificateTemplate->translations[0]->title;
                } else {
                    $titlesArray[] = null;
                }
            }
        }
        $salesWithCertificate = $salesWithCertificate->toArray();
        // dd($salesWithCertificate[0]['created_at']);

        // dd($titlesArray);
        return view(getTemplate() . '.panel.certificates.certificate_list', compact('certificateTemplatesArray', 'salesWithCertificate'));
    }

    public function achievements(Request $request)
    {
        $user = auth()->user();

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

        return view(getTemplate() . '.panel.certificates.achievements', $data);
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

    public function makeCertificate($quizResultId)
    {
        $user = auth()->user();

        $makeCertificate = new MakeCertificate();

        $quizResult = QuizzesResult::where('id', $quizResultId)
            ->where('user_id', $user->id)
            ->where('status', QuizzesResult::$passed)
            ->with(['quiz' => function ($query) {
                $query->with(['webinar']);
            }])
            ->first();

        if (!empty($quizResult)) {
            return $makeCertificate->makeQuizCertificate($quizResult);
        }

        return view('errors.404');
    }


    public function makeCourseCertificate($WebinarId, $format = 'png')

    {

        // dd($WebinarId);
        $user = auth()->user();

        $makeCertificate = new MakeCertificate();
        $course = Webinar::where('id', $WebinarId)->first();
        //  dd($course);
        if (!empty($course)) {
            return $makeCertificate->makeCourseCertificate($course, $format);
        }




        return view('errors.404');
    }


    // public function makeBundleCertificate($bundleId,$format ='png')

    // {

    //   //dd($bundleId);
    //     $user = auth()->user();
    //     $student = $user->student->bundleStudent->first();
    //     $gpa=$student->gpa;
    //    // dd($gpa);

    //     $makeCertificate = new MakeCertificate();
    //     $bunble=Bundle::where('id', $bundleId)->first();
    //   //  dd($course);
    //     if (!empty($bunble)) {
    //         return $makeCertificate->makebundleCertificate($bunble,$format, $gpa);
    //     }



    //     return view('errors.404');
    // }


    public function makeBundleCertificate($bundleId, $format = 'png')
    {
        $user = auth()->user();
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

        return view('errors.404');
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
