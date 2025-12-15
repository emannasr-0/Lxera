<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebinarAssignmentHistoryResource;
use App\Http\Resources\WebinarAssignmentResource;
use App\Models\Api\Organization;
use App\Models\Api\Webinar;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Sale;
use App\Models\File;
use App\Models\Api\WebinarAssignment;
use App\Models\Api\WebinarAssignmentAttachment;
use App\Models\Api\WebinarAssignmentHistory;
use App\Models\Translation\WebinarAssignmentTranslation;
use App\Models\WebinarChapterItem;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        if (!getFeaturesSettings('webinar_assignment_status')) {
            abort(403);
        }

        $user = apiAuth();

        $query = WebinarAssignment::where('creator_id', $user->id);

        $courseAssignmentsCount = deepClone($query)->count();

        $pendingReviewCount = deepClone($query)->whereHas('instructorAssignmentHistories', function ($query) use ($user) {
            $query->where('instructor_id', $user->id);
            $query->where('status', WebinarAssignmentHistory::$pending);
        })->count();

        $passedCount = deepClone($query)->whereHas('instructorAssignmentHistories', function ($query) use ($user) {
            $query->where('instructor_id', $user->id);
            $query->where('status', WebinarAssignmentHistory::$passed);
        })->count();

        $failedCount = deepClone($query)->whereHas('instructorAssignmentHistories', function ($query) use ($user) {
            $query->where('instructor_id', $user->id);
            $query->where('status', WebinarAssignmentHistory::$notPassed);
        })->count();

        $assignments = $query->with([
            'webinar',
            'instructorAssignmentHistories' => function ($query) use ($user) {
                $query->where('instructor_id', $user->id);
            },
        ])->orderBy('created_at', 'desc')
            ->paginate(10);

        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
            [
                'course_assignments_count' => $courseAssignmentsCount,
                'pending_reviews_count' => $pendingReviewCount,
                'passed_count' => $passedCount,
                'failed_count' => $failedCount,
                'assignments' => WebinarAssignmentResource::collection($assignments),

            ]);

    }

        public function store($url_name, Request $request)
    {
       

        $data = $request->all();

        $validator = Validator::make($data, [
            'webinar_id' => 'required',
            'chapter_id' => 'required',
            'title' => 'required|max:255',
            'description' => 'required',
            'grade' => 'required|integer',
            'pass_grade' => 'required|integer',
            'deadline' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        // if (!empty($data['sequence_content']) and $data['sequence_content'] == 'on') {
        $data['check_previous_parts'] = (!empty($data['check_previous_parts']) and $data['check_previous_parts'] == 'on');

        $data['access_after_day'] = !empty($data['access_after_day']) ? strtotime($data['access_after_day']) : null;
        // } else {
        //     $data['check_previous_parts'] = false;
        //     $data['access_after_day'] = null;
        // }

        $webinar = Webinar::find($data['webinar_id']);

        if (!$webinar) {
            return response()->json([
                'code' => 404,
                'message' => 'Webinar not found',
            ], 404);
        }


        if (!empty($webinar)) {
            $assignment = WebinarAssignment::create([
                'creator_id' => $webinar->creator_id,
                'webinar_id' => $data['webinar_id'],
                'chapter_id' => $data['chapter_id'],
                'grade' => $data['grade'] ?? null,
                'pass_grade' => $data['pass_grade'] ?? null,
                'deadline' => $data['deadline'] ? strtotime($data['deadline']) : null,
                'attempts' => $data['attempts'] ?? null,
                'check_previous_parts' => $data['check_previous_parts'],
                'access_after_day' => $data['access_after_day'],
                'status' => (!empty($data['status']) and $data['status'] == 'on') ? File::$Active : File::$Inactive,
                'created_at' => time(),
            ]);

            if ($assignment) {
                WebinarAssignmentTranslation::updateOrCreate([
                    'webinar_assignment_id' => $assignment->id,
                    'locale' => mb_strtolower($data['locale']),
                ], [
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);


                $this->handleAttachments($data['attachments'], $webinar->creator_id, $assignment->id);

                if (!empty($assignment->chapter_id)) {
                    WebinarChapterItem::makeItem($webinar->creator_id, $assignment->chapter_id, $assignment->id, WebinarChapterItem::$chapterAssignment);
                }
            }

            return response()->json([
                'code' => 200,
                'assignment' => $assignment,
            ], 200);
        }

        return response()->json([], 422);
    }
    public function update($url_name, Request $request, $id)
    {
        try {
            $organization = Organization::where('url_name', $url_name)->first();
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }

        

            $data = $request->all();

            $jsonData = $request->json()->all();

            if (!$data) {
                return response()->json([
                    'code' => 422,
                    'errors' => ['Invalid input data.'],
                ], 422);
            }

            // Validation rules
            $validator = Validator::make($data, [
                'webinar_id'       => 'required',
                'chapter_id'       => 'required',
                'title'            => 'required|max:255',
                'description'      => 'required',
                'grade'            => 'required|integer',
                'pass_grade'       => 'required|integer',
                'deadline'         => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data['check_previous_parts'] = !empty($data['check_previous_parts']) && $data['check_previous_parts'] === 'on';
            $data['access_after_day'] = !empty($data['access_after_day']) ? strtotime($data['access_after_day']) : null;

            $assignment = WebinarAssignment::find($id);

            if (!$assignment) {
                return response()->json([], 422);
            }

            $changeChapter = ($data['chapter_id'] != $assignment->chapter_id);
            $oldChapterId = $assignment->chapter_id;

            // Or for quick debugging:
            if (empty($data)) {
                return response()->json(['code' => 422, 'errors' => ['Data is empty']], 422);
            }

            $assignment->update([
                'chapter_id'          => $data['chapter_id'],
                'grade'               => $data['grade'] ?? null,
                'pass_grade'          => $data['pass_grade'] ?? null,
                'deadline'            => strtotime($data['deadline']),
                'attempts'            => $data['attempts'] ?? null,
                'check_previous_parts' => !empty($data['check_previous_parts']) && ($data['check_previous_parts'] === 'on' || $data['check_previous_parts'] == 1),
                'access_after_day'    => !empty($data['access_after_day']) ? strtotime($data['access_after_day']) : null,
                'status'              => (!empty($data['status']) && ($data['status'] === 'on' || $data['status'] == 1)) ? File::$Active : File::$Inactive,

            ]);

            if ($changeChapter) {
                WebinarChapterItem::changeChapter(
                    $assignment->creator_id,
                    $oldChapterId,
                    $assignment->chapter_id,
                    $assignment->id,
                    WebinarChapterItem::$chapterAssignment
                );
            }

            WebinarAssignmentTranslation::updateOrCreate(
                [
                    'webinar_assignment_id' => $assignment->id,
                    'locale'                => mb_strtolower($data['locale']),
                ],
                [
                    'title'       => $data['title'],
                    'description' => $data['description'],
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'errors' => $validator->errors()->toArray(),
                ], 422);
            }

            $this->handleAttachments($data['attachments'] ?? [], $assignment->creator_id, $assignment->id);

            removeContentLocale();

            return response()->json([
                'status' => 'success',
                'message' => 'Assignment Updated Successfully',
                'assignment' => $assignment,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $formattedErrors = [];

            foreach ($e->errors() as $field => $messages) {
                $rule = strtolower(array_key_first($e->validator->failed()[$field]));

                // Get final English message by revalidating in 'en' locale
                app()->setLocale('en');
                $englishValidator = Validator::make($e->validator->getData(), $e->validator->getRules());
                $englishMessage = $englishValidator->errors()->first($field);

                // Set locale back to original
                app()->setLocale('ar');

                $formattedErrors[$field] = [
                    'ar' => $messages[0],
                    'en' => $englishMessage
                ];
            }

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $formattedErrors
            ], 422);
        }
    }

     public function destroy($url_name, $id)
    {
     
        $assignment = WebinarAssignment::where('id', $id)->first();

        if (!empty($assignment)) {

            WebinarChapterItem::where('user_id', $assignment->creator_id)
                ->where('item_id', $assignment->id)
                ->where('type', WebinarChapterItem::$chapterAssignment)
                ->delete();

            $assignment->delete();
        }

        return response()->json([
            'code' => 200
        ], 200);
    }
     private function handleAttachments($attachments, $creatorId, $assignmentId)
    {

        WebinarAssignmentAttachment::where('creator_id', $creatorId)
            ->where('assignment_id', $assignmentId)
            ->delete();

        if (!is_array($attachments) || empty($attachments)) {
            return;
        }

        foreach ($attachments as $attachment) {
            if (
                !empty($attachment['title']) &&
                isset($attachment['attach']) &&
                $attachment['attach'] instanceof \Illuminate\Http\UploadedFile
            ) {
                $path = $attachment['attach']->store('assignments/attachments', 'public');

                WebinarAssignmentAttachment::create([
                    'creator_id' => $creatorId,
                    'assignment_id' => $assignmentId,
                    'title' => $attachment['title'],
                    'attach' => $path,
                ]);
            }
        }
    }

    public function students(Request $request)
    {
        if (!getFeaturesSettings('webinar_assignment_status')) {
            abort(403);
        }

        $user = apiAuth();

        $assignment = WebinarAssignment::where('creator_id', $user->id)
            // ->where('creator_id', $user->id)
            ->with([
                'webinar',
            ])
            ->first();

        if (!empty($assignment)) {
            $webinar = $assignment->webinar;

            $query = $assignment->assignmentHistory()
                ->where('instructor_id', $user->id)
                ->where('student_id', '!=', $user->id)
                ->with([
                    'student'
                ]);

            $courseAssignmentsCount = WebinarAssignment::where('creator_id', $user->id)
                ->where('webinar_id', $webinar->id)
                ->count();

            $pendingReviewCount = deepClone($query)->where('status', WebinarAssignmentHistory::$pending)->count();
            $passedCount = deepClone($query)->where('status', WebinarAssignmentHistory::$passed)->count();
            $failedCount = deepClone($query)->where('status', WebinarAssignmentHistory::$notPassed)->count();


            $histories = $query->orderBy('created_at', 'desc')
                ->get();
            //  dd($histories);
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
            $resource = WebinarAssignmentHistoryResource::collection($histories);
            //  dd($resource->groupBy('id')) ;
            //  $resource=$resource->groupBy('student_id')

            $data = [
                'pageTitle' => trans('update.students_assignments'),
                'assignment' => $assignment,
                'histories' => $histories,

                'webinar' => $webinar,
                'courseAssignmentsCount' => $courseAssignmentsCount,
                'pendingReviewCount' => $pendingReviewCount,
                'passedCount' => $passedCount,
                'failedCount' => $failedCount,
            ];

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
                'assignment_histories' => $resource,
                'count' => $courseAssignmentsCount,
                'pending_count' => $pendingReviewCount,
                'passed_count' => $passedCount,
                'failed_count' => $failedCount,

            ]);

            //  return view('web.default.panel.assignments.students', $data);
        }

        abort(404);
    }

    public function submmision($url_name,$id)
    {
        $organization = Organization::where('url_name', $url_name);
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        if (!getFeaturesSettings('webinar_assignment_status')) {
            abort(403);
        }

        $user = apiAuth();

        $assignment = WebinarAssignment::where('creator_id', $user->id)
            ->where('id',$id)
            // ->where('creator_id', $user->id)
            ->with([
                'webinar',
            ])
            ->first();

        if (!empty($assignment)) {
            $webinar = $assignment->webinar;

            $query = $assignment->assignmentHistory()
                ->where('instructor_id', $user->id)
                ->where('student_id', '!=', $user->id)
                ->with([
                    'student'
                ]);

            $courseAssignmentsCount = WebinarAssignment::where('creator_id', $user->id)
                ->where('webinar_id', $webinar->id)
                ->count();

            $pendingReviewCount = deepClone($query)->where('status', WebinarAssignmentHistory::$pending)->count();
            $passedCount = deepClone($query)->where('status', WebinarAssignmentHistory::$passed)->count();
            $failedCount = deepClone($query)->where('status', WebinarAssignmentHistory::$notPassed)->count();


            $histories = $query->orderBy('created_at', 'desc')
                ->get();
            //  dd($histories);
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
            $resource = WebinarAssignmentHistoryResource::collection($histories);
            //  dd($resource->groupBy('id')) ;
            //  $resource=$resource->groupBy('student_id')

            $data = [
                'pageTitle' => trans('update.students_assignments'),
                'assignment' => $assignment,
                'histories' => $histories,

                'webinar' => $webinar,
                'courseAssignmentsCount' => $courseAssignmentsCount,
                'pendingReviewCount' => $pendingReviewCount,
                'passedCount' => $passedCount,
                'failedCount' => $failedCount,
            ];

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $resource) ;

            //  return view('web.default.panel.assignments.students', $data);
        }

        abort(404);
    }

    public function setGrade(Request $request, $historyId)
    {
        $user = apiAuth();
        validateParam($request->all(), [
            'grade' => 'required|integer',
        ]);

        $assignmentHistory = WebinarAssignmentHistory::where('id', $historyId)->first();
        abort_unless($assignmentHistory, 404);
        $assignment = $assignmentHistory->assignment;
        $webinar = $assignment->webinar;
        $data = $request->all();
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

        return apiResponse2(1, 'stored', trans('api.public.stored'));

    }
}
