@extends('web.default.layouts.app')

@section('content')
    <div class=" js-font-resize container mt-20 my-50">
        <div class=" js-font-resize row align-items-center justify-content-center">
            <div class=" js-font-resize col-12 col-md-8">
                <div class=" js-font-resize installment-request-card d-flex align-items-center justify-content-center flex-column border rounded-lg">
                    <img src="/assets/default/img/installment/request_submitted.svg" alt="{{ trans('update.installment_request_submitted') }}" width="267" height="265">

                    <h1 class=" js-font-resize font-20 mt-30">{{ trans('update.installment_request_submitted') }}</h1>
                    <p class=" js-font-resize font-14 text-gray mt-5">{{ trans('update.installment_request_submitted_hint') }}</p>

                    <a href="/panel/financial/installments" class=" js-font-resize btn btn-primary mt-15">{{ trans('update.my_installments') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
