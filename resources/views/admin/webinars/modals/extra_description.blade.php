<!-- Modal -->
<div class=" js-font-resize d-none" id="extraDescriptionForm">
    <h3 class=" js-font-resize section-title after-line font-20 text-dark-blue mb-25">{{ trans('update.add_items') }}</h3>

    <div class=" js-font-resize js-form" data-action="{{ getAdminPanelUrl() }}/webinar-extra-description/store">
        <input type="hidden" name="webinar_id" value="{{  !empty($webinar) ? $webinar->id :''  }}">
        <input type="hidden" name="type">

        <div class=" js-font-resize js-form-groups">
            @if(!empty(getGeneralSettings('content_translate')))
                <div class=" js-font-resize js-no-company-input form-group">
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

            <div class=" js-font-resize js-no-company-input form-group">
                <label class=" js-font-resize input-label">{{ trans('public.title') }}</label>
                <input type="text" name="value" class=" js-font-resize js-ajax-title form-control"/>
                <div class=" js-font-resize invalid-feedback"></div>
            </div>
        </div>

        <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
            <button type="button" id="saveExtraDescription" class=" js-font-resize btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class=" js-font-resize btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </div>
</div>
