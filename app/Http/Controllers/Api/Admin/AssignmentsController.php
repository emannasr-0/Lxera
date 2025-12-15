<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Api\Organization;
use App\Models\File;
use App\Models\Sale;
use App\Models\Translation\WebinarAssignmentTranslation;
use App\Models\Webinar;
use App\Models\WebinarAssignment;
use App\Models\WebinarAssignmentAttachment;
use App\Models\WebinarAssignmentHistory;
use App\Models\WebinarChapterItem;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AssignmentsController extends Controller
{

    public function index(Request $request)
    {
        $this->authorize('admin_webinar_assignments_lists');

        $query = WebinarAssignment::query();

        // Counts before pagination
        $courseAssignmentsCount = deepClone($query)->count();

        $pendingReviewCount = deepClone($query)->whereHas('assignmentHistory', function ($q) {
            $q->where('status', WebinarAssignmentHistory::$pending);
        })->count();

        $passedCount = deepClone($query)->whereHas('assignmentHistory', function ($q) {
            $q->where('status', WebinarAssignmentHistory::$passed);
        })->count();

        $failedCount = deepClone($query)->whereHas('assignmentHistory', function ($q) {
            $q->where('status', WebinarAssignmentHistory::$notPassed);
        })->count();

        // Filters
        $query = $this->handleAssignmentsFilters($request, $query);

        // Pagination params
        $perPage = (int) $request->get('per_page', 10);
        $page    = (int) $request->get('page', 1);

        // Main query with relations
        $assignments = $query->with([
            'webinar',
            'chapter',
            'attachments',
            'instructorAssignmentHistories' => function ($q) {
                $q->orderBy('created_at', 'desc')->with([
                    'messages' => function ($m) {
                        $m->orderBy('created_at', 'desc');
                    }
                ]);
            },
        ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // mapping data
        $assignmentsData = $assignments->map(function ($assignment) {
            return [
                'assignmentId'         => $assignment->id,
                'assignmentTitle'      => $assignment->title,
                'webinarTitle'         => $assignment->webinar->title,
                'students'             => $assignment->instructorAssignmentHistories,
                'studentsCount'        => $assignment->instructorAssignmentHistories->count(),
                'assignmentGrade'      => $assignment->grade,
                'assignmentPassGrade'  => $assignment->pass_grade,
                'assignmentStatus'     => $assignment->status,

                // Extra fields
                'id'                   => $assignment->id,
                'chapter_id'           => $assignment->chapter_id,
                'webinar_id'           => $assignment->webinar_id,
                'chapterTitle'         => $assignment->chapter?->title,
                'title'                => $assignment->title,
                'description'          => $assignment->description,
                'grade'                => $assignment->grade,
                'pass_grade'           => $assignment->pass_grade,
                'deadline'             => $assignment->deadline,
                'attempts'             => $assignment->attempts,
                'check_previous_parts' => $assignment->check_previous_parts,
                'access_after_day'     => $assignment->access_after_day,
                'status'               => $assignment->status,
                'locale'               => $assignment->locale,
                'attachments' => $assignment->attachments?->map(function ($att) {
                    return [
                        'file_name' => $att->file_name,
                        'file_url'  => $att->file_url,
                    ];
                }),
            ];
        });

        // Basic data
        $data = [
            'assignmentsTable'       => $assignmentsData,
            'courseAssignmentsCount' => $courseAssignmentsCount,
            'pendingReviewCount'     => $pendingReviewCount,
            'passedCount'            => $passedCount,
            'failedCount'            => $failedCount,

            // pagination meta
            'pagination' => [
                'current_page' => $assignments->currentPage(),
                'per_page'     => $assignments->perPage(),
                'total'        => $assignments->total(),
                'last_page'    => $assignments->lastPage(),
                'from'         => $assignments->firstItem(),
                'to'           => $assignments->lastItem(),
            ],
        ];

        // webinar_ids filter
        $webinar_ids = $request->get('webinar_ids');
        if (!empty($webinar_ids)) {
            $data['webinars'] = Webinar::select('id')
                ->whereIn('id', $webinar_ids)
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }


    private function handleAssignmentsFilters(Request $request, $query)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $webinar_ids = $request->get('webinar_ids');
        $student_ids = $request->get('student_ids');
        $status = $request->get('status', null);

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($webinar_ids)) {
            $query->whereIn('webinar_id', $webinar_ids);
        }

        if (!empty($student_ids)) {
            $query->whereIn('student_id', $student_ids);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function students($url_name, Request $request, $id)
    {
        try {
            $organization = Organization::where('url_name', $url_name)->first();
            if (!$organization) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Organization not found'
                ], 404);
            }

            $this->authorize('admin_webinar_assignments_students');

            $assignment = WebinarAssignment::find($id);
            if (!$assignment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Assignment not found'
                ], 404);
            }

            $webinar = $assignment->webinar;

            $query = $assignment->instructorAssignmentHistories()
                ->with(['student' => function ($q) {
                    $q->select('id', 'full_name', 'email');
                }]);

            if ($search = $request->input('student')) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $counts = [
                'pending' => deepClone($query)->where('status', WebinarAssignmentHistory::$pending)->count(),
                'passed' => deepClone($query)->where('status', WebinarAssignmentHistory::$passed)->count(),
                'failed' => deepClone($query)->where('status', WebinarAssignmentHistory::$notPassed)->count(),
                'total' => deepClone($query)->count()
            ];

            $query = $this->handleAssignmentsFilters($request, $query);

            $perPage = $request->input('per_page', 10);
            $histories = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $histories->getCollection()->transform(function ($history) use ($assignment) {
                $history->usedAttemptsCount = 0;
                $history->purchase_date = "--";
                $history->last_submission = "--";
                $history->first_submission = "--";

                $sale = Sale::where('buyer_id', $history->student_id)
                    ->where('webinar_id', $assignment->webinar_id)
                    ->whereNull('refund_at')
                    ->first(['created_at']);

                if ($sale) {
                    $history->purchase_date = \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d');
                }

                if ($history->messages->isNotEmpty()) {
                    $history->last_submission = \Carbon\Carbon::parse($history->messages->first()->created_at)->format('Y-m-d');
                    $history->first_submission = \Carbon\Carbon::parse($history->messages->last()->created_at)->format('Y-m-d');
                    $history->usedAttemptsCount = $history->messages->count();

                    // format each message created_at too
                    $history->messages->transform(function ($msg) {
                        $msg->created_at = \Carbon\Carbon::parse($msg->created_at)->format('Y-m-d');
                        return $msg;
                    });
                }

                // also format history->created_at itself
                $history->created_at = \Carbon\Carbon::parse($history->created_at)->format('Y-m-d');

                return $history;
            });


            $data = [
                'assignment' => $assignment->only(['id', 'title', 'grade', 'pass_grade', 'status']),
                'webinar' => $webinar->only(['id', 'title']),
                'histories' => $histories,
                'counts' => $counts,
                'search' => $request->input('search', '')
            ];


            if ($student_ids = $request->get('student_ids')) {
                $data['students'] = User::whereIn('id', $student_ids)
                    ->get(['id', 'full_name', 'email'])
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'full_name' => mb_convert_encoding($user->full_name, 'UTF-8', 'UTF-8'),
                            'email' => $user->email
                        ];
                    });
            }

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error('Assignment students error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Server error occurred',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function conversations($url_name, $assignmentId, $historyId)
    {
        $organization = Organization::where('url_name', $url_name);
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_webinar_assignments_conversations');

        $assignment = WebinarAssignment::findOrFail($assignmentId);

        $history = WebinarAssignmentHistory::where('assignment_id', $assignmentId)
            ->where('id', $historyId)
            ->with([
                'messages' => function ($query) {
                    $query->with([
                        'sender'
                    ]);
                }
            ])
            ->first();

        if (!empty($history)) {
            $data = [
                'status' => 'success',
                'assignment' => $assignment,
                'conversations' => $history->messages,
            ];

            return response()->json($data);
        }

        abort(404);
    }

    public function store($url_name, Request $request)
    {
        $this->authorize('admin_webinars_edit');

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

            $this->authorize('admin_webinars_edit');

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
        $this->authorize('admin_webinars_edit');

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
}
