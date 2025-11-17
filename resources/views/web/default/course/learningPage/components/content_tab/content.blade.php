@php
    $icon = '';
    $hintText= '';

    if ($type == \App\Models\WebinarChapter::$chapterSession) {
        $icon = 'video';
        $hintText = dateTimeFormat($item->date, 'j M Y  H:i') . ' | ' . $item->duration . ' ' . trans('public.min');
    } elseif ($type == \App\Models\WebinarChapter::$chapterFile) {
        $hintText = $item->file_type . ($item->volume > 0 ? ' | '.$item->getVolume() : '');

        $icon = $item->getIconByType();
    } elseif ($type == \App\Models\WebinarChapter::$chapterTextLesson) {
        $icon = 'file-text';
        $hintText= $item->study_time . ' ' . trans('public.min');
    }

    $checkSequenceContent = $item->checkSequenceContent();

    $sequenceContentHasError = (!empty($checkSequenceContent) and (!empty($checkSequenceContent['all_passed_items_error']) or !empty($checkSequenceContent['access_after_day_error'])));

@endphp

<div class=" js-font-resize  d-flex align-items-start p-10 cursor-pointer {{ (!empty($checkSequenceContent) and $sequenceContentHasError) ? 'js-sequence-content-error-modal' : 'tab-item' }}"
     data-type="{{ $type }}"
     data-id="{{ $item->id }}"
     data-passed-error="{{ !empty($checkSequenceContent['all_passed_items_error']) ? $checkSequenceContent['all_passed_items_error'] : '' }}"
     data-access-days-error="{{ !empty($checkSequenceContent['access_after_day_error']) ? $checkSequenceContent['access_after_day_error'] : '' }}"
>

        <span class=" js-font-resize chapter-icon bg-acadima-pink mr-10">
            <i data-feather="{{ $icon }}" class=" js-font-resize text-light" width="16" height="16"></i>
        </span>

    <div>
        <div class=" js-font-resize ">
            <span class=" js-font-resize font-weight-500 font-14 text-dark d-block">{{ $item->title }}</span>
            <span class=" js-font-resize font-12 text-gray d-block">{{ $hintText }}</span>
        </div>

        @if ($user->isUser())

            <div class=" js-font-resize tab-item-info mt-15">
                <p class=" js-font-resize font-12 text-gray d-block">
                    @php
                        $description = !empty($item->description) ? $item->description : (!empty($item->summary) ? $item->summary : '');
                    @endphp

                    {!! truncate($description, 150) !!}
                </p>

                <div class=" js-font-resize d-flex align-items-center justify-content-between mt-15">
                    <label class=" js-font-resize mb-0 mr-10 cursor-pointer font-weight-normal font-14 text-dark-blue" for="readToggle{{ $type }}{{ $item->id }}">{{ trans('public.i_passed_this_lesson') }}</label>
                    <div class=" js-font-resize custom-control custom-switch">
                        <input type="checkbox" @if($sequenceContentHasError) disabled @endif id="readToggle{{ $type }}{{ $item->id }}" data-item-id="{{ $item->id }}" data-item="{{ $type }}_id" value="{{ $item->webinar_id }}" class=" js-font-resize js-passed-lesson-toggle custom-control-input" @if(!empty($item->checkPassedItem())) checked @endif>
                        <label class=" js-font-resize custom-control-label" for="readToggle{{ $type }}{{ $item->id }}"></label>
                    </div>
                </div>
            </div>

        @endif
    </div>
</div>
