@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')

    <section class=" js-font-resize mt-25">
        <h2 class=" js-font-resize section-title">{{ trans('quiz.filter_certificates') }}</h2>

        <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
            <form action="" method="get" class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-lg-6">
                    <div class=" js-font-resize row">
                        <div class=" js-font-resize col-12 col-md-6">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('public.from') }}</label>
                                <div class=" js-font-resize input-group">
                                    <div class=" js-font-resize input-group-prepend">
                                        <span class=" js-font-resize input-group-text" id="dateInputGroupPrepend">
                                            <i data-feather="calendar" width="18" height="18" class=" js-font-resize text-light"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="from" autocomplete="off" class=" js-font-resize form-control @if(!empty(request()->get('from'))) datepicker @else datefilter @endif" value="{{ request()->get('from','') }}" aria-describedby="dateInputGroupPrepend"/>
                                </div>
                            </div>
                        </div>
                        <div class=" js-font-resize col-12 col-md-6">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('public.to') }}</label>
                                <div class=" js-font-resize input-group">
                                    <div class=" js-font-resize input-group-prepend">
                                        <span class=" js-font-resize input-group-text" id="dateInputGroupPrepend">
                                            <i data-feather="calendar" width="18" height="18" class=" js-font-resize text-light"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="to" autocomplete="off" class=" js-font-resize form-control @if(!empty(request()->get('to'))) datepicker @else datefilter @endif" value="{{ request()->get('to','') }}" aria-describedby="dateInputGroupPrepend"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize col-12 col-lg-4">
                    <div class=" js-font-resize form-group">
                        <label class=" js-font-resize input-label">{{ trans('product.course') }}</label>
                        <select name="webinar_id" class=" js-font-resize form-control">
                            <option value="all">{{ trans('webinars.all_courses') }}</option>

                            @foreach($userWebinars as $userWebinar)
                                <option value="{{ $userWebinar->id }}" @if(request()->get('webinar_id','') == $userWebinar->id) selected @endif>{{ $userWebinar->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class=" js-font-resize col-12 col-lg-2 d-flex align-items-center justify-content-end">
                    <button type="submit" class=" js-font-resize btn btn-sm btn-acadima-primary w-100 mt-2">{{ trans('public.show_results') }}</button>
                </div>
            </form>
        </div>
    </section>

    <section class=" js-font-resize mt-35">
        <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class=" js-font-resize section-title">{{ trans('quiz.my_certificates') }}</h2>
        </div>

        @if(!empty($certificates) and count($certificates))
            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('product.course') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.certificate_id') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.date') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($certificates as $certificate)
                                    <tr>
                                        <td class=" js-font-resize text-left">
                                            <span class=" js-font-resize d-block text-dark-blue font-weight-500">{{ $certificate->webinar->title }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            {{ $certificate->id }}
                                        </td>

                                        <td class=" js-font-resize align-middle">
                                            <span class=" js-font-resize text-dark-blue font-weight-500">{{ dateTimeFormat($certificate->created_at, 'j M Y') }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle font-weight-normal">
                                            <div class=" js-font-resize btn-group dropdown table-actions">
                                                <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>
                                                <div class=" js-font-resize dropdown-menu">
                                                    <a href="/panel/certificates/webinars/{{ $certificate->id }}/show" target="_blank" class=" js-font-resize webinar-actions d-block">{{ trans('public.open') }}</a>
                                                </div>
                                            </div>
                                        </td>
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

    <div class=" js-font-resize my-30">
        {{ $certificates->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script src="/assets/default/js/panel/certificates.min.js"></script>
@endpush
