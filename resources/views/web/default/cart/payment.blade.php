@extends(getTemplate().'.layouts.app')

@push('styles_top')

@endpush

@section('content')
    <section class=" js-font-resize cart-banner position-relative text-center">
        <h1 class=" js-font-resize font-30 text-white font-weight-bold">{{ trans('cart.checkout') }}</h1>
        <span class=" js-font-resize payment-hint font-20 text-white d-block">{{ handlePrice($total) . ' ' .  trans('cart.for_items',['count' => $count]) }}</span>
    </section>

    <section class=" js-font-resize container mt-45">


        @if(!empty($totalCashbackAmount))

            <div class=" js-font-resize d-flex align-items-center mb-25 p-15 success-transparent-alert">
                <div class=" js-font-resize success-transparent-alert__icon d-flex align-items-center justify-content-center">
                    <i data-feather="credit-card" width="18" height="18" class=" js-font-resize "></i>
                </div>

                <div class=" js-font-resize ml-10">
                    <div class=" js-font-resize font-14 font-weight-bold ">{{ trans('update.get_cashback') }}</div>
                    <div class=" js-font-resize font-12 ">{{ trans('update.by_purchasing_this_cart_you_will_get_amount_as_cashback',['amount' => handlePrice($totalCashbackAmount)]) }}</div>
                </div>
            </div>
        @endif

        @php
            $isMultiCurrency = !empty(getFinancialCurrencySettings('multi_currency'));
            $userCurrency = currency();
            $invalidChannels = [];
        @endphp

        <h2 class=" js-font-resize section-title">{{ trans('financial.select_a_payment_gateway') }}</h2>

        <form action="/payments/payment-request" method="post" class=" js-font-resize  mt-25">
            {{ csrf_field() }}
            <input type="hidden" name="order_id" value="{{ $order->id }}">

            <div class=" js-font-resize row">
                {{-- @if(!empty($paymentChannels))
                    @foreach($paymentChannels as $paymentChannel)
                        @if(!$isMultiCurrency or (!empty($paymentChannel->currencies) and in_array($userCurrency, $paymentChannel->currencies)))
                            <div class=" js-font-resize col-6 col-lg-4 mb-40 charge-account-radio">
                                <input type="radio" name="gateway" id="{{ $paymentChannel->title }}" data-class=" js-font-resize {{ $paymentChannel->class_name }}" value="{{ $paymentChannel->id }}">
                                <label for="{{ $paymentChannel->title }}" class=" js-font-resize rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                                    <img src="{{ $paymentChannel->image }}" width="120" height="60" alt="">

                                    <p class=" js-font-resize mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                                        {{ trans('financial.pay_via') }}
                                        <span class=" js-font-resize font-weight-bold font-14">{{ $paymentChannel->title }}</span>
                                    </p>
                                </label>
                            </div>
                        @else
                            @php
                                $invalidChannels[] = $paymentChannel;
                            @endphp
                        @endif
                    @endforeach
                @endif --}}
@php($paymentChannel = $paymentChannels->firstWhere('id', 34))

@if ($paymentChannel)
    <div class=" js-font-resize col-6 col-lg-4 mb-40 charge-account-radio">
        <input type="radio" class=" js-font-resize online-gateway" name="gateway" id="{{ $paymentChannel->class_name }}"
            @if (old('gateway') == $paymentChannel->class_name) checked @endif value="{{ $paymentChannel->class_name }}">
        <label for="{{ $paymentChannel->class_name }}"
            class=" js-font-resize rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
            <img src="{{ asset('store/1/default_images/payment gateways/paymob.png') }}" width="120" height="60" alt="">
            <p class=" js-font-resize mt-50 font-14 font-weight-500 text-dark-blue">
                {{ trans('financial.pay_via') }}
                <span class=" js-font-resize font-weight-bold">{{ $paymentChannel->title }}</span>
            </p>
        </label>
    </div>
@endif


                <div class=" js-font-resize col-6 col-lg-4 mb-40 charge-account-radio">
                    <input type="radio" @if(empty($userCharge) or ($total > $userCharge)) disabled @endif name="gateway" id="offline" value="credit">
                    <label for="offline" class=" js-font-resize rounded-sm p-20 p-lg-40 d-flex flex-column align-items-center justify-content-center">
                        <img src="/assets/default/img/activity/pay.svg" width="120" height="60" alt="">

                        <p class=" js-font-resize mt-30  font-weight-500 text-dark-blue">
                            {{ trans('financial.account') }}
                            <span class=" js-font-resize font-weight-bold">{{ trans('financial.charge') }}</span>
                        </p>

                        <span class=" js-font-resize mt-5">{{ handlePrice($userCharge) }}</span>
                    </label>
                </div>
            </div>

            @if(!empty($invalidChannels) and empty(getFinancialSettings("hide_disabled_payment_gateways")))
                <div class=" js-font-resize d-flex align-items-center mt-30 rounded-lg border p-15">
                    <div class=" js-font-resize size-40 d-flex-center rounded-circle bg-gray200">
                        <i data-feather="info" class=" js-font-resize text-gray" width="20" height="20"></i>
                    </div>
                    <div class=" js-font-resize ml-5">
                        <h4 class=" js-font-resize font-14 font-weight-bold text-gray">{{ trans('update.disabled_payment_gateways') }}</h4>
                        <p class=" js-font-resize font-12 text-gray">{{ trans('update.disabled_payment_gateways_hint') }}</p>
                    </div>
                </div>

                <div class=" js-font-resize row mt-20">
                    @foreach($invalidChannels as $invalidChannel)
                        <div class=" js-font-resize col-6 col-lg-4 mb-40 charge-account-radio">
                            <div class=" js-font-resize disabled-payment-channel bg-white border rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                                <img src="{{ $invalidChannel->image }}" width="120" height="60" alt="">

                                <p class=" js-font-resize mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                                    {{ trans('financial.pay_via') }}
                                    <span class=" js-font-resize font-weight-bold font-14">{{ $invalidChannel->title }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif


            <div class=" js-font-resize d-flex align-items-center justify-content-between mt-45">
                <span class=" js-font-resize font-16 font-weight-500 text-gray">{{ trans('financial.total_amount') }} {{ handlePrice($total) }}</span>
                <button type="button" id="paymentSubmit" disabled class=" js-font-resize btn btn-sm btn-primary">{{ trans('public.start_payment') }}</button>
            </div>
        </form>

        @if(!empty($razorpay) and $razorpay)
            <form action="/payments/verify/Razorpay" method="get">
                <input type="hidden" name="order_id" value="{{ $order->id }}">

                <script src="https://checkout.razorpay.com/v1/checkout.js"
                        data-key="{{ getRazorpayApiKey()['api_key'] }}"
                        data-amount="{{ (int)($order->total_amount * 100) }}"
                        data-buttontext="product_price"
                        data-description="Rozerpay"
                        data-currency="{{ currency() }}"
                        data-image="{{ $generalSettings['logo'] }}"
                        data-prefill.name="{{ $order->user->full_name }}"
                        data-prefill.email="{{ $order->user->email }}"
                        data-theme.color="#43d477">
                </script>
            </form>
        @endif
    </section>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/parts/payment.min.js"></script>
@endpush
