@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')


    <section class="mt-35">
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">{{ trans('quiz.my_certificates') }}</h2>
        </div>

        @if(!empty($certificateTemplatesArray) and count($certificateTemplatesArray))
            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table text-center custom-table">
                                <thead>
                                <tr>
                                    <th class="text-center">شهادة</th>
                                    <th class="text-center">دبلومة</th>
                                    <th class="text-center">{{ trans('public.certificate_id') }}</th>
                                    <th class="text-center">{{ trans('public.price') }}</th>
                                    <th class="text-center">تاريخ الشراء </th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                               @foreach($certificateTemplatesArray as $index => $certificate)
                                    <tr>
                                        <td class="text-left">
                                            <span class="d-block text-dark-blue font-weight-500">{{ $certificate->title }}</span>
                                        </td>
                                        
                                        <td class="align-middle">
                                            @if(!empty($salesWithCertificate[$index]['certificate_bundle_id']))
                                            {{ \App\Models\Bundle::find($salesWithCertificate[$index]['certificate_bundle_id'])->title }}
                                            @endif
                                        </td>
                                        
                                        <td class="align-middle">
                                            {{ $certificate->id }}
                                        </td>
                                        <td class="align-middle">
                                            {{ $certificate->price }} {{ $currency }}
                                        </td>
                                        <td class="align-middle">
                                            <span class="text-dark-blue font-weight-500">{{ dateTimeFormat($salesWithCertificate[$index]['created_at'], 'j M Y') }}</span>
                                        </td>
                                        <!--<td class="align-middle font-weight-normal">-->
                                        <!--    <div class="btn-group dropdown table-actions">-->
                                        <!--        <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">-->
                                        <!--            <i data-feather="more-vertical" height="20"></i>-->
                                        <!--        </button>-->
                                        <!--        <div class="dropdown-menu">-->
                                        <!--            <a href="/panel/certificates/webinars/{{ $certificate->id }}/show" target="_blank" class="webinar-actions d-block">{{ trans('public.open') }}</a>-->
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
