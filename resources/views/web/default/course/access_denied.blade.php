@extends('web.default.layouts.app')

@section('content')
    <div class=" js-font-resize container">
        <div class=" js-font-resize course-private-content text-center w-100 border rounded-lg">
            <div class=" js-font-resize course-private-content-icon m-auto">
                <img src="/assets/default/img/course/private_content_icon.svg" alt="private content icon" class=" js-font-resize img-cover">
            </div>

            <div class=" js-font-resize mt-30">
                <h2 class=" js-font-resize font-20 text-dark-blue">{{ trans('update.access_denied') }}</h2>
                <p class=" js-font-resize font-14 font-weight-500 text-gray">{{ trans('update.you_have_an_overdue_installment_please_pay_it_to_access_this_course') }}</p>

                <a href="/panel/financial/installments" class=" js-font-resize btn btn-primary mt-15">{{ trans('update.view_installments') }}</a>
            </div>
        </div>
    </div>
@endsection
