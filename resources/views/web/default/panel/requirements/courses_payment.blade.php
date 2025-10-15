@extends(getTemplate() . '.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

<style>
    .installment-card {
        background-color: #FBFBFB !important;
    }

    .discount {
        min-height: 17px;
    }
</style>

@section('content')

    @include('web.default.panel.requirements.requirements_includes.progress')

    <section class="row mt-80 mx-0 justify-content-center">
      
        @foreach ($webinarsOrders as $webinarsOrder)
            <section
                class="bg-secondary-acadima position-relative col-xl-9 col-12 justify-content-center align-items-center rounded-sm mb-80 py-35 px-0">
                <h2 class="mb-25 col-12 text-light">
                    {{ clean($webinarsOrder->webinar->title, 't') }}</h2>

                @if ($webinarsOrder->status == 'waiting')
                    <div class="w-100 text-center">
                        <p class="alert alert-info text-center mx-30">
                            طلبك تحت المراجعه من قبل الإدارة المالية يرجي الانتظار حتي يتم مراجعته
                        </p>
                    </div>
                @elseif ($webinarsOrder->status == 'rejected')
                    <div class="w-100 text-center">
                        <p class="alert alert-danger text-center text-white mx-30">
                            طلبك تم رفضه من قبل الإدارة المالية لمعرفة السبب اضغط هنا
                        </p>
                        <a href="/panel/financial/offline-payments" class="btn btn-success p-5 mt-20 bg-secondary">للذهاب
                            لمتابعة طلبك اضغط
                            هنا</a>
                    </div>
                @elseif($webinarsOrder->status == 'approved')
                    <div class="w-100 text-center">
                        {{-- <p class="alert alert-success text-center mx-30">
                            طلبك تم رفضه من قبل الإدارة المالية لمعرفة السبب اضغط هنا
                        </p> --}}
                        <div class="col-12 p-0">
                            <div class="installment-card p-15 w-100 h-100">
                                <div class="mt-20 d-flex flex-column">

                                    <button type="button" class="btn btn-primary"
                                        disabled>{{ trans('panel.purchased') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        @endforeach


    </section>

@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/cropit/jquery.cropit.js"></script>
    <script src="/assets/default/js/parts/img_cropit.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>

    <script>
        var editEducationLang = '{{ trans('site.edit_education') }}';
        var editExperienceLang = '{{ trans('site.edit_experience') }}';
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var saveErrorLang = '{{ trans('site.store_error_try_again') }}';
        var notAccessToLang = '{{ trans('public.not_access_to_this_content') }}';
    </script>

    <script src="/assets/default/js/panel/user_setting.min.js"></script>
@endpush
