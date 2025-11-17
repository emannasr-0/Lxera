<div class=" js-font-resize gift-webinar-card bg-white">
    <figure>
        <div class=" js-font-resize image-box">
            <a href="{{ $bundle->getUrl() }}">
                <img src="{{ $bundle->getImage() }}" class=" js-font-resize img-cover" alt="{{ $bundle->title }}">
            </a>
        </div>

        <figcaption class=" js-font-resize mt-10">
            <div class=" js-font-resize user-inline-avatar d-flex align-items-center">
                <div class=" js-font-resize avatar bg-gray200">
                    <img src="{{ $bundle->teacher->getAvatar() }}" class=" js-font-resize img-cover" alt="{{ $bundle->teacher->full_name }}">
                </div>
                <a href="{{ $bundle->teacher->getProfileUrl() }}" target="_blank" class=" js-font-resize user-name ml-5 font-14">{{ $bundle->teacher->full_name }}</a>
            </div>

            <a href="{{ $bundle->getUrl() }}">
                <h3 class=" js-font-resize mt-15 webinar-title font-weight-bold font-16 text-dark-blue">{{ clean($bundle->title,'title') }}</h3>
            </a>

            @if($bundle->getRate())
                @include('web.default.includes.webinar.rate',['rate' => $bundle->getRate()])
            @endif

            <div class=" js-font-resize webinar-price-box mt-15">
                @if(!empty($bundle->price) and $bundle->price > 0)
                    @if($bundle->bestTicket() < $bundle->price)
                        <span class=" js-font-resize real">{{ handlePrice($bundle->bestTicket()) }}</span>
                        <span class=" js-font-resize off ml-10">{{ handlePrice($bundle->price) }}</span>
                    @else
                        <span class=" js-font-resize real">{{ handlePrice($bundle->price) }}</span>
                    @endif
                @else
                    <span class=" js-font-resize real font-14">{{ trans('public.free') }}</span>
                @endif
            </div>
        </figcaption>
    </figure>
</div>
