@php

    $hasBought = $bundleData['bundle']->bundle->checkUserHasBought(auth()->user());
    $canSale = ($bundleData['bundle']->bundle->canSale() and !$hasBought);
    $mainTitle = trans('panel.early_registration_fees');
    $subTitle = trans('panel.price_inclusive_tax');

@endphp

<section class="row mx-0 col-12">
    {{-- <div class="col-12 text-center mb-20">
        @if (!($hasBought or !empty($bundleData['bundle']->bundle->getInstallmentOrder())) && !empty($bundleData['bundle']->studentRequirement))
            <p class="alert alert-success text-center">
                لقد تم بالفعل رفع متطلبات القبول وتم الموافقة عليها يرجي الذهاب للخطوة
                التاليه
                للدفع
            </p>
        @endif
    </div> --}}

    {{-- has bought --}}
    @if ($hasBought)
        @php
            if (!empty($bundleData['bundle']->bundle->getInstallmentOrder())) {
                $mainTitle = trans('panel.installment');
            }
        @endphp

        <div class="col-12 mb-md-0 mb-20">
            <div class="installment-card p-15 w-100 h-100">
                <div class="row">
                    <div class="col-12">
                        <h4 class="font-16 font-weight-bold text-pink">
                            {{ $mainTitle }}
                        </h4>

                        <div class="">
                            <p class="text-dark font-14 text-ellipsis">
                                {{ $subTitle }}
                            </p>
                        </div>
                    </div>

                    <div class="col-12 p-0">
                        <div class=" d-flex flex-column w-100 pt-0">


                            <div class="order-2 col-12">

                                <div class="mt-20 d-flex flex-column">

                                    <button type="button" class="btn btn-primary"
                                        disabled>{{ trans('panel.purchased') }}</button>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- direct buy --}}
        <div
            class="col-12 mb-md-0 mb-20 {{ !empty($bundleData['installments']) && count($bundleData['installments']) ? 'col-md-6' : '' }}">
            <div class="installment-card p-15 w-100 h-100">
                <div class="row">
                    <div class="col-12">
                        <h4 class="font-16 font-weight-bold text-pink text-center">
                            {{ trans('panel.early_registration_fee') }}
                        </h4>

                        <div class="">
                            <p class="text-dark font-14 text-center">
                                <!-- {{ trans('panel.price_includes_tax') }} -->
                                {{ trans('panel.best_value_single_payment') }}
                            </p>
                        </div>
                    </div>

                    <div class="col-12 p-0">
                        <div class="installment-card__payments d-flex flex-column w-100 pt-0">

                            <div class="d-flex align-items-center justify-content-center flex-column order-1">
                                @if ($bundleData['bundle']->bundle->price > 0)
                                    <div id="priceBox"
                                        class="order-1 col-12 text-center d-flex align-items-center justify-content-center mt-20 {{ !empty($activeSpecialOffer) ? ' flex-column ' : '' }}">
                                        <div class="text-center">
                                            @php
                                                $realPrice = handleCoursePagePrice(
                                                    $bundleData['bundle']->bundle->price,
                                                );
                                            @endphp
                                            @if (!($hasBought or !empty($bundleData['bundle']->bundle->getInstallmentOrder())))
                                                <p style="text-decoration: line-through;">
                                                    {{-- {{ handleCoursePagePrice($bundleData['bundle']->bundle->price / (1 - $bundleData['bundle']->bundle->discount_rate))['price'] }} --}}

                                                    {{-- {{ round($bundleData['bundle']->bundle->price /(1 - 0.3)) }} ر.س --}}

                                                    {{-- {{ handleCoursePagePrice(($bundleData['bundle']->bundle->price + ($bundleData['bundle']->bundle->price * 0.30)) )}} --}}

                                                </p>

                                                <span id="realPrice"
                                                    data-value="{{ $bundleData['bundle']->bundle->price }}"
                                                    data-special-offer="{{ !empty($activeSpecialOffer) ? $activeSpecialOffer->percent : '' }}"
                                                    class="d-block @if (!empty($activeSpecialOffer)) font-16 text-gray text-decoration-line-through @else font-36 text-primary @endif">
                                                    {{ $realPrice['price'] }}
                                                </span>

                                                {{-- <p class="font-12 font-weight-bold text-center text-danger mt-15 discount">
                                                 خصم
                                                {{ substr(explode('.', $bundleData['bundle']->bundle->discount_rate)[1], 0, 2) }}
                                                % عند دفع كامل الرسوم مرة واحده
                                            </p> --}}
                                            @endif

                                            @if (!empty($realPrice['tax']) and empty($activeSpecialOffer))
                                                <span class="d-block font-14 text-gray">+
                                                    {{ $realPrice['tax'] }}
                                                    tax</span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="d-flex align-items-center justify-content-center mt-20 order-1 col-12 ">
                                        <span class="font-36 text-primary">{{ trans('public.free') }}</span>
                                    </div>
                                @endif
                            </div>


                            <div class=" mb-15 order-3">
                                <div class="d-flex align-items-center font-12 text-gray">
                                    @if (!($hasBought or !empty($bundleData['bundle']->bundle->getInstallmentOrder())))
                                        <section class="bundle-details mt-3 order-3 col-12">
                                            <p class="bundle-details text-dark mt-10">
                                                <!-- {{ trans('panel.online_program_100') }} -->
                                                  Single payment to enroll in the program
                                            </p>

                                            {{-- <p class="bundle-details text-pink mt-10">
                                            مكونة من
                                            {{ $bundleData['bundle']->bundle->bundleWebinars->count() }}
                                            فصول دراسية
                                        </p>
                                        <p class="bundle-details text-dark mt-10">
                                            مكونة من
                                            {{ convertMinutesToHourAndMinute($bundleData['bundle']->bundle->getBundleDuration()) }}
                                            ساعات دراسية
                                        </p> --}}
                                        </section>
                                    @endif
                                </div>
                            </div>

                            <form action="{{ route('purchase_bundle') }}" method="POST" class="order-2 col-12">
                                {{ csrf_field() }}
                                <input type="hidden" name="item_id" value="{{ $bundleData['bundle']->bundle->id }}">



                                <div class="mt-20 d-flex flex-column">
                                    @if ($hasBought or !empty($bundleData['bundle']->bundle->getInstallmentOrder()))
                                        <button type="button" class="btn btn-primary"
                                            disabled>{{ trans('panel.purchased') }}</button>
                                    @elseif($bundleData['bundle']->bundle->price > 0)
                                        <button type="{{ $canSale ? 'submit' : 'button' }}"
                                            @if (!$canSale) disabled @endif class="btn btn-primary">
                                            @if (!$canSale)
                                                {{ trans('update.disabled_add_to_cart') }}
                                            @else
                                                {{ trans('panel.pay_full_fee_here') }}
                                            @endif
                                        </button>


                                        @if ($canSale and !empty(getFeaturesSettings('direct_bundles_payment_button_status')))
                                            <button type="button"
                                                class="btn btn-outline-danger js-bundle-direct-payment">
                                                {{ trans('update.buy_now') }}
                                            </button>
                                        @endif
                                    @else
                                        <a href="{{ $canSale ? '/bundles/' . $bundleData['bundle']->bundle->slug . '/free' : '#' }}"
                                            class="btn btn-primary @if (!$canSale) disabled @endif">{{ trans('update.enroll_on_bundle') }}</a>
                                    @endif
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- installment --}}
        @if (!empty($bundleData['installments']) && count($bundleData['installments']) > 0)
            <div class="col-12 col-md-6">
                @include('web.default.installment.card', [
                    'installment' => $bundleData['installments']->last(),
                    'itemPrice' => $bundleData['bundle']->bundle->getPrice(),
                    'itemId' => $bundleData['bundle']->bundle->id,
                    'itemType' => 'bundles',
                ])
            </div>
        @endif

        @if (empty($bundleData['bundle']->class_id) && empty($bundleData['bundle']->bridging))
            <div class="text-center mx-auto mt-15">
                <p class="text-pink font-weight-bold">{{trans('panel.or')}}</p>
                <p class="text-dark font-14 text-ellipsis">Not ready to pay now, dont miss out reserve your place today and pay later </p>
                <a href="/panel/{{ $bundleData['bundle']->bundle->id }}/book_seat"
                    class="btn btn-acadima-primary mx-auto mt-5">{{trans('panel.reserve_seat_here')}}  </a>
            </div>
        @endif

    @endif
</section>
