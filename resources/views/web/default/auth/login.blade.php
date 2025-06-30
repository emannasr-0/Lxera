@extends(getTemplate() . '.auth.auth_layout')

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
    a {
            color: #c14b93 !important;
        }
    /* a:hover{
       text-decoration:underline;
        color:#599FAF !important;
    } */
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
</style>
    <div class="p-3 p-lg-5 m-md-3 bg-secondary-acadima border-radius-lg col-sm-7 col-md-8 col-lg-4 shadow border " >
        <div class="col-7 col-md-7 p-0 mb-5 mt-3 mt-md-auto mx-auto d-flex flex-column align-items-center">
            <img src="{{ asset('store/Acadima/acadima-logo.webp') }}" alt="logo" width="100%" class="">
            
        </div>

        <h1 class="font-20 font-weight-bold m-3 " style="display: flex; align-items: center; gap: 8px;">
                <svg width="34" height="29" viewBox="0 0 34 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                    d="M22 27C22 23.3181 17.5228 20.3333 12 20.3333C6.47715 20.3333 2 23.3181 2 27M32 12L25.3333 18.6667L22 15.3333M12 15.3333C8.3181 15.3333 5.33333 12.3486 5.33333 8.66667C5.33333 4.98477 8.3181 2 12 2C15.6819 2 18.6667 4.98477 18.6667 8.66667C18.6667 12.3486 15.6819 15.3333 12 15.3333Z"
                    stroke="#c14b93" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            <!-- {{ trans('auth.login_h1') }} -->
              Login
            </h1>

        {{-- show messages --}}
        @if (!empty(session()->has('msg')))
            <div class="alert alert-info alert-dismissible fade show mt-30" role="alert">
                {{ session()->get('msg') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form method="POST" action="/login?{{  request()->getQueryString() }}" class="needs-validation" novalidate="">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group ltr">
                <!--<label class="input-label" for="username">{{ trans('auth.email_or_mobile') }}:</label>-->
                <!-- <label class="input-label text-light" for="username">Email</label> -->
                 <div class="border-radius-lg input-size form-control input-flex">
                    <img src="{{ asset('store/Images/Registration/Mail.svg') }}" alt="Mail" class="mb-1">
                    <input name="username" type="text" class="form-control @error('username') is-invalid @enderror border-none"
                    id="username" value="{{ old('username') }}" aria-describedby="emailHelp" placeholder="Email">
                 </div>
                
                @error('username')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <!-- <label class="input-label text-light" for="password">
                    {{ trans('auth.password') }}
                </label> -->
                <div class="border-radius-lg input-size form-control input-flex">
                    <img src="{{ asset('store/Images/Registration/Lock.svg') }}" alt="Mail" class="mb-1">
                    <input name="password" type="password" class="form-control @error('password')  is-invalid @enderror border-none"
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

            @if (!empty(getGeneralSecuritySettings('captcha_for_login')))
                @include('web.default.includes.captcha_input')
            @endif


            <div class="text-right forgetpw d-flex justify-content-end text-center text-secondary">
                <a href="/forget-password" target="_blank" class="text-pink mb-30">Forgot password?</a>
                <!-- <span class="text-dark"> | </span>
                {{-- <span style="width: 2px; height: 22px;" class="bg-dark"></span> --}} -->
                <!-- <a href="https://anasacademy.uk/certificate/certificate-check.php" target="_blank" class="text-cyan">Certificate Verification</a> -->
            </div>
            <div class="form-group d-flex flex-row justify-content-start">
                <button type="submit" class="btn bg-button-acadima btn-lg cs-btn btn-border-radius mr-auto" tabindex="4">
                    <!-- {{ trans('auth.login') }} -->
                      Login
                </button>
            </div>
            <!-- <div class="text-center mt-30 mb-50">
                <a href="https://support.anasacademy.uk/" target="_blank" class="text-cyan">Support and Communication Team</a>

            </div> -->
        </form>

        @if (session()->has('login_failed_active_session'))
            <div class="d-flex align-items-center mt-20 p-15 danger-transparent-alert ">
                <div class="danger-transparent-alert__icon d-flex align-items-center justify-content-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-alert-octagon">
                        <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2">
                        </polygon>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div class="ml-10 mr-10">
                    <div class="font-14 font-weight-bold ">
                        {{ session()->get('login_failed_active_session')['title'] }}</div>
                    <div class="font-12 ">{{ session()->get('login_failed_active_session')['msg'] }}</div>
                </div>
            </div>
        @endif

        <div class="mt-20 text-center registertext">
            <span>Don't have an account?</span>
            <br>
            <a href="/register?{{  request()->getQueryString() }}" class="font-weight-bold text-pink">
                <!-- {{ trans('auth.signup') }} -->
                  Register
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