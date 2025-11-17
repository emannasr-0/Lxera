@php
    $checkSequenceContent = $item->checkSequenceContent();
   $sequenceContentHasError = (!empty($checkSequenceContent) and (!empty($checkSequenceContent['all_passed_items_error']) or !empty($checkSequenceContent['access_after_day_error'])));
@endphp


<div class=" js-font-resize {{ (!empty($checkSequenceContent) and $sequenceContentHasError) ? 'js-sequence-content-error-modal' : 'tab-item' }} p-10 cursor-pointer {{ $class ?? '' }}"
     data-type="{{ $type }}"
     data-id="{{ $item->id }}"
     data-passed-error="{{ !empty($checkSequenceContent['all_passed_items_error']) ? $checkSequenceContent['all_passed_items_error'] : '' }}"
     data-access-days-error="{{ !empty($checkSequenceContent['access_after_day_error']) ? $checkSequenceContent['access_after_day_error'] : '' }}"
>

    <div class=" js-font-resize d-flex align-items-center">
        <span class=" js-font-resize chapter-icon bg-acadima-pink mr-10">
            <i data-feather="award" class=" js-font-resize text-light" width="16" height="16"></i>
        </span>

        <div class=" js-font-resize flex-grow-1">
            <span class=" js-font-resize font-weight-500 font-14 text-dark d-block">{{ $item->title }}</span>

            <div class=" js-font-resize d-flex align-items-center justify-content-between">
                <span class=" js-font-resize font-12 text-gray d-block">
                    @if(!empty($item->time))
                        {{ $item->time .' '. trans('public.min') }}
                    @else
                        {{ trans('update.unlimited_time') }}
                    @endif

                    {{ ($item->quizQuestions ? ' | ' . (($item->display_limited_questions and !empty($item->display_number_of_questions)) ? $item->display_number_of_questions : $item->quizQuestions->count()) .' '. trans('public.questions') : '') }}
                </span>

                @if(!empty($quiz->result_status))
                    @if($quiz->result_status == 'passed')
                        <span class=" js-font-resize font-12 text-primary">{{ trans('quiz.passed') }}</span>
                    @elseif($quiz->result_status == 'failed')
                        <span class=" js-font-resize font-12 text-danger">{{ trans('quiz.failed') }}</span>
                    @elseif($quiz->result_status == 'waiting')
                        <span class=" js-font-resize font-12 text-warning">{{ trans('quiz.waiting') }}</span>
                    @endif
                @endif
            </div>
        </div>

    </div>
</div>
