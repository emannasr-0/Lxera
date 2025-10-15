@extends(getTemplate() . '.auth.auth_layout')

@section('content')
    @php
        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';
        $siteGeneralSettings = getGeneralSettings();
    @endphp

    <style>
        .content {
            overflow: auto;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-content: center;
            align-items: center;
            flex-direction: column;
            flex-wrap: wrap;
        }
        .bg-secondary-acadima {
            background-color: #fff !important;

        }
        .border-radius-lg {
            border-radius: 20px !important;
        }
        .text-light {
            color: #fff !important;
        }
        .text-dark {
            color: #000 !important;
        }
        .cs-btn{
        background-color:#c14b93 !important;
        width:100%;
        height:50px;
        color: #fff;

    }
   
    /* .cs-btn:hover{
        background-color:#599FAF !important;
        color: #fff;

    } */
        .text-cyan {
            color: #CCF5FF;
        }
        .text-cyan:hover {
            color: #ccf5ffa4;
        }
        .text-pink {
        color: #c14b93 !important;
        font-size:16px;
        }
        .ltr {
        direction: ltr;
    }
    .btn-border-radius {
        border-radius: 20px !important;
        font-size: 14px !important;
    }
    .input-size {
        padding:0.75rem 1rem 0.75rem 1rem;
        height:60px !important;
        font-size:16px !important;

    }
    .input-flex {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        background-color: #fdfdff;
    }
    .border-none {
        border: none;
    }
    .ltr {
            direction: ltr;
        }
    </style>


    <div class="login-container p-3 p-lg-5 m-md-3 bg-secondary-acadima border-radius-lg shadow border">
        <div class="col-7 col-md-7 p-0 mb-0 mb-lg-5 mt-3 mt-md-auto mx-auto d-flex flex-column align-items-center">
            <img src="{{ asset('store/Acadima/acadima-logo.webp') }}" alt="logo" width="100%" class="">
            
        </div>

        <h1 class="font-20 font-weight-bold mt-3 ltr" style="display: flex; align-items: center; gap: 8px;">
                    <svg width="34" height="29" viewBox="0 0 34 29"   fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M22 27C22 23.3181 17.5228 20.3333 12 20.3333C6.47715 20.3333 2 23.3181 2 27M32 12L25.3333 18.6667L22 15.3333M12 15.3333C8.3181 15.3333 5.33333 12.3486 5.33333 8.66667C5.33333 4.98477 8.3181 2 12 2C15.6819 2 18.6667 4.98477 18.6667 8.66667C18.6667 12.3486 15.6819 15.3333 12 15.3333Z"
                        stroke="#c14b93" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <!-- {{ trans('auth.forget_password') }} -->
                  Forgot Password
            </h1>

        <div class="col-12 p-0">
            <div class="login-card">
                <form method="post" action="/forget-password" class="mt-35">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    @if ($registerMethod == 'mobile')
                        <div class="d-flex align-items-center wizard-custom-radio mb-20">
                            <div class="wizard-custom-radio-item flex-grow-1">
                                <input type="radio" name="type" value="email" id="emailType" class=""
                                    {{ (empty(old('type')) or old('type') == 'email') ? 'checked' : '' }}>
                                <label class="font-12 cursor-pointer px-15 py-10"
                                    for="emailType">
                                    {{ trans('public.email') }}
                                </label>
                            </div>

                            <div class="wizard-custom-radio-item flex-grow-1">
                                <input type="radio" name="type" value="mobile" id="mobileType" class=""
                                    {{ old('type') == 'mobile' ? 'checked' : '' }}>
                                <label class="font-12 cursor-pointer px-15 py-10"
                                    for="mobileType">{{ trans('public.mobile') }}</label>
                            </div>
                        </div>
                    @endif

                    <div class="js-email-fields form-group {{ old('type') == 'mobile' ? 'd-none' : '' }} ltr" >
                        <!-- <label class="input-label text-light" for="email">
                            {{ trans('public.email') }}:
                        </label> -->
                        <div class="border-radius-lg input-size form-control input-flex">
                            <img src="{{ asset('store/Images/Registration/Mail.svg') }}" alt="Mail" class="mb-1">
                            <input name="email" type="email" class="form-control @error('email') is-invalid @enderror border-none"
                                id="email" value="{{ old('email') }}" aria-describedby="emailHelp" placeholder="Email">
                        </div>

                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    @if ($registerMethod == 'mobile')
                        <div class="js-mobile-fields {{ old('type') == 'mobile' ? '' : 'd-none' }}">
                            @include('web.default.auth.register_includes.mobile_field')
                        </div>
                    @endif

                    @if (!empty(getGeneralSecuritySettings('captcha_for_forgot_pass')))
                        @include('web.default.includes.captcha_input')
                    @endif


                    <button type="submit"
                        class="btn bg-button-acadima btn-block mt-20 cs-btn btn-border-radius mr-auto">
                        <!-- {{ trans('auth.reset_password') }} -->
                          Reset Password
                    </button>
                </form>

                <div class="text-center mt-20">
                    <span
                        class=" text-dark d-inline-flex align-items-center justify-content-center">or</span>
                </div>

                <div class="text-center mt-1">
                    <span class="text-secondary">
                        <a href="/login" class="text-pink font-weight-bold">
                            <!-- {{ trans('auth.login') }} -->
                              Login
                        </a>
                    </span>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/parts/forgot_password.min.js"></script>
@endpush
