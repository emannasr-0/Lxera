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
            background-color: #c14b93 !important;
            width:100%;
        height:50px;
        color: #fff;

        }

        /* .cs-btn:hover {
            background-color: #599FAF !important;
            color: #fff;
        } */

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
            color: #c14b93 !important;
        }
        /* a:hover {
            color: #599FAF !important;

        } */
        .bg-secondary-acadima {
            background-color: #fff !important;
        }
        .text-pink {
        color: #c14b93 !important;
        font-size:16px;
        }
        /* .text-cyan:hover {
        color: #ccf5ffa4 !important;
        } */
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
    .select-border-radius {
        border-radius: 20px;
        height:55px !important;
        padding: 5px 16px;
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
    <div class="px-3 px-lg-5 py-2 m-md-3 bg-secondary-acadima col-sm-7 col-md-8 col-lg-4 border-radius-lg ltr border shadow">
        <div class="col-6 col-md-6 p-0 mb-0 mb-lg-5 mt-3 mt-md-auto mx-auto d-flex flex-column align-items-center">
            <img src="{{ asset('store/Acadima/acadima-logo.webp') }}" alt="logo" width="100%" class="">
            
        </div>

        <h1 class="font-20 font-weight-bold mb-3 mt-3 ltr" style="display: flex; align-items: center; gap: 8px;">
            <svg width="34" height="29" viewBox="0 0 34 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M22 27C22 23.3181 17.5228 20.3333 12 20.3333C6.47715 20.3333 2 23.3181 2 27M32 12L25.3333 18.6667L22 15.3333M12 15.3333C8.3181 15.3333 5.33333 12.3486 5.33333 8.66667C5.33333 4.98477 8.3181 2 12 2C15.6819 2 18.6667 4.98477 18.6667 8.66667C18.6667 12.3486 15.6819 15.3333 12 15.3333Z"
                    stroke="#c14b93" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <!-- {{ trans('auth.signup') }} -->
              Register
        </h1>
        

        {{-- show messages --}}
        @if (!empty(session()->has('msg')))
            <div class="alert alert-info alert-dismissible fade show mt-30 text-black" role="alert">
                {{ session()->get('msg') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form method="post" action="/register" class="mt-20" id="registerForm">

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

                <!-- <div class="form-group">
                    <label class="input-label text-light" for="email">اعد كتابة الإيميل
                        {{ !empty($optional) ? '(' . trans('public.optional') . ')' : '' }}*</label>
                    <input name="email_confirmation" type="text"
                        class="form-control @error('email_confirmation') is-invalid @enderror"
                        value="{{ old('email_confirmation') }}" id="email" aria-describedby="emailHelp">

                    @error('email_confirmation')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div> -->

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


            {{-- @if (getFeaturesSettings('timezone_in_register'))
                @php
                    $selectedTimezone = getGeneralSettings('default_time_zone');
                @endphp

                <div class="form-group">
                    <label class="input-label text-light">{{ trans('update.timezone') }}</label>
                    <select name="timezone" class="form-control select2" data-allow-clear="false">
                        <option value="" {{ empty($user->timezone) ? 'selected' : '' }} disabled>
                            {{ trans('public.select') }}</option>
                        @foreach (getListOfTimezones() as $timezone)
                            <option value="{{ $timezone }}" @if ($selectedTimezone == $timezone) selected @endif>
                                {{ $timezone }}</option>
                        @endforeach
                    </select>
                    @error('timezone')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            @endif --}}

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

            {{-- <div class="custom-control custom-checkbox">
                <input type="checkbox" name="term" value="1"
                    {{ (!empty(old('term')) and old('term') == '1') ? 'checked' : '' }}
                    class="custom-control-input @error('term') is-invalid @enderror" id="term">
                <label class="custom-control-label font-14 mr-20" for="term">
                    <p class="term">
                        {{ trans('auth.i_agree_with') }}

                        <a href="pages/terms" target="_blank"
                            class="text-secondary font-weight-bold font-14">{{ trans('auth.terms_and_rules') }}</a>

                    </p>
                </label>

                @error('term')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            @error('term')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror --}}
            <!--end-->

            {{-- application type --}}
           {{-- <div class="form-group">
                <!-- <label class="form-label text-light">Type<span class="text-danger">*</span></label> -->
                <select id="typeSelect" name="type" required 
                class="form-control @error('type') is-invalid @enderror select-border-radius"
                    onchange="toggleHiddenType()">
                    <option selected hidden value="">Choose the type you want to study</option> 
                    @if (count($categories) > 0)
                        <option value="programs" @if (old('type', request()->type) == 'programs') selected @endif>
                            Programs </option>
                    @endif
                    <option value="courses" @if (old('type', request()->type) == 'courses') selected @endif> Courses</option>
                </select>

                @error('type')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div> --}}

            {{-- course --}}
            {{-- <div class="form-group">
                <!-- <label for="application2" class="form-label" id="all_course">الدورات    التدربيه<span class="text-danger">*</span></label> -->
                <select id="mySelect2" name="webinar_id" class="form-control @error('webinar_id') is-invalid @enderror select-border-radius">
                    <option selected hidden value="">Choose the course you want to study</option>

                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @if (old('webinar_i`d', request()->webinar_id) == $course->id) selected @endif>
                            {{ $course->title }} </option>
                    @endforeach

                </select>

                @error('webinar_id')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div> --}}

            {{-- programs --}}
            <section class="" id="diplomas_section">
                <div class="form-group mt-15">
                    <!-- <label class="input-label text-light">البرنامج</label> -->

                    <select id="bundle_id" class="custom-select @error('bundle_id')  is-invalid @enderror select-border-radius"
                        name="bundle_id">
                        <option selected hidden value="">
                            {{  trans('panel.academic_program_type_selection')  }}    
                        </option>

                        {{-- Loop through top-level categories --}}
                        @foreach ($categories as $category)
                            <optgroup label="{{ $category->title }}">

                                {{-- Display bundles directly under the current category --}}
                                @foreach ($category->activeBundles as $bundleItem)
                                    <option value="{{ $bundleItem->id }}"
                                        has_certificate="{{ $bundleItem->has_certificate }}"
                                        early_enroll="{{ $bundleItem->early_enroll }}"
                                        @if (old('bundle_id', request()->bundle_id) == $bundleItem->id) selected @endif>
                                        {{ $bundleItem->title }}</option>
                                @endforeach

                                {{-- Display bundles under subcategories --}}
                                @foreach ($category->activeSubCategories as $subCategory)
                                    @foreach ($subCategory->activeBundles as $bundleItem)
                                        <option value="{{ $bundleItem->id }}"
                                            has_certificate="{{ $bundleItem->has_certificate }}"
                                            early_enroll="{{ $bundleItem->early_enroll }}"
                                            @if (old('bundle_id', request()->bundle_id) == $bundleItem->id) selected @endif>
                                            {{ $bundleItem->title }}</option>
                                    @endforeach
                                @endforeach

                            </optgroup>
                        @endforeach
                    </select>

                    @error('bundle_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </section>
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
                <a href="/login?{{  request()->getQueryString() }}" class="text-pink font-weight-bold">Login</a>
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
