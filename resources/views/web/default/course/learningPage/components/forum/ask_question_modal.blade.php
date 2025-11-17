<div id="askNewQuestionModal" class=" js-font-resize d-none">
    <div class=" js-font-resize custom-modal-body">
        <h2 class=" js-font-resize section-title after-line">{{ trans('update.new_question') }}</h2>

        <form action="{{ $course->getForumPageUrl() }}/store" class=" js-font-resize mt-20">
            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('public.title') }}</label>
                <input type="text" name="title" class=" js-font-resize form-control" value=""/>
                <span class=" js-font-resize invalid-feedback"></span>
            </div>

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
                <textarea name="description" rows="5" class=" js-font-resize form-control"></textarea>
                <span class=" js-font-resize invalid-feedback"></span>
            </div>

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('update.attach_a_file') }} ({{ trans('public.optional') }})</label>

                <div class=" js-font-resize input-group mr-10">
                    <div class=" js-font-resize input-group-prepend">
                        <button type="button" class=" js-font-resize input-group-text panel-file-manager" data-input="questionAttachmentInput_record" data-preview="holder">
                            <i data-feather="upload" width="18" height="18" class=" js-font-resize text-white"></i>
                        </button>
                    </div>
                    <input type="text" name="attach" id="questionAttachmentInput_record" value="" class=" js-font-resize form-control" placeholder=""/>
                </div>
            </div>

            <div class=" js-font-resize d-flex align-items-center justify-content-end mt-3">
                <button type="button" class=" js-font-resize js-save-question btn btn-sm btn-primary">{{ trans('admin/main.post') }}</button>
                <button type="button" class=" js-font-resize close-swl btn btn-sm btn-danger ml-2">{{ trans('public.close') }}</button>
            </div>
        </form>
    </div>
</div>
