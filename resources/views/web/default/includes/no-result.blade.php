<div class="no-result default-no-result mt-50 d-flex align-items-center justify-content-center flex-column">
    @include('web.default.panel.includes.sidebar_icons.webinars')
    <div class="d-flex align-items-center flex-column mt-30 text-center">
        <h2 class="text-pink">{{ $title }}</h2>
        <p class="mt-1 text-center text-gray font-weight-500">{!! $hint !!}</p>
        {{-- @if(!empty($btn))
            <a href="{{ $btn['url'] }}" class="btn btn-sm btn-primary mt-25">{{ $btn['text'] }}</a>
        @endif --}}
    </div>
</div>
