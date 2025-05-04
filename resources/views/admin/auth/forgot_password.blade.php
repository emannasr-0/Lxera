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
        .text-left {
            text-align: left !important;
        }

    </style> 

    <div class="p-4 m-3">
    <div class="col-7 col-md-7 p-0 mb-5 mt-3 mt-md-auto mx-auto d-flex flex-column align-items-center">

        <img src="{{ asset('store/Acadima/acadima-logo.webp') }}" alt="logo" width="100%" class="">
        </div>

        <h4 class="text-left text-pink">
            <!-- {{ trans('auth.forget_password') }} -->
              Forgot Password
        </h4>

        <p class="text-muted text-left">
            <!-- {{ trans('update.we_will_send_a_link_to_reset_your_password') }} -->
              We will send a link to reset your password
        </p>

        <form method="POST" action="{{ getAdminPanelUrl() }}/forget-password">
            {{ csrf_field() }}

            <div class="form-group ltr">
                <!-- <label for="email">{{ trans('auth.email') }}</label> -->
                <div class="border-radius-lg input-size form-control input-flex">
                    <img src="{{ asset('store/Images/Registration/Mail.svg') }}" alt="Mail" class="mb-1">
                    <input id="email" type="email" value="{{ old('email') }}" class="form-control  @error('email')  is-invalid @enderror border-none"
                       name="email" tabindex="1" required autofocus placeholder="Email">
                @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            @if(!empty(getGeneralSecuritySettings('captcha_for_admin_forgot_pass')))
                @include('admin.includes.captcha_input')
            @endif

            <button type="submit" class="btn bg-button-acadima btn-block mt-20 cs-btn btn-border-radius mr-auto">
                <!-- {{ trans('auth.reset_password') }} -->
                  Reset Password
            </button>
        </form>

        <div class="text-center mt-20">
            <span class=" text-dark d-inline-flex align-items-center justify-content-center">or</span>
        </div>

        <div class="text-center mt-1">
            <span class="text-secondary">
                <a href="{{ getAdminPanelUrl() }}/login" class="text-pink font-weight-bold">
                    <!-- {{ trans('auth.login') }} -->
                    Login
                </a>
            </span>
        </div>
    </div>
@endsection
