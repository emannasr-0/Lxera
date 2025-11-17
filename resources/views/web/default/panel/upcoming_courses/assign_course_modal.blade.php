<div id="upcomingAssignCourseModal" class=" js-font-resize " data-action="/panel/upcoming_courses/{{ $upcomingCourse->id }}/assign-course">
    <div class=" js-font-resize custom-modal-body">
        <h2 class=" js-font-resize section-title after-line">{{ trans('update.assign_published_course') }}</h2>

        <div class=" js-font-resize mt-20">
            <input type="hidden" name="upcoming_id" value="{{ $upcomingCourse->id }}">

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('product.course') }}</label>
                <select name="course" class=" js-font-resize js-ajax-course form-control js-select2">
                    <option value="">{{ trans('update.select_a_course') }}</option>
                    @foreach($webinars as $webinar)
                        <option value="{{ $webinar->id }}">{{ $webinar->title }}</option>
                    @endforeach
                </select>
                <div class=" js-font-resize invalid-feedback d-block"></div>
            </div>

            <div class=" js-font-resize d-flex align-items-center justify-content-end mt-20">
                <button type="button" class=" js-font-resize js-save-assign-course btn btn-sm btn-primary">{{ trans('public.save') }}</button>
                <button type="button" class=" js-font-resize close-swl btn btn-sm btn-danger ml-2">{{ trans('public.close') }}</button>
            </div>
        </div>
    </div>
</div>
