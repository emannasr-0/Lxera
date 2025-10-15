@extends(getTemplate() . '.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <style>
        .service-card svg {
            width: 40px;
            !important;
            height: 40px;
            !important;
            fill: var(--secondary);
        }

        /* .module-box:hover{
                            background-color: var(--secondary) !important;

                        }
                        /* .module-box:hover a{
                            background-color: var(--secondary);
                        } */

        .module-box:hover .service-card svg {
            fill: var(--primary);
        }

        */
    </style>
@endpush

@section('content')
    @include('web.default.panel.services.includes.progress', ['title' => trans('panel.electronic_services')])

    @if (Session::has('success'))
        <div class="container d-flex justify-content-center mt-80">
            <p class="alert alert-success w-75 text-center"> {{ Session::get('success') }} </p>
        </div>
    @endif

    @if (Session::has('error'))
            <div class="container d-flex justify-content-center mt-80">
                <p class="alert alert-danger w-75 text-center"> {{ Session::get('error') }} </p>
            </div>
        @endif

    @if ($services->count() > 0)
        <section class="row p-20">
            @foreach ($services as $service)
                <div class="col-12 col-lg-3 mt-35 ">
                    @include('web.default.panel.services.includes.service_card', ['service' => $service])
                </div>
            @endforeach
        </section>
    @else
        @include(getTemplate() . '.includes.no-result', [
            'file_name' => 'webinar.png',
            'title' => trans('panel.no_services_available'),
            'hint' => '',
        ])
    @endif
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
