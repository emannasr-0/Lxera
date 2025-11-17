<!-- Modal -->
<div class=" js-font-resize d-none" id="interactiveFileModal">
    <h3 class=" js-font-resize section-title after-line font-20 text-dark-blue mb-25">{{ trans('update.new_interactive_file') }}</h3>
    <form action="{{ getAdminPanelUrl() }}/files/store" method="post" enctype="multipart/form-data">
        <input type="hidden" name="webinar_id" value="{{  !empty($webinar) ? $webinar->id :''  }}">
        <input type="hidden" name="storage" value="upload_archive" class=" js-font-resize ">
        <input type="hidden" name="file_type" value="archive" class=" js-font-resize ">

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
            <input type="text" name="title" class=" js-font-resize form-control" placeholder="{{ trans('forms.maximum_255_characters') }}"/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.chapter') }}</label>
            <select class=" js-font-resize custom-select" name="chapter_id">
                <option value="">{{ trans('admin/main.no_chapter') }}</option>

                @if(!empty($chapters))
                    @foreach($chapters as $chapter)
                        <option value="{{ $chapter->id }}">{{ $chapter->title }}</option>
                    @endforeach
                @endif
            </select>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize row">
            <div class=" js-font-resize col-6">
                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">{{ trans('update.interactive_type') }}</label>
                    <select name="interactive_type" class=" js-font-resize js-interactive-type form-control">
                        <option value="adobe_captivate">{{ trans('update.adobe_captivate') }}</option>
                        <option value="i_spring">{{ trans('update.i_spring') }}</option>
                        <option value="custom">{{ trans('update.custom') }}</option>
                    </select>
                    <div class=" js-font-resize invalid-feedback"></div>
                </div>
            </div>

            <div class=" js-font-resize col-6">
                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">{{ trans('public.accessibility') }}</label>
                    <select class=" js-font-resize custom-select" name="accessibility" required>
                        <option selected disabled>{{ trans('public.choose_accessibility') }}</option>
                        <option value="free">{{ trans('public.free') }}</option>
                        <option value="paid">{{ trans('public.paid') }}</option>
                    </select>
                    <div class=" js-font-resize invalid-feedback"></div>
                </div>
            </div>
        </div>

        <div class=" js-font-resize js-interactive-file-name-input form-group d-none">
            <label class=" js-font-resize input-label">{{ trans('update.interactive_file_name') }}</label>
            <input type="text" name="interactive_file_name" class=" js-font-resize js-ajax-interactive_file_name form-control" value="" placeholder="{{ trans('update.interactive_file_name_placeholder') }}"/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group js-file-path-input">
            <div class=" js-font-resize local-input input-group">
                <div class=" js-font-resize input-group-prepend">
                    <button type="button" class=" js-font-resize input-group-text admin-file-manager" data-input="file_path_record" data-preview="holder">
                        <i class=" js-font-resize fa fa-upload"></i>
                    </button>
                </div>
                <input type="text" name="file_path" id="file_path_record" value="" class=" js-font-resize js-ajax-file_path form-control" placeholder="{{ trans('update.choose_zip_file') }}"/>
                <div class=" js-font-resize invalid-feedback"></div>
            </div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
            <textarea name="description" class=" js-font-resize js-ajax-description form-control" rows="6"></textarea>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group mt-20">
            <div class=" js-font-resize d-flex align-items-center justify-content-between">
                <label class=" js-font-resize cursor-pointer input-label" for="fileStatusSwitch_record">{{ trans('public.active') }}</label>
                <div class=" js-font-resize custom-control custom-switch">
                    <input type="checkbox" name="status" class=" js-font-resize custom-control-input" id="fileStatusSwitch_record">
                    <label class=" js-font-resize custom-control-label" for="fileStatusSwitch_record"></label>
                </div>
            </div>
        </div>

        <div class=" js-font-resize form-group mb-1">
            <div class=" js-font-resize d-flex align-items-center justify-content-between">
                <label class=" js-font-resize cursor-pointer input-label" for="SequenceContentSwitch_record">{{ trans('update.sequence_content') }}</label>
                <div class=" js-font-resize custom-control custom-switch">
                    <input type="checkbox" name="sequence_content" class=" js-font-resize js-sequence-content-switch custom-control-input" id="SequenceContentSwitch_record">
                    <label class=" js-font-resize custom-control-label" for="SequenceContentSwitch_record"></label>
                </div>
            </div>
        </div>

        <div class=" js-font-resize js-sequence-content-inputs pl-2 d-none">
            <div class=" js-font-resize form-group mb-1">
                <div class=" js-font-resize d-flex align-items-center justify-content-between">
                    <label class=" js-font-resize cursor-pointer input-label" for="checkPreviousPartsSwitch_record">{{ trans('update.check_previous_parts') }}</label>
                    <div class=" js-font-resize custom-control custom-switch">
                        <input type="checkbox" checked name="check_previous_parts" class=" js-font-resize custom-control-input" id="checkPreviousPartsSwitch_record">
                        <label class=" js-font-resize custom-control-label" for="checkPreviousPartsSwitch_record"></label>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('update.access_after_day') }}</label>
                <input type="number" name="access_after_day" value="" class=" js-font-resize js-ajax-access_after_day form-control" placeholder="{{ trans('update.access_after_day_placeholder') }}"/>
                <div class=" js-font-resize invalid-feedback"></div>
            </div>
        </div>

        <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
            <button type="button" id="saveInteractiveFile" class=" js-font-resize btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class=" js-font-resize btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </form>
</div>
