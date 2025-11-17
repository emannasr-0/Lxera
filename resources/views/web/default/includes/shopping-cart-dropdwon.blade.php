<div class=" js-font-resize dropdown">
    <button type="button" {{ (empty($userCarts) or count($userCarts) < 1) ? 'disabled' : '' }} class=" js-font-resize btn btn-transparent dropdown-toggle" id="navbarShopingCart" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
        <i data-feather="shopping-cart" width="20" height="20" class=" js-font-resize mr-10"></i>

        @if(!empty($userCarts) and count($userCarts))
            <span class=" js-font-resize badge badge-circle-primary d-flex align-items-center justify-content-center">{{ count($userCarts) }}</span>
        @endif
    </button>

    <div class=" js-font-resize dropdown-menu" aria-labelledby="navbarShopingCart">
        <div class=" js-font-resize d-md-none border-bottom mb-20 pb-10 text-right">
            <i class=" js-font-resize close-dropdown" data-feather="x" width="32" height="32" class=" js-font-resize mr-10"></i>
        </div>
        <div class=" js-font-resize h-100">
            <div class=" js-font-resize navbar-shopping-cart h-100" data-simplebar>
                @if(!empty($userCarts) and count($userCarts) > 0)
                    <div class=" js-font-resize mb-auto">
                        @foreach($userCarts as $cart)
                            @php
                                $cartItemInfo = $cart->getItemInfo();
                                $cartTaxType = !empty($cartItemInfo['isProduct']) ? 'store' : 'general';
                            @endphp

                            @if(!empty($cartItemInfo))
                                <div class=" js-font-resize navbar-cart-box d-flex align-items-center">

                                    <a href="{{ $cartItemInfo['itemUrl'] }}" target="_blank" class=" js-font-resize navbar-cart-img">
                                        <img src="{{ $cartItemInfo['imgPath'] }}" alt="product title" class=" js-font-resize img-cover"/>
                                    </a>

                                    <div class=" js-font-resize navbar-cart-info">
                                        <a href="{{ $cartItemInfo['itemUrl'] }}" target="_blank">
                                            <h4>{{ $cartItemInfo['title'] }}</h4>
                                        </a>
                                        <div class=" js-font-resize price mt-10">
                                            @if(!empty($cartItemInfo['discountPrice']))
                                                <span class=" js-font-resize text-primary font-weight-bold">{{ handlePrice($cartItemInfo['discountPrice'], true, true, false, null, true, $cartTaxType) }}</span>
                                                <span class=" js-font-resize off ml-15">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true, $cartTaxType) }}</span>
                                            @else
                                                <span class=" js-font-resize text-primary font-weight-bold">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true, $cartTaxType) }}</span>
                                            @endif

                                            @if(!empty($cartItemInfo['quantity']))
                                                <span class=" js-font-resize font-12 text-warning font-weight-500 ml-10">({{ $cartItemInfo['quantity'] }} {{ trans('update.product') }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class=" js-font-resize navbar-cart-actions">
                        <div class=" js-font-resize navbar-cart-total mt-15 border-top d-flex align-items-center justify-content-between">
                            <strong class=" js-font-resize total-text">{{ trans('cart.total') }}</strong>
                            <strong class=" js-font-resize text-primary font-weight-bold">{{ !empty($totalCartsPrice) ? handlePrice($totalCartsPrice, true, true, false, null, true, $cartTaxType) : 0 }}</strong>
                        </div>

                        <a href="/cart/" class=" js-font-resize btn btn-sm btn-primary btn-block mt-50 mt-md-15">{{ trans('cart.go_to_cart') }}</a>
                    </div>
                @else
                    <div class=" js-font-resize d-flex align-items-center text-center py-50">
                        <i data-feather="shopping-cart" width="20" height="20" class=" js-font-resize mr-10"></i>
                        <span class=" js-font-resize ">{{ trans('cart.your_cart_empty') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
