@extends(getTemplate() . '.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')




    <section class="mt-40">
        @include('web.default.panel.services.includes.progress', [
            'title' => trans('panel.electronic_services_requests'),
        ])

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
            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table text-center custom-table">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">{{trans('panel.service_name')}}</th>
                                        <th class="text-center">{{ trans('panel.service_price') }}</th>
                                        <th class="text-center">{{ trans('panel.request_status') }}</th>
                                        <th class="text-center">{{ trans('panel.request_content') }}</th>
                                        <th class="text-center">{{ trans('panel.request_date') }}</th>
                                        <th class="text-center">{{ trans('panel.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($services as $service)
                                        <tr @if ($service->status == 'canceled') class="bg-light" style="opacity: 0.5" @endif>
                                            <td class="text-center text-light">
                                                <span>{{ $loop->iteration }}</span>
                                            </td>

                                            <td class="text-center text-light">
                                                <span>{{ $service->title }}</span>
                                            </td>

                                            <td class="text-center align-middle text-light">
                                                <span class="font-16 font-weight-bold text-primary">
                                                    {{ $service->price > 0 ? handlePrice($service->price, false) : trans('panel.free') }}
                                                </span>
                                            </td>

                                            <td class="text-center align-middle text-light">

                                                @switch(($service?->pivot?->bundleTransform?->status ?? $service?->pivot?->BridgingRequest?->status)??
                                                    $service->pivot->status)
                                                    @case('pending')
                                                        <span class="text-warning">{{ trans('public.waiting') }}</span>
                                                    @break

                                                    @case('approved')
                                                        <span class="text-secondary">{{ trans('financial.approved') }}</span>
                                                    @break

                                                    @case('canceled')
                                                        <span class="text-primary">{{trans('panel.canceled')}}</span>
                                                    @break

                                                    @case('paid')
                                                        <span class="text-primary">{{trans('panel.accepted_and_paid')}} </span>
                                                    @break

                                                    @case('refunded')
                                                        <span class="text-primary"> {{trans('panel.accepted_and_refund_received')}}</span>
                                                    @break

                                                    @case('rejected')
                                                        <span class="text-danger">{{ trans('public.rejected') }}</span>
                                                        @include('admin.includes.message_button', [
                                                            'url' => '#',
                                                            'btnClass' => 'd-block m-auto mt-2',
                                                            'btnText' =>
                                                                '<span class="ml-2">' . ' {{trans("panel.rejection_reason")}}</span>',
                                                            'hideDefaultClass' => true,
                                                            'deleteConfirmMsg' => trans('panel.this_is_the_rejection_reason'),
                                                            'message' => $service->pivot->message,
                                                            'id' => $service->pivot->id . '_message',
                                                        ])
                                                    @break
                                                @endswitch
                                            </td>


 
                                            <td class="text-center text-light">
                                                @include('admin.services.requestContentMessage', [
                                                    'url' => '#',
                                                    'btnClass' =>
                                                        'd-flex align-items-center justify-content-center mt-1 text-primary',
                                                    'btnText' => '<span class="ml-2">' . " {{trans('panel.request_content')}}</span>",
                                                    'hideDefaultClass' => true,
                                                    'deleteConfirmMsg' => 'test',
                                                    'message' => $service->pivot->content,
                                                    'id' => $service->pivot->id . '_content',
                                                ])
                                            </td>

                                            <td class="font-12 text-light">
                                                {{ Carbon\Carbon::parse($service->pivot->created_at)->translatedFormat(handleDateAndTimeFormat('Y M j | H:i')) }}
                                            </td>

                                            <td>
                                                @if (
                                                    !empty(
                                                        $service->pivot->bundleTransform &&
                                                            $service->pivot->bundleTransform->type == 'pay' &&
                                                            $service->pivot->status == 'approved' &&
                                                            $service->pivot->bundleTransform->status != 'paid' &&
                                                            $service->pivot->bundleTransform->status == 'approved'
                                                    ))
                                                    <a class="btn btn-primary"
                                                        href="/panel/bundletransform/{{ $service->pivot->bundleTransform->id }}/pay">
                                                        {{trans('panel.pay_difference_and_complete_transfer')}}
                                                    </a>

                                                    {{-- @elseif (!empty($service->pivot->bundleTransform && $service->pivot->bundleTransform->type=="refund" && $service->pivot->status=="approved" && $service->pivot->bundleTransform->status!="paid"))
                                                <a class="btn btn-secondary" href="/panel/bundletransform/{{ $service->pivot->bundleTransform->id}}/refund">استيرداد الفرق و إتمام التحويل</a> --}}
                                                @elseif (
                                                    !empty($service->pivot->BridgingRequest && $service->pivot->BridgingRequest->bridging_id) &&
                                                        $service->pivot->status == 'approved'  && $service->pivot->BridgingRequest->status!="paid")
                                                    <a class="btn btn-primary"
                                                        href="/panel/bundleBridging/{{ $service->pivot->BridgingRequest->bridging_id }}/pay">
                                                        {{trans('panel.pay_for_program')}}
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-center text-center">
                    {{ $services->links() }}
                </div>
            </div>
        @else
            @include(getTemplate() . '.includes.no-result', [
                'file_name' => 'webinar.png',
                'title' => trans('panel.no_requests'),
                'hint' => "<a href='/panel/services' class= 'text-primary'>{{trans('panel.submit_service_request')}}</a>",
            ])
        @endif

    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script src="/assets/default/js/panel/financial/account.min.js"></script>

    <script>
        (function($) {
            "use strict";

            @if (session()->has('sweetalert'))
                Swal.fire({
                    icon: "{{ session()->get('sweetalert')['status'] ?? 'success' }}",
                    html: '<h3 class="font-20 text-center text-light py-25">{{ session()->get('sweetalert')['msg'] ?? '' }}</h3>',
                    showConfirmButton: false,
                    width: '25rem',
                });
            @endif
        })(jQuery)
    </script>
@endpush
