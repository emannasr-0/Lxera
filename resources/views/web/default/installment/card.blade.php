<style>
    .installment-card__payments .installment-step:first-child:before {

        background: var(--primary);
    }
</style>
<div class=" js-font-resize installment-card p-15 w-100 h-100">
    <div class=" js-font-resize row">
        <div class=" js-font-resize col-12">
            <h4 class=" js-font-resize font-16 font-weight-bold text-pink text-center">
                <!-- {{ $installment->main_title }} -->
                  Early Registration Installment Plan
            </h4>

            <div class=" js-font-resize ">
                <p class=" js-font-resize text-dark font-14 text-center">
                    <!-- {{ nl2br($installment->description) }} -->
                      Affordable Installment Plan
                </p>
            </div>

            {{-- @if (!empty($installment->capacity))
                @php
                    $reachedCapacityPercent = $installment->reachedCapacityPercent();
                @endphp

                @if ($reachedCapacityPercent > 0)
                    <div class=" js-font-resize mt-20 d-flex align-items-center">
                        <div class=" js-font-resize progress card-progress flex-grow-1">
                            <span class=" js-font-resize progress-bar rounded-sm {{ $reachedCapacityPercent > 50 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ $reachedCapacityPercent }}%"></span>
                        </div>
                        <div class=" js-font-resize ml-10 font-12 text-danger">{{ trans('update.percent_capacity_reached',['percent' => $reachedCapacityPercent]) }}</div>
                    </div>
                @endif
            @endif --}}

            @if (!empty($installment->banner))
                <div class=" js-font-resize mt-20">
                    <img src="{{ $installment->banner }}" alt="{{ $installment->main_title }}" class=" js-font-resize img-fluid">
                </div>
            @endif

            @if (!empty($installment->options))
                <div class=" js-font-resize mt-20">
                    @php
                        $installmentOptions = explode(
                            \App\Models\Installment::$optionsExplodeKey,
                            $installment->options,
                        );
                    @endphp

                    @foreach ($installmentOptions as $installmentOption)
                        <div class=" js-font-resize d-flex align-items-center mb-1">
                            <i data-feather="check" width="25" height="25" class=" js-font-resize text-primary"></i>
                            <span class=" js-font-resize ml-10 font-14 text-gray">{{ $installmentOption }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class=" js-font-resize col-12 p-0">
            <div class=" js-font-resize installment-card__payments d-flex flex-column w-100 h-100 pt-0">

                @php
                    // dd($itemId);
                    $totalPayments = $installment->totalPayments($itemPrice ?? 1);
                    $installmentTotalInterest = $installment->totalInterest($itemPrice, $totalPayments);
                @endphp

                <div class=" js-font-resize d-flex align-items-center justify-content-center flex-column order-1 mt-20">
                    {{-- <p style="text-decoration: line-through;">
                        @if ($bundleData['bundle']->bundle->discount_rate == 0.2511)
                            {{ handlePrice(($totalPayments / (1 - 0.2511))) }}
                        @else
                            {{ handlePrice(($totalPayments / (1 - 0.2324))) }}
                        @endif

                    </p> --}}

                    {{-- <p style="text-decoration: line-through;">
                        {{round($totalPayments/(1 - 0.3)) }} ر.س
                    </p> --}}
                    <span class=" js-font-resize font-36 font-weight-bold text-primary">{{ handlePrice($totalPayments) }}</span>
                    {{-- <p class=" js-font-resize font-12 font-weight-bold text-center text-danger mt-15 discount">
                        خصم {{ substr(explode('.', $bundleData['bundle']->bundle->discount_rate)[1], 0, 2) }}
                        % عند دفع كامل الرسوم مرة واحده
                    </p> --}}
                    {{-- <span class=" js-font-resize mt-10 font-12 text-gray">{{ trans('update.total_payment') }} @if ($installmentTotalInterest > 0)
                            ({{ trans('update.percent_interest',['percent' => $installmentTotalInterest]) }})
                        @endif</span> --}}
                </div>

                <div class=" js-font-resize mt-25 mb-15 order-3 mt-35">
                    <div class=" js-font-resize installment-step d-flex align-items-center font-12 text-gray">
                        {{ !empty($installment->upfront) ? trans('update.amount_upfront', ['amount' => handlePrice($installment->getUpfront($itemPrice))]) . ($installment->upfront_type == 'percent' ? " ({$installment->upfront}%)" : '') : trans('update.no_upfront') }}
                    </div>

                    @foreach ($installment->steps as $installmentStep)
                        <div class=" js-font-resize installment-step d-flex align-items-center font-12 text-gray">
                            {{ $installmentStep->getDeadlineTitle($itemPrice, $itemId) }}</div>
                    @endforeach
                </div>

                <a href="/installments/{{ $installment->id }}?item={{ $itemId }}&item_type={{ $itemType }}&{{ http_build_query(request()->all()) }}"
                    target="_blank"
                    class=" js-font-resize btn btn-primary btn-block mt-20 order-2">{{ trans('panel.pay_in_installments_here') }}</a>
            </div>
        </div>
    </div>
</div>
