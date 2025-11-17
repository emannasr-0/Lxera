@extends('web.default.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/video/video-js.min.css">
@endpush

@section('content')
    <div class=" js-font-resize container pt-50 mt-10">
        <div class=" js-font-resize text-center">
            <h1 class=" js-font-resize font-36">{{ trans('update.select_an_installment_plan') }}</h1>
            <p class=" js-font-resize mt-10 font-16 text-gray">{{ trans('update.please_select_an_installment_plan_in_order_to_finalize_your_purchase') }}</p>
        </div>

        <div class=" js-font-resize d-flex align-items-center flex-column flex-lg-row mt-50 border rounded-lg p-15 p-lg-25">
            <div class=" js-font-resize default-package-icon">
                <img src="/assets/default/img/become-instructor/default.png" class=" js-font-resize img-cover" alt="{{ trans('update.installment_overview') }}" width="176" height="144">
            </div>

            <div class=" js-font-resize ml-lg-25 w-100 mt-20 mt-lg-0">
                <h2 class=" js-font-resize font-24 font-weight-bold text-light">{{ $overviewTitle }}</h2>

                <div class=" js-font-resize d-flex flex-wrap align-items-center justify-content-between w-100">

                    <div class=" js-font-resize d-flex align-items-center mt-20">
                        <i data-feather="check-square" width="20" height="20" class=" js-font-resize text-gray"></i>
                        <span class=" js-font-resize font-14 text-gray ml-5">{{ handlePrice($cash) }} {{ trans('update.cash') }}</span>
                    </div>

                    <div class=" js-font-resize d-flex align-items-center mt-20">
                        <i data-feather="menu" width="20" height="20" class=" js-font-resize text-gray"></i>
                        <span class=" js-font-resize font-14 text-gray ml-5">{{ $plansCount }} {{ trans('update.installment_plans') }}</span>
                    </div>

                    <div class=" js-font-resize d-flex align-items-center mt-20">
                        <i data-feather="dollar-sign" width="20" height="20" class=" js-font-resize text-gray"></i>
                        <span class=" js-font-resize font-14 text-gray ml-5">{{ handlePrice($minimumAmount) }} {{ trans('update.minimum_installment_amount') }}</span>
                    </div>

                </div>
            </div>
        </div>

        @foreach($installments as $installmentRow)
            @include('web.default.installment.card',['installment' => $installmentRow, 'itemPrice' => $itemPrice, 'itemId' => $itemId, 'itemType' => $itemType])
        @endforeach
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/video/video.min.js"></script>
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script src="/assets/default/js/parts/installment_verify.min.js"></script>
@endpush
