<div class=" js-font-resize d-none" id="joinWebinarModal">
    <h3 class=" js-font-resize section-title after-line font-20 text-dark-blue mb-25">{{ trans('webinars.next_session_info') }}</h3>

    <div class=" js-font-resize mt-25">
        <div class=" js-font-resize row">
            <div class=" js-font-resize col-12 col-md-7">
                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">{{ trans('webinars.session_title') }}</label>
                    <input type="text" readonly class=" js-font-resize js-join-session-title form-control" value=""/>
                </div>
            </div>
            <div class=" js-font-resize col-12 col-md-5">
                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">&nbsp;</label>
                    <div class=" js-font-resize input-group">
                        <div class=" js-font-resize input-group-prepend">
                            <span class=" js-font-resize input-group-text">
                                <i data-feather="calendar" width="18" height="18" class=" js-font-resize text-white"></i>
                            </span>
                        </div>
                        <input type="text" readonly value="" class=" js-font-resize js-join-session-date form-control"/>
                    </div>
                </div>
            </div>
        </div>

        <div class=" js-font-resize row">
            <div class=" js-font-resize col-12">
                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
                    <textarea class=" js-font-resize js-join-session-description form-control" readonly rows="5"></textarea>
                </div>
            </div>
        </div>
    </div>

    <h3 class=" js-font-resize section-title after-line font-16 text-dark-blue mb-25">{{ trans('webinars.join_information') }}</h3>

    <div class=" js-font-resize row js-join-session-link-row">
        <div class=" js-font-resize col-12">
            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('public.link') }}</label>
                <div class=" js-font-resize input-group">
                    <div class=" js-font-resize input-group-prepend">
                        <button type="button" class=" js-font-resize input-group-text js-copy" data-input="link" data-toggle="tooltip" data-placement="top" title="{{ trans('public.copy') }}" data-copy-text="{{ trans('public.copy') }}" data-done-text="{{ trans('public.done') }}">
                            <i data-feather="copy" width="18" height="18" class=" js-font-resize text-white"></i>
                        </button>
                    </div>
                    <input type="text" name="link" readonly value="" class=" js-font-resize js-join-session-link form-control"/>
                </div>
            </div>
        </div>
    </div>

    <div class=" js-font-resize row">
        <div class=" js-font-resize col-12 col-md-6">
            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('webinars.system') }}</label>
                <input type="text" readonly class=" js-font-resize js-join-session-session_api form-control" value=""/>
            </div>
        </div>
        <div class=" js-font-resize col-12 col-md-6">
            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('auth.password') }}</label>
                <input type="text" readonly class=" js-font-resize js-join-session-api_secret form-control" value=""/>
            </div>
        </div>
    </div>

    <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
        <a href="" target="_blank" class=" js-font-resize js-join-session-link-action btn btn-sm btn-primary">{{ trans('footer.join') }}</a>
        <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
    </div>
</div>
