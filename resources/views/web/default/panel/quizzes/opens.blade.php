@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')
    <section>
        <h2 class=" js-font-resize section-title">{{ trans('quiz.filter_results') }}</h2>

        <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
            <form action="/panel/quizzes/opens" method="get" class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-lg-4">
                    <div class=" js-font-resize row">
                        <div class=" js-font-resize col-12 col-md-6">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label text-black">{{ trans('public.from') }}</label>
                                <div class=" js-font-resize input-group">
                                    <div class=" js-font-resize input-group-prepend">
                                        <span class=" js-font-resize input-group-text" id="dateInputGroupPrepend">
                                            <i data-feather="calendar" width="18" height="18" class=" js-font-resize text-light"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="from" autocomplete="off" class=" js-font-resize form-control @if(!empty(request()->get('from'))) datepicker @else datefilter @endif" aria-describedby="dateInputGroupPrepend" value="{{ request()->get('from','') }}"/>
                                </div>
                            </div>
                        </div>
                        <div class=" js-font-resize col-12 col-md-6">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label text-black">{{ trans('public.to') }}</label>
                                <div class=" js-font-resize input-group">
                                    <div class=" js-font-resize input-group-prepend">
                                        <span class=" js-font-resize input-group-text" id="dateInputGroupPrepend">
                                            <i data-feather="calendar" width="18" height="18" class=" js-font-resize text-light"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="to" autocomplete="off" class=" js-font-resize form-control @if(!empty(request()->get('to'))) datepicker @else datefilter @endif" aria-describedby="dateInputGroupPrepend" value="{{ request()->get('to','') }}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" js-font-resize col-12 col-lg-6">
                    <div class=" js-font-resize row">
                        <div class=" js-font-resize col-12 col-lg-6">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label text-black">{{ trans('quiz.quiz_or_webinar') }}</label>
                                <input type="text" name="quiz_or_webinar" class=" js-font-resize form-control" value="{{ request()->get('quiz_or_webinar','') }}"/>
                            </div>
                        </div>
                        <div class=" js-font-resize col-12 col-lg-6">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label text-black">{{ trans('public.instructor') }}</label>
                                <input type="text" name="instructor" class=" js-font-resize form-control" value="{{ request()->get('instructor','') }}"/>
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
            <h2 class=" js-font-resize section-title">{{ trans('quiz.open_quizzes') }}</h2>
        </div>

        @if($quizzes->count() > 0)
            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('public.instructor') }}</th>
                                    <th>{{ trans('quiz.quiz') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('quiz.quiz_grade') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.date') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($quizzes as $quiz)
                                    <tr>
                                        <td class=" js-font-resize text-left">
                                            <div class=" js-font-resize user-inline-avatar d-flex align-items-center">
                                                <div class=" js-font-resize avatar bg-gray200">
                                                    <img src="{{ $quiz->creator->getAvatar() }}" class=" js-font-resize img-cover" alt="">
                                                </div>
                                                <div class=" js-font-resize  ml-5">
                                                    <span class=" js-font-resize d-block text-pink font-weight-500">{{ $quiz->creator->full_name }}</span>
                                                    <span class=" js-font-resize mt-5 font-12 text-black d-block">{{ $quiz->creator->email }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class=" js-font-resize text-left">
                                            <span class=" js-font-resize d-block text-black font-weight-500">{{ $quiz->title }}</span>
                                            <span class=" js-font-resize font-12 mt-5 text-black d-block">{{ $quiz->webinar->title }}</span>
                                        </td>
                                        <td class=" js-font-resize text-black font-weight-500 align-middle">{{ $quiz->quizQuestions->sum('grade') }}</td>


                                        <td class=" js-font-resize text-black font-weight-500 align-middle">{{ dateTimeFormat($quiz->created_at,'j M Y H:i')}}</td>

                                        <td class=" js-font-resize align-middle text-right font-weight-normal">
                                            <div class=" js-font-resize btn-group dropdown table-actions table-actions-lg table-actions-lg">
                                                <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>
                                                <div class=" js-font-resize dropdown-menu">
                                                    <a href="/panel/quizzes/{{ $quiz->id }}/start" class=" js-font-resize webinar-actions d-block mt-10">{{ trans('public.start') }}</a>
                                                    <a href="/course/learning/{{ $quiz->webinar->id }}" target="_blank" class=" js-font-resize webinar-actions d-block mt-10">{{ trans('webinars.webinar_page') }}</a>
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
                'file_name' => 'result.png',
                'title' => trans('quiz.quiz_result_no_result'),
                'hint' => '',
                // 'hint' => trans('quiz.quiz_result_no_result_hint'),
            ])
        @endif
    </section>

    <div class=" js-font-resize my-30">
        {{ $quizzes->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

@endpush
