@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{trans('admin/main.classes')}}</div>

                <div class=" js-font-resize breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class=" js-font-resize row">


            <div class=" js-font-resize col-lg-4 col-md-6 col-sm-6 col-12">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-primary">
                        <i class=" js-font-resize fas fa-question"></i></div>
                    <div class=" js-font-resize card-wrap">
                        <div class=" js-font-resize card-header">
                            <h4>{{trans('admin/main.question_count')}}</h4>
                        </div>
                        <div class=" js-font-resize card-body">
                            {{ $totalQuestions }}
                        </div>
                    </div>
                </div>
            </div>


            <div class=" js-font-resize col-lg-4 col-md-6 col-sm-6 col-12">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-success">
                        <i class=" js-font-resize fas fa-check"></i></div>
                    <div class=" js-font-resize card-wrap">
                        <div class=" js-font-resize card-header">
                            <h4>{{trans('update.resolved')}}</h4>
                        </div>
                        <div class=" js-font-resize card-body">
                            {{ $resolvedCount }}
                        </div>
                    </div>
                </div>
            </div>


            <div class=" js-font-resize col-lg-4 col-md-6 col-sm-6 col-12">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-warning">
                        <i class=" js-font-resize fas fa-hourglass"></i>
                    </div>
                    <div class=" js-font-resize card-wrap">
                        <div class=" js-font-resize card-header">
                            <h4>{{trans('update.not_resolved')}}</h4>
                        </div>
                        <div class=" js-font-resize card-body">
                            {{ $notResolvedCount }}
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
                                    <label class=" js-font-resize input-label">{{trans('admin/main.search')}}</label>
                                    <input type="text" name="title" value="{{ request()->get('title') }}" class=" js-font-resize form-control">
                                </div>
                            </div>
                            <div class=" js-font-resize col-md-3">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{trans('admin/main.date')}}</label>
                                    <div class=" js-font-resize input-group">
                                        <input type="date" id="fsdate" class=" js-font-resize text-center form-control" name="date" value="{{ request()->get('date') }}" placeholder="Date">
                                    </div>
                                </div>
                            </div>

                            <div class=" js-font-resize col-md-3">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{trans('admin/main.user')}}</label>

                                    <select name="user_id" class=" js-font-resize form-control search-user-select2"
                                            data-placeholder="{{ trans('public.search_user') }}">

                                        @if(!empty($user))
                                            <option value="{{ $user->id }}" selected>{{ $user->full_name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class=" js-font-resize col-md-2">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{trans('admin/main.status')}}</label>
                                    <select name="status" data-plugin-selectTwo class=" js-font-resize form-control populate">
                                        <option value="">{{trans('admin/main.all_status')}}</option>
                                        <option value="resolved" @if(request()->get('status') == 'resolved') selected @endif>{{trans('update.resolved')}}</option>
                                        <option value="not_resolved" @if(request()->get('status') == 'not_resolved') selected @endif>{{trans('update.not_resolved')}}</option>
                                        <option value="pined" @if(request()->get('status') == 'pined') selected @endif>{{trans('update.pined')}}</option>
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

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-12">
                    <div class=" js-font-resize card">

                        <div class=" js-font-resize card-body">
                            <div class=" js-font-resize table-responsive">
                                <table class=" js-font-resize table table-striped font-14 ">
                                    <tr>
                                        <th class=" js-font-resize text-left">{{trans('update.question_title')}}</th>
                                        <th class=" js-font-resize ">{{trans('admin/main.created_at')}}</th>
                                        <th class=" js-font-resize ">{{trans('admin/main.updated_at')}}</th>
                                        <th class=" js-font-resize ">{{trans('admin/main.creator')}}</th>
                                        <th>{{trans('public.answers')}}</th>
                                        <th>{{trans('update.pined')}}</th>
                                        <th>{{trans('update.resolved')}}</th>
                                        <th width="120">{{trans('admin/main.actions')}}</th>
                                    </tr>

                                    @foreach($forums as $forum)
                                        <tr class=" js-font-resize text-center">
                                            <td width="18%" class=" js-font-resize text-left">
                                                <span class=" js-font-resize font-weight-bold">{{ $forum->title }}</span>
                                            </td>

                                            <td class=" js-font-resize ">{{ dateTimeFormat($forum->created_at, 'j M Y | H:i') }}</td>

                                            <td class=" js-font-resize ">
                                                @if(!empty($forum->last_answer))
                                                    {{ dateTimeFormat($forum->last_answer->created_at, 'j M Y | H:i') }}
                                                @else
                                                    --
                                                @endif
                                            </td>

                                            <td class=" js-font-resize ">{{ $forum->user->full_name }}</td>

                                            <td class=" js-font-resize ">{{ $forum->answers_count }}</td>

                                            <td class=" js-font-resize ">
                                                @if($forum->pin)
                                                    {{ trans('admin/main.yes') }}
                                                @else
                                                    {{ trans('admin/main.no') }}
                                                @endif
                                            </td>

                                            <td class=" js-font-resize ">
                                                @if(!empty($forum->resolved))
                                                    {{ trans('admin/main.yes') }}
                                                @else
                                                    {{ trans('admin/main.no') }}
                                                @endif
                                            </td>


                                            <td width="200" class=" js-font-resize btn-sm">
                                                @can('admin_course_question_forum_answers')
                                                    <a href="{{ getAdminPanelUrl() }}/webinars/{{ $forum->webinar_id }}/forums/{{ $forum->id }}/answers" target="_blank" class=" js-font-resize btn-transparent btn-sm text-primary mt-1 mr-1" data-toggle="tooltip" data-placement="top" title="{{ trans('public.answers') }}">
                                                        <i class=" js-font-resize fa fa-eye"></i>
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <div class=" js-font-resize card-footer text-center">
                            {{ $forums->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
