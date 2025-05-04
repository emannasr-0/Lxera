@extends('admin.layouts.app')

@push('libraries_top')
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.list') }} {{ 'المجموعات' }} </h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a>  {{ $item->title }}</a></div>
                <div class="breadcrumb-item"><a href="#">{{ 'المجموعات' }}</a></div>
            </div>
        </div>
    </section>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ 'كل المجموعات' }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalGroups }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                @can('admin_users_export_excel')
                    <a href="{{ getAdminPanelUrl() }}/courses/excelGroup?{{ http_build_query(request()->all()) }}"
                        class="btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
                @endcan
                <div class="h-10"></div>
            </div>

            <div class="card-body">
                <div class="table-responsive text-center">
                    <table class="table table-striped font-14">
                        <tr>
                            <th>{{ '#' }}</th>
                            <th>اسم المجموعه</th>
                            <th> عدد الطلاب</th>
                            <th> سعة المجموعة</th>
                            <th> تاريخ البدأ</th>
                            <th> تاريخ الإنتهاء</th>
                            <th>الحالة</th>
                            <th width="120">{{ trans('admin/main.actions') }}</th>
                        </tr>

                        @foreach ($groups as $group)
                        @php
                            $type = $group->bundle ? 'bundles' : 'courses';
                        @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="text-center">{{ $group->name }}</td>
                                <td class="text-center">
                                    {{ $group->enrollments->count() }}
                                    </td>
                                <td class="text-center @if ($group->enrollments->count()>$group->capacity) text-danger @endif" >{{ $group->capacity }}</td>
                                <td class="text-center">{{ $group->start_date }}</td>
                                <td class="text-center">{{ $group->end_date }}</td>
                                <td class="text-center">
                                    <span
                                        class="{{ $group->status == 'active' ? 'text-success' : 'text-danger' }}">{{ trans('admin/main.' . $group->status) }}</span>
                                </td>
                                <td class="text-center mb-2" width="120">

                                    @can('admin_users_impersonate')
                                        <a href="{{ getAdminPanelUrl() }}/{{  $type }}/groups/{{ $group->id }}/show"
                                            class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top"
                                            title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan

                                    @can('admin_users_edit')
                                        <a href="{{ getAdminPanelUrl() }}/{{  $type }}/groups/{{ $group->id }}/edit"
                                            class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top"
                                            title="{{ trans('admin/main.edit') }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    @endcan

                                    @can('admin_users_delete')
                                        @include('admin.includes.delete_button', [
                                            'url' => getAdminPanelUrl() . '/courses/groups/' . $group->id . '/delete',
                                            'btnClass' => '',
                                            'deleteConfirmMsg' => trans('update.group_delete_confirm_msg'),
                                        ])
                                    @endcan
                                </td>

                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>

            {{-- <div class="card-footer text-center">
                {{ $groups->appends(request()->input())->links() }}
            </div> --}}
        </div>

    </div>
@endsection


@push('scripts_bottom')
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script>
        var undefinedActiveSessionLang = '{{ trans('webinars.undefined_active_session') }}';
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var selectChapterLang = '{{ trans('update.select_chapter') }}';
    </script>

    <script src="/assets/default/js/panel/make_next_session.min.js"></script>
@endpush
