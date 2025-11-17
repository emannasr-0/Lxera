<!-- Modal -->
<div class=" js-font-resize d-none" id="webinarPrerequisitesModal">
    <h3 class=" js-font-resize section-title after-line font-20 text-dark-blue mb-25">{{ trans('public.add_prerequisites') }}</h3>

    <div class=" js-font-resize js-prerequisites-form" data-action="{{ getAdminPanelUrl() }}/prerequisites/store" >
        <input type="hidden" name="webinar_id" value="{{  !empty($webinar) ? $webinar->id :''  }}">

        <div class=" js-font-resize form-group mt-15">
            <label class=" js-font-resize input-label d-block">{{ trans('public.select_prerequisites') }}</label>
            <select id="prerequisitesSelect" name="prerequisite_id" class=" js-font-resize js-ajax-prerequisite_id form-control prerequisites-select" data-webinar-id="{{  !empty($webinar) ? $webinar->id : '' }}" data-placeholder="{{ trans('public.search_prerequisites') }}">

            </select>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group mt-30 d-flex align-items-center justify-content-between">
            <label class=" js-font-resize " for="str_requiredPrerequisitesSwitch">{{ trans('public.required') }}</label>
            <div class=" js-font-resize custom-control custom-switch">
                <input type="checkbox" name="required" class=" js-font-resize custom-control-input" id="str_requiredPrerequisitesSwitch">
                <label class=" js-font-resize custom-control-label" for="str_requiredPrerequisitesSwitch"></label>
            </div>
        </div>

        <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
            <button type="button" id="savePrerequisites" class=" js-font-resize btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class=" js-font-resize btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </div>
</div>
