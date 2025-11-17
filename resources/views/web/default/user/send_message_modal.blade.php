<div class=" js-font-resize d-none" id="sendMessageModal">
    <h3 class=" js-font-resize section-title after-line font-20 text-dark-blue mb-25">{{ trans('site.send_message') }}</h3>

    <form action="/users/{{ $user->id }}/send-message" method="post">
        {{ csrf_field() }}

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.title') }}</label>
            <input type="text" name="title" class=" js-font-resize form-control"/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.email') }}</label>
            <input type="text" name="email" class=" js-font-resize form-control"/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
            <textarea name="description" class=" js-font-resize form-control" rows="6"></textarea>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label font-weight-500">{{ trans('site.captcha') }}</label>
            <div class=" js-font-resize row align-items-center">
                <div class=" js-font-resize col">
                    <input type="text" name="captcha" class=" js-font-resize form-control">

                    <div class=" js-font-resize invalid-feedback"></div>
                </div>
                <div class=" js-font-resize col d-flex align-items-center">
                    <img id="captchaImageComment" class=" js-font-resize captcha-image" src="">

                    <button type="button" class=" js-font-resize js-refresh-captcha btn-transparent ml-15">
                        <i data-feather="refresh-ccw" width="24" height="24" class=" js-font-resize "></i>
                    </button>
                </div>
            </div>
        </div>

        <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
            <button type="button" class=" js-font-resize js-send-message-submit btn btn-primary">{{ trans('site.send_message') }}</button>
            <button type="button" class=" js-font-resize btn btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
        </div>
    </form>
</div>
