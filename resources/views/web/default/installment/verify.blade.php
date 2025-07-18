@extends('web.default.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/video/video-js.min.css">
@endpush

@section('content')

    {{-- hero section --}}
    @if ($installment->needToVerify())
        @include('web.default.includes.hero_section', [
            'inner' =>
                "<h1 style='color:#fff' class='font-36'>" .
                trans('panel.mortgage_review_confirmation') .
                '</h1>',
        ])
    @else
        @include('web.default.includes.hero_section', [
            'inner' =>
                "<h1 style='color:#fff' class='font-36'>" .
                trans('panel.mortgage_review_confirmation') .
                '</h1>',
        ])
    @endif

    <div class="container pt-50 mt-10">
        {{--  <div class="text-center"> --}}


        {{--   @if ($installment->needToVerify()) --}}
        {{--  <h1 class="font-36">مراجعة وتأكيد الأقساط </h1> --}}
        {{-- {{ trans('update.verify_your_installments') }} --}}
        {{-- <p class="mt-10 font-16 text-gray">{{ trans('update.verify_your_installments_hint') }}</p> --}}

        {{--   @else --}}
        {{--  <h1 class="font-36">مراجعة وتأكيد الأقساط </h1 > --}}
        {{-- {{ trans('update.verify_your_installments2') }} --}}
        {{-- <p class="mt-10 font-16 text-gray">{{ trans('update.verify_your_installments_hint2') }}</p> --}}

        {{--  @endif --}}
        {{-- </div> --}}

        <div
            class="become-instructor-packages d-flex align-items-center flex-column flex-lg-row mt-50 shadow border rounded-lg p-15 p-lg-25">
            {{-- <div class="default-package-icon">
                <img src="/assets/default/img/become-instructor/default.png" class="img-cover" alt="{{ trans('update.installment_overview') }}" width="176" height="144">
            </div> --}}

            <div class="ml-lg-25 w-100 mt-20 mt-lg-0">
                <h2 class="font-24 font-weight-bold text-pink">{{ trans('panel.overview_of_installment') }}</h2>
                {{-- <h2 class="font-24 font-weight-bold text-light">{{ trans('update.installment_overview') }}</h2> --}}

                @if ($itemType == 'course')
                    <a href="{{ $item->getUrl() }}" target="_blank"
                        class="font-14 font-weight-500 text-dark">Diploma in {{ $item->title }}</a>
                @else
                    <div class="font-14 font-weight-500 text-dark">Diploma in {{ $item->title }}</div>
                @endif

                <div class="d-flex flex-wrap align-items-center justify-content-around w-100">

                    {{-- <div class="d-flex align-items-center mt-20">
                        <i data-feather="check-square" width="20" height="20" class="text-gray"></i>
                        <span class="font-14 text-light ml-5">{{ !empty($installment->upfront) ? handlePrice($installment->getUpfront($itemPrice)).' '. trans('update.upfront') : trans('update.no_upfront') }}</span>
                    </div> --}}

                    <div class="col-12 p-0 d-flex align-items-center mt-20">
                        <svg width="20" height="20" viewBox="0 0 24 21" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_14_166)">
                                <path
                                    d="M11.4751 0.00504603C13.1159 0.00504603 14.7566 -0.00840416 16.3974 0.00929346C17.8982 0.0255753 19.3057 0.380236 20.517 1.33449C21.8591 2.3921 22.5587 3.81782 22.7926 5.50547C22.874 6.09444 22.8406 6.68625 22.8441 7.27665C22.8448 7.4338 22.8873 7.53786 23.0244 7.62352C23.5973 7.98243 23.9238 8.51831 23.9579 9.19082C24.0038 10.1061 24.0296 11.0264 23.9398 11.941C23.8799 12.5449 23.5736 13.022 23.0689 13.3483C22.8998 13.4574 22.8378 13.5784 22.8448 13.778C22.8796 14.7875 22.8441 15.7927 22.5141 16.7597C21.6983 19.1489 20.0193 20.4876 17.594 20.8996C17.1144 20.981 16.6285 21.0001 16.1419 21.0001C12.9384 20.9987 9.73484 21.0029 6.53131 20.9973C5.26995 20.9951 4.06149 20.7509 2.96442 20.0869C1.3007 19.0788 0.399923 17.554 0.0998968 15.6363C0.0142745 15.0955 -0.0010401 14.5482 -0.00034398 14.0003C0.0031366 11.5283 -0.00521679 9.05632 0.00383272 6.58431C0.00870553 5.32 0.255131 4.10948 0.899734 3.0101C1.90423 1.29839 3.42733 0.394394 5.33469 0.102737C5.89367 0.0170804 6.45683 0.00150651 7.02138 0.00363022C8.5062 0.00858556 9.99032 0.00504603 11.4751 0.00504603ZM11.4445 19.2296V19.231C13.0073 19.231 14.5708 19.231 16.1335 19.231C16.5067 19.231 16.8791 19.2325 17.2501 19.1779C18.5588 18.9868 19.6441 18.4177 20.3833 17.2666C21.0509 16.2281 21.1637 15.0601 21.1421 13.8616C21.1393 13.7094 21.0419 13.6988 20.9291 13.6988C20.5212 13.6988 20.1125 13.7094 19.7046 13.6959C18.0486 13.6421 16.6187 12.3205 16.5463 10.6732C16.4753 9.07685 17.5905 7.61078 19.1331 7.35522C19.7123 7.25895 20.2935 7.31841 20.8741 7.30921C21.0732 7.30567 21.1498 7.25399 21.1484 7.03879C21.147 6.64944 21.1344 6.26151 21.0927 5.8757C20.9479 4.54342 20.4293 3.41502 19.3259 2.62075C18.5268 2.04523 17.617 1.78614 16.6549 1.78118C13.3818 1.76277 10.108 1.77339 6.83413 1.77268C6.4436 1.77268 6.05448 1.77268 5.66535 1.82578C4.27381 2.0155 3.13914 2.62642 2.39778 3.8787C1.94739 4.63899 1.7483 5.47857 1.74412 6.35566C1.73229 8.95155 1.73925 11.5481 1.73855 14.144C1.73855 14.5235 1.73786 14.9022 1.79007 15.2802C1.98359 16.6755 2.59269 17.806 3.79975 18.5536C4.53764 19.0102 5.35418 19.2162 6.20762 19.2247C7.95278 19.2424 9.69795 19.2296 11.4438 19.2296H11.4445ZM20.7725 11.9205C20.7725 11.9205 20.7725 11.9212 20.7725 11.9219C21.1539 11.9219 21.5354 11.9205 21.9169 11.9219C22.1285 11.9226 22.2413 11.8249 22.2413 11.6041C22.2413 10.8721 22.2406 10.1408 22.2413 9.40886C22.2413 9.19224 22.1417 9.0811 21.9259 9.0818C21.1198 9.08393 20.313 9.06694 19.5076 9.09242C18.8686 9.11295 18.2121 9.78617 18.2762 10.5741C18.3395 11.3513 18.9605 11.9014 19.7318 11.9191C20.0784 11.9269 20.4251 11.9205 20.7718 11.9205H20.7725Z"
                                    fill="#c14b93" class=" cls-3 "></path>
                                <path
                                    d="M9.74904 7.97669C8.63874 7.97669 7.52843 7.97952 6.41813 7.97456C5.9935 7.97244 5.65658 7.67087 5.5814 7.24825C5.51109 6.85678 5.74707 6.44761 6.12506 6.29612C6.23296 6.25294 6.34364 6.24515 6.45711 6.24515C8.66032 6.24515 10.8635 6.24374 13.0667 6.24586C13.5805 6.24586 13.9647 6.61751 13.9696 7.10455C13.9752 7.59796 13.5826 7.97527 13.0535 7.97669C11.9523 7.97952 10.8503 7.9774 9.74904 7.97669Z"
                                    fill="#c14b93" class=" cls-3 "></path>
                            </g>
                            <defs>
                                <clipPath id="clip0_14_166">
                                    <rect width="24" height="21" fill="white"></rect>
                                </clipPath>
                            </defs>
                        </svg>
                        <span class="font-14 text-dark ml-5">{{ $installment->steps_count + 1 }}
                            {{ trans('update.installments') }}
                            (Total {{ handlePrice($installment->totalPayments($itemPrice, false) + (!empty($installment->upfront) ? $installment->getUpfront($itemPrice) : 0)) }})</span>
                    </div>

                    {{-- <div class="d-flex align-items-center mt-20"> --}}
                    {{-- <svg width="20" height="20" viewBox="0 0 24 21" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip0_14_166)">
<path d="M11.4751 0.00504603C13.1159 0.00504603 14.7566 -0.00840416 16.3974 0.00929346C17.8982 0.0255753 19.3057 0.380236 20.517 1.33449C21.8591 2.3921 22.5587 3.81782 22.7926 5.50547C22.874 6.09444 22.8406 6.68625 22.8441 7.27665C22.8448 7.4338 22.8873 7.53786 23.0244 7.62352C23.5973 7.98243 23.9238 8.51831 23.9579 9.19082C24.0038 10.1061 24.0296 11.0264 23.9398 11.941C23.8799 12.5449 23.5736 13.022 23.0689 13.3483C22.8998 13.4574 22.8378 13.5784 22.8448 13.778C22.8796 14.7875 22.8441 15.7927 22.5141 16.7597C21.6983 19.1489 20.0193 20.4876 17.594 20.8996C17.1144 20.981 16.6285 21.0001 16.1419 21.0001C12.9384 20.9987 9.73484 21.0029 6.53131 20.9973C5.26995 20.9951 4.06149 20.7509 2.96442 20.0869C1.3007 19.0788 0.399923 17.554 0.0998968 15.6363C0.0142745 15.0955 -0.0010401 14.5482 -0.00034398 14.0003C0.0031366 11.5283 -0.00521679 9.05632 0.00383272 6.58431C0.00870553 5.32 0.255131 4.10948 0.899734 3.0101C1.90423 1.29839 3.42733 0.394394 5.33469 0.102737C5.89367 0.0170804 6.45683 0.00150651 7.02138 0.00363022C8.5062 0.00858556 9.99032 0.00504603 11.4751 0.00504603ZM11.4445 19.2296V19.231C13.0073 19.231 14.5708 19.231 16.1335 19.231C16.5067 19.231 16.8791 19.2325 17.2501 19.1779C18.5588 18.9868 19.6441 18.4177 20.3833 17.2666C21.0509 16.2281 21.1637 15.0601 21.1421 13.8616C21.1393 13.7094 21.0419 13.6988 20.9291 13.6988C20.5212 13.6988 20.1125 13.7094 19.7046 13.6959C18.0486 13.6421 16.6187 12.3205 16.5463 10.6732C16.4753 9.07685 17.5905 7.61078 19.1331 7.35522C19.7123 7.25895 20.2935 7.31841 20.8741 7.30921C21.0732 7.30567 21.1498 7.25399 21.1484 7.03879C21.147 6.64944 21.1344 6.26151 21.0927 5.8757C20.9479 4.54342 20.4293 3.41502 19.3259 2.62075C18.5268 2.04523 17.617 1.78614 16.6549 1.78118C13.3818 1.76277 10.108 1.77339 6.83413 1.77268C6.4436 1.77268 6.05448 1.77268 5.66535 1.82578C4.27381 2.0155 3.13914 2.62642 2.39778 3.8787C1.94739 4.63899 1.7483 5.47857 1.74412 6.35566C1.73229 8.95155 1.73925 11.5481 1.73855 14.144C1.73855 14.5235 1.73786 14.9022 1.79007 15.2802C1.98359 16.6755 2.59269 17.806 3.79975 18.5536C4.53764 19.0102 5.35418 19.2162 6.20762 19.2247C7.95278 19.2424 9.69795 19.2296 11.4438 19.2296H11.4445ZM20.7725 11.9205C20.7725 11.9205 20.7725 11.9212 20.7725 11.9219C21.1539 11.9219 21.5354 11.9205 21.9169 11.9219C22.1285 11.9226 22.2413 11.8249 22.2413 11.6041C22.2413 10.8721 22.2406 10.1408 22.2413 9.40886C22.2413 9.19224 22.1417 9.0811 21.9259 9.0818C21.1198 9.08393 20.313 9.06694 19.5076 9.09242C18.8686 9.11295 18.2121 9.78617 18.2762 10.5741C18.3395 11.3513 18.9605 11.9014 19.7318 11.9191C20.0784 11.9269 20.4251 11.9205 20.7718 11.9205H20.7725Z" fill="black" class=" cls-3 "></path>
<path d="M9.74904 7.97669C8.63874 7.97669 7.52843 7.97952 6.41813 7.97456C5.9935 7.97244 5.65658 7.67087 5.5814 7.24825C5.51109 6.85678 5.74707 6.44761 6.12506 6.29612C6.23296 6.25294 6.34364 6.24515 6.45711 6.24515C8.66032 6.24515 10.8635 6.24374 13.0667 6.24586C13.5805 6.24586 13.9647 6.61751 13.9696 7.10455C13.9752 7.59796 13.5826 7.97527 13.0535 7.97669C11.9523 7.97952 10.8503 7.9774 9.74904 7.97669Z" fill="black" class=" cls-3 "></path>
</g>
<defs>
<clipPath id="clip0_14_166">
<rect width="24" height="21" fill="white"></rect>
</clipPath>
</defs>
</svg>                        <span class="font-14 text-light ml-5">{{ handlePrice($installment->totalPayments($itemPrice)) }} {{ trans('financial.total_amount') }}</span> --}}
                    {{-- </div> --}}

                    <!-- <div class="d-flex align-items-center mt-20">
                        <i data-feather="calendar" width="20" height="20" class="text-light"></i>

                        <span class="font-14 text-light ml-5"> {{trans('panel.ends_on')}}
                            {{ dateTimeFormat($installment->deadline_type == 'days' ? $installment->steps->max('deadline') * 86400 + $item->start_date : $installment->steps->max('deadline'), 'j M Y') }}</span>
                    </div> -->

                </div>
            </div>
        </div>

        <form action="/panel/bundles/purchase/{{ $installment->id }}" method="post">
            {{ csrf_field() }}
            <input type="hidden" name="item" value="{{ request()->get('item') }}">
            <input type="hidden" name="item_type" value="{{ request()->get('item_type') }}">

            {{-- Verify Section --}}
            @if ($installment->request_uploads or $installment->needToVerify())
                <div class="border rounded-lg p-15 mt-20">
                    @if ($installment->needToVerify())
                        <h3 class="font-16 font-weight-bold text-dark-blue">{{ trans('update.verify_installments') }}</h3>

                        <div class="font-16 text-gray mt-10">{!! nl2br($installment->verification_description) !!}</div>

                        {{-- Banner --}}
                        @if (!empty($installment->verification_banner))
                            <img src="{{ $installment->verification_banner }}" alt="{{ $installment->main_title }}"
                                class="img-fluid mt-30">
                        @endif

                        {{-- Video --}}
                        @if (!empty($installment->verification_video))
                            <div class="installment-video-card mt-50">
                                <video id="my-video" class="video-js" controls preload="auto">
                                    <source src="{{ $installment->verification_video }}" type="video/mp4" />
                                </video>
                            </div>
                        @endif
                    @endif

                    @if ($installment->request_uploads)
                        <div class="{{ $installment->needToVerify() ? 'mt-20' : '' }}">
                            <h4 class="font-16 font-weight-bold text-dark-blue">{{ trans('update.attachments') }}</h4>
                            <p class="mt-5 font-12 text-gray">
                                {{ trans('update.attach_your_documents_and_send_them_to_admin') }}</p>

                            @error('attachments')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <div class="js-attachments">

                                <div class="js-main-row row">
                                    <div class="col-12 col-md-6 mt-15">
                                        <div class="form-group mb-0">
                                            <label class="font-14 text-dark-blue">{{ trans('public.title') }}</label>
                                            <input type="text" name="attachments[record][title]" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 mt-15">
                                        <div class="form-group">
                                            <label
                                                class="font-14 text-dark-blue">{{ trans('update.attach_a_file_optional') }}</label>

                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <button type="button" class="input-group-text panel-file-manager"
                                                        data-input="file_record" data-preview="holder">
                                                        <i data-feather="arrow-up" width="18" height="18"
                                                            class="text-white"></i>
                                                    </button>
                                                </div>
                                                <input type="text" name="attachments[record][file]" id="file_record"
                                                    class="form-control rounded-0" />

                                                <button type="button"
                                                    class="js-add-btn btn btn-primary h-40px btn-sm installment-verify-attachment-add-btn">
                                                    <i data-feather="plus" width="16" height="16"
                                                        class="text-white"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endif
                </div>
            @endif


            {{-- Installment Terms & Rules --}}
            <div class="border rounded-lg p-35 mt-30 shadow">
                <h3 class="font-16 font-weight-bold text-pink">{{trans('panel.terms_and_rules_of_installment')}} </h3>
                <br>
                {{--   <div class="font-16 text-pink">{!! nl2br(getInstallmentsTermsSettings('terms_description')) !!}</div> --}}
                <div class="font-16 text-dark">
                    {{trans('panel.welcome_message')}}
                    <br>
                    {{trans('panel.installment_plan')}}
                    <br>
                    {{trans('panel.payment_plan_fees')}}
                    <br>
                    {{trans('panel.late_payment')}}
                    <br>
                    {{trans('panel.non_refundable')}}<br>
                    {{trans('panel.default')}}
                    <br>
                    {{trans('panel.privacy')}}
                    <br>
                    {{trans('panel.changes_in_terms')}}
                    <br>
                    
                    <br>

                </div>

                <div class="mt-10 border bg-secondary-acadima p-15 rounded-sm ">
                    <h4 class="font-14 text-pink font-weight-bold">{{trans('panel.important')}}</h4>
                    <p class="mt-5 font-14 text-dark">{{trans('panel.agreement')}}
                    </p>
                </div>
            </div>

            @if (!empty($hasPhysicalProduct))
                @include('web.default.cart.includes.shipping_and_delivery')
            @endif

            @if (!empty(request()->get('quantity')))
                <input type="hidden" name="quantity" value="{{ request()->get('quantity') }}">
            @endif

            @if (!empty(request()->get('specifications')) and count(request()->get('specifications')))
                @foreach (request()->get('specifications') as $k => $specification)
                    <input type="hidden" name="specifications[{{ $k }}]" value="{{ $specification }}">
                @endforeach
            @endif

            <div class="d-flex align-items-center justify-content-between border-top pt-10 mt-20">
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-gray200">{{ trans('update.back') }}</a>

                <button type="submit" class="btn btn-sm btn-primary">
                    @if ($installment->needToVerify())
                        @if (!empty($installment->upfront))
                            {{-- {{ trans('update.submit_and_checkout') }} --}}
                            {{trans('panel.accept')}}
                        @else
                            {{-- {{ trans('update.submit_request') }} --}}
                            {{trans('panel.submit_application')}}
                        @endif
                    @else
                        @if (!empty($installment->upfront))
                            {{-- {{ trans('update.proceed_to_checkout') }} --}}
                            {{trans('panel.accept')}}
                        @else
                            {{--  {{ trans('update.finalize_request') }} --}}
                            {{trans('panel.submit_application')}}
                        @endif
                    @endif
                </button>
            </div>
        </form>

    </div>
@endsection

@push('scripts_bottom')
    <script>
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>

    <script src="/assets/default/vendors/video/video.min.js"></script>
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>

    <script src="/assets/default/js/parts/get-regions.min.js"></script>
    <script src="/assets/default/js/parts/installment_verify.min.js"></script>
@endpush
