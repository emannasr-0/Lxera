@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')
    <section>
        <h2 class=" js-font-resize section-title">{{ trans('quiz.certificates_statistics') }}</h2>

        <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-6 col-lg-3 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/56.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-dark font-weight-bold mt-5">{{ $activeQuizzes }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('quiz.active_certificates') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-lg-3 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/57.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-dark font-weight-bold mt-5">{{ $achievementsCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('quiz.student_achievements') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-lg-3 d-flex align-items-center justify-content-center mt-5 mt-lg-0">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/60.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-dark font-weight-bold mt-5">{{ $failedResults }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('quiz.failed_students') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-lg-3 d-flex align-items-center justify-content-center mt-5 mt-lg-0">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/hours.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-dark font-weight-bold mt-5">{{ $avgGrade }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('quiz.average_grade') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class=" js-font-resize mt-25">
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
                                            <i data-feather="calendar" width="18" height="18" class=" js-font-resize text-light"></i>
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
    </section>

    <section class=" js-font-resize mt-35">
        <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class=" js-font-resize section-title">{{ trans('quiz.active_certificates') }}</h2>
        </div>

        @if(!empty($quizzes) and count($quizzes))
            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('quiz.quiz') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('quiz.grade') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('quiz.average') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('quiz.generated_certificates') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.date') }}</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($quizzes as $quiz)
                                    <tr>
                                        <td class=" js-font-resize text-left">
                                            <span class=" js-font-resize d-block text-light font-weight-500">{{ $quiz->title }}</span>
                                            <span class=" js-font-resize d-block mt-5 font-12 text-gray">{{ !empty($quiz->webinar) ? $quiz->webinar->title : trans('update.delete_item') }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            <span class=" js-font-resize text-light font-weight-500">{{ $quiz->pass_mark }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            <span class=" js-font-resize text-light font-weight-500">{{ round($quiz->avg_grade, 2) }}</span>
                                        </td>
                                        <td class=" js-font-resize text-light font-weight-500 align-middle">{{ count($quiz->certificates) }}</td>
                                        <td class=" js-font-resize align-middle">
                                            <span class=" js-font-resize text-light font-weight-500">{{ dateTimeFormat($quiz->created_at, 'j M Y') }}</span>
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
                'file_name' => 'certificate.png',
                'title' => trans('quiz.certificates_no_result'),
                'hint' => nl2br(trans('quiz.certificates_no_result_hint')),
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

