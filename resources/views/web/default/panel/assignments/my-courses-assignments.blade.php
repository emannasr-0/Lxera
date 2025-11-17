@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')

@endpush

@section('content')
    <section>
        <h2 class=" js-font-resize section-title">{{ trans('update.assignment_statistics') }}</h2>

        <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35 bg-secondary-acadima">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/homework.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 font-weight-bold mt-5 text-dark">{{ $courseAssignmentsCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.course_assignments') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/58.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 font-weight-bold mt-5 text-dark">{{ $pendingReviewCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.pending_review') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/45.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-dark font-weight-bold mt-5">{{ $passedCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('quiz.passed') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/pin.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-dark font-weight-bold mt-5">{{ $failedCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('quiz.failed') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <section class=" js-font-resize mt-35">
        <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class=" js-font-resize section-title">{{ trans('update.your_students_assignments') }}</h2>
        </div>

        @if($assignments->count() > 0)

            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('update.title_and_course') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('update.min_grade') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('quiz.average') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('update.submissions') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.pending') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('quiz.passed') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('quiz.failed') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.status') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($assignments as $assignment)
                                    <tr>
                                        <td class=" js-font-resize text-left">
                                            <span class=" js-font-resize d-block font-16 font-weight-500 text-dark">{{ $assignment->title }}</span>
                                            <span class=" js-font-resize d-block font-12 font-weight-500 text-gray">{{ $assignment->webinar->title }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle text-dark">
                                            <span class=" js-font-resize font-weight-500">{{ $assignment->min_grade ?? '-' }}</span>
                                        </td>

                                        <td class=" js-font-resize align-middle text-dark">
                                            <span class=" js-font-resize font-weight-500">{{ $assignment->average_grade ?? '-' }}</span>
                                        </td>

                                        <td class=" js-font-resize align-middle text-dark">
                                            <span class=" js-font-resize font-weight-500">{{ $assignment->submissions }}</span>
                                        </td>

                                        <td class=" js-font-resize align-middle text-dark">
                                            <span class=" js-font-resize font-weight-500">{{ $assignment->pendingCount }}</span>
                                        </td>

                                        <td class=" js-font-resize align-middle text-dark">
                                            <span>{{ $assignment->passedCount }}</span>
                                        </td>

                                        <td class=" js-font-resize align-middle text-dark">
                                            <span>{{ $assignment->failedCount }}</span>
                                        </td>

                                        <td class=" js-font-resize align-middle text-dark">
                                            @switch($assignment->status)
                                                @case('active')
                                                <span class=" js-font-resize text-dark font-weight-500">{{ trans('public.active') }}</span>
                                                @break
                                                @case('inactive')
                                                <span class=" js-font-resize text-danger font-weight-500">{{ trans('public.inactive') }}</span>
                                                @break
                                            @endswitch
                                        </td>


                                        <td class=" js-font-resize align-middle text-right text-dark">

                                            <div class=" js-font-resize btn-group dropdown table-actions">
                                                <button type="button" class=" js-font-resize btn-transparent dropdown-toggle text-black"
                                                        data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>

                                                <div class=" js-font-resize dropdown-menu menu-lg">
                                                    <a href="/panel/assignments/{{ $assignment->id }}/students?status=pending" target="_blank"
                                                       class=" js-font-resize webinar-actions d-block mt-10 font-weight-normal">{{ trans('update.pending_review') }}</a>

                                                    <a href="/panel/assignments/{{ $assignment->id }}/students" target="_blank"
                                                       class=" js-font-resize webinar-actions d-block mt-10 font-weight-normal">{{ trans('update.all_assignments') }}</a>
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

            <div class=" js-font-resize my-30">
                {{ $assignments->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'meeting.png',
                'title' => trans('update.my_assignments_no_result'),
                'hint' => nl2br(trans('update.my_assignments_no_result_hint')),
            ])
        @endif
    </section>
@endsection

@push('scripts_bottom')

@endpush
