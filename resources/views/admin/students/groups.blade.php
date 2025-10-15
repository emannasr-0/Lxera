@extends('admin.layouts.app')

@push('libraries_top')
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.list') }} {{ 'المجموعات' }} </h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a>مجموعة {{ $group->name }}</a></div>
                <div class="breadcrumb-item"><a href="#"> {{ $item->title }}</a></div>
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
                            <h4>{{ 'كل الطلاب' }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $enrollments->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            @can('admin_users_export_excel')
                <a href="{{ getAdminPanelUrl() }}/courses/groups/{{ $group->id }}/exportExcel?{{ http_build_query(request()->all()) }}"
                    class="btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
            @endcan
            <div class="h-10"></div>
        </div>

        <div class="card-body">
            <div class="table-responsive text-center">
                <table class="table table-striped font-14">
                    <tr>
                        <th>{{ '#' }}</th>
                        <th>كود الطالب</th>
                        <th>{{ trans('admin/main.name') }}</th>
                        <th>{{ trans('admin/main.register_date') }}</th>
                        <th>{{ trans('admin/main.status') }}</th>
                        <th width="120">{{ trans('admin/main.actions') }}</th>
                    </tr>

                    @foreach ($enrollments as $index => $enrollment)
                        <tr>
                            <td>{{ ++$index }}</td>
                            <td>{{ $enrollment->user->user_code }}</td>

                            <td class="text-left">
                                <div class="d-flex align-items-center">
                                    <figure class="avatar mr-2">
                                        <img src="{{ $enrollment->user->getAvatar() }}"
                                            alt="{{ $enrollment->user->student ? $enrollment->user->student->ar_name : null }}">
                                    </figure>
                                    <div class="media-body ml-1">
                                        <div class="mt-0 mb-1 font-weight-bold">
                                            {{ $enrollment->user->student ? $enrollment->user->student->ar_name : null }}
                                        </div>

                                        @if ($enrollment->user->mobile)
                                            <div class="text-primary text-left font-600-bold" style="font-size:12px;">
                                                {{ $enrollment->user->mobile }}</div>
                                        @endif

                                        @if ($enrollment->user->email)
                                            <div class="text-primary text-small font-600-bold">
                                                {{ $enrollment->user->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                {{ Carbon\Carbon::parse($enrollment->created_at)->format('Y-m-d | H:i') }}
                            </td>

                            <td>
                                @if ($enrollment->user->ban and !empty($enrollment->user->ban_end_at) and $enrollment->user->ban_end_at > time())
                                    <div class="mt-0 mb-1 font-weight-bold text-danger">{{ trans('admin/main.ban') }}
                                    </div>
                                    <div class="text-small font-600-bold">Until
                                        {{ dateTimeFormat($enrollment->user->ban_end_at, 'Y/m/j') }}</div>
                                @else
                                    <div
                                        class="mt-0 mb-1 font-weight-bold {{ $enrollment->user->status == 'active' ? 'text-success' : 'text-warning' }}">
                                        {{ trans('admin/main.' . $enrollment->user->status) }}</div>
                                @endif
                            </td>

                            <td class="text-center mb-2" width="120">
                                @can('admin_users_transform')
                                    @if (!empty($enrollment->user->student))
                                        @include('admin.includes.transform_button', [
                                            'url' => getAdminPanelUrl() . '/courses/groups/' .$group->id . '/change',
                                            'btnClass' => 'btn-transparent  text-primary',
                                            'btnText' => '<i class="fa fa-retweet"></i>',
                                            'hideDefaultClass' => true,
                                            'id' =>$enrollment->user->id,
                                            'from' =>$group,
                                            'items' => $group->item->groups,
                                            'user' => $enrollment->user,
                                            'title' => "تحويل الطالب من المجوعة " . $group->name . " إلي مجموعة اخري "
                                        ])
                                    @endif
                                @endcan

                                @can('admin_users_impersonate')
                                    <a href="{{ getAdminPanelUrl() }}/users/{{ $enrollment->user->id }}/impersonate"
                                        target="_blank" class="btn-transparent  text-primary" data-toggle="tooltip"
                                        data-placement="top" title="{{ trans('admin/main.login') }}">
                                        <i class="fa fa-user-shield"></i>
                                    </a>
                                @endcan

                                @can('admin_users_edit')
                                    <a href="{{ getAdminPanelUrl() }}/users/{{ $enrollment->user->id }}/edit"
                                        class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top"
                                        title="{{ trans('admin/main.edit') }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                @endcan

                                @can('admin_users_delete')
                                    @include('admin.includes.delete_button', [
                                        'url' =>
                                            getAdminPanelUrl() . '/users/' . $enrollment->user->id . '/delete',
                                        'btnClass' => '',
                                        'deleteConfirmMsg' => trans('update.user_delete_confirm_msg'),
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
