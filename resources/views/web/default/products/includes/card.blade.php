<div class=" js-font-resize product-card">
    <figure>
        <div class=" js-font-resize image-box">
            <a href="{{ $product->getUrl() }}" class=" js-font-resize image-box__a">
                @php
                    $hasDiscount = $product->getActiveDiscount();
                @endphp

                @if($product->getAvailability() < 1)
                    <span class=" js-font-resize out-of-stock-badge">
                    <span>{{ trans('update.out_of_stock') }}</span>
                </span>
                @elseif($hasDiscount)
                <span class=" js-font-resize badge badge-danger">{{ trans('public.offer',['off' => $hasDiscount->percent]) }}</span>
                @elseif($product->isPhysical() and empty($product->delivery_fee))
                    <span class=" js-font-resize badge badge-warning">{{ trans('update.free_shipping') }}</span>
                @endif

                <img src="{{ $product->thumbnail }}" class=" js-font-resize img-cover" alt="{{ $product->title }}">
            </a>

            @if($product->getAvailability() > 0)
                <div class=" js-font-resize hover-card-action">
                    <button type="button" data-id="{{ $product->id }}" class=" js-font-resize btn-add-product-to-cart d-flex align-items-center justify-content-center border-0 cursor-pointer">
                        <i data-feather="shopping-cart" width="20" height="20" class=" js-font-resize "></i>
                    </button>
                </div>
            @endif
        </div>

        <figcaption class=" js-font-resize product-card-body">
            <div class=" js-font-resize user-inline-avatar d-flex align-items-center">
                <div class=" js-font-resize avatar bg-gray200">
                    <img src="{{ $product->creator->getAvatar() }}" class=" js-font-resize img-cover" alt="{{ $product->creator->full_name }}">
                </div>
                <a href="{{ $product->creator->getProfileUrl() }}" target="_blank" class=" js-font-resize user-name ml-5 font-14">{{ $product->creator->full_name }}</a>
            </div>

            <a href="{{ $product->getUrl() }}">
                <h3 class=" js-font-resize mt-15 product-title font-weight-bold font-16 text-light">{{ clean($product->title,'title') }}</h3>
            </a>

            @if(!empty($product->category))
                <span class=" js-font-resize d-block font-14 mt-10">{{ trans('public.in') }} <a href="/products?category_id={{ $product->category->id }}" target="_blank" class=" js-font-resize text-decoration-underline">{{ $product->category->title }}</a></span>
            @endif

            @include('web.default.includes.webinar.rate',['rate' => $product->getRate()])


            <div class=" js-font-resize product-price-box mt-25">
            @if(!empty($isRewardProducts) and !empty($product->point))
                    <span class=" js-font-resize text-warning real font-14">{{ $product->point }} {{ trans('update.points') }}</span>
                @elseif($product->price > 0)
                    @if($product->getPriceWithActiveDiscountPrice() < $product->price)
                        <span class=" js-font-resize real">{{ handlePrice($product->getPriceWithActiveDiscountPrice(), true, true, false, null, true, 'store') }}</span>
                        <span class=" js-font-resize off ml-10">{{ handlePrice($product->price, true, true, false, null, true, 'store') }}</span>
                    @else
                        <span class=" js-font-resize real">{{ handlePrice($product->price, true, true, false, null, true, 'store') }}</span>
                    @endif
                @else
                    <span class=" js-font-resize real">{{ trans('public.free') }}</span>
                @endif
            </div>
        </figcaption>
    </figure>
</div>
