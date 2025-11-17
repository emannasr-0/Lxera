<li data-id="{{ !empty($bundleWebinar) ? $bundleWebinar->id :'' }}" class=" js-font-resize accordion-row bg-white rounded-sm panel-shadow mt-20 py-15 py-lg-30 px-10 px-lg-20">
    <div class=" js-font-resize d-flex align-items-center justify-content-between " role="tab" id="bundleWebinar_{{ !empty($bundleWebinar) ? $bundleWebinar->id :'record' }}">
        <div class=" js-font-resize font-weight-bold text-dark-blue" href="#collapseBundleWebinar{{ !empty($bundleWebinar) ? $bundleWebinar->id :'record' }}" aria-controls="collapseBundleWebinar{{ !empty($bundleWebinar) ? $bundleWebinar->id :'record' }}" data-parent="#bundleWebinarsAccordion" role="button" data-toggle="collapse" aria-expanded="true">
            <span>{{ (!empty($bundleWebinar) and !empty($bundleWebinar->webinar)) ? $bundleWebinar->webinar->title : trans('update.add_new_course') }}</span>
        </div>

        <div class=" js-font-resize d-flex align-items-center">
            <i data-feather="move" class=" js-font-resize move-icon mr-10 cursor-pointer" height="20"></i>

            @if(!empty($bundleWebinar))
                <div class=" js-font-resize btn-group dropdown table-actions mr-15">
                    <button type="button" class=" js-font-resize btn-transparent dropdown-toggle d-flex align-items-center justify-content-center" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i data-feather="more-vertical" height="20"></i>
                    </button>
                    <div class=" js-font-resize dropdown-menu">
                        <a href="/panel/bundle-webinars/{{ $bundleWebinar->id }}/delete" class=" js-font-resize delete-action btn btn-sm btn-transparent">{{ trans('public.delete') }}</a>
                    </div>
                </div>
            @endif

            <i class=" js-font-resize collapse-chevron-icon" data-feather="chevron-down" height="20" href="#collapseBundleWebinar{{ !empty($bundleWebinar) ? $bundleWebinar->id :'record' }}" aria-controls="collapseBundleWebinar{{ !empty($bundleWebinar) ? $bundleWebinar->id :'record' }}" data-parent="#bundleWebinarsAccordion" role="button" data-toggle="collapse" aria-expanded="true"></i>
        </div>
    </div>

    <div id="collapseBundleWebinar{{ !empty($bundleWebinar) ? $bundleWebinar->id :'record' }}" aria-labelledby="bundleWebinar_{{ !empty($bundleWebinar) ? $bundleWebinar->id :'record' }}" class=" js-font-resize  collapse @if(empty($bundleWebinar)) show @endif" role="tabpanel">
        <div class=" js-font-resize panel-collapse text-gray">
            <div class=" js-font-resize bundleWebinar-form" data-action="/panel/bundle-webinars/{{ !empty($bundleWebinar) ? $bundleWebinar->id . '/update' : 'store' }}">
                <input type="hidden" name="ajax[{{ !empty($bundleWebinar) ? $bundleWebinar->id : 'new' }}][bundle_id]" value="{{ !empty($bundle) ? $bundle->id :'' }}">

                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 col-lg-6">
                        <div class=" js-font-resize form-group mt-15">
                            <label class=" js-font-resize input-label d-block">{{ trans('panel.select_course') }}</label>
                            <select name="ajax[{{ !empty($bundleWebinar) ? $bundleWebinar->id : 'new' }}][webinar_id]" class=" js-font-resize js-ajax-webinar_id form-control {{ !empty($bundleWebinar) ? 'select2' : 'bundleWebinars-select2' }}" data-bundle-id="{{  !empty($bundle) ? $bundle->id : '' }}">
                                <option value="">{{ trans('panel.select_course') }}</option>

                                @if(!empty($webinars))
                                    @foreach($webinars as $webinar)
                                        <option value="{{ $webinar->id }}" {{ (!empty($bundleWebinar) and $bundleWebinar->webinar_id == $webinar->id) ? 'selected' : '' }}>{{ $webinar->title }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class=" js-font-resize invalid-feedback"></div>
                        </div>

                        <div class=" js-font-resize mt-5">
                            <p class=" js-font-resize font-12 text-gray">- {{ trans('update.bundle_webinars_required_hint') }}</p>
                        </div>

                    </div>
                </div>

                <div class=" js-font-resize mt-30 d-flex align-items-center">
                    <button type="button" class=" js-font-resize js-save-bundleWebinar btn btn-sm btn-primary">{{ trans('public.save') }}</button>

                    @if(empty($bundleWebinar))
                        <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 cancel-accordion">{{ trans('public.close') }}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</li>
