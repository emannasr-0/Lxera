@extends(getTemplate().'.layouts.app')

@section('content')
    <div class="container">
        <div class="row login-container mx-20 mx-lg-0 shadow border">
            <div class="col-12 col-md-6 p-0">
                <img src="{{ getPageBackgroundSettings('certificate_validation') }}" class="img-cover" alt="Login">
            </div>

            <div class="col-12 col-md-6">

                <div class="login-card px-0">
                    <h1 class="font-20 font-weight-bold text-pink">{{ trans('site.certificate_validation') }}</h1>
                    <p class="font-14 text-dark mt-15">{{ trans('site.certificate_validation_hint') }}</p>


                    <form method="post" action="/certificate/validate" class="mt-35">
                        {{ csrf_field() }}


                        <div class="form-group">
                            <label class="input-label text-dark" for="code">{{ trans('public.certificate_id') }}:</label>
                            <input type="tel" name="certificate_code" class="form-control text-black" id="certificate_code" aria-describedby="certificate_idHelp">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label class="input-label text-dark">{{ trans('site.captcha') }}</label>
                            <div class="row d-flex flex-column flex-lg-row align-items-center">
                                <div class="col-12 col-lg">
                                    <input type="text" name="captcha" class="form-control text-black">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-12 col-lg d-flex align-items-center justify-content-md-start justify-content-center mt-3 mt-lg-0">
                                    <img id="captchaImageComment" class="captcha-image" src="">

                                    <button type="button" id="refreshCaptcha" class="btn-transparent bg-button-acadima ml-15 text-light">
                                        <i data-feather="refresh-ccw" width="24" height="24" class="text-light"></i>
                                    </button>
                                </div>
                            </div>

                        </div>

                        <button type="button" id="formSubmit" class="btn btn-acadima-primary btn-block mt-20">{{ trans('cart.validate') }}</button>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <div id="certificateModal" class="d-none">
        <h3 class="section-title after-line">{{ trans('site.certificate_is_valid') }}</h3>
        <div class="mt-25 d-flex flex-column align-items-center">
            <img src="/assets/default/img/check.png" alt="" width="120" height="117">
            <p class="mt-10 text-light">{{ trans('site.certificate_is_valid_hint') }}</p>
            <div class="w-75">

                <div class="mt-15 d-flex justify-content-between">
                    <span class="text-light font-weight-bold">{{ trans('quiz.student') }}:</span>
                    <span class="text-light modal-student"></span>
                </div>

                <div class="mt-10 d-flex justify-content-between">
                    <span class="text-light font-weight-bold">{{ trans('public.date') }}:</span>
                    <span class="text-light"><span class="modal-date"></span></span>
                </div>

                <div class="mt-10 d-flex justify-content-between">
                    <span class="text-light font-weight-bold">{{ trans('webinars.webinar') }}:</span>
                    <span class="text-light"><span class="modal-webinar"></span></span>
                </div>
            </div>
        </div>

        <div class="mt-30 d-flex align-items-center justify-content-end">
            <button type="button" class="btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
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
