@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
@endpush

@section('content')
    <section>
        <h2 class=" js-font-resize section-title">{{ trans('quiz.results_statistics') }}</h2>

        <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-6 col-md-3 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/43.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-black font-weight-bold mt-5">{{ $waitingCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('quiz.need_to_review') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/42.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-black font-weight-bold mt-5">{{ $quizResultsCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('public.results') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/58.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-black font-weight-bold mt-5">{{ $quizAvgGrad }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('quiz.average_grade') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/45.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-black font-weight-bold mt-5">{{ $successRate }}%</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('quiz.success_rate') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class=" js-font-resize mt-25">
        <h2 class=" js-font-resize section-title">{{ trans('quiz.filter_results') }}</h2>

        <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
            <form action="/panel/quizzes/results" method="get" class=" js-font-resize row">
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
                                    <input type="text" name="from" autocomplete="off" class=" js-font-resize form-control @if(!empty(request()->get('from'))) datepicker @else datefilter @endif" aria-describedby="dateInputGroupPrepend" value="{{ request()->get('from','') }}"/>
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
                                    <input type="text" name="to" autocomplete="off" class=" js-font-resize form-control @if(!empty(request()->get('to'))) datepicker @else datefilter @endif" aria-describedby="dateInputGroupPrepend" value="{{ request()->get('to','') }}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize col-12 col-lg-6">
                    <div class=" js-font-resize row">
                        <div class=" js-font-resize col-12 col-lg-5">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('quiz.quiz_or_webinar') }}</label>
                                <select name="quiz_id" class=" js-font-resize form-control select2" data-placeholder="{{ trans('public.all') }}">
                                    <option value="all">{{ trans('public.all') }}</option>

                                    @foreach($quizzes as $quiz)
                                        <option value="{{ $quiz->id }}" @if(request()->get('quiz_id') == $quiz->id) selected @endif>{{ $quiz->title .' - '. ($quiz->webinar ? $quiz->webinar->title : '-') }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class=" js-font-resize col-12 col-lg-7">
                            <div class=" js-font-resize row">
                                <div class=" js-font-resize col-12 col-lg-7">
                                    <div class=" js-font-resize form-group">
                                        <label class=" js-font-resize input-label">{{ trans('quiz.student') }}</label>
                                        <select name="user_id" class=" js-font-resize form-control select2" data-placeholder="{{ trans('public.all') }}">
                                            <option value="all">{{ trans('public.all') }}</option>

                                            @foreach($allStudents as $student)
                                                <option value="{{ $student->id }}" @if(request()->get('user_id') == $student->id) selected @endif>{{ $student->full_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class=" js-font-resize col-12 col-lg-5">
                                    <div class=" js-font-resize form-group">
                                        <label class=" js-font-resize input-label">{{ trans('public.status') }}</label>
                                        <select class=" js-font-resize form-control" id="status" name="status">
                                            <option value="all">{{ trans('public.all') }}</option>
                                            <option value="passed" {{ request()->get('status','') === "passed" ? 'selected' : '' }}>{{ trans('quiz.passed') }}</option>
                                            <option value="failed" {{ request()->get('status','') === "failed" ? 'selected' : '' }}>{{ trans('quiz.failed') }}</option>
                                            <option value="waiting" {{ request()->get('status','') === "waiting" ? 'selected' : '' }}>{{ trans('quiz.waiting') }}</option>
                                        </select>
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
            <h2 class=" js-font-resize section-title">{{ trans('quiz.student_results') }}</h2>

            <form action="/panel/quizzes/results" method="get" class=" js-font-resize ">
                <div class=" js-font-resize d-flex align-items-center flex-row-reverse flex-md-row justify-content-start justify-content-md-center mt-20 mt-md-0">
                    <label class=" js-font-resize mb-0 mr-10 cursor-pointer font-14 text-gray font-weight-500" for="onlyOpenQuizzesSwitch">{{ trans('quiz.show_only_open_results') }}</label>
                    <div class=" js-font-resize custom-control custom-switch">
                        <input type="checkbox" name="open_results" class=" js-font-resize custom-control-input" id="onlyOpenQuizzesSwitch" @if(request()->get('open_results',null) == 'on') checked @endif>
                        <label class=" js-font-resize custom-control-label" for="onlyOpenQuizzesSwitch"></label>
                    </div>
                </div>
            </form>
        </div>

        @if($quizzesResults->count() > 0)

            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('quiz.student') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('quiz.quiz') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('quiz.quiz_grade') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('quiz.student_grade') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.status') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.date') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($quizzesResults as $result)

                                    <tr>
                                        <td class=" js-font-resize text-left align-middle">
                                            <div class=" js-font-resize user-inline-avatar d-flex align-items-center">
                                                <div class=" js-font-resize avatar bg-gray200">
                                                    <img src="{{ $result->user->getAvatar() }}" class=" js-font-resize img-cover" alt="">
                                                </div>
                                                <div class=" js-font-resize  ml-5">
                                                    <span class=" js-font-resize d-block text-black">{{ $result->user->full_name }}</span>
                                                    <span class=" js-font-resize mt-5 font-12 text-gray d-block">{{ $result->user->email }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class=" js-font-resize text-middle align-middle text-black">
                                            <span class=" js-font-resize d-block">{{ $result->quiz->title }}</span>
                                            <span class=" js-font-resize font-12 text-gray d-block">{{ $result->quiz->webinar->title }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle text-black">{{ $result->getQuestions()->sum('grade') }}</td>
                                        <td class=" js-font-resize align-middle text-black">{{ $result->user_grade }}</td>
                                        <td class=" js-font-resize align-middle text-black">
                                            @switch($result->status)
                                                @case(\App\Models\QuizzesResult::$passed)
                                                <span class=" js-font-resize text-primary">{{ trans('quiz.passed') }}</span>
                                                @break
                                                @case(\App\Models\QuizzesResult::$failed)
                                                <span class=" js-font-resize text-danger">{{ trans('quiz.failed') }}</span>
                                                @break
                                                @case(\App\Models\QuizzesResult::$waiting)
                                                <span class=" js-font-resize text-warning">{{ trans('quiz.waiting') }}</span>
                                                @break
                                            @endswitch
                                        </td>
                                        <td class=" js-font-resize align-middle text-black">{{ dateTimeFormat($result->created_at, 'j M Y H:i') }}</td>
                                        <td class=" js-font-resize align-middle text-right">
                                            <div class=" js-font-resize btn-group dropdown table-actions table-actions-lg table-actions-lg">
                                                <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20" class=" js-font-resize text-black"></i>
                                                </button>
                                                <div class=" js-font-resize dropdown-menu font-weight-normal">
                                                    @if($result->status != 'waiting')
                                                        <a href="/panel/quizzes/{{ $result->id }}/result" class=" js-font-resize webinar-actions d-block mt-10">{{ trans('public.view') }}</a>
                                                    @endif

                                                    @if($result->status == 'waiting')
                                                        <a href="/panel/quizzes/{{ $result->id }}/edit-result" class=" js-font-resize webinar-actions d-block mt-10">{{ trans('public.review') }}</a>
                                                    @endif

                                                    <a href="/panel/quizzes/results/{{ $result->id }}/delete" class=" js-font-resize webinar-actions d-block mt-10 delete-action">{{ trans('public.delete') }}</a>
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
                    'hint' => trans('quiz.quiz_result_no_result_hint'),
                ])
        @endif
    </section>

    <div class=" js-font-resize my-30">
        {{ $quizzesResults->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>

    <script src="/assets/default/js/panel/quiz_list.min.js"></script>
@endpush
