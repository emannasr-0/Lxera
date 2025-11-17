<div class=" js-font-resize special-offer-card d-flex flex-column flex-md-row align-items-center justify-content-between rounded-lg shadow-xs bg-white p-15 p-md-30">
    <div class=" js-font-resize d-flex flex-column">
        <strong class=" js-font-resize special-offer-title font-16 text-dark-blue font-weight-bold">{{ trans('panel.special_offer') }}</strong>
        <span class=" js-font-resize font-14 text-gray">{{ $activeSpecialOffer->name }}</span>
    </div>

    <div class=" js-font-resize mt-20 mt-md-0 mb-30 mb-md-0">
        @php
            $remainingTimes = $activeSpecialOffer->getRemainingTimes()
        @endphp
        <div id="offerCountDown" class=" js-font-resize d-flex time-counter-down"
             data-day="{{ $remainingTimes['day'] }}"
             data-hour="{{ $remainingTimes['hour'] }}"
             data-minute="{{ $remainingTimes['minute'] }}"
             data-second="{{ $remainingTimes['second'] }}">

            <div class=" js-font-resize d-flex align-items-center flex-column mr-10">
                <span class=" js-font-resize bg-gray300 rounded p-10 font-16 font-weight-bold text-dark time-item days"></span>
                <span class=" js-font-resize font-12 mt-1 text-gray">{{ trans('public.day') }}</span>
            </div>
            <div class=" js-font-resize d-flex align-items-center flex-column mr-10">
                <span class=" js-font-resize bg-gray300 rounded p-10 font-16 font-weight-bold text-dark time-item hours"></span>
                <span class=" js-font-resize font-12 mt-1 text-gray">{{ trans('public.hr') }}</span>
            </div>
            <div class=" js-font-resize d-flex align-items-center flex-column mr-10">
                <span class=" js-font-resize bg-gray300 rounded p-10 font-16 font-weight-bold text-dark time-item minutes"></span>
                <span class=" js-font-resize font-12 mt-1 text-gray">{{ trans('public.min') }}</span>
            </div>
            <div class=" js-font-resize d-flex align-items-center flex-column">
                <span class=" js-font-resize bg-gray300 rounded p-10 font-16 font-weight-bold text-dark time-item seconds"></span>
                <span class=" js-font-resize font-12 mt-1 text-gray">{{ trans('public.sec') }}</span>
            </div>
        </div>
    </div>

    <div class=" js-font-resize offer-percent-box d-flex flex-column align-items-center justify-content-center">
        <span class=" js-font-resize percent text-white">{{ $activeSpecialOffer->percent }}%</span>
        <span class=" js-font-resize off text-white">{{ trans('public.off') }}</span>
    </div>
</div>
