@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/chartjs/chart.min.css"/>
@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a></div>
                <div class=" js-font-resize breadcrumb-item"><a href="{{ getAdminPanelUrl() }}/webinars">{{trans('admin/main.classes')}}</a></div>
                <div class=" js-font-resize breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>
    </section>

    <div class=" js-font-resize section-body">
        <section>
            <h2 class=" js-font-resize section-title">{{ $webinar->title }}</h2>

            <div class=" js-font-resize activities-container mt-3 p-3 p-lg-3">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-6 col-md-3 d-flex align-items-center justify-content-center">
                        <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/48.svg" width="64" height="64" alt="">
                            <strong class=" js-font-resize font-30 font-weight-bold text-dark mt-1">{{ $studentsCount }}</strong>
                            <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('public.students') }}</span>
                        </div>
                    </div>

                    <div class=" js-font-resize col-6 col-md-3 d-flex align-items-center justify-content-center">
                        <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/125.svg" width="64" height="64" alt="">
                            <strong class=" js-font-resize font-30 font-weight-bold text-dark mt-1">{{ $commentsCount }}</strong>
                            <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('panel.comments') }}</span>
                        </div>
                    </div>

                    <div class=" js-font-resize col-6 col-md-3 d-flex align-items-center justify-content-center">
                        <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/sales.svg" width="64" height="64" alt="">
                            <strong class=" js-font-resize font-30 font-weight-bold text-dark mt-1">{{ $salesCount }}</strong>
                            <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('panel.sales') }}</span>
                        </div>
                    </div>

                    <div class=" js-font-resize col-6 col-md-3 d-flex align-items-center justify-content-center">
                        <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/33.png" width="64" height="64" alt="">
                            <strong class=" js-font-resize font-30 font-weight-bold text-dark mt-1">{{ (!empty($salesAmount) and $salesAmount > 0) ? handlePrice($salesAmount) : 0 }}</strong>
                            <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('panel.sales_amount') }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <section class=" js-font-resize row">

            <div class=" js-font-resize col-6 col-md-3 mt-3">
                <div class=" js-font-resize dashboard-stats rounded-sm panel-shadow p-10 p-md-3 d-flex align-items-center">
                    <div class=" js-font-resize stat-icon stat-icon-chapters">
                        <img src="/assets/default/img/icons/course-statistics/1.svg" alt="">
                    </div>
                    <div class=" js-font-resize d-flex flex-column ml-2">
                        <span class=" js-font-resize font-30 font-weight-bold text-dark">{{ $chaptersCount }}</span>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('public.chapters') }}</span>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize col-6 col-md-3 mt-3">
                <div class=" js-font-resize dashboard-stats rounded-sm panel-shadow p-10 p-md-3 d-flex align-items-center">
                    <div class=" js-font-resize stat-icon stat-icon-sessions">
                        <img src="/assets/default/img/icons/course-statistics/2.svg" alt="">
                    </div>
                    <div class=" js-font-resize d-flex flex-column ml-2">
                        <span class=" js-font-resize font-30 font-weight-bold text-dark">{{ $sessionsCount }}</span>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('public.sessions') }}</span>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize col-6 col-md-3 mt-3">
                <div class=" js-font-resize dashboard-stats rounded-sm panel-shadow p-10 p-md-3 d-flex align-items-center">
                    <div class=" js-font-resize stat-icon stat-icon-pending-quizzes">
                        <img src="/assets/default/img/icons/course-statistics/3.svg" alt="">
                    </div>
                    <div class=" js-font-resize d-flex flex-column ml-2">
                        <span class=" js-font-resize font-30 font-weight-bold text-dark">{{ $pendingQuizzesCount }}</span>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.pending_quizzes') }}</span>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize col-6 col-md-3 mt-3">
                <div class=" js-font-resize dashboard-stats rounded-sm panel-shadow p-10 p-md-3 d-flex align-items-center">
                    <div class=" js-font-resize stat-icon stat-icon-pending-assignments">
                        <img src="/assets/default/img/icons/course-statistics/4.svg" alt="">
                    </div>
                    <div class=" js-font-resize d-flex flex-column ml-2">
                        <span class=" js-font-resize font-30 font-weight-bold text-dark">{{ $pendingAssignmentsCount }}</span>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.pending_assignments') }}</span>
                    </div>
                </div>
            </div>

        </section>

        <section>
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-3 mt-3">
                    <div class=" js-font-resize course-statistic-cards-shadow py-3 px-2 py-md-3 px-md-3 rounded-sm bg-white">
                        <div class=" js-font-resize d-flex align-items-center flex-column">
                            <img src="/assets/default/img/activity/33.png" width="64" height="64" alt="">

                            <span class=" js-font-resize font-30 text-dark mt-3 font-weight-bold">{{ $courseRate }}</span>
                            @include('admin.webinars.includes.rate',['rate' => $courseRate, 'className' => 'mt-2', 'dontShowRate' => true, 'showRateStars' => true])
                        </div>

                        <div class=" js-font-resize d-flex align-items-center justify-content-between mt-3 pt-3 border-top font-16 font-weight-500">
                            <span class=" js-font-resize text-gray">{{ trans('update.total_rates') }}</span>
                            <span class=" js-font-resize text-dark font-weight-bold">{{ $courseRateCount }}</span>
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize col-12 col-md-3 mt-3">
                    <div class=" js-font-resize course-statistic-cards-shadow py-3 px-2 py-md-3 px-md-3 rounded-sm bg-white">
                        <div class=" js-font-resize d-flex align-items-center flex-column">
                            <img src="/assets/default/img/activity/88.svg" width="64" height="64" alt="">

                            <span class=" js-font-resize font-30 text-dark mt-3 font-weight-bold">{{ $webinar->quizzes->count() }}</span>
                            <span class=" js-font-resize mt-2 font-16 font-weight-500 text-gray">{{ trans('quiz.quizzes') }}</span>
                        </div>

                        <div class=" js-font-resize d-flex align-items-center justify-content-between mt-3 pt-3 border-top font-16 font-weight-500">
                            <span class=" js-font-resize text-gray">{{ trans('quiz.average_grade') }}</span>
                            <span class=" js-font-resize text-dark font-weight-bold">{{ $quizzesAverageGrade }}</span>
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize col-12 col-md-3 mt-3">
                    <div class=" js-font-resize course-statistic-cards-shadow py-3 px-2 py-md-3 px-md-3 rounded-sm bg-white">
                        <div class=" js-font-resize d-flex align-items-center flex-column">
                            <img src="/assets/default/img/activity/homework.svg" width="64" height="64" alt="">

                            <span class=" js-font-resize font-30 text-dark mt-3 font-weight-bold">{{ $webinar->assignments->count() }}</span>
                            <span class=" js-font-resize mt-2 font-16 font-weight-500 text-gray">{{ trans('update.assignments') }}</span>
                        </div>

                        <div class=" js-font-resize d-flex align-items-center justify-content-between mt-3 pt-3 border-top font-16 font-weight-500">
                            <span class=" js-font-resize text-gray">{{ trans('quiz.average_grade') }}</span>
                            <span class=" js-font-resize text-dark font-weight-bold">{{ $assignmentsAverageGrade }}</span>
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize col-12 col-md-3 mt-3">
                    <div class=" js-font-resize course-statistic-cards-shadow py-3 px-2 py-md-3 px-md-3 rounded-sm bg-white">
                        <div class=" js-font-resize d-flex align-items-center flex-column">
                            <img src="/assets/default/img/activity/39.svg" width="64" height="64" alt="">

                            <span class=" js-font-resize font-30 text-dark mt-3 font-weight-bold">{{ $courseForumsMessagesCount }}</span>
                            <span class=" js-font-resize mt-2 font-16 font-weight-500 text-gray">{{ trans('update.forum_messages') }}</span>
                        </div>

                        <div class=" js-font-resize d-flex align-items-center justify-content-between mt-3 pt-3 border-top font-16 font-weight-500">
                            <span class=" js-font-resize text-gray">{{ trans('update.forum_students') }}</span>
                            <span class=" js-font-resize text-dark font-weight-bold">{{ $courseForumsStudentsCount }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class=" js-font-resize row">
                @include('admin.webinars.course_statistics.includes.pie_charts',[
                    'cardTitle' => trans('update.students_user_roles'),
                    'cardId' => 'studentsUserRolesChart',
                    'cardPrimaryLabel' => trans('public.students'),
                    'cardSecondaryLabel' => trans('public.instructors'),
                    'cardWarningLabel' => trans('home.organizations'),
                ])

                @include('admin.webinars.course_statistics.includes.pie_charts',[
                    'cardTitle' => trans('update.course_progress'),
                    'cardId' => 'courseProgressChart',
                    'cardPrimaryLabel' => trans('update.completed'),
                    'cardSecondaryLabel' => trans('webinars.in_progress'),
                    'cardWarningLabel' => trans('update.not_started'),
                ])

                @include('admin.webinars.course_statistics.includes.pie_charts',[
                    'cardTitle' => trans('quiz.quiz_status'),
                    'cardId' => 'quizStatusChart',
                    'cardPrimaryLabel' => trans('quiz.passed'),
                    'cardSecondaryLabel' => trans('public.pending'),
                    'cardWarningLabel' => trans('quiz.failed'),
                ])

                @include('admin.webinars.course_statistics.includes.pie_charts',[
                    'cardTitle' => trans('update.assignments_status'),
                    'cardId' => 'assignmentsStatusChart',
                    'cardPrimaryLabel' => trans('quiz.passed'),
                    'cardSecondaryLabel' => trans('public.pending'),
                    'cardWarningLabel' => trans('quiz.failed'),
                ])

            </div>
        </section>


        <section>
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-6 mt-3">
                    <div class=" js-font-resize course-statistic-cards-shadow monthly-sales-card pt-2 px-2 pb-3 rounded-sm bg-white">
                        <div class=" js-font-resize d-flex align-items-center justify-content-between">
                            <h3 class=" js-font-resize font-16 text-dark font-weight-bold">{{ trans('panel.monthly_sales') }}</h3>

                            <span class=" js-font-resize font-16 font-weight-500 text-gray">{{ dateTimeFormat(time(),'M Y') }}</span>
                        </div>

                        <div class=" js-font-resize monthly-sales-chart mt-2">
                            <canvas id="monthlySalesChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize col-12 col-md-6 mt-3">
                    <div class=" js-font-resize course-statistic-cards-shadow monthly-sales-card pt-2 px-2 pb-3 rounded-sm bg-white">
                        <div class=" js-font-resize d-flex align-items-center justify-content-between">
                            <h3 class=" js-font-resize font-16 text-dark font-weight-bold">{{ trans('update.course_progress') }} (%)</h3>
                        </div>

                        <div class=" js-font-resize monthly-sales-chart mt-2">
                            <canvas id="courseProgressLineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class=" js-font-resize mt-5">
            <h2 class=" js-font-resize section-title">{{ trans('panel.students_list') }}</h2>

            @if(!empty($students) and !$students->isEmpty())
                <div class=" js-font-resize panel-section-card py-3 px-3 mt-3">
                    <div class=" js-font-resize row">
                        <div class=" js-font-resize col-12 ">
                            <div class=" js-font-resize table-responsive">
                                <table class=" js-font-resize table custom-table text-center ">
                                    <thead>
                                    <tr>
                                        <th class=" js-font-resize text-left text-gray">{{ trans('quiz.student') }}</th>
                                        <th class=" js-font-resize text-center text-gray">{{ trans('update.progress') }}</th>
                                        <th class=" js-font-resize text-center text-gray">{{ trans('update.passed_quizzes') }}</th>
                                        <th class=" js-font-resize text-center text-gray">{{ trans('update.unsent_assignments') }}</th>
                                        <th class=" js-font-resize text-center text-gray">{{ trans('update.pending_assignments') }}</th>
                                        <th class=" js-font-resize text-center text-gray">{{ trans('panel.purchase_date') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @php
                                        $usersLists = new \Illuminate\Support\Collection($students->items());
                                        $usersLists = $usersLists->merge($unregisteredUsers);
                                    @endphp

                                    @foreach($usersLists as $user)

                                        <tr>
                                            <td class=" js-font-resize text-left">
                                                <div class=" js-font-resize user-inline-avatar d-flex align-items-center">
                                                    <div class=" js-font-resize avatar bg-gray200">
                                                        <img src="{{ $user->getAvatar() }}" class=" js-font-resize img-cover" alt="">
                                                    </div>
                                                    <div class=" js-font-resize  ml-2">
                                                        <span class=" js-font-resize d-block text-dark font-weight-500">{{ $user->full_name }}</span>
                                                        <span class=" js-font-resize mt-2 d-block font-12 text-gray">{{ $user->email }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class=" js-font-resize align-middle">
                                                <span class=" js-font-resize text-dark font-weight-500">{{ $user->course_progress ?? 0 }}%</span>
                                            </td>
                                            <td class=" js-font-resize align-middle">
                                                <span class=" js-font-resize text-dark font-weight-500">{{ $user->passed_quizzes ?? 0 }}</span>
                                            </td>
                                            <td class=" js-font-resize align-middle">
                                                <span class=" js-font-resize text-dark font-weight-500">{{ $user->unsent_assignments ?? 0 }}</span>
                                            </td>
                                            <td class=" js-font-resize align-middle">
                                                <span class=" js-font-resize text-dark font-weight-500">{{ $user->pending_assignments ?? 0 }}</span>
                                            </td>
                                            <td class=" js-font-resize align-middle">
                                                @if(empty($user->id))
                                                    <span class=" js-font-resize text-warning">{{ trans('update.unregistered') }}</span>
                                                @else
                                                    <span class=" js-font-resize text-dark font-weight-500">{{ dateTimeFormat($user->created_at,'j M Y | H:i') }}</span>
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

                <div class=" js-font-resize my-3">
                    {{ $students->appends(request()->input())->links() }}
                </div>
            @else

                @include(getTemplate() . '.includes.no-result',[
                    'file_name' => 'studentt.png',
                    'title' => trans('update.course_statistic_students_no_result'),
                    'hint' =>  nl2br(trans('update.course_statistic_students_no_result_hint')),
                ])
            @endif

        </section>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/chartjs/chart.min.js"></script>
    <script src="/assets/default/js/panel/course_statistics.min.js"></script>

    <script>
        (function ($) {
            "use strict";

            @if(!empty($studentsUserRolesChart))
            makePieChart('studentsUserRolesChart', @json($studentsUserRolesChart['labels']),@json($studentsUserRolesChart['data']));
            @endif

            @if(!empty($courseProgressChart))
            makePieChart('courseProgressChart', @json($courseProgressChart['labels']),@json($courseProgressChart['data']));
            @endif

            @if(!empty($quizStatusChart))
            makePieChart('quizStatusChart', @json($quizStatusChart['labels']),@json($quizStatusChart['data']));
            @endif

            @if(!empty($assignmentsStatusChart))
            makePieChart('assignmentsStatusChart', @json($assignmentsStatusChart['labels']),@json($assignmentsStatusChart['data']));
            @endif


            @if(!empty($monthlySalesChart))
            handleMonthlySalesChart(@json($monthlySalesChart['labels']),@json($monthlySalesChart['data']));
            @endif

            @if(!empty($courseProgressLineChart))
            handleCourseProgressChart(@json($courseProgressLineChart['labels']),@json($courseProgressLineChart['data']));
            @endif

            // handleCourseProgressChartChart();
        })(jQuery)
    </script>
@endpush
