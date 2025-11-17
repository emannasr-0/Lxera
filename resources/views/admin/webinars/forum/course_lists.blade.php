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

        <div class=" js-font-resize section-body">

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-12">
                    <div class=" js-font-resize card">

                        <div class=" js-font-resize card-body">
                            <div class=" js-font-resize table-responsive">
                                <table class=" js-font-resize table table-striped font-14 ">
                                    <tr>
                                        <th>{{trans('admin/main.id')}}</th>
                                        <th class=" js-font-resize text-left">{{trans('admin/main.course')}}</th>
                                        <th class=" js-font-resize text-left">{{trans('admin/main.instructor')}}</th>
                                        <th>{{trans('admin/main.question_count')}}</th>
                                        <th width="120">{{trans('admin/main.actions')}}</th>
                                    </tr>

                                    @foreach($webinars as $webinar)
                                        <tr class=" js-font-resize text-center">
                                            <td>{{ $webinar->id }}</td>
                                            <td width="18%" class=" js-font-resize text-left">
                                                <a class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold" href="{{ $webinar->getUrl() }}">{{ $webinar->title }}</a>
                                            </td>

                                            <td class=" js-font-resize text-left">{{ $webinar->teacher->full_name }}</td>

                                            <td class=" js-font-resize ">{{ $webinar->forums_count }}</td>


                                            <td width="200" class=" js-font-resize btn-sm">
                                                @can('admin_course_question_forum_list')
                                                    <a href="{{ getAdminPanelUrl() }}/webinars/{{ $webinar->id }}/forums" target="_blank" class=" js-font-resize btn-transparent btn-sm text-primary mt-1 mr-1" data-toggle="tooltip" data-placement="top" title="{{ trans('public.questions') }}">
                                                        <i class=" js-font-resize fa fa-question"></i>
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <div class=" js-font-resize card-footer text-center">
                            {{ $webinars->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
