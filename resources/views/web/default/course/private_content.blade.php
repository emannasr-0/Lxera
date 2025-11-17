@extends('web.default.layouts.app')

@section('content')
    <div class=" js-font-resize container">
        <div class=" js-font-resize course-private-content text-center w-100 border rounded-lg">
            <div class=" js-font-resize course-private-content-icon m-auto">
                <img src="/assets/default/img/course/private_content_icon.svg" alt="private content icon" class=" js-font-resize img-cover">
            </div>

            @if(!empty($userNotAccess) and $userNotAccess)
                <div class=" js-font-resize mt-30">
                    <h2 class=" js-font-resize font-20 text-dark-blue">{{ trans('update.not_access_to_content') }}</h2>
                    <p class=" js-font-resize font-14 font-weight-500 text-gray">{{ trans('update.not_access_to_content_hint') }}</p>
                </div>
            @else
                <div class=" js-font-resize mt-30">
                    <h2 class=" js-font-resize font-20 font-weight-bold text-dark-blue">{{ trans('update.private_content') }}</h2>
                    <p class=" js-font-resize font-14 font-weight-500 text-gray">{{ trans('update.private_content_login_hint') }}</p>
                </div>

                <a href="/login" class=" js-font-resize btn btn-primary mt-15">{{ trans('auth.login') }}</a>
            @endif
        </div>
    </div>
@endsection
