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
        background-color:#1024dd !important;
        width:100%;
        height:50px;
        color:#fff;
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
        color: #1024dd !important;
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
    </style>

    <div class=" js-font-resize login-container p-3 p-lg-5 m-md-3  bg-secondary-acadima border-radius-lg shadow border">
        <div class=" js-font-resize col-7 col-md-7 p-0 mb-0 mb-lg-5 mt-3 mt-md-auto mx-auto d-flex flex-column align-items-center">
            <img src="{{ asset('store/Acadima/logo2.webp') }}" alt="logo" width="70%" class=" js-font-resize ">
            
        </div>
        <h1 class=" js-font-resize font-20 font-weight-bold mt-3 ltr" style="display: flex; align-items: center; gap: 8px;">
                    <svg width="34" height="29" viewBox="0 0 34 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M22 27C22 23.3181 17.5228 20.3333 12 20.3333C6.47715 20.3333 2 23.3181 2 27M32 12L25.3333 18.6667L22 15.3333M12 15.3333C8.3181 15.3333 5.33333 12.3486 5.33333 8.66667C5.33333 4.98477 8.3181 2 12 2C15.6819 2 18.6667 4.98477 18.6667 8.66667C18.6667 12.3486 15.6819 15.3333 12 15.3333Z"
                            stroke="#1024dd" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <!-- {{ trans('auth.reset_password') }} -->
                      Reset Password
                </h1>
        <div class=" js-font-resize col-12 p-0">
            <div class=" js-font-resize login-card">
                

                <form method="post" action="/reset-password" class=" js-font-resize mt-35">
                    {{ csrf_field() }}

                    <div class=" js-font-resize form-group ltr">
                        <!-- <label class=" js-font-resize input-label" for="email">{{ trans('auth.email') }}:</label> -->
                        <div class=" js-font-resize border-radius-lg input-size form-control input-flex">
                            <img src="{{ asset('store/Images/Registration/Mail.svg') }}" alt="Mail" class=" js-font-resize mb-1">
                            <input type="email" name="email" class=" js-font-resize form-control @error('email') is-invalid @enderror border-none"
                                id="email" value="{{ request()->get('email') }}" aria-describedby="emailHelp" placeholder="Email">
                        </div>

                        @error('email')
                            <div class=" js-font-resize invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class=" js-font-resize form-group ltr">
                        <!-- <label class=" js-font-resize input-label" for="password">{{ trans('auth.password') }}:</label> -->
                        <div class=" js-font-resize border-radius-lg input-size form-control input-flex">
                            <img src="{{ asset('store/Images/Registration/Lock.svg') }}" alt="Lock" class=" js-font-resize mb-1">
                            <input name="password" type="password" class=" js-font-resize form-control border-none"
                                id="password" aria-describedby="passwordHelp" placeholder="Password">
                            <span class=" js-font-resize icon2" onclick="togglePasswordVisibility('password', 'toggleIconPassword')">
                                <img id="toggleIconPassword" src="{{ asset('store/Images/Registration/Show.svg') }}" alt="Show" class=" js-font-resize icon2">
                            </span>
                        </div>

                        @error('password')
                            <div class=" js-font-resize invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class=" js-font-resize form-group ltr">
                        <!-- <label class=" js-font-resize input-label" for="confirm_password">{{ trans('auth.retype_password') }}:</label> -->
                        <div class=" js-font-resize border-radius-lg input-size form-control input-flex">
                            <img src="{{ asset('store/Images/Registration/Lock.svg') }}" alt="Lock" class=" js-font-resize mb-1">
                            <input name="password_confirmation" type="password" class=" js-font-resize form-control border-none"
                                id="confirm_password" aria-describedby="confirmPasswordHelp" placeholder="Retype Password">
                            <span class=" js-font-resize icon2" onclick="togglePasswordVisibility('confirm_password', 'toggleIconConfirmPassword')">
                                <img id="toggleIconConfirmPassword" src="{{ asset('store/Images/Registration/Show.svg') }}" alt="Show" class=" js-font-resize icon2">
                            </span>
                        </div>

                        @error('password_confirmation')
                            <div class=" js-font-resize invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <input hidden name="token" placeholder="token" value="{{ $token }}">

                    <button type="submit"
                        class=" js-font-resize btn bg-button-acadima btn-block mt-20 cs-btn btn-border-radius mr-auto">
                        <!-- {{ trans('auth.reset_password') }} -->
                          Reset Password
                    </button>
                </form>

                <div class=" js-font-resize text-center mt-20">
                    <span
                        class=" js-font-resize badge badge-circle-gray300 text-dark d-inline-flex align-items-center justify-content-center">or</span>
                </div>

                <div class=" js-font-resize text-center mt-1">
                    <span class=" js-font-resize text-secondary">
                        <a href="/panel" class=" js-font-resize text-pink font-weight-bold">Dashboard</a>
                    </span>
                </div>

            </div>
        </div>
    </div>
@endsection
<script>
    function togglePasswordVisibility(inputId, iconId) {
        var passwordInput = document.getElementById(inputId);
        var toggleIcon = document.getElementById(iconId);

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            toggleIcon.src = "{{ asset('store/Images/Registration/Hide.svg') }}"; // Change to Hide icon
        } else {
            passwordInput.type = "password";
            toggleIcon.src = "{{ asset('store/Images/Registration/Show.svg') }}"; // Change to Show icon
        }
    }
</script>
