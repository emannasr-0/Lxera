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
    @include('web.default.panel.services.includes.progress', ['title' => trans('panel.pro_courses')])

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

    @if ($professionalWebinars->count() > 0)
        <section class="row p-20">
            @foreach ($professionalWebinars as $webinar)
                <div class="col-12 col-lg-3 mt-35 ">
                    @include('web.default.panel.newEnrollment.webinarCart', ['webinar' => $webinar])
                </div>
            @endforeach
        </section>
    @else
        @include(getTemplate() . '.includes.no-result', [
            'file_name' => 'webinar.png',
            'title' => trans('panel.no_courses_available'),
            'hint' => '',
        ])
    @endif
@endsection