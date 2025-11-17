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
                <div class=" js-font-resize breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">

            <section class=" js-font-resize card">
                <div class=" js-font-resize card-body">
                    <form method="get" class=" js-font-resize mb-0">

                        <div class=" js-font-resize row">
                            <div class=" js-font-resize col-md-4">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{trans('admin/main.search')}}</label>
                                    <input name="full_name" type="text" class=" js-font-resize form-control" value="{{ request()->get('full_name') }}">
                                </div>
                            </div>

                            <div class=" js-font-resize col-md-4">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{ trans('admin/main.role') }}</label>
                                    <select name="role_id" class=" js-font-resize form-control">
                                        <option value="">{{ trans('public.all') }}</option>
                                        @foreach($staffsRoles as $role)
                                            <option value="{{ $role->id }}" @if(!empty(request()->get('role_id')) and request()->get('role_id') == $role->id) selected @endif>{{ $role->caption }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class=" js-font-resize col-md-4">
                                <div class=" js-font-resize form-group">
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
                        <div class=" js-font-resize card-header">

                        </div>

                        <div class=" js-font-resize card-body">
                            <div class=" js-font-resize table-responsive">
                                <table class=" js-font-resize table table-striped font-14">
                                    <tr>
                                        <th>{{ trans('admin/main.id') }}</th>
                                        <th class=" js-font-resize text-left">{{ trans('admin/main.name') }}</th>
                                        <th>{{ trans('admin/main.role_name') }}</th>
                                        <th>{{ trans('admin/main.register_date') }}</th>
                                        <th>{{ trans('admin/main.status') }}</th>
                                        <th width="120">{{ trans('admin/main.actions') }}</th>
                                    </tr>

                                    @foreach($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td class=" js-font-resize text-left">
                                                <div class=" js-font-resize d-flex align-items-center">
                                                    <figure class=" js-font-resize avatar mr-2">
                                                        <img src="{{ $user->getAvatar() }}" alt="{{ $user->full_name }}">
                                                    </figure>
                                                    <div class=" js-font-resize media-body ml-1">
                                                        <div class=" js-font-resize mt-0 mb-1 font-weight-bold">{{ $user->full_name }}</div>

                                                        @if($user->mobile)
                                                            <div class=" js-font-resize text-primary text-small font-600-bold">{{ $user->mobile }}</div>
                                                        @endif

                                                        @if($user->email)
                                                            <div class=" js-font-resize text-primary text-small font-600-bold">{{ $user->email }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            <td class=" js-font-resize text-center">{{ $user->role->caption }}</td>
                                            <td>{{ dateTimeFormat($user->created_at, 'j M Y | H:i') }}</td>

                                            <td>
                                                <div class=" js-font-resize media-body">
                                                    @if($user->ban and !empty($user->ban_end_at) and $user->ban_end_at > time())
                                                        <div class=" js-font-resize mt-0 mb-1 font-weight-bold text-danger">{{ trans('admin/main.ban') }}</div>
                                                        <div class=" js-font-resize text-small font-600-bold">Until {{ dateTimeFormat($user->ban_end_at, 'Y/m/j') }}</div>
                                                    @else
                                                        <div class=" js-font-resize mt-0 mb-1 font-weight-bold {{ ($user->status == 'active') ? 'text-success' : 'text-warning' }}">{{ trans('admin/main.'.$user->status) }}</div>
                                                        <div class=" js-font-resize text-small font-600-bold {{ ($user->verified ? ' text-success ' : ' text-warning ') }}">({{ trans('public.'.($user->verified ? 'verified' : 'not_verified')) }})</div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class=" js-font-resize text-center mb-2" width="120">

                                                @can('admin_users_edit')
                                                    <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/edit" class=" js-font-resize btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                        <i class=" js-font-resize fa fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('admin_users_delete')
                                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/users/'.$user->id.'/delete' , 'btnClass' => '', 'deleteConfirmMsg' => trans('update.user_delete_confirm_msg')])
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <div class=" js-font-resize card-footer text-center">
                            {{ $users->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
