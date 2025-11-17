@php
    $checkSequenceContent = $quiz->checkSequenceContent();
    $sequenceContentHasError = (!empty($checkSequenceContent) and (!empty($checkSequenceContent['all_passed_items_error']) or !empty($checkSequenceContent['access_after_day_error'])));
@endphp


<div class=" js-font-resize accordion-row rounded-sm border mt-15 p-15">
    <div class=" js-font-resize d-flex align-items-center justify-content-between" role="tab" id="quiz_{{ $quiz->id }}">
        <div class=" js-font-resize d-flex align-items-center" href="#collapseQuiz{{ !empty($tagId) }}{{ $quiz->id }}" aria-controls="collapseQuiz{{ !empty($tagId) }}{{ $quiz->id }}" data-parent="#{{ $accordionParent }}" role="button" data-toggle="collapse" aria-expanded="true">
            <span class=" js-font-resize mr-15 d-flex">
                <span class=" js-font-resize chapter-icon chapter-content-icon">
                <i data-feather="file-text" width="20" height="20" class=" js-font-resize text-secondary"></i>
                </span>
            </span>

            <div class=" js-font-resize ">
                <span class=" js-font-resize font-weight-bold font-14 text-secondary d-block">{{ $quiz->title }}</span>
            </div>
        </div>

        <i class=" js-font-resize collapse-chevron-icon" data-feather="chevron-down" height="20" href="#collapseQuiz{{ !empty($tagId) }}{{ !empty($quiz) ? $quiz->id :'record' }}" aria-controls="collapseQuiz{{ !empty($tagId) }}{{ !empty($quiz) ? $quiz->id :'record' }}" data-parent="#{{ $accordionParent }}" role="button" data-toggle="collapse" aria-expanded="true"></i>
    </div>

    <div id="collapseQuiz{{ !empty($tagId) }}{{ $quiz->id }}" aria-labelledby="quiz_{{ $quiz->id }}" class=" js-font-resize  collapse" role="tabpanel">
        <div class=" js-font-resize panel-collapse">
            <div class=" js-font-resize d-flex align-items-center mt-20">
                <div class=" js-font-resize d-flex align-items-center text-gray text-center font-14 mr-20">
                    <i data-feather="help-circle" width="18" height="18" class=" js-font-resize text-gray mr-5"></i>
                    <span class=" js-font-resize line-height-1">{{ $quiz->quizQuestions->count() }} {{ trans('public.questions') }}</span>
                </div>

                <div class=" js-font-resize d-flex align-items-center text-gray text-center font-14 mr-20">
                    <i data-feather="clock" width="18" height="18" class=" js-font-resize text-gray mr-5"></i>
                    <span class=" js-font-resize line-height-1">{{ $quiz->time }} {{ trans('public.min') }}</span>
                </div>

                <div class=" js-font-resize d-flex align-items-center text-gray text-center font-14 mr-20">
                    <i data-feather="check-square" width="18" height="18" class=" js-font-resize text-gray mr-5"></i>
                    <span class=" js-font-resize line-height-1">{{ trans('update.passed_grade') }}: {{ $quiz->pass_mark }}/{{ $quiz->quizQuestions->sum('grade') }}</span>
                </div>

                <div class=" js-font-resize d-flex align-items-center text-gray text-center font-14 mr-20">
                    <i data-feather="check-square" width="18" height="18" class=" js-font-resize text-gray mr-5"></i>
                    <span class=" js-font-resize line-height-1">{{ trans('update.attempts') }}: {{ (!empty($user) and !empty($quiz->result_count)) ? $quiz->result_count : '0' }}/{{ $quiz->attempt }}</span>
                </div>

                @if(!empty($user) and !empty($quiz->result_status))
                    <div class=" js-font-resize d-flex align-items-center text-gray text-center font-14 mr-20">
                        <i data-feather="check-square" width="18" height="18" class=" js-font-resize text-gray mr-5"></i>
                        <div class=" js-font-resize line-height-1 text-gray font-14 text-center">
                            <span class=" js-font-resize @if($quiz->result_status == 'passed') text-primary @elseif($quiz->result_status == 'failed') text-danger @else text-warning @endif">
                                @if($quiz->result_status == 'passed')
                                    {{ trans('quiz.passed') }}
                                @elseif($quiz->result_status == 'failed')
                                    {{ trans('quiz.failed') }}
                                @elseif($quiz->result_status == 'waiting')
                                    {{ trans('quiz.waiting') }}
                                @endif
                            </span>
                        </div>
                    </div>
                @endif

            </div>

            <div class=" js-font-resize d-flex justify-content-end mt-20">
                @if(!empty($user) and $quiz->can_try and $hasBought)
                    <a href="/panel/quizzes/{{ $quiz->id }}/start" class=" js-font-resize course-content-btns btn btn-sm btn-primary">{{ trans('quiz.quiz_start') }}</a>
                @else
                    <button type="button" class=" js-font-resize course-content-btns btn btn-sm btn-gray disabled {{ ((empty($user)) ? 'not-login-toast' : (!$hasBought ? 'not-access-toast' : (!$quiz->can_try ? 'can-not-try-again-quiz-toast' : ''))) }}">
                        {{ trans('quiz.quiz_start') }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
