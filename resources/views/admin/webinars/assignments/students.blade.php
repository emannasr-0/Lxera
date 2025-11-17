@extends('admin.layouts.app')

@push('styles_top')

@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class=" js-font-resize row">
            <div class=" js-font-resize col-lg-4 col-md-6 col-sm-6 col-12">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-success">
                        <i class=" js-font-resize fas fa-eye"></i></div>
                    <div class=" js-font-resize card-wrap">
                        <div class=" js-font-resize card-header">
                            <h4>{{trans('update.pending_review')}}</h4>
                        </div>
                        <div class=" js-font-resize card-body">
                            {{ $pendingReviewCount }}
                        </div>
                    </div>
                </div>
            </div>
            <div class=" js-font-resize col-lg-4 col-md-6 col-sm-6 col-12">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-warning">
                        <i class=" js-font-resize fas fa-calculator"></i></div>
                    <div class=" js-font-resize card-wrap">
                        <div class=" js-font-resize card-header">
                            <h4>{{trans('quiz.passed')}}</h4>
                        </div>
                        <div class=" js-font-resize card-body">
                            {{ $passedCount }}
                        </div>
                    </div>
                </div>
            </div>
            <div class=" js-font-resize col-lg-4 col-md-6 col-sm-6 col-12">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-danger">
                        <i class=" js-font-resize fas fa-comment-slash"></i></div>
                    <div class=" js-font-resize card-wrap">
                        <div class=" js-font-resize card-header">
                            <h4>{{trans('quiz.failed')}}</h4>
                        </div>
                        <div class=" js-font-resize card-body">
                            {{ $failedCount }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class=" js-font-resize section-body">
            <section class=" js-font-resize card">
                <div class=" js-font-resize card-body">
                    <form method="get" class=" js-font-resize mb-0">
                        <div class=" js-font-resize row">
                            <div class=" js-font-resize col-md-2">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{trans('admin/main.start_date')}}</label>
                                    <div class=" js-font-resize input-group">
                                        <input type="date" id="fsdate" class=" js-font-resize text-center form-control" name="from" value="{{ request()->get('from') }}" placeholder="Start Date">
                                    </div>
                                </div>
                            </div>

                            <div class=" js-font-resize col-md-2">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{trans('admin/main.end_date')}}</label>
                                    <div class=" js-font-resize input-group">
                                        <input type="date" id="lsdate" class=" js-font-resize text-center form-control" name="to" value="{{ request()->get('to') }}" placeholder="End Date">
                                    </div>
                                </div>
                            </div>

                            <div class=" js-font-resize col-md-4">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{trans('admin/main.student')}}</label>
                                    <select name="student_ids[]" multiple="multiple" class=" js-font-resize form-control search-user-select2"
                                            data-placeholder="Search students">

                                        @if(!empty($students) and $students->count() > 0)
                                            @foreach($students as $student)
                                                <option value="{{ $student->id }}" selected>{{ $student->full_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class=" js-font-resize col-md-2">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{trans('admin/main.status')}}</label>
                                    <select name="status" class=" js-font-resize form-control populate">
                                        <option value="">{{ trans('public.all') }}</option>
                                        @foreach(\App\Models\WebinarAssignmentHistory::$assignmentHistoryStatus as $status)
                                            <option value="{{ $status }}" {{ (request()->get('status') == $status) ? 'selected' : '' }}>{{ trans('update.assignment_history_status_'.$status) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class=" js-font-resize col-md-2">
                                <div class=" js-font-resize form-group mt-1">
                                    <label class=" js-font-resize input-label mb-4"> </label>
                                    <input type="submit" class=" js-font-resize text-center btn btn-primary w-100" value="{{trans('admin/main.show_results')}}">
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </section>

            <section class=" js-font-resize card">
                <div class=" js-font-resize card-body">
                    <table class=" js-font-resize table table-striped font-14" id="datatable-details">

                        <tr>
                            <th>{{ trans('quiz.student') }}</th>
                            <th class=" js-font-resize text-center">{{ trans('panel.purchase_date') }}</th>
                            <th class=" js-font-resize text-center">{{ trans('update.first_submission') }}</th>
                            <th class=" js-font-resize text-center">{{ trans('update.last_submission') }}</th>
                            <th class=" js-font-resize text-center">{{ trans('update.attempts') }}</th>
                            <th class=" js-font-resize text-center">{{ trans('quiz.grade') }}</th>
                            <th class=" js-font-resize text-center">{{ trans('public.status') }}</th>
                            <th class=" js-font-resize text-right">{{ trans('admin/main.action') }}</th>
                        </tr>

                        @foreach($histories as $history)
                            <tr>
                                <td class=" js-font-resize text-left">
                                    <div class=" js-font-resize user-inline-avatar d-flex align-items-center">
                                        <div class=" js-font-resize avatar bg-gray200">
                                            <img src="{{ $history->student->getAvatar() }}" class=" js-font-resize img-cover" alt="">
                                        </div>
                                        <div class=" js-font-resize ml-1">
                                            <span class=" js-font-resize d-block font-weight-500 text-light">{{ $history->student->full_name }}</span>
                                            <span class=" js-font-resize mt-1 font-12 text-gray d-block">{{ $history->student->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class=" js-font-resize align-middle">
                                    <span class=" js-font-resize font-weight-500">{{ !empty($history->purchase_date) ? dateTimeFormat($history->purchase_date, 'j M Y') : '-' }}</span>
                                </td>

                                <td class=" js-font-resize align-middle">
                                    <span class=" js-font-resize font-weight-500">{{ !empty($history->first_submission) ? dateTimeFormat($history->first_submission, 'j M Y | H:i') : '-' }}</span>
                                </td>

                                <td class=" js-font-resize align-middle">
                                    <span class=" js-font-resize font-weight-500">{{ !empty($history->last_submission) ? dateTimeFormat($history->last_submission, 'j M Y | H:i') : '-' }}</span>
                                </td>

                                <td class=" js-font-resize align-middle">
                                    <span class=" js-font-resize font-weight-500">{{ !empty($assignment->attempts) ? "{$history->usedAttemptsCount}/{$assignment->attempts}" : '-' }}</span>
                                </td>

                                <td class=" js-font-resize align-middle">
                                    <span>{{ (!empty($history->grade)) ? $history->grade : '-' }}</span>
                                </td>

                                <td class=" js-font-resize align-middle">
                                    @if(empty($history) or ($history->status == \App\Models\WebinarAssignmentHistory::$notSubmitted))
                                        <span class=" js-font-resize text-danger font-weight-500">{{ trans('update.assignment_history_status_not_submitted') }}</span>
                                    @else
                                        @switch($history->status)
                                            @case(\App\Models\WebinarAssignmentHistory::$passed)
                                            <span class=" js-font-resize text-primary font-weight-500">{{ trans('quiz.passed') }}</span>
                                            @break
                                            @case(\App\Models\WebinarAssignmentHistory::$pending)
                                            <span class=" js-font-resize text-warning font-weight-500">{{ trans('public.pending') }}</span>
                                            @break
                                            @case(\App\Models\WebinarAssignmentHistory::$notPassed)
                                            <span class=" js-font-resize font-weight-500 text-danger">{{ trans('quiz.failed') }}</span>
                                            @break
                                        @endswitch
                                    @endif
                                </td>

                                <td class=" js-font-resize align-middle text-right">
                                    @can('admin_webinar_assignments_conversations')
                                        <a href="{{ getAdminPanelUrl() }}/assignments/{{ $assignment->id }}/history/{{ $history->id }}/conversations" class=" js-font-resize btn-transparent text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.conversations') }}">
                                            <i class=" js-font-resize fa fa-eye" aria-hidden="true"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach

                    </table>
                </div>
            </section>
        </div>
    </section>

@endsection

@push('scripts_bottom')

@endpush
