<div class=" js-font-resize text-left">
    <h3 class=" js-font-resize section-title font-16 text-dark-blue mb-25">{{ trans('update.upgrade_your_plan') }}</h3>

    <div class=" js-font-resize text-center">
        <img src="/assets/default/img/icons/diamond.png" class=" js-font-resize buy-with-points-modal-img" alt="diamond">

        <p class=" js-font-resize font-14 font-weight-500 text-gray mt-30">
            <span class=" js-font-resize d-block">{{ trans('update.your_account_limited') }}</span>
            <span class=" js-font-resize d-block">{{ trans('update.your_account_'. $type .'_limited_hint') }}</span>
            @if(!empty($currentCount))
                <span class=" js-font-resize d-block">{{ trans('update.your_current_plan_'.$type,['count' => $currentCount]) }}</span>
            @endif
        </p>
    </div>

    <div class=" js-font-resize d-flex align-items-center mt-25">
        <a href="/panel/financial/registration-packages" class=" js-font-resize btn btn-primary btn-sm flex-grow-1">{{ trans('update.upgrade') }}</a>
        <button type="button" class=" js-font-resize btn btn-outline-danger ml-15 btn-sm flex-grow-1 close-swl">{{ trans('public.cancel') }}</button>
    </div>
</div>
