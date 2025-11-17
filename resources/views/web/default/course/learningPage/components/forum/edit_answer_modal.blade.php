<div id="editQuestionAnswerModal" class=" js-font-resize d-none">
    <div class=" js-font-resize custom-modal-body">
        <h2 class=" js-font-resize section-title after-line">{{ trans('update.edit_answer') }}</h2>

        <form action="" class=" js-font-resize mt-20">

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
                <textarea name="description" rows="5" class=" js-font-resize form-control"></textarea>
                <span class=" js-font-resize invalid-feedback"></span>
            </div>

            <div class=" js-font-resize d-flex align-items-center justify-content-end mt-3">
                <button type="button" class=" js-font-resize js-save-question-answer btn btn-sm btn-primary">{{ trans('admin/main.post') }}</button>
                <button type="button" class=" js-font-resize close-swl btn btn-sm btn-danger ml-2">{{ trans('public.close') }}</button>
            </div>
        </form>
    </div>
</div>
