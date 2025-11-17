@extends(getTemplate().'.layouts.app')

@section('content')
    <div class=" js-font-resize container">
        <div class=" js-font-resize row login-container mx-20 mx-lg-0 shadow border">
            <div class=" js-font-resize col-12 col-md-6 p-0">
                <img src="{{ getPageBackgroundSettings('certificate_validation') }}" class=" js-font-resize img-cover" alt="Login">
            </div>

            <div class=" js-font-resize col-12 col-md-6">

                <div class=" js-font-resize login-card px-0">
                    <h1 class=" js-font-resize font-20 font-weight-bold text-pink">{{ trans('site.certificate_validation') }}</h1>
                    <p class=" js-font-resize font-14 text-dark mt-15">{{ trans('site.certificate_validation_hint') }}</p>


                    <form method="post" action="/certificate/validate" class=" js-font-resize mt-35">
                        {{ csrf_field() }}


                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label text-dark" for="code">{{ trans('public.certificate_id') }}:</label>
                            <input type="tel" name="certificate_code" class=" js-font-resize form-control text-black" id="certificate_code" aria-describedby="certificate_idHelp">
                            <div class=" js-font-resize invalid-feedback"></div>
                        </div>

                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label text-dark">{{ trans('site.captcha') }}</label>
                            <div class=" js-font-resize row d-flex flex-column flex-lg-row align-items-center">
                                <div class=" js-font-resize col-12 col-lg">
                                    <input type="text" name="captcha" class=" js-font-resize form-control text-black">
                                    <div class=" js-font-resize invalid-feedback"></div>
                                </div>
                                <div class=" js-font-resize col-12 col-lg d-flex align-items-center justify-content-md-start justify-content-center mt-3 mt-lg-0">
                                    <img id="captchaImageComment" class=" js-font-resize captcha-image" src="">

                                    <button type="button" id="refreshCaptcha" class=" js-font-resize btn-transparent bg-button-acadima ml-15 text-light">
                                        <i data-feather="refresh-ccw" width="24" height="24" class=" js-font-resize text-light"></i>
                                    </button>
                                </div>
                            </div>

                        </div>

                        <button type="button" id="formSubmit" class=" js-font-resize btn btn-acadima-primary btn-block mt-20">{{ trans('cart.validate') }}</button>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <div id="certificateModal" class=" js-font-resize d-none">
        <h3 class=" js-font-resize section-title after-line">{{ trans('site.certificate_is_valid') }}</h3>
        <div class=" js-font-resize mt-25 d-flex flex-column align-items-center">
            <img src="/assets/default/img/check.png" alt="" width="120" height="117">
            <p class=" js-font-resize mt-10 text-light">{{ trans('site.certificate_is_valid_hint') }}</p>
            <div class=" js-font-resize w-75">

                <div class=" js-font-resize mt-15 d-flex justify-content-between">
                    <span class=" js-font-resize text-light font-weight-bold">{{ trans('quiz.student') }}:</span>
                    <span class=" js-font-resize text-light modal-student"></span>
                </div>

                <div class=" js-font-resize mt-10 d-flex justify-content-between">
                    <span class=" js-font-resize text-light font-weight-bold">{{ trans('public.date') }}:</span>
                    <span class=" js-font-resize text-light"><span class=" js-font-resize modal-date"></span></span>
                </div>

                <div class=" js-font-resize mt-10 d-flex justify-content-between">
                    <span class=" js-font-resize text-light font-weight-bold">{{ trans('webinars.webinar') }}:</span>
                    <span class=" js-font-resize text-light"><span class=" js-font-resize modal-webinar"></span></span>
                </div>
            </div>
        </div>

        <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
            <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
        </div>
    </div>

@endsection

@push('scripts_bottom')
    <script>
        var certificateNotFound = '{{ trans('site.certificate_not_found') }}';
        var close = '{{ trans('public.close') }}';
    </script>

    <script src="/assets/default/js/parts/certificate_validation.min.js"></script>
@endpush
