<div class=" js-font-resize no-result default-no-result mt-50 d-flex align-items-center justify-content-center flex-column">
    @include('web.default.panel.includes.sidebar_icons.webinars')
    <div class=" js-font-resize d-flex align-items-center flex-column mt-30 text-center">
        <h2 class=" js-font-resize text-pink">{{ $title }}</h2>
        <p class=" js-font-resize mt-1 text-center text-gray font-weight-500">{!! $hint !!}</p>
        {{-- @if(!empty($btn))
            <a href="{{ $btn['url'] }}" class=" js-font-resize btn btn-sm btn-primary mt-25">{{ $btn['text'] }}</a>
        @endif --}}
    </div>
</div>
