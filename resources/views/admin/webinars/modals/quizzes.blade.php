<!-- Modal -->
<div class=" js-font-resize d-none" id="quizzesModal">
    <h3 class=" js-font-resize section-title after-line font-20 text-dark-blue mb-25">{{ trans('public.add_quiz') }}</h3>

    <div class=" js-font-resize js-form" data-action="{{ getAdminPanelUrl() }}/webinar-quiz/store">
        <input type="hidden" name="webinar_id" value="{{  !empty($webinar) ? $webinar->id :''  }}">

        <div class=" js-font-resize form-group mt-15">
            <label class=" js-font-resize input-label d-block">{{ trans('public.select_a_quiz') }}</label>

            <select name="quiz_id" class=" js-font-resize js-ajax-quiz_id form-control quiz-select2" data-placeholder="{{ trans('public.add_quiz') }}">
                <option disabled selected></option>
                @if(!empty($webinar))
                    @foreach($teacherQuizzes as $teacherQuiz)
                        <option value="{{ $teacherQuiz->id }}">{{ $teacherQuiz->title }}</option>
                    @endforeach
                @endif
            </select>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.chapter') }}</label>
            <select class=" js-font-resize js-ajax-chapter_id custom-select" name="chapter_id">
                <option value="">{{ trans('admin/main.no_chapter') }}</option>

                @if(!empty($chapters))
                    @foreach($chapters as $chapter)
                        <option value="{{ $chapter->id }}">{{ $chapter->title }}</option>
                    @endforeach
                @endif
            </select>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
            <button type="button" id="saveQuiz" class=" js-font-resize btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class=" js-font-resize btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </div>
</div>
