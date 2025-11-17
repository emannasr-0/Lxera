<div class=" js-font-resize d-none" id="productShareModal">
    <h3 class=" js-font-resize section-title after-line font-20 text-dark-blue mb-25">{{ trans('public.share') }}</h3>

    <div class=" js-font-resize text-center">
        <i data-feather="share-2" width="50" height="50" class=" js-font-resize webinar-icon"></i>

        <p class=" js-font-resize mt-20 font-14">{{ trans('update.share_this_product_with_others') }}</p>

        <div class=" js-font-resize position-relative d-flex align-items-center justify-content-between p-15 mt-15 border border-gray250 rounded-sm mt-5">
            <div class=" js-font-resize js-product-share-link font-weight-bold px-16 text-ellipsis font-14">{{ $product->getUrl() }}</div>

            <button type="button" class=" js-font-resize js-product-share-link-copy btn btn-primary btn-sm font-14 font-weight-500 flex-none" data-toggle="tooltip" data-placement="top" title="{{ trans('public.copy') }}">{{ trans('public.copy') }}</button>
        </div>

        <div class=" js-font-resize mt-32 mt-lg-40 row align-items-center font-14">
            <a href="{{ $product->getShareLink('telegram') }}" target="_blank" class=" js-font-resize col text-center">
                <img src="/assets/default/img/social/telegram.svg" width="50" height="50" alt="telegram">
                <span class=" js-font-resize mt-10 d-block">{{ trans('public.telegram') }}</span>
            </a>

            <a href="{{ $product->getShareLink('whatsapp') }}" target="_blank" class=" js-font-resize col text-center">
                <img src="/assets/default/img/social/whatsapp.svg" width="50" height="50" alt="whatsapp">
                <span class=" js-font-resize mt-10 d-block">{{ trans('public.whatsapp') }}</span>
            </a>

            <a href="{{ $product->getShareLink('facebook') }}" target="_blank" class=" js-font-resize col text-center">
                <img src="/assets/default/img/social/facebook.svg" width="50" height="50" alt="facebook">
                <span class=" js-font-resize mt-10 d-block">{{ trans('public.facebook') }}</span>
            </a>

            <a href="{{ $product->getShareLink('twitter') }}" target="_blank" class=" js-font-resize col text-center">
                <img src="/assets/default/img/social/twitter.svg" width="50" height="50" alt="twitter">
                <span class=" js-font-resize mt-10 d-block">{{ trans('public.twitter') }}</span>
            </a>
        </div>
    </div>

    <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
        <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
    </div>
</div>
