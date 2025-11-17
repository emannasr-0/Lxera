<div class=" js-font-resize gift-webinar-card bg-white">
    <figure>
        <div class=" js-font-resize image-box">
            <a href="{{ $webinar->getUrl() }}">
                <img src="{{ $webinar->getImage() }}" class=" js-font-resize img-cover" alt="{{ $webinar->title }}">
            </a>
        </div>

        <figcaption class=" js-font-resize mt-10">
            <div class=" js-font-resize user-inline-avatar d-flex align-items-center">
                <div class=" js-font-resize avatar bg-gray200">
                    <img src="{{ $webinar->teacher->getAvatar() }}" class=" js-font-resize img-cover" alt="{{ $webinar->teacher->full_name }}">
                </div>
                <a href="{{ $webinar->teacher->getProfileUrl() }}" target="_blank" class=" js-font-resize user-name ml-5 font-14">{{ $webinar->teacher->full_name }}</a>
            </div>

            <a href="{{ $webinar->getUrl() }}">
                <h3 class=" js-font-resize mt-15 webinar-title font-weight-bold font-16 text-dark-blue">{{ clean($webinar->title,'title') }}</h3>
            </a>

            @if($webinar->getRate())
                @include('web.default.includes.webinar.rate',['rate' => $webinar->getRate()])
            @endif

            <div class=" js-font-resize webinar-price-box mt-15">
                @if(!empty($webinar->price) and $webinar->price > 0)
                    @if($webinar->bestTicket() < $webinar->price)
                        <span class=" js-font-resize real">{{ handlePrice($webinar->bestTicket()) }}</span>
                        <span class=" js-font-resize off ml-10">{{ handlePrice($webinar->price) }}</span>
                    @else
                        <span class=" js-font-resize real">{{ handlePrice($webinar->price) }}</span>
                    @endif
                @else
                    <span class=" js-font-resize real font-14">{{ trans('public.free') }}</span>
                @endif
            </div>
        </figcaption>
    </figure>
</div>
