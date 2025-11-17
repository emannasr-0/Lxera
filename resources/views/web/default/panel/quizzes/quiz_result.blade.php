@extends(getTemplate().'.layouts.app')

@section('content')
    <div class=" js-font-resize container">
        <section class=" js-font-resize mt-40">
            <h2 class=" js-font-resize font-weight-bold font-16 text-light">{{ $quiz->title }}</h2>
            <p class=" js-font-resize text-gray font-14 mt-5">
                <a href="{{ $quiz->webinar->getUrl() }}" target="_blank" class=" js-font-resize text-gray">{{ $quiz->webinar->title }}</a>
                | {{ trans('public.by') }}
                <span class=" js-font-resize font-weight-bold">
                    <a href="{{ $quiz->creator->getProfileUrl() }}" target="_blank" class=" js-font-resize "> {{ $quiz->creator->full_name }}</a>
                </span>
            </p>

            <div class=" js-font-resize activities-container shadow-sm rounded-lg mt-25 p-20 p-lg-35">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-6 col-md-3 d-flex align-items-center justify-content-center">
                        <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/58.svg" width="64" height="64" alt="">
                            <strong class=" js-font-resize font-30 font-weight-bold text-secondary mt-5">{{ $quiz->pass_mark }}/{{ $questionsSumGrade }}</strong>
                            <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('public.min') }} {{ trans('quiz.grade') }}</span>
                        </div>
                    </div>

                    <div class=" js-font-resize col-6 col-md-3 d-flex align-items-center justify-content-center">
                        <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/88.svg" width="64" height="64" alt="">
                            <strong class=" js-font-resize font-30 font-weight-bold text-secondary mt-5">{{ $numberOfAttempt }}/{{ $quiz->attempt }}</strong>
                            <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('quiz.attempts') }}</span>
                        </div>
                    </div>

                    <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                        <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/45.svg" width="64" height="64" alt="">
                            <strong class=" js-font-resize font-30 font-weight-bold text-secondary mt-5">{{ $quizResult->user_grade }}/{{  $questionsSumGrade }}</strong>
                            <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('quiz.your_grade') }}</span>
                        </div>
                    </div>

                    <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                        <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/44.svg" width="64" height="64" alt="">
                            <strong class=" js-font-resize font-30 font-weight-bold text-{{ ($quizResult->status == 'passed') ? 'primary' : ($quizResult->status == 'waiting' ? 'warning' : 'danger') }} mt-5">
                                {{ trans('quiz.'.$quizResult->status) }}
                            </strong>
                            <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('public.status') }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <section class=" js-font-resize mt-30 quiz-form">
            <form action="{{ !empty($newQuizStart) ? '/panel/quizzes/'. $newQuizStart->quiz->id .'/update-result' : '' }} " method="post">
                {{ csrf_field() }}
                <input type="hidden" name="quiz_result_id" value="{{ !empty($newQuizStart) ? $newQuizStart->id : ''}}" class=" js-font-resize form-control" placeholder=""/>
                <input type="hidden" name="attempt_number" value="{{  $numberOfAttempt }}" class=" js-font-resize form-control" placeholder=""/>
                <input type="hidden" class=" js-font-resize js-quiz-question-count" value="{{ $quizQuestions->count() }}"/>

                @foreach($quizQuestions as $key => $question)

                    <fieldset class=" js-font-resize question-step question-step-{{ $key + 1 }}">
                        <div class=" js-font-resize rounded-lg shadow-sm py-25 px-20">
                            <div class=" js-font-resize quiz-card">
                                <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class=" js-font-resize font-weight-bold font-16 text-secondary">{{ $question->title }}?</h3>
                                        <p class=" js-font-resize text-gray font-14 mt-5">
                                            <span>{{ trans('quiz.question_grade') }} : {{ $question->grade }}</span> | <span>{{ trans('quiz.your_grade') }} : {{ (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["grade"])) ? $userAnswers[$question->id]["grade"] : 0 }}</span>
                                        </p>
                                    </div>

                                    <div class=" js-font-resize rounded-sm border border-gray200 p-15 text-gray">{{ $key + 1 }}/{{ $quizQuestions->count() }}</div>
                                </div>
                                @if($question->type === \App\Models\QuizzesQuestion::$descriptive)

                                    <div class=" js-font-resize form-group mt-35">
                                        <label class=" js-font-resize input-label text-secondary">{{ trans('quiz.student_answer') }}</label>
                                        <textarea name="question[{{ $question->id }}][answer]" rows="10" disabled class=" js-font-resize form-control">{{ (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["answer"])) ? $userAnswers[$question->id]["answer"] : '' }}</textarea>
                                    </div>

                                    <div class=" js-font-resize form-group mt-35">
                                        <label class=" js-font-resize input-label text-secondary">{{ trans('quiz.correct_answer') }}</label>
                                        <textarea rows="10" name="question[{{ $question->id }}][correct_answer]" @if(empty($newQuizStart) or $newQuizStart->quiz->creator_id != $authUser->id) disabled @endif class=" js-font-resize form-control">{{ $question->correct }}</textarea>
                                    </div>

                                    @if(!empty($newQuizStart) and $newQuizStart->quiz->creator_id == $authUser->id)
                                        <div class=" js-font-resize form-group mt-35">
                                            <label class=" js-font-resize font-16 text-secondary">{{ trans('quiz.grade') }}</label>
                                            <input type="text" name="question[{{ $question->id }}][grade]" value="{{ (!empty($userAnswers[$question->id]) and !empty($userAnswers[$question->id]["grade"])) ? $userAnswers[$question->id]["grade"] : 0 }}" class=" js-font-resize form-control">
                                        </div>
                                    @endif

                                @else
                                    <div class=" js-font-resize question-multi-answers mt-35">
                                        @foreach($question->quizzesQuestionsAnswers as $key => $answer)
                                            <div class=" js-font-resize answer-item">
                                                @if($answer->correct)
                                                    <span class=" js-font-resize badge badge-primary text-dark-blue correct">{{ trans('quiz.correct') }}</span>
                                                @endif

                                                <input id="asw-{{ $answer->id }}" type="radio" disabled name="question[{{ $question->id }}][answer]" value="{{ $answer->id }}" {{ (!empty($userAnswers[$question->id]) and (int)$userAnswers[$question->id]["answer"] === $answer->id) ? 'checked' : '' }}>

                                                @if(!$answer->image)
                                                    <label for="asw-{{ $answer->id }}" class=" js-font-resize answer-label font-16 d-flex text-light align-items-center justify-content-center ">
                                                        <span class=" js-font-resize answer-title">
                                                            {{ $answer->title }}
                                                            @if(!empty($userAnswers[$question->id]) and (int)$userAnswers[$question->id]["answer"] ===  $answer->id)
                                                                <span class=" js-font-resize d-block">({{ trans('quiz.student_answer') }})</span>
                                                            @endif
                                                        </span>
                                                    </label>
                                                @else
                                                    <label for="asw-{{ $answer->id }}" class=" js-font-resize answer-label font-16 d-flex align-items-center text-light justify-content-center ">
                                                        <div class=" js-font-resize image-container">
                                                            @if(!empty($userAnswers[$question->id]) and (int)$userAnswers[$question->id]["answer"] ===  $answer->id)
                                                                <span class=" js-font-resize selected font-14">{{ trans('quiz.student_answer') }}</span>
                                                            @endif
                                                            <img src="{{ config('app_url') . $answer->image }}" class=" js-font-resize img-cover" alt="">
                                                        </div>
                                                    </label>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                @endforeach

                <div class=" js-font-resize row d-flex align-items-center m-10">
                    <button type="button" disabled class=" js-font-resize previous btn btn-sm btn-primary mr-md-10 mr-5">{{ trans('quiz.previous_question') }}</button>
                    <button type="button" class=" js-font-resize next btn btn-primary btn-sm mr-auto">{{ trans('quiz.next_question') }}</button>

                    @if(!empty($newQuizStart))
                        <button type="submit" class=" js-font-resize finish btn btn-sm btn-danger">{{ trans('public.finish') }}</button>
                    @endif
                </div>
            </form>
        </section>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/parts/quiz-start.min.js"></script>
@endpush
