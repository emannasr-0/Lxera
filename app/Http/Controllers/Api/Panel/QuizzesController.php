<?php

namespace App\Http\Controllers\Api\Panel;

use App\Models\Webinar;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\QuizResource;
use App\Models\Api\Quiz;
use App\Models\Api\QuizzesResult;
use App\Models\Api\WebinarChapterItem;
use App\Models\Role;
use App\Models\Translation\QuizTranslation;
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
                'data'=>$data
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
}
