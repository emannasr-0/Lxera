@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')
    {{-- <section>
        <h2 class=" js-font-resize section-title">{{ trans('quiz.my_certificates_statistics') }}</h2>

        <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/56.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-500 mt-5">{{ $certificatesCount }}</strong>
                        <span class=" js-font-resize font-16 font-weight-bold text-gray">{{ trans('panel.certificates') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/hours.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-500 mt-5">{{ $avgGrades }}</strong>
                        <span class=" js-font-resize font-16 font-weight-bold text-gray">{{ trans('quiz.average_grade') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/60.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-500 mt-5">{{ $failedQuizzes }}</strong>
                        <span class=" js-font-resize font-16 font-weight-bold text-gray">{{ trans('quiz.failed_quizzes') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section> --}}

    {{-- <section class=" js-font-resize mt-25">
        <h2 class=" js-font-resize section-title">{{ trans('quiz.filter_certificates') }}</h2>

        <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
            <form action="" method="get" class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-lg-4">
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
                                            <i data-feather="calendar" width="18" height="18" class=" js-font-resize text-black"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="to" autocomplete="off" class=" js-font-resize form-control @if(!empty(request()->get('to'))) datepicker @else datefilter @endif" value="{{ request()->get('to','') }}" aria-describedby="dateInputGroupPrepend"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize col-12 col-lg-6">
                    <div class=" js-font-resize row">
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
                        <div class=" js-font-resize col-12 col-lg-8">
                            <div class=" js-font-resize row">
                                <div class=" js-font-resize col-12 col-lg-6">
                                    <div class=" js-font-resize form-group">
                                        <label class=" js-font-resize input-label">{{ trans('quiz.quiz') }}</label>
                                        <select id="quizFilter" name="quiz_id" class=" js-font-resize form-control" @if(empty(request()->get('quiz_id'))) disabled @endif>
                                            <option value="all">{{ trans('quiz.all_quizzes') }}</option>

                                            @foreach($userAllQuizzes as $userQuiz)
                                                <option value="{{ $userQuiz->id }}" data-webinar-id="{{ $userQuiz->webinar_id }}" @if(request()->get('quiz_id','') == $userQuiz->id) selected @else class=" js-font-resize d-none" @endif>{{ $userQuiz->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class=" js-font-resize col-12 col-lg-6">
                                    <div class=" js-font-resize form-group">
                                        <label class=" js-font-resize input-label">{{ trans('quiz.grade') }}</label>
                                        <input type="text" name="grade" value="{{ request()->get('grade','') }}" class=" js-font-resize form-control"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" js-font-resize col-12 col-lg-2 d-flex align-items-center justify-content-end">
                    <button type="submit" class=" js-font-resize btn btn-sm btn-acadima-primary w-100 mt-2">{{ trans('public.show_results') }}</button>
                </div>
            </form>
        </div>
    </section> --}}

    {{-- <section class=" js-font-resize mt-35">
        <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class=" js-font-resize section-title">{{ trans('quiz.my_certificates') }}</h2>
        </div>

        @if(!empty($quizzes) and count($quizzes))
            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('public.certificate') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.certificate_id') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('quiz.minimum_grade') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('quiz.average_grade') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('quiz.my_grade') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.date') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($quizzes as $quiz)
                                    <tr>
                                        <td class=" js-font-resize text-left">
                                            <span class=" js-font-resize d-block text-light font-weight-500">{{ $quiz->title }}</span>
                                            <span class=" js-font-resize d-block font-12 text-gray mt-5">{{ $quiz->webinar->title }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle text-light">
                                            @if($quiz->can_download_certificate)
                                                @php
                                                    $getUserCertificate = $quiz->getUserCertificate($authUser,$quiz->result);
                                                @endphp

                                                @if(!empty($getUserCertificate))
                                                    {{ $getUserCertificate->id }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            <span class=" js-font-resize text-light font-weight-500">{{ $quiz->pass_mark }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            <span class=" js-font-resize text-light font-weight-500">{{ $quiz->total_mark }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle">{{ $quiz->result->user_grade }}</td>
                                        <td class=" js-font-resize align-middle">
                                            <span class=" js-font-resize text-light font-weight-500">{{ dateTimeFormat($quiz->result->created_at, 'j M Y') }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle font-weight-normal">
                                            @if($quiz->can_download_certificate)
                                                <div class=" js-font-resize btn-group dropdown table-actions">
                                                    <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i data-feather="more-vertical" height="20"></i>
                                                    </button>
                                                    <div class=" js-font-resize dropdown-menu">
                                                        <a href="/panel/quizzes/results/{{ $quiz->result->id }}/showCertificate" target="_blank" class=" js-font-resize webinar-actions d-block">{{ trans('public.open') }}</a>
                                                    </div>
                                                </div>
                                            @endif
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
    </section> --}}




    <section class=" js-font-resize mt-35">
        <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class=" js-font-resize section-title">{{trans('panel.courses')}}</h2>
        </div>

        @if(!empty($courseCertificates) and count($courseCertificates))
            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('public.certificate') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.certificate_id') }}</th>


                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($courseCertificates as $courseCertificate)
                                    <tr>
                                        <td class=" js-font-resize text-left">
                                            <span class=" js-font-resize d-block text-light font-weight-500">{{ $courseCertificate->title }}</span>
                                            <span class=" js-font-resize d-block font-12 text-gray mt-5">{{ $courseCertificate->webinar->title }}</span>

                                        </td>
                                        <td class=" js-font-resize align-middle text-light">


                                                {{$courseCertificate->certificate_code }}

                                        </td>



                                        <td class=" js-font-resize align-middle font-weight-normal">
                                            {{-- @if($courseCertificate->can_download_certificate) --}}
                                                {{-- <div class=" js-font-resize btn-group dropdown table-actions"> --}}
                                                    {{-- <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i data-feather="more-vertical" height="20"></i>
                                                    </button> --}}
                                                    {{-- <div class=" js-font-resize dropdown-menu"> --}}

                                                        <a href="/panel/course/{{$courseCertificate->webinar->id}}/showCertificate" target="_blank" class=" js-font-resize btn btn-sm btn-primary">تنزيل الشهاده كصوره</a>
                                                        <a href="/panel/course/{{$courseCertificate->webinar->id}}/showCertificate/pdf" target="_blank" class=" js-font-resize btn btn-sm btn-primary">تحميل الشهاده ك pdf</a>

                                                    {{-- </div>
                                                </div> --}}
                                            {{-- @endif --}}
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


    <section class=" js-font-resize mt-35">
        <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class=" js-font-resize section-title">{{trans('panel.programs')}}</h2>
        </div>

        @if(!empty($bundleCertificates) and count($bundleCertificates))
            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('public.certificate') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.certificate_id') }}</th>


                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                          {{-- @dump($bundleCertificates) --}}
                                @foreach($bundleCertificates as $bundleCertificate)
                                    <tr>
                                        <td class=" js-font-resize text-left">
                                            <span class=" js-font-resize d-block text-light font-weight-500">{{ $bundleCertificate->title }}</span>
                                            <span class=" js-font-resize d-block font-12 text-dark mt-5">{{ $bundleCertificate->bundle->title }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle text-dark">



                                                {{$bundleCertificate->certificate_code}}



                                        </td>



                                        <td class=" js-font-resize align-middle font-weight-normal">
                                            {{-- @if($courseCertificate->can_download_certificate) --}}
                                                {{-- <div class=" js-font-resize btn-group dropdown table-actions"> --}}
                                                    {{-- <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i data-feather="more-vertical" height="20"></i>
                                                    </button> --}}
                                                    {{-- <div class=" js-font-resize dropdown-menu"> --}}

                                                        <a href="/panel/bundle/{{$bundleCertificate->bundle->id}}/showCertificate" target="_blank" class=" js-font-resize btn btn-sm btn-acadima-primary">{{trans('panel.download_certificate_as_image')}}</a>
                                                        <a href="/panel/bundle/{{$bundleCertificate->bundle->id}}/showCertificate/pdf" target="_blank" class=" js-font-resize btn btn-sm btn-acadima-primary">{{trans('panel.download_certificate_as_pdf')}}</a>

                                                    {{-- </div> --}}
                                                {{-- </div> --}}
                                            {{-- @endif --}}
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
        {{ $quizzes->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script src="/assets/default/js/panel/certificates.min.js"></script>
@endpush
