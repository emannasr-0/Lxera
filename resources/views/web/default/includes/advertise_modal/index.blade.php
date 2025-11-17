@php
    $advertisingModalSettings = getAdvertisingModalSettings();
@endphp

@if(!empty($advertisingModalSettings))
    <div class=" js-font-resize d-none" id="advertisingModalSettings">
        <div class=" js-font-resize d-flex align-items-center justify-content-between">
            <h3 class=" js-font-resize section-title font-16 text-dark-blue mb-10">{{ $advertisingModalSettings['title'] ?? '' }}</h3>

            <button type="button" class=" js-font-resize btn-close-advertising-modal close-swl btn-transparent d-flex">
                <i data-feather="x" width="25" height="25" class=" js-font-resize "></i>
            </button>
        </div>

        <div class=" js-font-resize d-flex align-items-center justify-content-center">
            <img src="{{ $advertisingModalSettings['image'] ?? '' }}" class=" js-font-resize img-fluid rounded-lg" alt="{{ $advertisingModalSettings['title'] ?? 'ads' }}">
        </div>

        <p class=" js-font-resize font-14 text-gray mt-20">{!! $advertisingModalSettings['description'] ?? '' !!}</p>

        <div class=" js-font-resize row align-items-center mt-20">
            @if(!empty($advertisingModalSettings['button1']) and !empty($advertisingModalSettings['button1']['link']) and !empty($advertisingModalSettings['button1']['title']))
                <div class=" js-font-resize col-6">
                    <a href="{{ $advertisingModalSettings['button1']['link'] }}" class=" js-font-resize btn btn-primary btn-sm btn-block">{{ $advertisingModalSettings['button1']['title'] }}</a>
                </div>
            @endif

            @if(!empty($advertisingModalSettings['button2']) and !empty($advertisingModalSettings['button2']['link']) and !empty($advertisingModalSettings['button2']['title']))
                <div class=" js-font-resize col-6">
                    <a href="{{ $advertisingModalSettings['button2']['link'] }}" class=" js-font-resize btn btn-outline-primary btn-sm btn-block">{{ $advertisingModalSettings['button2']['title'] }}</a>
                </div>
            @endif
        </div>
    </div>
@endif
