@php
    $days = ['saturday', 'sunday','monday','tuesday','wednesday','thursday','friday'];
@endphp

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/wrunner-html-range-slider-with-2-handles/css/wrunner-default-theme.css">
@endpush

<div class=" js-font-resize wizard-step-1">
    <h3 class=" js-font-resize font-20 text-dark font-weight-bold">{{ trans('update.meeting_time') }}</h3>

    <span class=" js-font-resize d-block mt-30 text-gray wizard-step-num">
        {{ trans('update.step') }} 4/4
    </span>

    <span class=" js-font-resize d-block font-16 font-weight-500 mt-30">{{ trans('update.what_time_is_better_for_the_meeting') }}</span>

    <div class=" js-font-resize mb-30 custom-control custom-checkbox mt-30 full-checkbox w-100">
        <input type="checkbox" name="flexible_date" value="1" class=" js-font-resize custom-control-input" id="date">
        <label class=" js-font-resize custom-control-label font-14 w-100" for="date">{{ trans('update.im_flexible') }}</label>
    </div>

    <div id="dateTimeCard">
        <div class=" js-font-resize mb-30 form-group d-flex align-items-center flex-wrap">
            @foreach($days as $day)
                <div class=" js-font-resize wizard-custom-checkbox">
                    <input type="radio" name="day[]" value="{{ $day }}" id="{{ $day }}" {{ (request()->get('day') == $day) ? 'checked' : '' }}/>
                    <label for="{{ $day }}" class=" js-font-resize cursor-pointer">{{ trans('panel.'.$day) }}</label>
                </div>
            @endforeach
        </div>

        <div
            class=" js-font-resize range"
            id="timeRange"
            data-minLimit="0"
            data-maxLimit="23"
        >
            <input type="hidden" name="min_time" value="0">
            <input type="hidden" name="max_time" value="23">

        </div>
    </div>
</div>

@push('scripts_bottom')
    <script src="/assets/vendors/wrunner-html-range-slider-with-2-handles/js/wrunner-jquery.js"></script>
@endpush
