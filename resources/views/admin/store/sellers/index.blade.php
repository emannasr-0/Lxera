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
                            <div class=" js-font-resize col-md-3">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{trans('admin/main.search')}}</label>
                                    <input name="full_name" type="text" class=" js-font-resize form-control" value="{{ request()->get('full_name') }}">
                                </div>
                            </div>

                            <div class=" js-font-resize col-md-3">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{ trans('admin/main.role') }}</label>
                                    <select name="role_name" class=" js-font-resize form-control">
                                        <option value="">{{ trans('public.all') }}</option>

                                        <option value="{{ \App\Models\Role::$teacher }}" @if(request()->get('role_name') == \App\Models\Role::$teacher) selected @endif>{{ trans('home.instructors') }}</option>
                                        <option value="{{ \App\Models\Role::$organization }}" @if(request()->get('role_name') == \App\Models\Role::$organization) selected @endif>{{ trans('home.organizations') }}</option>
                                        <option value="{{ \App\Models\Role::$admin }}" @if(request()->get('role_name') == \App\Models\Role::$admin) selected @endif>{{ trans('admin/main.admin') }}</option>

                                    </select>
                                </div>
                            </div>


                            <div class=" js-font-resize col-md-3">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{ trans('admin/main.users_group') }}</label>
                                    <select name="group_id" data-plugin-selectTwo class=" js-font-resize form-control populate">
                                        <option value="">{{ trans('admin/main.select_users_group') }}</option>
                                        @foreach($userGroups as $userGroup)
                                            <option value="{{ $userGroup->id }}" @if(request()->get('group_id') == $userGroup->id) selected @endif>{{ $userGroup->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class=" js-font-resize col-md-3">
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
                                        <th>{{ trans('update.physical_products') }}</th>
                                        <th>{{ trans('update.virtual_products') }}</th>
                                        <th>{{ trans('admin/main.total_sales') }}</th>
                                        <th>{{ trans('update.pending_orders') }}</th>
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

                                            <td class=" js-font-resize text-center">
                                                <span class=" js-font-resize d-block font-14">{{ $user->physical_products_count }}</span>
                                                <span class=" js-font-resize d-block font-12">{{ !empty($user->physical_products_sales) ? handlePrice($user->physical_products_sales) : 0 }}</span>
                                            </td>

                                            <td class=" js-font-resize text-center">
                                                <span class=" js-font-resize d-block font-14">{{ $user->virtual_products_count }}</span>
                                                <span class=" js-font-resize d-block font-12">{{ !empty($user->virtual_products_sales) ? handlePrice($user->virtual_products_sales) : 0 }}</span>
                                            </td>

                                            <td class=" js-font-resize text-center">
                                                <span class=" js-font-resize d-block font-12">{{ !empty($user->total_sales) ? handlePrice($user->total_sales) : 0 }}</span>
                                            </td>


                                            <td>{{ $user->pending_orders_count }}</td>


                                            <td class=" js-font-resize text-center mb-2" width="120">
                                                @can('admin_users_impersonate')
                                                    <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/impersonate" target="_blank" class=" js-font-resize btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.login') }}">
                                                        <i class=" js-font-resize fa fa-user-shield"></i>
                                                    </a>
                                                @endcan

                                                @can('admin_users_edit')
                                                    <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/edit" class=" js-font-resize btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                        <i class=" js-font-resize fa fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('admin_users_delete')
                                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/users/'.$user->id.'/delete' , 'btnClass' => ''])
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
