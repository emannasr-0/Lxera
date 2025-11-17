@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')


    <section class=" js-font-resize mt-35">
        <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class=" js-font-resize section-title">{{ trans('quiz.my_certificates') }}</h2>
        </div>

        @if(!empty($certificateTemplatesArray) and count($certificateTemplatesArray))
            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table text-center custom-table">
                                <thead>
                                <tr>
                                    <th class=" js-font-resize text-center">شهادة</th>
                                    <th class=" js-font-resize text-center">دبلومة</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.certificate_id') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.price') }}</th>
                                    <th class=" js-font-resize text-center">تاريخ الشراء </th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                               @foreach($certificateTemplatesArray as $index => $certificate)
                                    <tr>
                                        <td class=" js-font-resize text-left">
                                            <span class=" js-font-resize d-block text-dark-blue font-weight-500">{{ $certificate->title }}</span>
                                        </td>
                                        
                                        <td class=" js-font-resize align-middle">
                                            @if(!empty($salesWithCertificate[$index]['certificate_bundle_id']))
                                            {{ \App\Models\Bundle::find($salesWithCertificate[$index]['certificate_bundle_id'])->title }}
                                            @endif
                                        </td>
                                        
                                        <td class=" js-font-resize align-middle">
                                            {{ $certificate->id }}
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            {{ $certificate->price }} {{ $currency }}
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            <span class=" js-font-resize text-dark-blue font-weight-500">{{ dateTimeFormat($salesWithCertificate[$index]['created_at'], 'j M Y') }}</span>
                                        </td>
                                        <!--<td class=" js-font-resize align-middle font-weight-normal">-->
                                        <!--    <div class=" js-font-resize btn-group dropdown table-actions">-->
                                        <!--        <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">-->
                                        <!--            <i data-feather="more-vertical" height="20"></i>-->
                                        <!--        </button>-->
                                        <!--        <div class=" js-font-resize dropdown-menu">-->
                                        <!--            <a href="/panel/certificates/webinars/{{ $certificate->id }}/show" target="_blank" class=" js-font-resize webinar-actions d-block">{{ trans('public.open') }}</a>-->
                                        <!--        </div>-->
                                        <!--    </div>-->
                                        <!--</td>-->
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'cert.png',
                'title' => trans('quiz.my_certificates_no_result'),
                'hint' => nl2br(trans('quiz.my_certificates_no_result_hint')),
            ])
        @endif
    </section>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script src="/assets/default/js/panel/certificates.min.js"></script>
@endpush
