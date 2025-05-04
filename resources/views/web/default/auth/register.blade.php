@extends(getTemplate() . '.auth.auth_layout')
@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
@endpush

@section('content')
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
        .cs-btn {
            background-color: #CCF5FF !important;
            width:100%;
        height:50px;
        }

        .cs-btn:hover {
            background-color: #599FAF !important;
            color: #fff;

        }

        .custom-control-label::after,
        .custom-control-label::before {
            left: initial !important;
            right: -1.5rem !important;
        }

        .iti__country-list {
            position: absolute;
            z-index: 2;
            list-style: none;
            text-align: left;
            padding: 0;
            margin: 0 0 0 -1px;
            box-shadow: 1px 1px 4px rgba(0, 0, 0, .2);
            background-color: #fff;
            color: #000 !important;
            border: 1px solid #fff;
            white-space: nowrap;
            max-height: 200px;
            overflow-y: scroll;
            -webkit-overflow-scrolling: touch;
            left: 0 !important;
            direction: ltr !important;
            width: 580%;
        }
        .iti__selected-dial-code {
            color: #000;
        }
        a {
            color: #CCF5FF !important;
        }
        a:hover {
            color: #599FAF !important;

        }
        .bg-secondary-acadima {
            background-color: #141F25 !important;
        }
        .text-cyan {
        color: #CCF5FF !important;
        font-size:16px;
        }
        .text-cyan:hover {
        color: #ccf5ffa4 !important;
        }
        .border-radius-lg {
            border-radius: 20px !important;
        }
        .btn-border-radius {
            border-radius: 20px !important;
            font-size: 16px !important;
        }
        .ltr {
            direction: ltr;
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
        }
        .border-none {
            border: none;
        }
        input:-internal-autofill-selected {
        background-color: #fdfdff !important;
    }
    </style>
    @php
        $siteGeneralSettings = getGeneralSettings();
    @endphp
    @php
        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';
        $showOtherRegisterMethod = getFeaturesSettings('show_other_register_method') ?? false;
        $showCertificateAdditionalInRegister = getFeaturesSettings('show_certificate_additional_in_register') ?? false;
        $selectRolesDuringRegistration = getFeaturesSettings('select_the_role_during_registration') ?? null;
    @endphp
    <div class="p-3 p-lg-5 m-md-3 bg-secondary-acadima col-sm-7 col-md-8 col-lg-4 border-radius-lg ltr">
        <div class="col-6 col-md-6 p-0 mb-0 mb-lg-5 mt-3 mt-md-auto mx-auto d-flex flex-column align-items-center">
            <img src="{{ asset('store/Acadima/acadima-logo.webp') }}" alt="logo" width="100%" class="">
            <h1 class="font-20 font-weight-bold mb-3 mt-3 ltr">
            <svg width="34" height="29" viewBox="0 0 34 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M22 27C22 23.3181 17.5228 20.3333 12 20.3333C6.47715 20.3333 2 23.3181 2 27M32 12L25.3333 18.6667L22 15.3333M12 15.3333C8.3181 15.3333 5.33333 12.3486 5.33333 8.66667C5.33333 4.98477 8.3181 2 12 2C15.6819 2 18.6667 4.98477 18.6667 8.66667C18.6667 12.3486 15.6819 15.3333 12 15.3333Z"
                    stroke="#CCF5FF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <!-- {{ trans('auth.signup') }} -->
              Register
        </h1>
        </div>

        

        {{-- show messages --}}
        @if (!empty(session()->has('msg')))
            <div class="alert alert-info alert-dismissible fade show mt-30" role="alert">
                {{ session()->get('msg') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form method="post" action="/register" class="mt-35" id="registerForm">

            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            @if (!empty($selectRolesDuringRegistration) and count($selectRolesDuringRegistration))
                <div class="form-group ltr">
                </div>
            @endif
            <!-- <div class="form-group">
                <label class="input-label text-light" for="full_name">الأسم الثلاثي باللغة العربية  *</label>

                <input name="full_name" type="text" value="{{ old('full_name') }}"
                    class="form-control @error('full_name') is-invalid @enderror" placeholder="أدخل الأسم ">
                @error('full_name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div> -->

            <div class="form-group ltr">
                <!-- <label class="input-label text-light" for="en_name">Full Name   *</label> -->
                
                <div class="border-radius-lg input-size form-control input-flex">
                    <img src="{{ asset('store/Images/Registration/User_01.svg') }}" alt="Mail" class="mb-1">
                    <input name="en_name" type="text" value="{{ old('en_name') }}"
                    class="form-control @error('en_name') is-invalid @enderror border-none" placeholder="Full Name">
                </div>

                    @error('en_name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            @if ($registerMethod == 'mobile')
                @include('web.default.auth.register_includes.mobile_field')

                @if ($showOtherRegisterMethod)
                    @include('web.default.auth.register_includes.email_field', ['optional' => false])
                @endif
            @else
                @include('web.default.auth.register_includes.email_field')


                @if ($showOtherRegisterMethod)
                    @include('web.default.auth.register_includes.mobile_field', ['optional' => false])
                @endif
            @endif




            <div class="password-section">

                <div class="form-group col-12 p-0 ltr">
                    <!-- <label class="input-label text-light" for="password">{{ trans('auth.password') }}:</label> -->
                    <div class="border-radius-lg input-size form-control input-flex">
                        <img src="{{ asset('store/Images/Registration/Lock.svg') }}" alt="Mail" class="mb-1">

                        <input name="password" type="password" class="form-control @error('password') is-invalid @enderror border-none"
                            id="password" aria-describedby="passwordHelp" placeholder="Password">
                        <span class="icon2" onclick="togglePasswordVisibility()">
                            <img id="toggleIcon" src="{{ asset('store/Images/Registration/Show.svg') }}" alt="Show" class="icon2">
                        </span>
                    </div>

                        @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
<!-- 
                <div class="form-group  col-12 p-0 pr-1 ">
                    <label class="input-label text-light" for="confirm_password">{{ trans('auth.retype_password') }}:</label>
                    <input name="password_confirmation" type="password"
                        class="form-control @error('password_confirmation') is-invalid @enderror" id="confirm_password"
                        aria-describedby="confirmPasswordHelp">
                    @error('password_confirmation')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div> -->

            </div>


            @if (!empty($referralSettings) and $referralSettings['status'])
                <div class="form-group ">
                    <label class="input-label text-light" for="referral_code">{{ trans('financial.referral_code') }}:</label>
                    <input name="referral_code" type="text"
                        class="form-control @error('referral_code') is-invalid @enderror" id="referral_code"
                        value="{{ !empty($referralCode) ? $referralCode : old('referral_code') }}"
                        aria-describedby="confirmPasswordHelp">
                    @error('referral_code')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            @endif

            @if (!empty(getGeneralSecuritySettings('captcha_for_register')))
                @include('web.default.includes.captcha_input')
            @endif
            <!--start-->

            <div class="form-group d-flex flex-row justify-content-start mt-30">
                <button type="submit" class="btn bg-button-acadima btn-lg cs-btn btn-border-radius mr-auto">
                    Register</i>
                </button>
            </div>

        </form>

        <div class="mt-20 text-center registertext ltr">
            <span>
                 Already have an account?
                <br>
                <a href="/login?{{  request()->getQueryString() }}" class="text-cyan font-weight-bold">Login</a>
            </span>
        </div>



    </div>
@endsection
@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
@endpush
<script>
    window.onload = function() {
        let form = document.getElementById('registerForm');
        console.log(form);
        console.log(registerForm);
        form.onsubmit = function(event) {
            event.preventDefault();
            let code = document.getElementsByClassName('iti__selected-dial-code')[0].innerHTML;
            console.log(code);

            document.getElementById('code').value = code;

            form.submit();

        }

        toggleHiddenType();

    }
</script>


{{-- type toggle --}}
<script>
    function toggleHiddenType() {
        console.log("toggleHiddenType");
        var select = document.getElementById("typeSelect");
        var hiddenDiplomaInput = document.getElementById("mySelect1");
        var hiddenDiplomaLabel = document.getElementById("degree");
        var hiddenBundleInput = document.getElementById("bundle_id");
        var hiddenDiplomaLabel1 = document.getElementById("hiddenLabel1");
        let diplomasSection = document.getElementById("diplomas_section");

        var hiddenCourseInput = document.getElementById("mySelect2");
        var hiddenCourseLabel = document.getElementById("all_course");

        console.log(select);
        if (select) {
            var type = select.value;
            if (type == 'programs') {
                diplomasSection.classList.remove('d-none');
                hiddenCourseInput.closest('div').classList.add('d-none');
                resetSelect(hiddenCourseInput);

            } else if (type == 'courses') {
                hiddenCourseInput.closest('div').classList.remove('d-none');
                diplomasSection.classList.add('d-none');
                resetSelect(hiddenBundleInput);

            } else {
                diplomasSection.classList.add('d-none');
                hiddenCourseInput.closest('div').classList.add('d-none');
                resetSelect(hiddenBundleInput);
                resetSelect(hiddenCourseInput);
                // education.classList.add('d-none');
            }
        }
    }


    function resetSelect(selector) {

        selector.selectedIndex = 0; // This sets the first option as selected
        selector.removeAttribute('required');
    }

    function togglePasswordVisibility() {
        var passwordInput = document.getElementById("password");
        var toggleIcon = document.getElementById("toggleIcon");

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            toggleIcon.src = "{{ asset('store/Images/Registration/Hide.svg') }}"; // Change to Hide icon
        } else {
            passwordInput.type = "password";
            toggleIcon.src = "{{ asset('store/Images/Registration/Show.svg') }}"; // Change to Show icon
        }
    }
</script>
