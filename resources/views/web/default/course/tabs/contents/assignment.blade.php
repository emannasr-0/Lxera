@php
    $checkSequenceContent = $assignment->checkSequenceContent();
    $sequenceContentHasError = (!empty($checkSequenceContent) and (!empty($checkSequenceContent['all_passed_items_error']) or !empty($checkSequenceContent['access_after_day_error'])));
@endphp

<div class=" js-font-resize accordion-row rounded-sm border mt-15 p-15">
    <div class=" js-font-resize d-flex align-items-center justify-content-between" role="tab" id="assignment_{{ $assignment->id }}">
        <div class=" js-font-resize d-flex align-items-center" href="#collapseAssignment{{ $assignment->id }}" aria-controls="collapseAssignment{{ $assignment->id }}" data-parent="#{{ $accordionParent }}" role="button" data-toggle="collapse" aria-expanded="true">

            <span class=" js-font-resize mr-15 chapter-icon chapter-content-icon">
                <i data-feather="feather" width="20" height="20" class=" js-font-resize text-gray"></i>
            </span>

            <span class=" js-font-resize font-weight-bold text-secondary font-14 file-title">{{ $assignment->title }}</span>
        </div>

        <i class=" js-font-resize collapse-chevron-icon" data-feather="chevron-down" height="20" href="#collapseAssignment{{ !empty($assignment) ? $assignment->id :'record' }}" aria-controls="collapseAssignment{{ !empty($assignment) ? $assignment->id :'record' }}" data-parent="#{{ $accordionParent }}" role="button" data-toggle="collapse" aria-expanded="true"></i>
    </div>

    <div id="collapseAssignment{{ $assignment->id }}" aria-labelledby="assignment_{{ $assignment->id }}" class=" js-font-resize  collapse" role="tabpanel">
        <div class=" js-font-resize panel-collapse">
            <div class=" js-font-resize text-gray">
                {!! nl2br(clean($assignment->description)) !!}
            </div>

            <div class=" js-font-resize d-flex align-items-center justify-content-between mt-20">

                <div class=" js-font-resize d-flex align-items-center">
                    <div class=" js-font-resize d-flex align-items-center text-gray text-center font-14 mr-20">
                        <i data-feather="clock" width="18" height="18" class=" js-font-resize text-gray mr-5"></i>
                        <span class=" js-font-resize line-height-1">{{ trans('update.min_grade') }}: {{ $assignment->pass_grade }}</span>
                    </div>
                </div>

                <div class=" js-font-resize ">
                    @if(!empty($checkSequenceContent) and $sequenceContentHasError)
                        <button
                            type="button"
                            class=" js-font-resize course-content-btns btn btn-sm btn-gray flex-grow-1 disabled js-sequence-content-error-modal"
                            data-passed-error="{{ !empty($checkSequenceContent['all_passed_items_error']) ? $checkSequenceContent['all_passed_items_error'] : '' }}"
                            data-access-days-error="{{ !empty($checkSequenceContent['access_after_day_error']) ? $checkSequenceContent['access_after_day_error'] : '' }}"
                        >{{ trans('public.read') }}</button>
                    @elseif(!empty($user) and $hasBought)
                        <a href="{{ $course->getLearningPageUrl() }}?type=assignment&item={{ $assignment->id }}" target="_blank" class=" js-font-resize course-content-btns btn btn-sm btn-primary">
                            {{ trans('public.read') }}
                        </a>
                    @else
                        <button type="button" class=" js-font-resize course-content-btns btn btn-sm btn-gray disabled {{ ((empty($user)) ? 'not-login-toast' : (!$hasBought ? 'not-access-toast' : '')) }}">
                            {{ trans('public.read') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
