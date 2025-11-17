@extends('admin.layouts.app')

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ trans('admin/main.users_groups') }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{ trans('admin/main.users_groups') }}</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-12">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-body">
                            <div class=" js-font-resize table-responsive">
                                <table class=" js-font-resize table table-striped font-14">
                                    <tr>
                                        <th>#</th>
                                        <th class=" js-font-resize text-left">{{ trans('admin/main.name') }}</th>
                                        <th>{{ trans('admin/main.users') }}</th>
                                        <th>{{ trans('admin/main.commission') }}</th>
                                        <th>{{ trans('admin/main.discount') }}</th>
                                        <th>{{ trans('admin/main.status') }}</th>
                                        <th>{{ trans('admin/main.actions') }}</th>
                                    </tr>

                                    @foreach($groups as $group)
                                        <tr>
                                            <td>{{ $group->id }}</td>
                                            <td class=" js-font-resize text-left">
                                                <span>{{ $group->name }}</span>
                                            </td>
                                            <td>{{ $group->groupUsers->count() }}</td>
                                            <td>{{ $group->commission ?? 0 }}%</td>
                                            <td>{{ $group->discount ?? 0 }}%</td>
                                            <td>
                                                <span class=" js-font-resize {{ $group->status == 'active' ? 'text-success' : 'text-danger' }}">{{ trans('admin/main.'.$group->status) }}</span>
                                            </td>
                                            <td>
                                                @can('admin_group_edit')
                                                    <a href="{{ getAdminPanelUrl() }}/users/groups/{{ $group->id }}/edit" class=" js-font-resize btn-transparent text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                        <i class=" js-font-resize fa fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('admin_group_delete')
                                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/users/groups/'. $group->id.'/delete','btnClass' => ''])
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach

                                </table>
                            </div>
                        </div>

                        <div class=" js-font-resize card-footer text-center">
                            {{ $groups->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
