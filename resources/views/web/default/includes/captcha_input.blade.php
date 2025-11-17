<div class=" js-font-resize form-group">
    <label class=" js-font-resize input-label font-weight-500">{{ trans('site.captcha') }}</label>
    <div class=" js-font-resize row align-items-center">
        <div class=" js-font-resize col">
            <input type="text" name="captcha" class=" js-font-resize form-control @error('captcha')  is-invalid @enderror">
            @error('captcha')
            <div class=" js-font-resize invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div class=" js-font-resize col d-flex align-items-center">
            <img id="captchaImageComment" class=" js-font-resize captcha-image" src="">

            <button type="button" id="refreshCaptcha" class=" js-font-resize btn-transparent ml-15">
                <i data-feather="refresh-ccw" width="24" height="24" class=" js-font-resize "></i>
            </button>
        </div>
    </div>
</div>
