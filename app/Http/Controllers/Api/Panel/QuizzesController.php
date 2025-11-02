<?php

namespace App\Http\Controllers\Api\Panel;

use App\Models\Webinar;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\QuizResource;
use App\Models\Api\Quiz;
use App\Models\Api\QuizzesResult;
use App\Models\Api\WebinarChapterItem;
use App\Models\Api\Organization;
use App\Models\Api\QuizzesQuestion;
use App\Models\Api\QuizzesQuestionsAnswer;
use App\Models\Role;
use App\Models\Translation\QuizTranslation;
use App\Models\Translation\QuizzesQuestionsAnswerTranslation;
use App\Models\Translation\QuizzesQuestionTranslation;
use App\Models\WebinarChapter;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizzesController extends Controller
{
    public function get_webinars_quizzes(Request $request)
    {
        $user = auth('api')->user();
        $webinars = Webinar::where(function ($query) use ($user) {
            $query->where('teacher_id', $user->id)
                ->orWhere('creator_id', $user->id)
                ->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                    $query->where('teacher_id', $user->id);
                });
        })->get();

        $locale = $request->get('locale', app()->getLocale());

        $data = [
            'pageTitle' => trans('quiz.new_quiz_page_title'),
            'webinars' => $webinars,
            'userLanguages' => getUserLanguagesLists(),
            'locale' => mb_strtolower($locale),
            'defaultLocale' => getDefaultLocale(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    public function results(Request $request)
    {

        $user = auth('api')->user();

        if (!$user->isUser()) {
            $quizzes = Quiz::where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhereHas('webinar', function ($query) use ($user) {
                        $query->where('teacher_id', $user->id)
                            ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                                $q->where('teacher_id', $user->id);
                            });
                    });
            })
                // ->where('status', 'active')
                ->get();

            $quizzesIds = $quizzes->pluck('id')->toArray();

            $query = QuizzesResult::whereIn('quiz_id', $quizzesIds);

            $studentsIds = $query->pluck('user_id')->toArray();
            $allStudents = User::select('id', 'full_name')->whereIn('id', $studentsIds)->get();

            $quizResultsCount = $query->count();
            $quizAvgGrad = round($query->avg('user_grade'), 2);
            $waitingCount = deepClone($query)->where('status', \App\Models\QuizzesResult::$waiting)->count();
            $passedCount = deepClone($query)->where('status', \App\Models\QuizzesResult::$passed)->count();
            $successRate = ($quizResultsCount > 0) ? round($passedCount / $quizResultsCount * 100) : 0;

            $query = $this->resultFilters($request, deepClone($query));

            $quizzesResults = $query->with([
                'quiz' => function ($query) {
                    $query->with(['quizQuestions', 'creator', 'webinar']);
                },
                'user'
            ])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('quiz.results'),
                'quizzesResults' => $quizzesResults,
                'quizResultsCount' => $quizResultsCount,
                'successRate' => $successRate,
                'quizAvgGrad' => $quizAvgGrad,
                'waitingCount' => $waitingCount,
                'quizzes' => $quizzes,
                'allStudents' => $allStudents
            ];

            return response()->json([
                'data' => $data
            ]);
        }

        abort(404);
    }

    public function resultFilters(Request $request, $query)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $quiz_id = $request->get('quiz_id', null);
        $total_mark = $request->get('total_mark', null);
        $status = $request->get('status', null);
        $user_id = $request->get('user_id', null);
        $instructor = $request->get('instructor', null);
        $open_results = $request->get('open_results', null);

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($quiz_id) and $quiz_id != 'all') {
            $query->where('quiz_id', $quiz_id);
        }

        if ($total_mark) {
            $query->where('total_mark', $total_mark);
        }

        if (!empty($user_id) and $user_id != 'all') {
            $query->where('user_id', $user_id);
        }

        if ($instructor) {
            $userIds = User::whereIn('role_name', [Role::$teacher, Role::$organization])
                ->where('full_name', 'like', '%' . $instructor . '%')
                ->pluck('id')->toArray();

            $query->whereIn('creator_id', $userIds);
        }

        if ($status and $status != 'all') {
            $query->where('status', strtolower($status));
        }

        if (!empty($open_results)) {
            $query->where('status', 'waiting');
        }

        return $query;
    }

    public function store(Request $request)
    {
        // $data = $request->get('ajax')['new'];
        $data = $request->all();
        $locale = $data['locale'] ?? getDefaultLocale();

        // Validation rules
        $rules = [
            'title' => 'required|max:255',
            'webinar_id' => 'nullable',
            'pass_mark' => 'required',
        ];

        // Validate input
        $validate = Validator::make($data, $rules);

        if ($validate->fails()) {
            return response()->json([
                'code' => 422,
                'errors' => $validate->errors()
            ], 422);
        }

        // Get the authenticated user
        $user = auth('api')->user();

        $webinar = null;
        $chapter = null;
        if (!empty($data['webinar_id'])) {
            $webinar = Webinar::where('id', $data['webinar_id'])
                ->where(function ($query) use ($user) {
                    $query->where('teacher_id', $user->id)
                        ->orWhere('creator_id', $user->id)
                        ->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                            $query->where('teacher_id', $user->id);
                        });
                })->first();

            if (!empty($webinar) && !empty($data['chapter_id'])) {
                $chapter = WebinarChapter::where('id', $data['chapter_id'])
                    ->where('webinar_id', $webinar->id)
                    ->first();
            }
        }

        // Create the quiz
        $quiz = Quiz::create([
            'webinar_id' => !empty($webinar) ? $webinar->id : null,
            'chapter_id' => !empty($chapter) ? $chapter->id : null,
            'creator_id' => $user->id,
            'attempt' => $data['attempt'] ?? null,
            'pass_mark' => $data['pass_mark'],
            'time' => $data['time'] ?? null,
            'status' => (!empty($data['status']) && $data['status'] == 'on') ? Quiz::ACTIVE : Quiz::INACTIVE,
            'certificate' => (!empty($data['certificate']) && $data['certificate'] == 'on'),
            'display_questions_randomly' => (!empty($data['display_questions_randomly']) && $data['display_questions_randomly'] == 'on'),
            'expiry_days' => (!empty($data['expiry_days']) && $data['expiry_days'] > 0) ? $data['expiry_days'] : null,
            'created_at' => time(),
        ]);

        // After quiz creation, update translation
        if (!empty($quiz)) {
            QuizTranslation::updateOrCreate([
                'quiz_id' => $quiz->id,
                'locale' => mb_strtolower($locale),
            ], [
                'title' => $data['title'],
            ]);

            // Create a chapter item if the quiz is associated with a chapter
            if (!empty($quiz->chapter_id)) {
                WebinarChapterItem::makeItem($quiz->creator_id, $quiz->chapter_id, $quiz->id, WebinarChapterItem::$chapterQuiz);
            }
        }

        // Send notification to all students if a webinar is associated
        // if (!empty($webinar)) {
        //     $webinar->sendNotificationToAllStudentsForNewQuizPublished($quiz);
        // }

        // Return success response with quiz details
        return response()->json([
            'code' => 200,
            'message' => 'Quiz created successfully.',
            'quiz' => $quiz,
        ]);
    }
    public function delete($url_name, Request $request, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

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
    public function index(Request $request)
    {
        $user = auth('api')->user();

        $allQuizzesLists = Quiz::select('id', 'webinar_id')
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhereHas('webinar', function ($query) use ($user) {
                        $query->where('teacher_id', $user->id)
                            ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                                $q->where('teacher_id', $user->id);
                            });
                    });
            })
            // ->where('status', 'active')
            ->get();


        $query = Quiz::where(function ($query) use ($user) {
            $query->where('creator_id', $user->id)
                ->orWhereHas('webinar', function ($query) use ($user) {
                    $query->where('teacher_id', $user->id)
                        ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                            $q->where('teacher_id', $user->id);
                        });
                });
        });

        $quizzesCount = deepClone($query)->count();

        $quizFilters = $this->filters($request, $query);

        $quizzes = $quizFilters->with([
            'webinar',
            'quizQuestions',
            'quizResults',
        ])->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        $userSuccessRate = [];
        $questionsCount = 0;
        $userCount = 0;

        foreach ($quizzes as $quiz) {

            $countSuccess = $quiz->quizResults
                ->where('status', \App\Models\QuizzesResult::$passed)
                ->pluck('user_id')
                ->count();

            $rate = 0;
            if ($countSuccess) {
                $rate = round($countSuccess / $quiz->quizResults->count() * 100);
            }

            $quiz->userSuccessRate = $rate;

            $questionsCount += $quiz->quizQuestions->count();
            $userCount += $quiz->quizResults
                ->pluck('user_id')
                ->count();
        }

        $data = [
            'pageTitle' => trans('quiz.quizzes_list_page_title'),
            'quizzes' => $quizzes,
            // 'userSuccessRate' => $userSuccessRate,
            // 'questionsCount' => $questionsCount,
            'quizzesCount' => $quizzesCount,
            // 'userCount' => $userCount,
            // 'allQuizzesLists' => $allQuizzesLists
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    public function filters(Request $request, $query)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $quiz_id = $request->get('quiz_id');
        $total_mark = $request->get('total_mark');
        $status = $request->get('status');
        $active_quizzes = $request->get('active_quizzes');


        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($quiz_id) and $quiz_id != 'all') {
            $query->where('id', $quiz_id);
        }

        if ($status and $status !== 'all') {
            $query->where('status', strtolower($status));
        }

        if (!empty($active_quizzes)) {
            $query->where('status', 'active');
        }

        if ($total_mark) {
            $query->where('total_mark', '>=', $total_mark);
        }

        return $query;
    }
    public function show($id)
    {
        $quiz = Quiz::where('id', $id)
            ->where('status', WebinarChapter::$chapterActive)->first();
        abort_unless($quiz, 404);

        if ($error = $quiz->canViewError()) {
            //       return $this->failure($error, 403, 403);
        }
        $resource = new QuizResource($quiz);
        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $resource);
    }

    public function created(Request $request)
    {
        $user = apiAuth();
        $quizzes = $user->userCreatedQuizzes()->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'desc')->get()->map(function ($quiz) {
                return $quiz->details;
            });

        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
            'quizzes' => $quizzes
        ]);
    }

    public function notParticipated(Request $request)
    {
        $user = apiAuth();
        $webinarIds = $user->getPurchasedCoursesIds();

        $quizzes = Quiz::whereIn('webinar_id', $webinarIds)
            ->where('status', 'active')
            ->whereDoesntHave('quizResults', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->handleFilters()
            ->orderBy('created_at', 'desc')
            ->get()->map(function ($quiz) {
                return $quiz->details;
            });

        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
            'quizzes' => $quizzes
        ]);
    }

    public function resultsByQuiz($quizId)
    {

        $user = apiAuth();
        $query = QuizzesResult::where('user_id', $user->id)
            ->where('quiz_id', $quizId);

        abort_unless(deepClone($query)->count(), 404);

        $result = (deepClone($query)->where('status', QuizzesResult::$passed)->first()) ?: null;
        if (!$result) {
            $result = deepClone($query)->latest()->first();
        }


        return apiResponse2(
            1,
            'retrieved',
            trans('api.public.retrieved'),
            $result->details
        );
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

        if ($data['table'] === 'webinar_chapters' && !empty($itemIds)) {
            foreach ($itemIds as $order => $id) {
               WebinarChapter::where('id', $id)
                  
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
