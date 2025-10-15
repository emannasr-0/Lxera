<div class="no-result default-no-result mt-30 d-flex align-items-center justify-content-center flex-column">
    @include('web.default.panel.includes.sidebar_icons.webinars')
    <div class="d-flex align-items-center flex-column mt-3 text-center">
        <h2>{{ $title }}</h2>
        <p class="mt-1 text-center">{!! $hint !!}</p>
        @if(!empty($btn))
            <a href="{{ $btn['url'] }}" class="btn btn-primary mt-2">{{ $btn['text'] }}</a>
        @endif
    </div>
</div>
