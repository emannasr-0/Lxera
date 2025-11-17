<div id="changeChapterModalHtml" class=" js-font-resize d-none">
    <div class=" js-font-resize custom-modal-body">
        <h2 class=" js-font-resize section-title after-line">{{ trans('update.change_chapter') }}</h2>

        <div class=" js-font-resize js-content-form change-chapter-form mt-20" data-action="/panel/chapters/change">

            <input type="hidden" name="ajax[webinar_id]" class=" js-font-resize " value="{{ $webinar->id }}">
            <input type="hidden" name="ajax[item_id]" class=" js-font-resize js-item-id" value="">
            <input type="hidden" name="ajax[item_type]" class=" js-font-resize js-item-type" value="">

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('public.chapter') }}</label>

                <select name="ajax[chapter_id]" class=" js-font-resize js-ajax-chapter_id custom-select">
                    <option value="">{{ trans('update.select_chapter') }}</option>

                    @if(!empty($webinar->chapters) and count($webinar->chapters))
                        @foreach($webinar->chapters as $chapter)
                            <option value="{{ $chapter->id }}">{{ $chapter->title }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class=" js-font-resize d-flex align-items-center justify-content-end mt-3">
                <button type="button" class=" js-font-resize save-change-chapter btn btn-sm btn-primary">{{ trans('public.save') }}</button>
                <button type="button" class=" js-font-resize close-swl btn btn-sm btn-danger ml-2">{{ trans('public.close') }}</button>
            </div>

        </div>
    </div>
</div>
