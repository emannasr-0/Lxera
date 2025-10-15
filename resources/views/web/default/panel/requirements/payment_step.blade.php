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

    .register-btn {
        background: #5e0a83 !important;
    }

    .register-btn:hover {
        background: #f70387 !important;
    }
</style>

@section('content')

    @include('web.default.panel.requirements.requirements_includes.progress')
    <section class="row mt-50 mt-lg-80 mx-0 justify-content-center">
        @if (count($bundleInstallments) > 0)
            @php
                $count = 0;
            @endphp

            @foreach ($bundleInstallments as $bundleId => $bundleData)
                @php
                    $count++;
                @endphp

                <section
                    class="bg-secondary-acadima position-relative col-xl-9 col-12 justify-content-center align-items-center rounded-sm mb-80 py-35 px-0 shadow border">
                    <h2 class="mb-25 col-12 text-pink">
                        {{ clean($bundleData['bundle']->bundle->title, 't') }}

                        {{-- @if (!$bundleData['bundle']->bundle->checkUserHasBought(auth()->user()) && $bundleData['bundle']->bundle->early_enroll != 1)
                            <span class="font-14 font-weight-bold text-center text-danger mt-15 discount pr-2">
                                خصم 30%
                            </span>
                        @endif --}}
                    </h2>


                    @if ($bundleData['bundle']->status == 'pending')
                        <div class="w-100 text-center">
                            <p class="alert alert-info text-center mx-30">
                                {{ trans('panel.seat_reservation_under_review') }}
                            </p>
                        </div>
                    @elseif ($bundleData['bundle']->status == 'fee_rejected')
                        <div class="w-100 text-center">
                            <p class="alert alert-danger text-center text-white mx-30">
                                {{ trans('panel.seat_reservation_rejected') }}

                            </p>
                            <a href="/panel/financial/offline-payments" class="btn btn-success p-5 mt-20 bg-secondary">
                                {{ trans('panel.go_to_follow_request') }}

                            </a>
                        </div>
                    @elseif ($bundleData['bundle']->status == 'paying')
                        <div class="w-100 text-center">
                            <p class="alert alert-info text-center mx-30">
                                {{ trans('panel.request_under_review') }}
                            </p>
                        </div>
                    @elseif ($bundleData['bundle']->status == 'rejected')
                        <div class="w-100 text-center">
                            <p class="alert alert-danger text-center text-white mx-30">
                                {{ trans('panel.request_rejected') }}
                            </p>
                            <a href="/panel/financial/offline-payments" class="btn btn-success p-5 mt-20 bg-secondary">
                                {{ trans('panel.go_to_follow_request') }}
                            </a>
                        </div>

                        {{-- @elseif ($bundleData['bundle']->bundle->early_enroll)
                        <div class="w-100 text-center">
                            <p class="alert alert-info text-center mx-30">
                                يرجى ملاحظة أن التسجيل الرسمي سيبدأ يوم 30 يوليو.
                                <br> بمجرد فتح التسجيل، ستتمكن من استكمال اجراءات التسجيل.
                            </p>
                        </div> --}}
                    @else
                        @include('web.default.panel.requirements.payment_card', [
                            'bundleData' => $bundleData,
                        ])
                    @endif
                </section>
            @endforeach
        @else
            <section class="w-100 text-center">
                <p class="alert alert-info text-center mx-30">
                    {{ trans('panel.no_diploma_registered') }}
                </p>
                <a href="{{ auth()->user()->student ? '/panel/newEnrollment' : '/apply' }}"
                    class="btn bg-secondary text-white p-5 mt-20">
                    {{ trans('panel.register_here') }}
                </a>
            </section>
        @endif

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
