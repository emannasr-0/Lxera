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
            <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-primary">
                        <i class=" js-font-resize fas fa-pen"></i>
                    </div>
                    <div class=" js-font-resize card-wrap">
                        <div class=" js-font-resize card-header">
                            <h4>{{trans('update.course_assignments')}}</h4>
                        </div>
                        <div class=" js-font-resize card-body">
                            {{ $courseAssignmentsCount }}
                        </div>
                    </div>
                </div>
            </div>
            <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-warning">
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
            <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-success">
                        <i class=" js-font-resize fas fa-check"></i></div>
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
            <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-danger">
                        <i class=" js-font-resize fas fa-times"></i></div>
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
                                    <label class=" js-font-resize input-label">{{trans('admin/main.class')}}</label>
                                    <select name="webinar_ids[]" multiple="multiple" class=" js-font-resize form-control search-webinar-select2"
                                            data-placeholder="Search classes">

                                        @if(!empty($webinars) and $webinars->count() > 0)
                                            @foreach($webinars as $webinar)
                                                <option value="{{ $webinar->id }}" selected>{{ $webinar->title }}</option>
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
                                        <option value="active" {{ (request()->get('status') == 'active') ? 'selected' : '' }}>{{ trans('admin/main.active') }}</option>
                                        <option value="inactive" {{ (request()->get('status') == 'inactive') ? 'selected' : '' }}>{{ trans('admin/main.inactive') }}</option>
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
                            <th>{{ trans('update.title_and_course') }}</th>
                            <th class=" js-font-resize text-center">{{ trans('public.students') }}</th>
                            <th class=" js-font-resize text-center">{{ trans('quiz.grade') }}</th>
                            <th class=" js-font-resize text-center">{{ trans('update.pass_grade') }}</th>
                            <th class=" js-font-resize text-center">{{ trans('public.status') }}</th>
                            <th class=" js-font-resize text-right">{{ trans('admin/main.action') }}</th>
                        </tr>

                        @foreach($assignments as $assignment)
                            <tr>
                                <td class=" js-font-resize text-left">
                                    <span class=" js-font-resize d-block font-16 font-weight-500 text-dark">{{ $assignment->title }}</span>
                                    <span class=" js-font-resize d-block font-12 font-weight-500 text-gray">{{ $assignment->webinar->title }}</span>
                                </td>

                                <td class=" js-font-resize align-middle text-light">
                                    <span class=" js-font-resize font-weight-500">{{ count($assignment->instructorAssignmentHistories) }}</span>
                                </td>

                                <td class=" js-font-resize align-middle text-light">
                                    <span>{{ $assignment->grade }}</span>
                                </td>

                                <td class=" js-font-resize align-middle text-light">
                                    <span>{{ $assignment->pass_grade }}</span>
                                </td>

                                <td class=" js-font-resize align-middle text-light">
                                    {{ trans('admin/main.'.$assignment->status) }}
                                </td>

                                <td class=" js-font-resize align-middle text-right">
                                    @can('admin_reviews_status_toggle')
                                        <a href="{{ getAdminPanelUrl() }}/assignments/{{ $assignment->id }}/students" class=" js-font-resize btn-transparent text-primary mr-1" data-toggle="tooltip" data-placement="top" title="{{ trans('public.students') }}">
                                            <i class=" js-font-resize fa fa-eye" aria-hidden="true"></i>
                                        </a>
                                    @endcan

                                    <a href="{{ getAdminPanelUrl() }}/webinars/{{ $assignment->webinar_id }}/edit" target="_blank" class=" js-font-resize btn-transparent text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.course') }}">
                                        <i class=" js-font-resize fa fa-edit" aria-hidden="true"></i>
                                    </a>
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
