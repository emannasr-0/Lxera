<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\QuizResultsExport;
use App\Exports\QuizzesAdminExport;
use App\Http\Controllers\Controller;
use App\Models\Api\Organization;
use App\Models\Api\QuizzesQuestionsAnswer;
use App\Models\Quiz;
use App\Models\QuizzesQuestion;
use App\Models\QuizzesResult;
use App\Models\Translation\QuizTranslation;
use App\Models\Translation\QuizzesQuestionsAnswerTranslation;
use App\Models\Translation\QuizzesQuestionTranslation;
use App\Models\Webinar;
use App\Models\WebinarChapter;
use App\Models\WebinarChapterItem;
use App\Models\File;

use App\Models\Session;
use App\Models\TextLesson;
use App\Models\Translation\WebinarChapterTranslation;

use App\Models\WebinarAssignment;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class QuizzesController extends Controller
{

    public function index($url_name, Request $request)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_quizzes_list');
        removeContentLocale();
        $teacherNameFilter = $request->input('teacher_name');
        $webinarNameFilter = $request->input('webinar_name');
        $query = Quiz::query();

        $totalQuizzes = deepClone($query)->count();
        $totalActiveQuizzes = deepClone($query)->where('status', 'active')->count();
        $totalStudents = QuizzesResult::groupBy('user_id')->count();
        $totalPassedStudents = QuizzesResult::where('status', 'passed')->groupBy('user_id')->count();
        if (!empty($teacherNameFilter)) {
            $query->whereHas('teacher', function ($query) use ($teacherNameFilter) {
                $query->filterBySearch(['full_name' => $teacherNameFilter]);
            });
        }
        if (!empty($webinarNameFilter)) {
            $query->whereHas('webinar.translations', function ($query) use ($webinarNameFilter) {
                $query->where('title', 'like', '%' . $webinarNameFilter . '%');
            });
        }
        $query = $this->filters($query, $request);

        // Get pagination values
        $perPage = $request->get('per_page', 10);
        $quizzes = $query->with([
            'webinar',
            'teacher',
            'quizQuestions',
            'quizResults',
        ])->paginate($perPage);

        $quizzesData = [];

         foreach ($quizzes as $quiz) {
            $quizzesData[] = [
                'quizId' => $quiz->id,
                'quizTitle' => $quiz->title ? $quiz->title : null,
                'time' => $quiz->time ? $quiz->time : null,
                'attempt' => $quiz->attempt ? $quiz->attempt : null,
                'expiry_days' => $quiz->expiry_days ? $quiz->expiry_days : null,
                'pass_mark' => $quiz->pass_mark ? $quiz->pass_mark : null,
                'webinarId' => $quiz->webinar ? $quiz->webinar->id : null,
                'webinarTitle' => $quiz->webinar ? $quiz->webinar->title : null,
                'teacher' => $quiz->teacher ? $quiz->teacher->full_name : null,
                'quizQuestions' => $quiz->quizQuestions->count(),
                'Students' => $quiz->quizResults->pluck('user_id')->count(),
                'passedStudents' => $quiz->quizResults->where('status', 'passed')->count(),
                'avgGrade' => $quiz->quizResults->avg('user_grade') ?? 0,
                'certificate' => $quiz->certificate,
                'status' => $quiz->status
            ];
        }

        $teacher_ids = $request->get('teacher_ids');
        $webinar_ids = $request->get('webinar_ids');

        $data = [
            'pagination' => [
                'total' => $quizzes->total(),
                'per_page' => $quizzes->perPage(),
                'current_page' => $quizzes->currentPage(),
                'last_page' => $quizzes->lastPage(),
            ],
            'quizzesTable' => $quizzesData,
            'totalQuizzes' => $totalQuizzes,
            'totalActiveQuizzes' => $totalActiveQuizzes,
            'totalStudents' => $totalStudents,
            'totalPassedStudents' => $totalPassedStudents,
        ];

        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')
                ->whereIn('id', $teacher_ids)->get();
        }

        if (!empty($webinar_ids)) {
            $data['webinars'] = Webinar::select('id')
                ->whereIn('id', $webinar_ids)->get();
        }

        return response()->json($data);
    }

    private function filters($query, $request)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $title = $request->get('title', null);
        $sort = $request->get('sort', null);
        $teacher_ids = $request->get('teacher_ids', null);
        $webinar_ids = $request->get('webinar_ids', null);
        $status = $request->get('status', null);

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($title)) {
            $query->whereTranslationLike('title', '%' . $title . '%');
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'have_certificate':
                    $query->where('certificate', true);
                    break;
                case 'students_count_asc':
                    $query->join('quizzes_results', 'quizzes_results.quiz_id', '=', 'quizzes.id')
                        ->select('quizzes.*', 'quizzes_results.quiz_id', DB::raw('count(quizzes_results.quiz_id) as result_count'))
                        ->groupBy('quizzes_results.quiz_id')
                        ->orderBy('result_count', 'asc');
                    break;

                case 'students_count_desc':
                    $query->join('quizzes_results', 'quizzes_results.quiz_id', '=', 'quizzes.id')
                        ->select('quizzes.*', 'quizzes_results.quiz_id', DB::raw('count(quizzes_results.quiz_id) as result_count'))
                        ->groupBy('quizzes_results.quiz_id')
                        ->orderBy('result_count', 'desc');
                    break;
                case 'passed_count_asc':
                    $query->join('quizzes_results', 'quizzes_results.quiz_id', '=', 'quizzes.id')
                        ->select('quizzes.*', 'quizzes_results.quiz_id', DB::raw('count(quizzes_results.quiz_id) as result_count'))
                        ->where('quizzes_results.status', 'passed')
                        ->groupBy('quizzes_results.quiz_id')
                        ->orderBy('result_count', 'asc');
                    break;

                case 'passed_count_desc':
                    $query->join('quizzes_results', 'quizzes_results.quiz_id', '=', 'quizzes.id')
                        ->select('quizzes.*', 'quizzes_results.quiz_id', DB::raw('count(quizzes_results.quiz_id) as result_count'))
                        ->where('quizzes_results.status', 'passed')
                        ->groupBy('quizzes_results.quiz_id')
                        ->orderBy('result_count', 'desc');
                    break;

                case 'grade_avg_asc':
                    $query->join('quizzes_results', 'quizzes_results.quiz_id', '=', 'quizzes.id')
                        ->select('quizzes.*', 'quizzes_results.quiz_id', 'quizzes_results.user_grade', DB::raw('avg(quizzes_results.user_grade) as grade_avg'))
                        ->groupBy('quizzes_results.quiz_id')
                        ->orderBy('grade_avg', 'asc');
                    break;

                case 'grade_avg_desc':
                    $query->join('quizzes_results', 'quizzes_results.quiz_id', '=', 'quizzes.id')
                        ->select('quizzes.*', 'quizzes_results.quiz_id', 'quizzes_results.user_grade', DB::raw('avg(quizzes_results.user_grade) as grade_avg'))
                        ->groupBy('quizzes_results.quiz_id')
                        ->orderBy('grade_avg', 'desc');
                    break;

                case 'created_at_asc':
                    $query->orderBy('created_at', 'asc');
                    break;

                case 'created_at_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if (!empty($teacher_ids)) {
            $query->whereIn('creator_id', $teacher_ids);
        }

        if (!empty($webinar_ids)) {
            $query->whereIn('webinar_id', $webinar_ids);
        }

        if (!empty($status) and $status !== 'all') {
            $query->where('status', strtolower($status));
        }

        return $query;
    }

    public function store($url_name, Request $request)
    {
        try {
            $organization = Organization::where('url_name', $url_name)->first();
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }

            $this->authorize('admin_quizzes_create');

            $data = $request->input([]);

            if (empty($data)) {
                return response()->json(['message' => 'No quiz data provided'], 400);
            }

            $locale = $data['locale'] ?? getDefaultLocale();

            $request->validate([
                'title' => 'required|string|max:255',
                'webinar_id' => 'required|exists:webinars,id',
                'pass_mark' => 'required|numeric',
            ]);

            $validate = $request->all();

            $webinar = Webinar::find($data['webinar_id']);
            if (!$webinar) {
                return response()->json(['message' => 'Webinar not found'], 404);
            }

            $chapter = null;
            if (!empty($data['chapter_id'])) {
                $chapter = WebinarChapter::where('id', $data['chapter_id'])
                    ->where('webinar_id', $webinar->id)
                    ->first();
            }

            $quiz = Quiz::create([
                'webinar_id' => $webinar->id,
                'chapter_id' => $chapter?->id,
                'creator_id' => $webinar->creator_id,
                'attempt' => $data['attempt'] ?? null,
                'pass_mark' => $data['pass_mark'],
                'time' => $data['time'] ?? null,
                'status' => $data['status'] ?? Quiz::INACTIVE,
                'certificate' => $data['certificate'] ?? 0,
                'display_questions_randomly' => $data['display_questions_randomly'] ?? 0,
                'expiry_days' => (!empty($data['expiry_days']) && $data['expiry_days'] > 0) ? $data['expiry_days'] : null,
                'created_at' => time(),
            ]);

            QuizTranslation::updateOrCreate([
                'quiz_id' => $quiz->id,
                'locale' => mb_strtolower($locale),
            ], [
                'title' => $data['title'],
            ]);

            if ($quiz->chapter_id) {
                WebinarChapterItem::makeItem($webinar->creator_id, $quiz->chapter_id, $quiz->id, WebinarChapterItem::$chapterQuiz);
            }

            //  $webinar->sendNotificationToAllStudentsForNewQuizPublished($quiz);

            return response()->json([
                'status' => 'success',
                'message' => 'Quiz created successfully',
                "data" => $quiz
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

public function update($url_name, Request $request, $id)
{
    try {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $quiz = Quiz::query()->findOrFail($id);
        $user = $quiz->creator;
        $quizQuestionsCount = $quiz->quizQuestions->count();

        $data = $request->all();

        if (!$data) {
            return response()->json(['message' => 'Invalid or missing data.'], 400);
        }

        $locale = $data['locale'] ?? getDefaultLocale();

        $request->validate([
            'title' => 'required|string|max:255',
            'webinar_id' => 'sometimes|exists:webinars,id',
            'pass_mark' => 'sometimes|numeric',
            'display_number_of_questions' => 'sometimes|nullable|between:1,' . $quizQuestionsCount,
        ]);

        $webinar = null;
        $chapter = null;

        if (!empty($data['webinar_id'])) {
            $webinar = Webinar::where('id', $data['webinar_id'])->first();

            if (!empty($webinar) && !empty($data['chapter_id'])) {
                $chapter = WebinarChapter::where('id', $data['chapter_id'])
                    ->where('webinar_id', $webinar->id)
                    ->first();
            }
        }

        $quiz->update([
            'webinar_id' => !empty($webinar) ? $webinar->id : null,
            'chapter_id' => !empty($chapter) ? $chapter->id : null,
            'attempt' => $data['attempt'] ?? $quiz->attempt,
            'pass_mark' => $data['pass_mark'] ?? $quiz->pass_mark,
            'time' => $data['time'] ?? $quiz->time,
            'status' => (!empty($data['status']) && $data['status'] === 'active') ? Quiz::ACTIVE : Quiz::INACTIVE,
            'certificate' => !empty($data['certificate']),
            'display_limited_questions' => !empty($data['display_limited_questions']),
            'display_number_of_questions' => (!empty($data['display_limited_questions']) && !empty($data['display_number_of_questions']))
                ? $data['display_number_of_questions'] : null,
            'display_questions_randomly' => !empty($data['display_questions_randomly']),
            'expiry_days' => (!empty($data['expiry_days']) && $data['expiry_days'] > 0) ? $data['expiry_days'] : null,
        ]);

        if (isset($data['title']) && trim($data['title']) !== '') {
            QuizTranslation::updateOrCreate(
                [
                    'quiz_id' => $quiz->id,
                    'locale' => mb_strtolower($locale),
                ],
                [
                    'title' => $data['title'],
                ]
            );
        }

        // التعامل مع Chapter Items
        $checkChapterItem = WebinarChapterItem::where('user_id', $user->id)
            ->where('item_id', $quiz->id)
            ->where('type', WebinarChapterItem::$chapterQuiz)
            ->first();

        if (!empty($quiz->chapter_id)) {
            if (empty($checkChapterItem)) {
                WebinarChapterItem::makeItem($user->id, $quiz->chapter_id, $quiz->id, WebinarChapterItem::$chapterQuiz);
            } elseif ($checkChapterItem->chapter_id != $quiz->chapter_id) {
                $checkChapterItem->delete();
                WebinarChapterItem::makeItem($user->id, $quiz->chapter_id, $quiz->id, WebinarChapterItem::$chapterQuiz);
            }
        } else if (!empty($checkChapterItem)) {
            $checkChapterItem->delete();
        }

        removeContentLocale();

        return response()->json([
            'status' => 'success',
            'message' => 'Data Updated Successfully'
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        $formattedErrors = [];

        $currentLocale = app()->getLocale();

        foreach ($e->errors() as $field => $messages) {
            $data = $e->validator->getData();
            $rules = [$field => $e->validator->getRules()[$field]];

            // Arabic message
            app()->setLocale('ar');
            $arValidator = Validator::make($data, $rules);
            $arMessage = $arValidator->errors()->first($field);

            // English message
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


    public function listChaptersByQuiz($url_name, $quiz_id)
    {
        try {
            $organization = Organization::where('url_name', $url_name)->first();
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }

            $quiz = Quiz::find($quiz_id);
            if (!$quiz) {
                return response()->json(['message' => 'Quiz not found'], 404);
            }

            if (!$quiz->webinar_id) {
                return response()->json(['message' => 'No webinar assigned to this quiz'], 404);
            }

            $webinar = Webinar::find($quiz->webinar_id);
            if (!$webinar) {
                return response()->json(['message' => 'Webinar not found'], 404);
            }

            $chapters = WebinarChapter::with(['chapterItems:id,chapter_id,type,item_id'])
                ->where('webinar_id', $webinar->id)
                ->orderBy('order')
                ->get(['id', 'webinar_id', 'order']);

            $chaptersData = $chapters->map(function ($chapter) {
                return [
                    'id'    => $chapter->id,
                    'title' => $chapter->title ?? null,
                    'order' => $chapter->order,
                    'items' => $chapter->chapterItems->map(function ($item) {
                        return [
                            'id'      => $item->id,
                            'type'    => $item->type,
                            'item_id' => $item->item_id,
                        ];
                    })->values(),

                ];
            })->values();

            return response()->json([
                'status'  => 'success',
                'quiz'    => [
                    'id'    => $quiz->id,
                    'title' => $quiz->title ?? null,
                ],
                'webinar' => [
                    'id'    => $webinar->id,
                    'title' => $webinar->title ?? null,
                ],
                'chapters' => $chaptersData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($url_name, Request $request, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_quizzes_delete');

        $quiz = Quiz::findOrFail($id);

        $quiz->delete();

        $checkChapterItem = WebinarChapterItem::where('item_id', $id)
            ->where('type', WebinarChapterItem::$chapterQuiz)
            ->first();

        if (!empty($checkChapterItem)) {
            $checkChapterItem->delete();
        }

        if ($request->ajax()) {
            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Quiz Deleted Successfully'
        ]);
    }

    public function results($url_name, $id, Request $request)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_quizzes_results');

        $perPage = $request->get('per_page', 10);

        $quizzesResults = QuizzesResult::where('quiz_id', $id)
            ->with([
                'quiz.webinar',
                'quiz.teacher',
                'user'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        $resultsData = $quizzesResults->map(function ($result) {
            return [
                'resultId' => $result->id,
                'quiz_title'    => optional($result->quiz->translations->first())->title ?? $result->quiz->title,
                // 'webinar_title' => optional($result->quiz->webinar)->title,
                'student_name'  => $result->user->full_name,
                'teacher_name'  => optional($result->quiz->teacher)->full_name,
                'grade'         => $result->user_grade,
                'quiz_date'     => date('Y-m-d', $result->created_at),
                'status'        => $result->status,
            ];
        });

        $data = [
            'pagination' => [
                'total'        => $quizzesResults->total(),
                'per_page'     => $quizzesResults->perPage(),
                'current_page' => $quizzesResults->currentPage(),
                'last_page'    => $quizzesResults->lastPage(),
            ],
            'quizzesResults' => $resultsData,
            'quiz_id' => $id
        ];

        return response()->json($data);
    }

    public function resultsExportExcel($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_quiz_result_export_excel');

        $quizzesResults = QuizzesResult::where('quiz_id', $id)
            ->with([
                'quiz' => function ($query) {
                    $query->with(['teacher']);
                },
                'user'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $export = new QuizResultsExport($quizzesResults);

        return Excel::download($export, 'quiz_result.xlsx');
    }

    public function resultDelete($url_name, $result_id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $this->authorize('admin_quizzes_results_delete');

        $quizzesResults = QuizzesResult::where('id', $result_id)->first();

        if (!empty($quizzesResults)) {
            $quizzesResults->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Quiz Result deleted successfully'
        ]);
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('admin_quizzes_lists_excel');

        $query = Quiz::query();

        $query = $this->filters($query, $request);

        $quizzes = $query->with([
            'webinar',
            'teacher',
            'quizQuestions',
            'quizResults',
        ])->get();

        return Excel::download(new QuizzesAdminExport($quizzes), trans('quiz.quizzes') . '.xlsx');
    }

    public function orderItems($url_name, Request $request, $quizId)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'items' => 'required|array',
            'table' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            return response()->json(['message' => 'Quiz not found'], 404);
        }

        $itemIds = array_filter(array_unique($data['items']));

        if ($data['table'] === 'quizzes_questions' && !empty($itemIds)) {
            foreach ($itemIds as $order => $id) {
                QuizzesQuestion::where('id', $id)
                    ->where('quiz_id', $quiz->id)
                    ->update(['order' => ($order + 1)]);
            }
        }

        return response()->json([
            'title' => trans('public.request_success'),
            'msg' => trans('update.items_sorted_successful')
        ]);
    }


    public function storeQuestion($url_name, Request $request)
    {
        try {

            $organization = Organization::where('url_name', $url_name)->first();
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }
            $data = $request->all();

            $rules = [
                'quiz_id' => 'required|exists:quizzes,id',
                'title' => 'required',
                'grade' => 'required|integer',
                'type' => 'required',
                'image' => 'nullable|max:255',
                'video' => 'nullable|max:255',
            ];

            $validate = Validator::make($data, $rules);

            if ($validate->fails()) {
                return response()->json([
                    'code' => 422,
                    'errors' => $validate->errors()
                ], 422);
            }

            if (!empty($data['image']) && !empty($data['video'])) {
                return response()->json([
                    'code' => 422,
                    'errors' => [
                        'image' => [trans('update.quiz_question_image_validation_by_video')],
                        'video' => [trans('update.quiz_question_image_validation_by_video')],
                    ],
                ], 422);
            }

            $imagePath = null;
            $videoPath = null;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = $image->getClientOriginalName();
                $imagePath = $image->storeAs('uploads/questions/images', $imageName, 'public');
                $data['image'] = $imagePath;
            }

            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $videoName = time() . '_' . $video->getClientOriginalName();
                $videoPath = $video->storeAs('uploads/questions/videos', $videoName, 'public');
                $data['video'] = $videoPath;
            }

            if ($data['type'] == QuizzesQuestion::$multiple and !empty($data['answers'])) {
                $answers = $data['answers'];

                $hasCorrect = false;
                foreach ($answers as $answer) {
                    if (isset($answer['correct'])) {
                        $hasCorrect = true;
                    }
                }

                if (!$hasCorrect) {
                    return response([
                        'code' => 422,
                        'errors' => [
                            'current_answer' => [trans('quiz.current_answer_required')]
                        ],
                    ], 422);
                }
            }

            $quiz = Quiz::where('id', $data['quiz_id'])->first();

            if (!empty($quiz)) {
                $creator = $quiz->creator;
                $order = QuizzesQuestion::query()->where('quiz_id', $quiz->id)->count() + 1;

                $quizQuestion = QuizzesQuestion::create([
                    'quiz_id' => $data['quiz_id'],
                    'creator_id' => $creator->id,
                    'grade' => $data['grade'],
                    'type' => $data['type'],
                    'image' => $imagePath,
                    'video' =>  $videoPath,
                    'order' => $order,
                    'created_at' => time()
                ]);

                if (!empty($quizQuestion)) {
                    QuizzesQuestionTranslation::updateOrCreate([
                        'quizzes_question_id' => $quizQuestion->id,
                        'locale' => mb_strtolower($data['locale']),
                    ], [
                        'title' => $data['title'],
                        'correct' => $data['correct'] ?? null,
                    ]);
                }

                $quiz->increaseTotalMark($quizQuestion->grade);

                if ($quizQuestion->type == QuizzesQuestion::$multiple and !empty($data['answers'])) {

                    foreach ($answers as $answer) {
                        if (!empty($answer['title']) or !empty($answer['file'])) {
                            $questionAnswer = QuizzesQuestionsAnswer::create([
                                'question_id' => $quizQuestion->id,
                                'creator_id' => $creator->id,
                                'image' => $answer['file'] ?? null,
                                'correct' => isset($answer['correct']) ? true : false,
                                'created_at' => time()
                            ]);

                            if (!empty($questionAnswer)) {
                                QuizzesQuestionsAnswerTranslation::updateOrCreate([
                                    'quizzes_questions_answer_id' => $questionAnswer->id,
                                    'locale' => $data['locale'] ?? 'ar',
                                ], [
                                    'title' => $answer['title'],
                                ]);
                            }
                        }
                    }
                }

                return response()->json([
                    'code' => 200,
                    'message' => 'Question created successfully.',
                    'data' => [
                        'question' => $quizQuestion->load('quizzesQuestionsAnswers')->setAttribute('image_url', $quizQuestion->image ? asset('store/' . $quizQuestion->image)
                            : null),
                    ]
                ], 200);
            }
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
            return response()->json([
                'code' => 422
            ], 422);
        }
    }

    public function updateQuestion($url_name, Request $request, $id)
    {
        try {
            $organization = Organization::where('url_name', $url_name)->first();
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }

            $this->validate($request, [
                'title' => 'sometimes|string',
                'grade' => 'sometimes|numeric',
                'type' => 'sometimes|string|in:multiple,descriptive',
                'image' => 'nullable|file|max:255',
                'video' => 'nullable|file|max:255',
            ]);

            $data = $request->all();

            // Ensure no image and video at the same time
            if (!empty($data['image']) && !empty($data['video'])) {
                return response()->json([
                    'code' => 422,
                    'errors' => [
                        'image' => [trans('update.quiz_question_image_validation_by_video')],
                        'video' => [trans('update.quiz_question_image_validation_by_video')],
                    ],
                ], 422);
            }

            // Validate correct answer for multiple-choice
            if ($data['type'] == QuizzesQuestion::$multiple && !empty($data['answers'])) {
                $hasCorrect = collect($data['answers'])->contains(function ($answer) {
                    return isset($answer['correct']);
                });

                if (!$hasCorrect) {
                    return response()->json([
                        'code' => 422,
                        'errors' => [
                            'current_answer' => [trans('quiz.current_answer_required')]
                        ],
                    ], 422);
                }
            }
            $quizQuestion = QuizzesQuestion::with(['quiz.creator', 'quiz.translations', 'translations', 'quizzesQuestionsAnswers.translations'])
                ->where('id', $id)
                ->first();


            if (!$quizQuestion) {
                return response()->json([
                    'code' => 404,
                    'message' => 'Question not found'
                ], 404);
            }

            $quiz = $quizQuestion->quiz;
            $creator = $quiz->creator;

            $imagePath = $quizQuestion->image;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('uploads/questions/images', $imageName, 'public');
            }

            $videoPath = $quizQuestion->video;
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $videoName = time() . '_' . $video->getClientOriginalName();
                $videoPath = $video->storeAs('uploads/questions/videos', $videoName, 'public');
            }

            // Update total marks
            $quiz->decreaseTotalMark($quizQuestion->grade);

            $quizQuestion->update([
                'grade' => $data['grade'],
                'type' => $data['type'],
                'image' => $imagePath,
                'video' => $videoPath,
                'updated_at' => time()
            ]);

            QuizzesQuestionTranslation::updateOrCreate(
                [
                    'quizzes_question_id' => $quizQuestion->id,
                    'locale' => $data['locale'] ?? 'ar',
                ],
                [
                    'title' => $data['title'],
                    'correct' => $data['correct'] ?? null,
                ]
            );

            $quiz->increaseTotalMark($quizQuestion->grade);

            // Handle answers if multiple choice
            if ($quizQuestion->type == QuizzesQuestion::$multiple && !empty($data['answers'])) {
                $answers = $data['answers'];
                $existingAnswers = QuizzesQuestionsAnswer::where('question_id', $quizQuestion->id)
                    ->get()
                    ->keyBy('id');

                $receivedAnswerIds = [];

                foreach ($answers as $answer) {
                    if (!empty($answer['title']) || !empty($answer['file'])) {
                        $quizAnswer = null;

                        if (!empty($answer['id'])) {
                            $quizAnswer = $existingAnswers->get($answer['id']);
                        }

                        if ($quizAnswer) {
                            $quizAnswer->update([
                                'image' => $answer['file'] ?? $quizAnswer->image,
                                'correct' => isset($answer['correct']),
                                'updated_at' => time(),
                            ]);
                        } else {
                            $quizAnswer = QuizzesQuestionsAnswer::create([
                                'question_id' => $quizQuestion->id,
                                'creator_id' => $creator->id,
                                'image' => $answer['file'] ?? null,
                                'correct' => isset($answer['correct']),
                                'created_at' => time(),
                            ]);
                        }

                        if ($quizAnswer) {
                            QuizzesQuestionsAnswerTranslation::updateOrCreate(
                                [
                                    'quizzes_questions_answer_id' => $quizAnswer->id,
                                    'locale' => $data['locale'] ?? 'ar',
                                ],
                                [
                                    'title' => $answer['title'],
                                ]
                            );

                            $receivedAnswerIds[] = $quizAnswer->id;
                        }
                    }
                }


                // Delete removed answers
                $toDelete = $existingAnswers->keys()->diff($receivedAnswerIds);
                if ($toDelete->isNotEmpty()) {
                    QuizzesQuestionsAnswer::whereIn('id', $toDelete)->delete();
                }
            }

            removeContentLocale();
            $quizQuestion->refresh()->load([
                'quiz.creator',
                'quiz.translations',
                'translations',
                'quizzesQuestionsAnswers.translations'
            ]);

            return response()->json([
                'code' => 200,
                'message' => 'Question updated successfully',
                'data' => $quizQuestion
            ], 200);
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
        } catch (\Exception $ex) {
            return response()->json([
                'code' => 500,
                'message' => 'An error occurred',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function getQuestionsByQuiz($url_name, Request $request, $quiz_id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $quiz = Quiz::with(['quizQuestions' => function ($query) {
            $query->orderBy('order', 'asc');
            $query->with('quizzesQuestionsAnswers');
        }])
            ->find($quiz_id);

        if (!$quiz) {
            return response()->json([
                'code' => 404,
                'message' => 'Quiz not found'
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Quiz with questions fetched successfully',
            'data' => [
                'quiz' => $quiz,

            ]
        ]);
    }

    public function deletequestion($url_name, Request $request, $id)
    {
        try {
            $organization = Organization::where('url_name', $url_name)->first();
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }

            $question = QuizzesQuestion::find($id);

            if (!$question) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Question not found.'
                ], 404);
            }

            $question->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Question deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
