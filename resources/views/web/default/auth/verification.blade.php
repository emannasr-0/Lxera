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
    </style>


    <div class="row login-container p-md-4 m-md-3">
        <div class="col-7 col-md-7 p-0 mb-5 mt-3 mt-md-auto">
            <img src="{{ asset('store/Acadima/acadima-logo.webp') }}" alt="logo" width="100%" class="">
        </div>
        <div class="col-12">

            <div class="login-card">
                <h1 class="font-20 font-weight-bold mb-3"><svg width="34" height="29" viewBox="0 0 34 29"
                        fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M22 27C22 23.3181 17.5228 20.3333 12 20.3333C6.47715 20.3333 2 23.3181 2 27M32 12L25.3333 18.6667L22 15.3333M12 15.3333C8.3181 15.3333 5.33333 12.3486 5.33333 8.66667C5.33333 4.98477 8.3181 2 12 2C15.6819 2 18.6667 4.98477 18.6667 8.66667C18.6667 12.3486 15.6819 15.3333 12 15.3333Z"
                            stroke="#5E0A83" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    {{ trans('auth.account_verification') }}
                </h1>
                
                {{-- show messages --}}
                @if (!empty(session()->has('msg')))
                    <div class="alert alert-info alert-dismissible fade show mt-30 text-black" role="alert" >
                        {{ session()->get('msg') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <p>{{ trans('auth.account_verification_hint', ['username' => $username]) }}</p>

                <form method="post" action="/verification" class="mt-35">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <input type="hidden" name="username" value="{{ $usernameValue }}">

                    <div class="form-group">
                        <label class="input-label" for="code">{{ trans('auth.code') }}:</label>
                        <input type="tel" name="code" class="form-control @error('code') is-invalid @enderror"
                            id="code" aria-describedby="codeHelp">
                        @error('code')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-block mt-20">{{ trans('auth.verification') }}</button>
                </form>

                <div class="text-center mt-20">
                    <span class="text-secondary">
                        <a href="/verification/resend" class="font-weight-bold">{{ trans('auth.resend_code') }}</a>
                    </span>
                </div>

            </div>
        </div>
    </div>
@endsection
