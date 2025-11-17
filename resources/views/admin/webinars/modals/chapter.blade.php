<!-- Modal -->
<div class=" js-font-resize d-none" id="webinarChapterModal">
    <h3 class=" js-font-resize section-title after-line font-20 text-dark-blue mb-25">{{ trans('public.add_new_chapter') }}</h3>
    <form action="{{ getAdminPanelUrl() }}/chapters/store" method="post">
        <input type="hidden" name="webinar_id" value="{{  !empty($webinar) ? $webinar->id :''  }}">

        @if(!empty(getGeneralSettings('content_translate')))
            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('auth.language') }}</label>
                <select name="locale" class=" js-font-resize form-control ">
                    @foreach($userLanguages as $lang => $language)
                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                    @endforeach
                </select>
                @error('locale')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        @else
            <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
        @endif


        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.title') }}</label>
            <input type="text" name="title" class=" js-font-resize form-control" placeholder=""/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize js-chapter-status form-group mt-3">
            <div class=" js-font-resize d-flex align-items-center justify-content-between">
                <label class=" js-font-resize cursor-pointer input-label" for="chapterStatusSwitch_record">{{ trans('admin/main.active') }}</label>
                <div class=" js-font-resize custom-control custom-switch">
                    <input type="checkbox" name="status" checked class=" js-font-resize custom-control-input" id="chapterStatusSwitch_record">
                    <label class=" js-font-resize custom-control-label" for="chapterStatusSwitch_record"></label>
                </div>
            </div>
        </div>

        @if(getFeaturesSettings('sequence_content_status'))
            <div class=" js-font-resize form-group mt-2 d-flex align-items-center justify-content-between js-switch-parent">
                <label class=" js-font-resize js-switch cursor-pointer" for="checkAllContentsPassSwitch_record">{{ trans('update.check_all_contents_pass') }}</label>

                <div class=" js-font-resize custom-control custom-switch">
                    <input id="checkAllContentsPassSwitch_record" type="checkbox" name="check_all_contents_pass" class=" js-font-resize custom-control-input js-chapter-check-all-contents-pass">
                    <label class=" js-font-resize custom-control-label" for="checkAllContentsPassSwitch_record"></label>
                </div>
            </div>
        @endif

        <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
            <button type="button" id="saveChapter" class=" js-font-resize btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class=" js-font-resize btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </form>
</div>
