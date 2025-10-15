<?php

namespace App\Http\Controllers\Api\Admin;

use App\BundleStudent;
use App\Http\Controllers\Controller;
use App\Models\Api\Bundle;
use App\Models\Api\Organization;
use App\Models\Api\Webinar;
use App\User;
use Illuminate\Http\Request;

class EmployeeProgressController extends Controller
{
    public function getBundlesAverageProgress()
    {
        $bundles = Bundle::with('bundleWebinars.webinar', 'bundleSales.buyer')
            ->paginate(3);

        $bundles->getCollection()->transform(function ($bundle) {
            $totalProgress = 0;
            $studentCount = 0;

            foreach ($bundle->bundleSales as $sale) {
                $student = $sale->buyer;
                if (!$student) continue;

                $bundleProgress = 0;
                $totalCourses = $bundle->bundleWebinars->count();

                foreach ($bundle->bundleWebinars as $bundleWebinar) {
                    $course = $bundleWebinar->webinar;
                    $percent = $course->getProgressApi(true, $student->id);
                    $bundleProgress += $percent;
                }

                if ($totalCourses > 0) {
                    $studentBundlePercent = round($bundleProgress / $totalCourses, 2);
                    $totalProgress += $studentBundlePercent;
                    $studentCount++;
                }
            }

            $averagePercent = $studentCount > 0 ? round($totalProgress / $studentCount, 2) : 0;

            return [
                'bundle_id'       => $bundle->id,
                'bundle_title'    => $bundle->title,
                'student_count'   => $studentCount,
                'average_percent' => $averagePercent
            ];
        });

        return response()->json($bundles);
    }

    public function getAllUsersBundlesProgress(Request $request, $url_name)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $searchName  = $request->input('name');
        $searchEmail = $request->input('email');
        $searchTitle = $request->input('bundle_title');

        $query = Bundle::with(['bundleWebinars.webinar', 'bundleSales.buyer']);

        if (!empty($searchTitle)) {
            $query->whereTranslationLike('title', "%{$searchTitle}%");
        }

        $bundles = $query->get();

        $data = collect();

        foreach ($bundles as $bundle) {
            foreach ($bundle->bundleSales as $sale) {
                $student = $sale->buyer;
                if (!$student) {
                    continue;
                }

                if (!empty($searchName) && stripos($student->full_name, $searchName) === false) {
                    continue;
                }
                if (!empty($searchEmail) && stripos($student->email, $searchEmail) === false) {
                    continue;
                }

                $bundleProgress = 0;
                $totalCourses = $bundle->bundleWebinars->count();

                foreach ($bundle->bundleWebinars as $bundleWebinar) {
                    $course = $bundleWebinar->webinar;
                    $percent = $course->getProgressApi(true, $student->id);
                    $bundleProgress += $percent;
                }

                $bundlePercent = $totalCourses > 0
                    ? round($bundleProgress / $totalCourses, 2)
                    : 0;

                $data->push([
                    'student_name'   => $student->full_name,
                    'student_email'  => $student->email,
                    'bundle_title'   => $bundle->title,
                    'date_joined'    => $sale->created_at
                        ? (is_numeric($sale->created_at)
                            ? date('Y-m-d H:i:s', $sale->created_at)
                            : $sale->created_at->format('Y-m-d H:i:s'))
                        : null,
                    'bundle_percent' => $bundlePercent,
                ]);
            }
        }

        $perPage = 10;
        $page = $request->input('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $data->forPage($page, $perPage)->values(),
            $data->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json($paginated);
    }

    // $dateOfJoining = $bundle->directRegister
    public function getBundleLearningProgress($url_name, $bundleId)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $bundle = Bundle::with('bundleWebinars.webinar')->findOrFail($bundleId);

        $students = User::whereHas('bundleSales', function ($q) use ($bundleId) {
            $q->where('bundle_id', $bundleId);
        })->get();

        $result = [];

        foreach ($students as $student) {
            $bundleProgress = 0;
            $totalCourses = $bundle->bundleWebinars->count();

            $coursesData = [];

            foreach ($bundle->bundleWebinars as $bundleWebinar) {
                $course = $bundleWebinar->webinar;

                $percent = $course->getProgressApi(true, $student->id);

                $coursesData[] = [
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'percent' => $percent,
                ];

                $bundleProgress += $percent;
            }

            $bundlePercent = $totalCourses > 0 ? round($bundleProgress / $totalCourses, 2) : 0;

            $result[] = [
                'student_id' => $student->id,
                'student_name' => $student->full_name,
                'bundle_id' => $bundle->id,
                'bundle_title' => $bundle->title,
                'bundle_percent' => $bundlePercent,
                'courses' => $coursesData,
            ];
        }

        return response()->json($result);
    }

    // public function getAllUsersBundlesProgress()
    // {
    //     $bundles = \App\Models\Bundle::with([
    //         'bundleWebinars.webinar.assignments.instructorAssignmentHistories',
    //         'bundleWebinars.webinar.quizzes.quizResults',
    //         'bundleSales.buyer'
    //     ])->get();

    //     $data = [];

    //     foreach ($bundles as $bundle) {
    //         foreach ($bundle->bundleSales as $sale) {
    //             $student = $sale->buyer;

    //             if (!$student) {
    //                 continue;
    //             }

    //             $totalItems = 0;
    //             $completedItems = 0;

    //             foreach ($bundle->bundleWebinars as $bundleWebinar) {
    //                 $course = $bundleWebinar->webinar;

    //                 // 1️⃣ Courses
    //                 $coursePercent = $course->getProgressApi(true, $student->id);
    //                 $totalItems++;
    //                 if ($coursePercent >= 100) {
    //                     $completedItems++;
    //                 }

    //                 // 2️⃣ Assignments
    //                 foreach ($course->assignments as $assignment) {
    //                     $totalItems++;
    //                     $completed = $assignment->instructorAssignmentHistories
    //                         ->where('student_id', $student->id)
    //                         ->where('status', 'passed')
    //                         ->count() > 0;
    //                     if ($completed) {
    //                         $completedItems++;
    //                     }
    //                 }

    //                 // 3️⃣ Quizzes
    //                 foreach ($course->quizzes as $quiz) {
    //                     $totalItems++;
    //                     $completed = $quiz->quizResults
    //                         ->where('user_id', $student->id) // change to student_id if needed
    //                         ->where('status', 'passed')
    //                         ->count() > 0;
    //                     if ($completed) {
    //                         $completedItems++;
    //                     }
    //                 }
    //             }

    //             $overallPercent = $totalItems > 0
    //                 ? round(($completedItems / $totalItems) * 100, 2)
    //                 : 0;

    //             $data[] = [
    //                 'student_id' => $student->id,
    //                 'student_name' => $student->full_name,
    //                 'student_email' => $student->email,
    //                 'bundle_id' => $bundle->id,
    //                 'bundle_title' => $bundle->title,
    //                 'overall_percent' => $overallPercent
    //             ];
    //         }
    //     }

    //     // ✅ Paginate manually since we built the array
    //     $page = request()->get('page', 1);
    //     $perPage = 10;
    //     $offset = ($page - 1) * $perPage;

    //     $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
    //         array_slice($data, $offset, $perPage),
    //         count($data),
    //         $perPage,
    //         $page,
    //         ['path' => request()->url(), 'query' => request()->query()]
    //     );

    //     return response()->json($paginated);
    // }

    public function index()
    {
        $user = apiAuth();
        $bundles = Bundle::with('bundleStudents.student.user')
            ->get()
            ->map(function ($bundle) {
                return [
                    'id' => $bundle->id,
                    'bundle_name_certificate' => $bundle->bundle_name_certificate,
                    'status' => $bundle->status,
                    'students' => collect($bundle->bundleStudents)->map(function ($bs) {
                        return [
                            'student_id' => $bs->student_id,
                            'user_name' => optional($bs->student->user)->full_name,
                            'email' => optional($bs->student->user)->email,
                            'assigned_at' => optional($bs->created_at)->format('d/m/Y'),
                        ];
                    }),
                ];
            });

        $webinars = Webinar::where('teacher_id', $user->id)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'course_name_certificate' => $course->course_name_certificate,
                    'status' => $course->status,
                ];
            });

        return response()->json([
            'bundles' => $bundles,
            'webinars' => $webinars
        ]);
    }

    public function destroy($url_name, $bundle_id, $student_id)
    {
        $organization = Organization::where('url_name', $url_name)->first();

        if (!$organization) {
            return response()->json(['message' => 'This organization was not found.'], 404);
        }
        $user = apiAuth();
        if (BundleStudent::where('bundle_id', $bundle_id)->where('student_id', $student_id)->delete()) {
            return response()->json([
                'message' => 'Student removed successfully from the bundle.',
            ]);
        }
        return response()->json([
            'message' => 'Student not found .',
        ]);
    }

    public function store(Request $request)
    {
        $bundlestudent = BundleStudent::where('bundle_id', $request->bundle_id)
            ->where('student_id', $request->student_id)
            ->first();
        if (!$bundlestudent) {
            $bundlestudent = BundleStudent::create([
                'bundle_id' => $request->bundle_id,
                'student_id' => $request->student_id,
            ]);
            if ($bundlestudent) {
                return response()->json([
                    'message' => 'Student added successfully to the bundle.',
                ]);
            } else {
                return response()->json([
                    'message' => 'Student Already exists in the bundle.',
                ]);
            }
        }

        return response()->json([
            'message' => 'Something went wrong, please try again.',
        ]);
    }
}
