@extends('web.default.layouts.app',['appFooter' => false, 'appHeader' => false])

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/agora/agora.css"/>
@endpush


@section('content')

    <div class=" js-font-resize agora-page">
        <div class=" js-font-resize agora-navbar d-flex align-items-center justify-content-between shoa px-35 py-10">
            <div class=" js-font-resize d-flex align-items-center">
                <a class=" js-font-resize navbar-brand d-flex align-items-center justify-content-center mr-0" href="/">
                    @if(!empty($generalSettings['logo']))
                        <img src="{{ asset('store/Acadima/acadima-logo.webp') }}" class=" js-font-resize " alt="site logo">
                    @endif
                </a>

                <span class=" js-font-resize font-weight-bold border-left border-gray200 ml-10 pl-10">{{ !empty($session->webinar) ? $session->webinar->title : $session->title }}</span>
            </div>

            <button id="collapseBtn" type="button" class=" js-font-resize btn-transparent d-none d-lg-flex">
                <i data-feather="menu" width="20" height="20" class=" js-font-resize "></i>
            </button>
        </div>

    <!-- Start Recording Button -->
    <form method="POST" action="{{ route('start_recording') }}">
        @csrf <!-- Include Laravel CSRF token -->
        <input type="hidden" name="channelName" value="anasacademy"> <!-- Replace with actual channel name -->
        <button type="submit">Start Recording</button>
    </form>


        <div class=" js-font-resize d-flex flex-column flex-lg-row">
            <div class=" js-font-resize agora-stream flex-grow-1 bg-info-light p-15">
                @include('web.default.course.agora.stream')
            </div>

            <div class=" js-font-resize agora-tabs show">
                <ul class=" js-font-resize nav nav-tabs pb-15 d-flex align-items-center justify-content-start px-15" id="tabs-tab" role="tablist">
                    <li class=" js-font-resize nav-item">
                        <a class=" js-font-resize position-relative font-14 d-flex align-items-center active" id="chat-tab"
                           data-toggle="tab" href="#chat" role="tab" aria-controls="chat"
                           aria-selected="true">
                            <i data-feather="message-circle" width="16" height="16" class=" js-font-resize agora-tabs-icons mr-1"></i>
                            <span class=" js-font-resize agora-tabs-link-text">{{ trans('update.chat') }}</span>
                        </a>
                    </li>

                    {{--<li class=" js-font-resize nav-item">
                        <a class=" js-font-resize position-relative font-14 d-flex align-items-center" id="users-tab" data-toggle="tab"
                           href="#users" role="tab" aria-controls="users"
                           aria-selected="false">
                            <i data-feather="users" width="16" height="16" class=" js-font-resize agora-tabs-icons mr-1"></i>
                            <span class=" js-font-resize agora-tabs-link-text">{{ trans('panel.users') }}</span>
                        </a>
                    </li>--}}
                </ul>

                <div class=" js-font-resize tab-content h-100" id="nav-tabContent">
                    <div class=" js-font-resize pb-20 tab-pane fade show active h-100" id="chat" role="tabpanel"
                         aria-labelledby="chat-tab">
                        @include('web.default.course.agora.chat')
                    </div>

                    {{--<div class=" js-font-resize pb-20 tab-pane fade  h-100" id="users" role="tabpanel"
                         aria-labelledby="users-tab">
                        @include('web.default.course.agora.users')
                    </div>--}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_top')
    <script>
        var userDefaultAvatar = '{{ getPageBackgroundSettings('user_avatar') }}';
        var joinedToChannel = '{{ trans('update.joined_the_live') }}';
        var appId = '{{ $appId }}';
        var accountName = '{{ $accountName }}';
        var userName = '{{ $userName }}';
        var channelName = '{{ $channelName }}';
        var streamRole = '{{ $streamRole }}';
        var redirectAfterLeave = '{{ url('/panel') }}';
        var liveEndedLang = '{{ trans('update.this_live_has_been_ended') }}';
        var redirectToPanelInAFewMomentLang = '{{ trans('update.a_few_moments_redirect_to_panel') }}';
        var streamStartAt = Number({{ $streamStartAt }});
        var sessionId = Number({{ $session->id }});
        var sessionStreamType = '{{ $sessionStreamType }}';
        var authUserId = Number({{ $authUserId }});
        var hostUserId = Number({{ $hostUserId }});
    </script>

@endpush

   
