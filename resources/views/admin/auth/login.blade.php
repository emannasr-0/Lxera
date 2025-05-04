@extends('admin.auth.auth_layout')

@section('content')
    @php
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
    .cs-btn{
        background-color:#c14b93 !important;
        width:100%;
        height:50px;
        color: #fff;

    }
    @media(max-width: 600px) {
        .cs-btn {
            width: 100%;
        }
    }
    /* .cs-btn:hover{
        background-color:#599FAF !important;
        color: #fff;

    } */
    a:hover{
       text-decoration:underline;
        /* color:#599FAF !important; */
    }
    .bg-secondary-acadima {
        background-color: #fff !important;
    }
    .bg-button-acadima {
        background-color: #c14b93 !important;
    }
    .text-cyan {
    color: #CCF5FF !important;
    font-size:16px;
    }
    .text-cyan:hover {
    color: #ccf5ffa4 !important;
    }
    .text-pink {
        color: #c14b93 !important;
        font-size:16px;
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
        /* background-color: #e8f0fe; */
    }
    .border-none {
        border: none;

    }
    input:focus, input::selection {
        background-color: #fdfdff !important;
    }
    .text-left {
            text-align: left !important;
        }
</style>

    <div class="p-4 m-3">
    <div class="col-7 col-md-7 p-0 mb-5 mt-3 mt-md-auto mx-auto d-flex flex-column align-items-center">

        <img src="{{ asset('store/Acadima/acadima-logo.webp') }}" alt="logo" width="80%" class="">
        </div>

        <h4 class="text-pink font-weight-normal text-left">
            <!-- {{ trans('admin/main.welcome') }}  -->
              Welcome to 
            <span class="font-weight-bold">
            <!-- {{ $siteGeneralSettings['site_name'] ?? '' }} -->
              Acadima
        </span></h4>

        <p class="text-muted text-left">
            <!-- {{ trans('auth.admin_tagline') }} -->
              Login to the admin page
        </p>

        <form method="POST" action="{{ getAdminPanelUrl() }}/login" class="needs-validation" novalidate="">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group ltr">
                <!-- <label for="email">{{ trans('auth.email') }}</label> -->
                <div class="border-radius-lg input-size form-control input-flex">
                    <img src="{{ asset('store/Images/Registration/Mail.svg') }}" alt="Mail" class="mb-1">
                    <input id="email" type="email" value="{{ old('email') }}" class="form-control  @error('email')  is-invalid @enderror border-none"
                       name="email" tabindex="1"
                       required autofocus>
                </div>

                @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="form-group ltr">
                <!-- <div class="d-block">
                    <label for="password" class="control-label">{{ trans('auth.password') }}</label>
                </div> -->
                <div class="border-radius-lg input-size form-control input-flex">
                    <img src="{{ asset('store/Images/Registration/Lock.svg') }}" alt="Mail" class="mb-1">
                    <input id="password" type="password" class="form-control  @error('password')  is-invalid @enderror"
                       name="password" tabindex="2" required>
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

            @if(!empty(getGeneralSecuritySettings('captcha_for_admin_login')))
                @include('admin.includes.captcha_input')
            @endif

            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="remember" class="custom-control-input" tabindex="3"
                           id="remember-me">
                    <label class="custom-control-label text-pink"
                           for="remember-me">
                           <!-- {{ trans('auth.remember_me') }} -->
                             Remember me
                        </label>
                </div>
            </div>

            <div class="form-group d-flex flex-row justify-content-start">
                <button type="submit" class="btn bg-button-acadima btn-lg cs-btn btn-border-radius mr-auto" tabindex="4">
                    <!-- {{ trans('auth.login') }} -->
                      Login
                </button>
            </div>
        </form>

        <div class="text-right d-flex justify-content-start text-center text-secondary">

        <a href="{{ getAdminPanelUrl() }}/forget-password" class="text-pink mb-30  ltr">
            <!-- {{ trans('auth.forget_your_password') }} -->
            Forgot password?
        </a>
        </div>

    </div>
@endsection
<script>
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