@extends(getTemplate() . '.layouts.app')


@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush



@section('content')


    @php
        $title = "<h1 class='font-30 text-white font-weight-bold'>
            <!-- " . trans('cart.checkout') . ' --> 
             Complete Payment
        </h1>';
        $subTitle = "<span class='payment-hint font-20 text-white d-block'>";

        if ($count > 0) {
            $subTitle .= $total . ' ريال سعودي ' . trans('cart.for_items', ['count' => $count]);
        }
        elseif (!empty($order->orderItems[0]->service)) {
            $subTitle .= trans('panel.service_request_fees') . $order->orderItems[0]->service->title;
        }
        elseif (!empty($type) && $type == 1) {
            $subTitle .= trans('panel.seat_reservation_fees');
            // $subTitle .= 'الرسوم الدراسية للبرنامج : '.($total).' ريال سعودي';
        } elseif (!empty($order->orderItems[0]->bundle)) {
            $subTitle .= trans('panel.tuition_fees_program') . $order->orderItems[0]->bundle->title;
        } elseif (!empty($order->orderItems[0]->webinar)) {
            $subTitle .= trans('panel.tuition_fees_course') . $order->orderItems[0]->webinar->title;
        }
        // close subtitle
        $subTitle .=
            ': <span class="price"> Total payment of ' .
            handlePrice($total) .
            '</span>    <span class="price-with-discount"></span> </span>';
    @endphp

    @include('web.default.includes.hero_section', ['inner' => $title . $subTitle])

    <section class="container mt-45 ">

        @if (!empty($totalCashbackAmount))
            <div class="d-flex align-items-center mb-25 p-15 success-transparent-alert">
                <div class="success-transparent-alert__icon d-flex align-items-center justify-content-center">
                    <i data-feather="credit-card" width="18" height="18" class=""></i>
                </div>

                <div class="ml-10">
                    <div class="font-14 font-weight-bold ">{{ trans('update.get_cashback') }}</div>
                    <div class="font-12 ">
                        {{ trans('update.by_purchasing_this_cart_you_will_get_amount_as_cashback', ['amount' => handlePrice($totalCashbackAmount)]) }}
                    </div>
                </div>
            </div>
        @endif

        @php

            $showOfflineFields = false;
            if ($errors->any() or !empty($editOfflinePayment)) {
                $showOfflineFields = true;
            }

            $isMultiCurrency = !empty(getFinancialCurrencySettings('multi_currency'));
            $userCurrency = currency();
            $invalidChannels = [];
        @endphp

        <!-- <h2 class="section-title">{{ trans('financial.select_a_payment_gateway') }}</h2> -->

        <form action="/payments/payment-request" method="post" class=" mt-25" enctype="multipart/form-data" id="cartForm">
            {{ csrf_field() }}
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <input type="hidden" name="discount_id" id="discount_id" value="{{ $order->orderItems[0]->discount_id }}">


            <div class="row align-items-center">
                {{-- online  --}}
                @if (!empty($paymentChannels))
                    @foreach ($paymentChannels as $paymentChannel)
                        @if (!$isMultiCurrency or !empty($paymentChannel->currencies) and in_array($userCurrency, $paymentChannel->currencies))
                            <div class="col-12 col-md-6 col-lg-4 mb-40 charge-account-radio d-none">
                                <input type="radio" name="gateway" class="online-gateway" checked
                                    id="{{ $paymentChannel->title }}" data-class="{{ $paymentChannel->class_name }}"
                                    value="{{ $paymentChannel->id }}">
                                <label for="{{ $paymentChannel->title }}"
                                    class="rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center bg-secondary-acadima">
                                    {{-- <img src="{{ $paymentChannel->image }}" width="120" height="60" alt=""> --}}
                                    @include('web.default.cart.includes.online_payment_icon')
                                    <p class="mt-30 mt-lg-50 font-weight-500 text-light">
                                        {{ trans('financial.pay_via') }}
                                        <span class="font-weight-bold font-14">{{ $paymentChannel->title }}</span>
                                    </p>
                                </label>
                            </div>
                        @else
                            @php
                                $invalidChannels[] = $paymentChannel;
                            @endphp
                        @endif
                    @endforeach
                @endif

                {{-- offline --}}
                @if (!empty(getOfflineBankSettings('offline_banks_status')))
                    <div class="col-12 col-md-6 col-lg-4 mb-40 charge-account-radio ">
                        <input type="radio" name="gateway" id="offline" value="offline"
                            @if (old('gateway') == 'offline' or !empty($editOfflinePayment)) checked @endif>
                        <label for="offline"
                            class="rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center bg-secondary-acadima">
                            {{-- <img src="/assets/default/img/activity/pay.svg" width="120" height="60" alt=""> --}}
                            @include('web.default.cart.includes.offline_payment_icon')
                            <p class="mt-30 mt-lg-50 font-weight-500 text-light">{{ trans('financial.pay_via') }}
                                <span class="font-weight-bold">{{ trans('financial.offline') }}</span>
                            </p>
                        </label>
                    </div>
                @endif

                @error('gateway')
                    <div class="invalid-feedback d-block"> {{ $message }}</div>
                @enderror

                {{-- account discharge --}}
                {{-- <div class="col-12 col-md-6 col-lg-4 mb-40 charge-account-radio">
                    <input type="radio" @if (empty($userCharge) or $total > $userCharge) disabled @endif name="gateway" id="offline"
                        value="credit">
                    <label for="offline"
                        class="rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                        <img src="/assets/default/img/activity/pay.svg" width="120" height="60" alt="">

                        <p class="mt-30 mt-lg-50 font-weight-500 text-light">
                            {{ trans('financial.account') }}
                            <span class="font-weight-bold">{{ trans('financial.charge') }}</span>
                        </p>

                        <span class="mt-5">{{ handlePrice($userCharge) }}</span>
                    </label>
                </div> --}}
            </div>

            @if (!empty($invalidChannels))
                <div class="d-flex align-items-center mt-30 rounded-lg border p-15">
                    <div class="size-40 d-flex-center rounded-circle bg-gray200">
                        <i data-feather="info" class="text-gray" width="20" height="20"></i>
                    </div>
                    <div class="ml-5">
                        <h4 class="font-14 font-weight-bold text-gray">{{ trans('update.disabled_payment_gateways') }}</h4>
                        <p class="font-12 text-gray">{{ trans('update.disabled_payment_gateways_hint') }}</p>
                    </div>
                </div>

                <div class="row mt-20">
                    @foreach ($invalidChannels as $invalidChannel)
                        <div class="col-6 col-lg-4 mb-40 charge-account-radio">
                            <div
                                class="disabled-payment-channel bg-white border rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                                <img src="{{ $invalidChannel->image }}" width="120" height="60" alt="">

                                <p class="mt-30 mt-lg-50 font-weight-500 text-light">
                                    {{ trans('financial.pay_via') }}
                                    <span class="font-weight-bold font-14">{{ $invalidChannel->title }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- offline banks --}}
            @if (!empty(getOfflineBankSettings('offline_banks_status')))
                <section class="mt-40 js-offline-payment-input mb-3"
                    style="{{ !$showOfflineFields ? 'display:none' : '' }}">
                    <h2 class="section-title">{{ trans('financial.bank_accounts_information') }}</h2>

                    <div class="row mt-25">
                        @foreach ($offlineBanks as $offlineBank)
                            <div class="col-12 col-lg-7 mb-30 mb-lg-0">
                                <div
                                    class="py-25 px-20 rounded-sm panel-shadow d-flex flex-column align-items-center justify-content-center">
                                    <img src="{{ $offlineBank->logo }}" width="120" height="60" alt="">

                                    <div class="mt-15 mt-30 w-100">

                                        <div class="d-flex align-items-center justify-content-between">
                                            <span
                                                class="font-14 font-weight-500 text-secondary">{{ trans('public.name') }}:</span>
                                            <span
                                                class="font-14 font-weight-500 text-gray">{{ $offlineBank->title }}</span>
                                        </div>

                                        @foreach ($offlineBank->specifications as $specification)
                                            <div class="d-flex align-items-center justify-content-between mt-10">
                                                <span
                                                    class="font-14 font-weight-500 text-secondary">{{ $specification->name }}:</span>
                                                <span
                                                    class="font-14 font-weight-500 text-gray">{{ $specification->value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- offline inputs --}}
                <div class="">
                    <h3 class="section-title mb-20 js-offline-payment-input"
                        style="{{ !$showOfflineFields ? 'display:none' : '' }}">{{ trans('financial.finalize_payment') }}
                    </h3>

                    <div class="row">

                        <div class="col-12 col-lg-3 mb-25 mb-lg-0 js-offline-payment-input "
                            style="{{ !$showOfflineFields ? 'display:none' : '' }}">
                            <div class="form-group">
                                <label class="input-label text-light">{{ trans('financial.account') }}</label>
                                <select name="account" class="form-control @error('account') is-invalid @enderror">
                                    <option selected disabled>{{ trans('financial.select_the_account') }}</option>

                                    @foreach ($offlineBanks as $offlineBank)
                                        <option value="{{ $offlineBank->id }}"
                                            @if (old('account') == $offlineBank->id) selected @endif>{{ $offlineBank->title }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('account')
                                    <div class="invalid-feedback"> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        <div class="col-12 col-lg-3 mb-25 mb-lg-0 js-offline-payment-input "
                            style="{{ !$showOfflineFields ? 'display:none' : '' }}">
                            <div class="form-group">
                                <label for="IBAN" class="input-label text-light"> اي بان (IBAN)</label>
                                <input type="text" name="IBAN" id="IBAN" value="{{ old('IBAN') }}"
                                    class="form-control @error('IBAN') is-invalid @enderror" />
                                @error('IBAN')
                                    <div class="invalid-feedback"> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 col-lg-3 mb-25 mb-lg-0 js-offline-payment-input "
                            style="{{ !$showOfflineFields ? 'display:none' : '' }}">
                            <div class="form-group">
                                <label class="input-label text-light">{{ trans('update.attach_the_payment_photo') }}</label>

                                <label for="attachmentFile" id="attachmentFileLabel"
                                    class="custom-upload-input-group flex-row-reverse text-light">
                                    <span class="custom-upload-icon text-light">
                                        <i data-feather="upload" width="18" height="18" class="text-white"></i>
                                    </span>
                                    <div class="custom-upload-input"></div>
                                </label>

                                <input type="file" name="attachment" id="attachmentFile" accept=".jpeg,.jpg,.png"
                                    class="form-control h-auto invisible-file-input @error('attachment') is-invalid @enderror"
                                    value="" />
                                @error('attachment')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>


                    </div>
                </div>
            @endif

            {{-- discount section --}}
            @if(!empty($enableCoupon) )
                <!-- <div >
                    <input type="checkbox" id='discount-checkbox'>
                    <label for="discount-checkbox" class="text-light">{{trans('panel.discount_coupon')}}</label>
                </div> -->
                <div class="row mt-30"  id='discountSection'>
                    <div class="col-12 col-lg-6">
                        <section class="">
                            <h3 class="section-title">
                                <!-- {{ trans('cart.coupon_code') }} -->
                                  Secure Checkout
                            </h3>
                            <div class="rounded-sm shadow mt-25 p-30">
                                <p class="text-gray font-14">{{ trans('cart.coupon_code_hint') }}</p>

                                @if (!empty($userGroup) and !empty($userGroup->discount))
                                    <p class="text-gray mt-25">
                                        {{ trans('cart.in_user_group', ['group_name' => $userGroup->name, 'percent' => $userGroup->discount]) }}
                                    </p>
                                @endif

                                    <div class="form-group">
                                        <input type="hidden" id="order_input" value={{ $order->id }}>
                                        <input type="text" name="coupon" id="coupon_input" class="form-control mt-10"
                                            placeholder="{{ trans('cart.enter_your_code_here') }}">
                                        <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                                    </div>

                                    <p  id="checkCoupon"
                                        class="btn btn-sm btn-primary">{{ trans('cart.validate') }}
                                    </p>
                            </div>
                        </section>
                    </div>

                    <div class="col-12 col-lg-6">
                        <section class="mt-45">
                            {{-- <h3 class="section-title">{{ trans('cart.cart_totals') }}</h3>  --}}
                            <div class="rounded-sm shadow  p-25">
                                <div class="cart-checkout-item">
                                    <h4 class="text-secondary font-14 font-weight-500">{{ trans('cart.sub_total') }}</h4>
                                    <span
                                        class="font-14 text-gray font-weight-bold">{{ handlePrice($order->total_amount) }}</span>
                                </div>

                                <div class="cart-checkout-item">
                                    <h4 class="text-secondary font-14 font-weight-500">{{ trans('public.discount') }} <span id="discount_percent">(0%)</span> </h4>
                                    <span class="font-14 text-gray font-weight-bold">
                                        <span id="totalDiscount">0</span>
                                    </span>
                                </div>

                                <div class="cart-checkout-item border-0">
                                    <h4 class="text-secondary font-14 font-weight-500">{{ trans('cart.total') }}</h4>
                                    <span class="font-14 text-gray font-weight-bold"><span
                                            id="totalAmount">{{ handlePrice($order->total_amount) }}</span></span>
                                </div>

                                {{--  <button type="submit" class="btn btn-sm btn-primary mt-15">{{ trans('cart.checkout') }}</button> --}}
                            </div>
                        </section>
                    </div>
                </div>
            @endif

            <div class="d-flex align-items-center justify-content-between mt-45">
                <span class="font-16 font-weight-500 text-gray">{{ trans('financial.total_amount') }}
                    <span class="price"> {{ handlePrice($total) }}</span> <span class="price-with-discount"></span>
                </span>
                <button type="submit" id="paymentSubmit"
                    class="btn btn-sm btn-primary">{{ trans('public.start_payment') }}</button>
            </div>
        </form>


        @if (!empty($razorpay) and $razorpay)
            <form action="/payments/verify/Razorpay" method="get">
                <input type="hidden" name="order_id" value="{{ $order->id }}">

                <script src="https://checkout.razorpay.com/v1/checkout.js" data-key="{{ env('RAZORPAY_API_KEY') }}"
                    data-amount="{{ (int) ($order->total_amount * 100) }}" data-buttontext="product_price" data-description="Rozerpay"
                    data-currency="{{ currency() }}" data-image="{{ $generalSettings['logo'] }}"
                    data-prefill.name="{{ $order->user->full_name }}" data-prefill.email="{{ $order->user->email }}"
                    data-theme.color="#43d477"></script>
            </form>
        @endif






    </section>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/parts/payment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script src="/assets/default/js/panel/financial/account.min.js"></script>



    <script src="/assets/default/js//parts/main.min.js"></script>
    <script src="/assets/default/js/panel/public.min.js"></script>
    <script>
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>
    <script src="/assets/default/js/parts/cart.min.js"></script>

    <script>
        window.onload = function(){

            let discountCheckbox= true;
            let discountSection= document.getElementById('discountSection');

            discountCheckbox.onchange = function(e){
                discountSection.classList.toggle('d-none');
            }
        }
    </script>
@endpush
