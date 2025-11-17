<div class=" js-font-resize d-none" id="exchangePointsModal">
    <h3 class=" js-font-resize section-title font-16 text-dark-blue mb-25">{{ trans('update.exchange_points') }}</h3>

    <div class=" js-font-resize text-center">
        <img src="/assets/default/img/rewards/wallet.png" class=" js-font-resize exchange-points-modal-img" alt="wallet">

        <p class=" js-font-resize font-14 font-weight-500 text-gray mt-30">
            <span class=" js-font-resize d-block">{{ trans('update.you_will_get_n_for_points',['amount' => handlePrice($earnByExchange) ,'points' => $availablePoints]) }}</span>
            <span class=" js-font-resize d-block">{{ trans('update.the_amount_will_be_charged_to_your_wallet') }}</span>
            <span class=" js-font-resize d-block">{{ trans('update.do_you_want_to_proceed') }}</span>
        </p>
    </div>

    <div class=" js-font-resize d-flex align-items-center mt-25">
        <button type="button" class=" js-font-resize js-apply-exchange btn btn-primary btn-sm flex-grow-1">{{ trans('update.exchange') }}</button>
        <button type="button" class=" js-font-resize close-swl btn btn-danger ml-15 btn-sm flex-grow-1">{{ trans('public.close') }}</button>
    </div>
</div>
