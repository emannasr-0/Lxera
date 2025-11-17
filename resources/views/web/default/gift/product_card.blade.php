<div class=" js-font-resize gift-webinar-card bg-white">
    <figure>
        <div class=" js-font-resize image-box">
            <a href="{{ $product->getUrl() }}">
                <img src="{{ $product->thumbnail }}" class=" js-font-resize img-cover" alt="{{ $product->title }}">
            </a>
        </div>

        <figcaption class=" js-font-resize mt-10">
            <div class=" js-font-resize user-inline-avatar d-flex align-items-center">
                <div class=" js-font-resize avatar bg-gray200">
                    <img src="{{ $product->creator->getAvatar() }}" class=" js-font-resize img-cover" alt="{{ $product->creator->full_name }}">
                </div>
                <a href="{{ $product->creator->getProfileUrl() }}" target="_blank" class=" js-font-resize user-name ml-5 font-14">{{ $product->creator->full_name }}</a>
            </div>

            <a href="{{ $product->getUrl() }}">
                <h3 class=" js-font-resize mt-15 webinar-title font-weight-bold font-16 text-dark-blue">{{ clean($product->title, 'title') }}</h3>
            </a>

            @if($product->getRate())
                @include('web.default.includes.webinar.rate',['rate' => $product->getRate()])
            @endif

            <div class=" js-font-resize webinar-price-box mt-15">
                @if(!empty($product->price) and $product->price > 0)
                    @if($product->getPriceWithActiveDiscountPrice() < $product->price)
                        <span class=" js-font-resize real">{{ handlePrice($product->getPriceWithActiveDiscountPrice(), true, true, false, null, true, 'store') }}</span>
                        <span class=" js-font-resize off ml-10">{{ handlePrice($product->price, true, true, false, null, true, 'store') }}</span>
                    @else
                        <span class=" js-font-resize real">{{ handlePrice($product->price, true, true, false, null, true, 'store') }}</span>
                    @endif
                @else
                    <span class=" js-font-resize real font-14">{{ trans('public.free') }}</span>
                @endif
            </div>
        </figcaption>
    </figure>
</div>
