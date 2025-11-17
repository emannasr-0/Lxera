@if($floatingBar->position == 'top' and $floatingBar->fixed)
    <style>
        .has-fixed-top-floating-bar {
            padding-top: {{ !empty($floatingBar->bar_height) ? $floatingBar->bar_height : 80 }}px;
        }

        .has-fixed-top-floating-bar .navbar.sticky {
            top: {{ !empty($floatingBar->bar_height) ? $floatingBar->bar_height : 80 }}px;
        }
    </style>
@endif

<div class=" js-font-resize floating-bar {{ $floatingBar->fixed ? "is-fixed" : '' }} {{ 'position-'.$floatingBar->position }} " style="{{ !empty($floatingBar->background_image) ? "background-image: url('$floatingBar->background_image');" : '' }} {{ (!empty($floatingBar->background_color) ? "background-color: $floatingBar->background_color;" : '') }} {{ !empty($floatingBar->bar_height) ? "height: {$floatingBar->bar_height}px;" : '' }}">
    <div class=" js-font-resize container h-100">
        <div class=" js-font-resize d-flex align-items-center justify-content-between h-100">
            <div class=" js-font-resize d-flex align-items-center">
                @if(!empty($floatingBar->icon))
                    <div class=" js-font-resize floating-bar__icon mr-5">
                        <img src="{{ $floatingBar->icon }}" alt="{{ $floatingBar->title ?? 'icon' }}" class=" js-font-resize img-fluid">
                    </div>
                @endif
                <div class=" js-font-resize ">
                    @if(!empty($floatingBar->title))
                        <h5 class=" js-font-resize font-16 font-weight-bold" style="{{ !empty($floatingBar->title_color) ? "color: $floatingBar->title_color" : '' }}">{{ $floatingBar->title }}</h5>
                    @endif

                    @if(!empty($floatingBar->description))
                        <div class=" js-font-resize font-14" style="{{ !empty($floatingBar->description_color) ? "color: $floatingBar->description_color" : '' }}">{{ $floatingBar->description }}</div>
                    @endif
                </div>
            </div>

            @if(!empty($floatingBar->btn_text))
                <a
                    href="{{ !empty($floatingBar->btn_url) ? $floatingBar->btn_url : '#!' }}"
                    class=" js-font-resize btn btn-sm"
                    style="{{ !empty($floatingBar->btn_color) ? "background-color: $floatingBar->btn_color; border-color: $floatingBar->btn_color;" : '' }} {{ !empty($floatingBar->btn_text_color) ? "color: $floatingBar->btn_text_color;" : '' }} "
                >{{ $floatingBar->btn_text }}</a>
            @endif
        </div>
    </div>
</div>
