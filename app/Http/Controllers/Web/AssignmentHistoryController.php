<?php

namespace App\Http\Controllers\Web;

use App\BundleStudent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\LearningPageAssignmentTrait;
use App\Models\Bundle;
use App\Models\BundleWebinar;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\WebinarAssignment;
use App\Models\WebinarAssignmentHistory;
use App\Models\WebinarAssignmentHistoryMessage;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class AssignmentHistoryController extends Controller
{
    use LearningPageAssignmentTrait;

    public function storeMessage(Request $request, $assignmentId, $historyId)
    {
        $user = auth()->user();

        $assignment = WebinarAssignment::where('id', $assignmentId)->first();

        if (!empty($assignment)) {
            $webinar = $assignment->webinar;

            if (!empty($webinar) and $webinar->checkUserHasBought($user)) {
                $studentId = $request->get('student_id');
                $assignmentHistory = $this->getAssignmentHistory($webinar, $assignment, $user, $studentId);

                if (!empty($assignmentHistory) and $historyId == $assignmentHistory->id) {

                    if ($user->id != $assignment->creator_id) {
                        $submissionTimes = $assignmentHistory->messages
                            ->where('sender_id', $user->id)
                            ->whereNotNull('file_path')
                            ->count();
                        $deadline = $this->getAssignmentDeadline($assignment, $user);

                        if (!$deadline or (!empty($assignment->attempts) and $submissionTimes >= $assignment->attempts)) {
                            $toastData = [
                                'title' => !$deadline ? trans('update.assignment_deadline_error_title') : trans('update.assignment_submission_error_title'),
                                'msg' => !$deadline ? trans('update.assignment_deadline_error_desc') : trans('update.assignment_submission_error_desc'),
                            ];

                            return response([
                                'code' => 401,
                                'errors' => $toastData,
                            ]);
                        }
                    }

                    $data = $request->all();

                    $rules = [                  
                        'file_title' => 'required|max:255',
                       // 'file_path' => 'required|mimes:psd,rar,png,jpg,jpeg,doc,docx,pdf,ai,indd',
                    ];


                    $validator = Validator::make($data, $rules);

                    $path = public_path($data['file_path']);
                    if ($request->hasFile('file_path')) {
                        $file = $request->file('file_path');
                        
                        // Debug the file's extension and mime type
                        \Log::info('File Extension: ' . $file->getClientOriginalExtension());
                        \Log::info('File Mime Type: ' . $file->getMimeType());
                        
                        // Continue with file validation and upload
                        $rules = [
                            'file_path' => 'required|mimes:psd,rar,png,jpg,jpeg,doc,docx,pdf,ai,indd',
                        ];
                        $validator = Validator::make($data, $rules);
                    
                        if ($validator->fails()) {
                            return response([
                                'code' => 422,
                                'errors' => $validator->errors(),
                            ], 422);
                        }
                    }
                    
                   
                    if ($validator->fails()) {

                     
                        return response([
                            'code' => 422,
                            'errors' => $validator->errors(),
                        ], 422);
                    }

                    if (!File::exists($path)) {
                        return response([
                            'code' => 422,
                            'errors' => [
                                'file_path' => ['ملف غير صحيح']
                            ],
                        ], 422);
                    }
                 
            WebinarAssignmentHistoryMessage::create([
                        'assignment_history_id' => $assignmentHistory->id,
                        'sender_id' => $user->id,
                        'message' => !empty($data['description']) ? $data['description'] : null,
                        'file_title' => $data['file_title'] ?? null,
                        'file_path' => $data['file_path'] ?? null,
                        'created_at' => time(),
                    ]);

                    if ($assignmentHistory->status == WebinarAssignmentHistory::$notSubmitted) {
                        $assignmentHistory->update([
                            'status' => WebinarAssignmentHistory::$pending
                        ]);
                    }

                    $notifyOptions = [
                        '[instructor.name]' => $assignmentHistory->instructor->full_name,
                        '[c.title]' => $webinar->title,
                        '[student.name]' => $assignmentHistory->student->full_name,
                        //'[assignment_grade]' => $assignmentHistory->grade,
                    ];

                    if ($user->id == $assignment->creator_id) {
                        sendNotification('instructor_send_message', $notifyOptions, $assignmentHistory->student_id);
                    } else {
                        sendNotification('student_send_message', $notifyOptions, $assignmentHistory->instructor_id);
                    }

                    return response()->json([
                        'code' => 200,
                    ]);
                }
            }
        }

        abort(403);
    }

    public function setGrade(Request $request, $assignmentId, $historyId)
    {
        $user = auth()->user();

        $assignment = WebinarAssignment::where('id', $assignmentId)
            ->where('creator_id', $user->id)
            ->first();

        if (!empty($assignment)) {
            $webinar = $assignment->webinar;
            //dd($webinar->type);
            if (!empty($webinar) and $webinar->isOwner($user->id)) {
                //
                $studentId = $request->get('student_id');
                $assignmentHistory = $this->getAssignmentHistory($webinar, $assignment, $user, $studentId);

                if (!empty($assignmentHistory) and $historyId == $assignmentHistory->id and $assignmentHistory->instructor_id == $user->id) {
                    $data = $request->all();

                    $rules = [
                        'grade' => 'required|integer',
                    ];

                    $validator = Validator::make($data, $rules);

                    if ($validator->fails()) {
                        return response([
                            'code' => 422,
                            'errors' => $validator->errors(),
                        ], 422);
                    }

                    $grade = $data['grade'];

                    $status = WebinarAssignmentHistory::$passed;

                    if ($grade < $assignment->pass_grade) {
                        $status = WebinarAssignmentHistory::$notPassed;
                    }

                    $assignmentHistory->update([
                        'status' => $status,
                        'grade' => $grade
                    ]);

                    if ($status == WebinarAssignmentHistory::$passed) {
                        $buyStoreReward = RewardAccounting::calculateScore(Reward::PASS_ASSIGNMENT);
                        RewardAccounting::makeRewardAccounting($assignmentHistory->student_id, $buyStoreReward, Reward::PASS_ASSIGNMENT, $assignment->id);
                    }

                    $notifyOptions = [
                        '[instructor.name]' => $assignmentHistory->instructor->full_name,
                        '[c.title]' => $webinar->title,
                        '[student.name]' => $assignmentHistory->student->full_name,
                        '[assignment_grade]' => $assignmentHistory->grade,
                    ];

                    sendNotification('instructor_set_grade', $notifyOptions, $assignmentHistory->student_id);

                    if ($webinar->type == 'graduation_project') {
                        $response = $this->getGraduationProjectAssignments($request, $studentId, $webinar); // Pass the webinar object
                       // return response()->json($response);
                    }


                //    Log::info(

                //     "'webinar_type' => $webinar->type"
                // );

                    return response()->json([
                        'code' => 200,
                        'type'=>$webinar->type,

                    ]);
                }
            }
        }

        abort(403);
    }


    public function deleteGraduationProject($historyId){
   
        $project=WebinarAssignmentHistory::where('id', $historyId)
        ->first();
       // dd($project);

        if ($project) {
            $project->delete();
            
            $toastData = [
                'title' => "حذف واجب",
                'msg' => "تم حذف الواجب بنجاح",
                'status' => 'success'
            ];
            return back()->with(['toast' => $toastData]);
       }

      return view('errors.404');


    }



    // public function getGraduationProjectAssignments(Request $request , $userId, $webinar)
    // {
    //   try {  // Get the authenticated user

    //      // Get the associated student

    //      $user=User::find( $userId);
    //      $student = $user->student;

    //     if (!$student) {
    //         return response()->json(['error' => 'Student not found'], 404);
    //     }

    //     // $bundlesIds = $user->purchasedBundles->pluck('bundle_id');
    //     // $userbundles = Bundle::whereIn('id', $bundlesIds)->get();
    //     $bundleWebinars = BundleWebinar::where('webinar_id', $webinar->id)->get();
    //     foreach ($bundleWebinars as $bundleWebinar) {
    //         $bundleId = $bundleWebinar->bundle_id;

    //         $graduationProjectWebinars = Bundle::find($bundleId)
    //         ->bundleWebinars()
    //         ->whereHas('webinar', function ($query) use($webinar){
    //             $query->where('type', 'graduation_project')->where('webinar_id', $webinar->id);
    //         })
    //         ->with('webinar')
    //         ->get()
    //         ->pluck('webinar.id');

    //         $assignments = WebinarAssignment::whereIn('webinar_id', $graduationProjectWebinars)
    //             ->with('assignmentHistory')
    //             ->get();

    //         $totalGrade = 0;
    //         $totalCount = 0;

    //         foreach ($assignments as $assignment) {
    //             if ($assignment->assignmentHistory) {
    //                 $totalGrade += $assignment->assignmentHistory->grade;
    //                 $totalCount++;
    //             }
    //         }

    //         $averageGrade = $totalCount > 0 ? $totalGrade / $totalCount : 0;


    //          // Convert the average grade to GPA
    //          $gpa = $this->convertGradeToGPA($averageGrade);

    //         // Save the average grade as 'gpa' in the BundleStudent model
    //         $bundleStudent = BundleStudent::where('bundle_id', $bundleId)
    //                                       ->where('student_id', $student->id) // Use the student ID here
    //                                       ->first();
    //        // dd($bundleStudent);
    //         if ($bundleStudent) {
    //             $bundleStudent->gpa = $gpa; // Save the average grade as 'gpa'
    //             $bundleStudent->save();
    //         } else {
    //             // Handle the case where BundleStudent is not found
    //             // For example, create a new BundleStudent record if necessary
    //         }

    //         // Output the assignments and the average grade for debugging purposes
    //         return ([
    //             'bundle_id' =>$bundleId,
    //             'student_id' => $student->id,
    //             'graduationProjectWebinars' => $graduationProjectWebinars,
    //             'assignments' => $assignments,
    //             'totalGrade' => $totalGrade,
    //             'totalCount' => $totalCount,
    //             'averageGrade' => $averageGrade,
    //         ]);
    //     }}
    //     catch(\Exception $e){
    //              return $e->getMessage();
    //     };
    // }

//     public function getGraduationProjectAssignments(Request $request, $userId, $webinar)
// {
//     try {
//         // Get the associated student
//         $user = User::find($userId);
//         $student = $user->student;
//         //return $webinar;
//         if (!$student) {
//             return response()->json(['error' => 'Student not found'], 404);
//         }

//         // Retrieve all bundles associated with the given webinar
//         $bundleWebinars = BundleWebinar::where('webinar_id', $webinar->id)->get();

//         if ($bundleWebinars->isEmpty()) {
//             return response()->json(['error' => 'No bundles found for the given webinar'], 404);
//         }

//         // Prepare an array to hold the total grade and count for GPA calculation
//         $totalGrade = 0;
//         $totalCount = 0;

//         // Prepare an array to hold results for each bundle
//         $results = [];

//         // Loop through each BundleWebinar
//         foreach ($bundleWebinars as $bundleWebinar) {
//             $bundleId = $bundleWebinar->bundle_id;

//             // Retrieve assignments related to the bundle
//             $graduationProjectWebinars = Bundle::find($bundleId)
//                 ->bundleWebinars()
//                 ->whereHas('webinar', function ($query) use($webinar) {
//                     $query->where('type', 'graduation_project')->where('webinar_id', $webinar->id);
//                 })
//                 ->with('webinar')
//                 ->get()
//                 ->pluck('webinar.id');

//             $assignments = WebinarAssignment::whereIn('webinar_id', $graduationProjectWebinars)
//                 ->with('assignmentHistory')
//                 ->get();

//             // Calculate total grades and counts for this bundle
//             foreach ($assignments as $assignment) {
//                 if ($assignment->assignmentHistory) {
//                     $totalGrade += $assignment->assignmentHistory->grade;
//                     $totalCount++;
//                 }
//             }
//               //return $assignments;
//             // Store results for the current bundle
//             $averageGrade = $totalCount > 0 ? $totalGrade / $totalCount : 0;
//             $gpa = $this->convertGradeToGPA($averageGrade);

//             // Save or update GPA in the BundleStudent model
//             $bundleStudent = BundleStudent::where('bundle_id', $bundleId)
//                                           ->where('student_id', $student->id)
//                                           ->first();

//             if ($bundleStudent) {
//                 $bundleStudent->gpa = $gpa; // Save the average grade as 'gpa'

//                 $bundleStudent->save();
//                 return $bundleStudent;
//             } else {
//                 // Handle creating a new BundleStudent record if necessary
//                 $bundleStudent = new BundleStudent();
//                 $bundleStudent->bundle_id = $bundleId;
//                 $bundleStudent->student_id = $student->id;
//                 $bundleStudent->gpa = $gpa;
//                 $bundleStudent->save();
//             }

//             // Store results for this bundle
//             $results[] = [
//                 'bundle_id' => $bundleId,
//                 'student_id' => $student->id,
//                 'graduationProjectWebinars' => $graduationProjectWebinars,
//                 'assignments' => $assignments,
//                 'totalGrade' => $totalGrade,
//                 'totalCount' => $totalCount,
//                 'averageGrade' => $averageGrade,
//             ];
//         }

//         // Return the aggregated results for all bundles
//         return response()->json($results);
//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// }


public function getGraduationProjectAssignments(Request $request, $userId, $webinar)
{
    try {
        // Get the associated student
        $user = User::find($userId);
        $student = $user->student;

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Retrieve all bundles associated with the given webinar
        $bundleWebinars = BundleWebinar::where('webinar_id', $webinar->id)->get();

        if ($bundleWebinars->isEmpty()) {
            return response()->json(['error' => 'No bundles found for the given webinar'], 404);
        }

        // Prepare an array to hold the total grade and count for GPA calculation
        $totalGrade = 0;
        $totalCount = 0;

        // Prepare an array to hold results for each bundle
        $results = [];

        // Loop through each BundleWebinar
        foreach ($bundleWebinars as $bundleWebinar) {
            $bundleId = $bundleWebinar->bundle_id;

            // Retrieve assignments related to the bundle
            $graduationProjectWebinars = Bundle::find($bundleId)
                ->bundleWebinars()
                ->whereHas('webinar', function ($query) use($webinar) {
                    $query->where('type', 'graduation_project')->where('webinar_id', $webinar->id);
                })
                ->with('webinar')
                ->get()
                ->pluck('webinar.id');

               $assignments = WebinarAssignment::whereIn('webinar_id', $graduationProjectWebinars)
                ->with(['assignmentHistory' => function($query) use($student) {
                    $query->where('student_id', $student->user_id); // Ensure we only get assignments for this student
                }])
                ->get();
            // Calculate total grades and counts for this bundle
            foreach ($assignments as $assignment) {
                if ($assignment->assignmentHistory) {
                    $totalGrade += $assignment->assignmentHistory->grade;
                    $totalCount++;
                }
            }

            // Store results for the current bundle
            $averageGrade = $totalCount > 0 ? $totalGrade / $totalCount : 0;
            $gpa = $this->convertGradeToGPA($averageGrade);
            // Save or update GPA in the BundleStudent model
            $bundleStudent = BundleStudent::where('bundle_id', $bundleId)
                                          ->where('student_id', $student->id)
                                          ->first();

     // return response()->json([$assignments,$bundleStudent]);
            if ($bundleStudent) {
                $bundleStudent->gpa = $gpa; // Save the average grade as 'gpa'
                $bundleStudent->save();
            // return response()->json($bundleStudent->gpa);
            }


            // Store results for this bundle
            $results[] = [
                'bundle_id' => $bundleId,
                'student_id' => $student->id,
                'graduationProjectWebinars' => $graduationProjectWebinars,
                'assignments' => $assignments,
                'totalGrade' => $totalGrade,
                'totalCount' => $totalCount,
                'averageGrade' => $averageGrade,
            ];
        }

        // Return the aggregated results for all bundles
        return response()->json($results);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



    private function convertGradeToGPA($grade)
{
    if ($grade >= 98) return 5.0;
    if ($grade >= 97) return 4.9;
    if ($grade >= 95) return 4.85;
    if ($grade >= 92) return 4.8;
    if ($grade >= 90) return 4.75;
    if ($grade >= 88) return 4.6;
    if ($grade >= 85) return 4.5;
    if ($grade >= 83) return 4.2;
    if ($grade >= 80) return 4.0;
    if ($grade >= 78) return 3.9;
    if ($grade >= 77) return 3.8;
    if ($grade >= 76) return 3.65;
    if ($grade >= 75) return 3.5;
    if ($grade >= 74) return 3.4;
    if ($grade >= 73) return 3.3;
    if ($grade >= 72) return 3.2;
    if ($grade >= 71) return 3.1;
    if ($grade >= 70) return 3.0;
    if ($grade >= 68) return 2.9;
    if ($grade >= 66) return 2.65;
    if ($grade >= 65) return 2.5;
    if ($grade >= 63) return 2.4;
    if ($grade >= 61) return 2.2;
    if ($grade >= 60) return 2.0;
    if ($grade < 60) return 0;
    return 0; // For grades below 60
}

    public function downloadAttach($assignmentId, $historyId, $messageId)
    {
        $user = auth()->user();

        $assignment = WebinarAssignment::where('id', $assignmentId)->first();

        if (!empty($assignment)) {
            $webinar = $assignment->webinar;

            if (!empty($webinar) and $webinar->checkUserHasBought($user)) {
                $allow = ($webinar->creator_id == $user->id or $webinar->teacher_id == $user->id or $user->isAdmin());

                $assignmentHistory = WebinarAssignmentHistory::where('instructor_id', $assignment->creator_id)
                    ->where('id', $historyId)
                    ->where(function ($query) use ($allow, $user) {
                        if (!$allow) {
                            $query->where('student_id', $user->id);
                        }
                    })
                    ->where('assignment_id', $assignment->id)
                    ->with([
                        'messages' => function ($query) {
                            $query->orderBy('created_at', 'desc');
                            $query->whereHas('sender');
                            $query->with([
                                'sender'
                            ]);
                        }
                    ])->first();

                $message = WebinarAssignmentHistoryMessage::where('id', $messageId)
                    ->where('assignment_history_id', $assignmentHistory->id)
                    ->first();

                if (!empty($message) and !empty($message->file_path)) {
                    $filePath = public_path($message->file_path);
                    $fileTitle = $message->file_title ?? 'attachment';

                    if (file_exists($filePath)) {
                        $fileInfo = pathinfo($filePath);
                        $type = (!empty($fileInfo) and !empty($fileInfo['extension'])) ? $fileInfo['extension'] : '';

                        $fileName = str_replace(' ', '-', $fileTitle);
                        $fileName = str_replace('.', '-', $fileName);
                        $fileName .= '.' . $type;

                        $headers = array(
                            'Content-Type: application/' . $type,
                        );

                        return response()->download($filePath, $fileName, $headers);
                    }else{
                        return view('errors.404');
                    }
                }
            }
        }

        abort(403);
    }
}
