@extends('web.default.layouts.app', ['appFooter' => false, 'appHeader' => false, 'justMobileApp' => true])

@php
    $mobileAppSettings = getMobileAppSettings();
@endphp

@section('content')
    <section class=" js-font-resize mobile-app-section my-50 position-relative">
        <div class=" js-font-resize container mobile-app-section__container">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-7">
                    <h1 class=" js-font-resize font-36 text-secondary font-weight-bold">{!! nl2br(trans('update.download_mobile_app_and_enjoy')) !!}</h1>
                    <p class=" js-font-resize mt-15 font-14 text-gray">{!! $mobileAppSettings['mobile_app_description'] ?? '' !!}</p>

                    @if(!empty($mobileAppSettings) and !empty($mobileAppSettings['mobile_app_buttons']))
                        <div class=" js-font-resize mt-20 d-flex align-items-center flex-wrap">
                            @foreach($mobileAppSettings['mobile_app_buttons'] as $mobileAppButton)
                                <a href="{{ $mobileAppButton['link'] ?? '' }}" target="_blank" class=" js-font-resize rounded-pill mobile-app__buttons btn btn-{{ $mobileAppButton['color'] ?? '' }} {{ (!empty($mobileAppButton['icon'])) ? 'has-icon' : '' }}">
                                    @if(!empty($mobileAppButton['icon']))
                                        <span class=" js-font-resize mobile-app__button-icon rounded-circle mr-10">
                                        <img src="{{ $mobileAppButton['icon'] }}" class=" js-font-resize img-cover rounded-circle" alt="{{ $mobileAppButton['title'] ?? '' }}">
                                    </span>
                                    @endif

                                    <span class=" js-font-resize ">{{ $mobileAppButton['title'] ?? '' }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class=" js-font-resize mobile-app-section__image d-flex align-items-center justify-content-center">

            <div class=" js-font-resize bubble-one"></div>
            <div class=" js-font-resize bubble-two"></div>
            <div class=" js-font-resize bubble-three"></div>

            <div class=" js-font-resize mobile-app-section__image-hero">
                <img src="/assets/default/img/home/dot.png" class=" js-font-resize mobile-app-section__dots" alt="dots">

                @if(!empty($mobileAppSettings['mobile_app_hero_image']))
                    <img src="{{ $mobileAppSettings['mobile_app_hero_image'] }}" class=" js-font-resize img-cover" alt="trans('update.download_mobile_app_and_enjoy')">
                @endif
            </div>
        </div>
    </section>

@endsection
