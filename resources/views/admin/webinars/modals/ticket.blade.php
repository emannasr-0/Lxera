<!-- Modal -->
<div class=" js-font-resize d-none" id="webinarTicketModal">
    <h3 class=" js-font-resize section-title after-line font-20 text-dark-blue mb-25">{{ trans('public.add_ticket') }}</h3>
    <div class=" js-font-resize js-form" data-action="{{ getAdminPanelUrl() }}/tickets/store">
        <input type="hidden" name="webinar_id" value="{{ !empty($webinar) ? $webinar->id :'' }}">

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
            <input type="text" name="title" class=" js-font-resize js-ajax-title form-control" placeholder="{{ trans('forms.maximum_64_characters') }}"/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.date') }}</label>
            <div class=" js-font-resize input-group">
                <div class=" js-font-resize input-group-prepend">
                    <span class=" js-font-resize input-group-text" id="dateRangeLabel">
                        <i class=" js-font-resize fa fa-calendar text-white"></i>
                    </span>
                </div>
                <input type="text" name="date" class=" js-font-resize js-ajax-date form-control date-range-picker" aria-describedby="dateRangeLabel"/>
                <div class=" js-font-resize invalid-feedback"></div>
            </div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.discount') }} <span class=" js-font-resize braces">(%)</span></label>
            <input type="text" name="discount" class=" js-font-resize js-ajax-discount form-control" placeholder="10"/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.capacity') }}</label>
            <input type="text" name="capacity" class=" js-font-resize js-ajax-capacity form-control" placeholder="{{ trans('forms.empty_means_unlimited') }}"/>
            <div class=" js-font-resize invalid-feedback"></div>
            <div class=" js-font-resize text-muted text-small mt-1">{{ trans('admin/main.price_plan_modal_capacity_hint') }}</div>
        </div>

        <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
            <button type="button" id="saveTicket" class=" js-font-resize btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class=" js-font-resize btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </div>
</div>
