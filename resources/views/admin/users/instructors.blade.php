@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ trans('admin/main.instructors') }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="#">{{ trans('admin/main.instructors') }}</a></div>
                <div class=" js-font-resize breadcrumb-item">{{ trans('admin/main.users') }}</div>
            </div>
        </div>
    </section>

    <div class=" js-font-resize section-body">
        <div class=" js-font-resize row">
            <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-primary">
                        <i class=" js-font-resize fas fa-users"></i>
                    </div>
                    <div class=" js-font-resize card-wrap">
                        <div class=" js-font-resize card-header">
                            <h4>{{ trans('admin/main.total_instructors') }}</h4>
                        </div>
                        <div class=" js-font-resize card-body">
                            {{ $totalInstructors }}
                        </div>
                    </div>
                </div>
            </div>
            <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-success">
                        <i class=" js-font-resize fas fa-briefcase"></i></div>
                    <div class=" js-font-resize card-wrap">
                        <div class=" js-font-resize card-header">
                            <h4>{{ trans('admin/main.organizations_instructors') }}</h4>
                        </div>
                        <div class=" js-font-resize card-body">
                            {{ $totalOrganizationsInstructors }}
                        </div>
                    </div>
                </div>
            </div>
            <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-warning">
                        <i class=" js-font-resize fas fa-info-circle"></i></div>
                    <div class=" js-font-resize card-wrap">
                        <div class=" js-font-resize card-header">
                            <h4>{{ trans('admin/main.inactive_instructors') }}</h4>
                        </div>
                        <div class=" js-font-resize card-body">
                            {{ $inactiveInstructors }}
                        </div>
                    </div>
                </div>
            </div>
            <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-danger">
                        <i class=" js-font-resize fas fa-ban"></i></div>
                    <div class=" js-font-resize card-wrap">
                        <div class=" js-font-resize card-header">
                            <h4>{{ trans('admin/main.ban_instructors') }}</h4>
                        </div>
                        <div class=" js-font-resize card-body">
                            {{ $banInstructors }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class=" js-font-resize card">
            <div class=" js-font-resize card-body">
                <form method="get" class=" js-font-resize mb-0">

                    <div class=" js-font-resize row">
                        <div class=" js-font-resize col-md-3">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('admin/main.search') }}</label>
                                <input name="full_name" type="text" class=" js-font-resize form-control" value="{{ request()->get('full_name') }}">
                            </div>
                        </div>

                        <div class=" js-font-resize col-md-3">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('admin/main.start_date') }}</label>
                                <div class=" js-font-resize input-group">
                                    <input type="date" id="from" class=" js-font-resize text-center form-control" name="from" value="{{ request()->get('from') }}" placeholder="Start Date">
                                </div>
                            </div>
                        </div>
                        <div class=" js-font-resize col-md-3">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('admin/main.end_date') }}</label>
                                <div class=" js-font-resize input-group">
                                    <input type="date" id="to" class=" js-font-resize text-center form-control" name="to" value="{{ request()->get('to') }}" placeholder="End Date">
                                </div>
                            </div>
                        </div>


                        <div class=" js-font-resize col-md-3">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('admin/main.filters') }}</label>
                                <select name="sort" data-plugin-selectTwo class=" js-font-resize form-control populate">
                                    <option value="">{{ trans('admin/main.filter_type') }}</option>
                                    <option value="sales_classes_asc" @if(request()->get('sort') == 'sales_classes_asc') selected @endif>{{ trans('admin/main.classes_sales_ascending') }}</option>
                                    <option value="sales_classes_desc" @if(request()->get('sort') == 'sales_classes_desc') selected @endif>{{ trans('admin/main.classes_sales_descending') }}</option>
                                    <option value="purchased_classes_asc" @if(request()->get('sort') == 'purchased_asc') selected @endif>{{ trans('admin/main.purchased_classes_ascending') }}</option>
                                    <option value="purchased_classes_desc" @if(request()->get('sort') == 'purchased_desc') selected @endif>{{ trans('admin/main.purchased_classes_descending') }}</option>
                                    <option value="sales_appointments_asc" @if(request()->get('sort') == 'appointments_asc') selected @endif>{{ trans('admin/main.sales_appointments_ascending') }}</option>
                                    <option value="sales_appointments_desc" @if(request()->get('sort') == 'appointments_desc') selected @endif> {{ trans('admin/main.sales_appointments_descending') }}</option>
                                    <option value="purchased_appointments_asc" @if(request()->get('sort') == 'purchased_appointments_asc') selected @endif>{{ trans('admin/main.purchased_appointments_ascending') }}</option>
                                    <option value="purchased_appointments_desc" @if(request()->get('sort') == 'purchased_appointments_desc') selected @endif>{{ trans('admin/main.purchased_appointments_descending') }}</option>
                                    <option value="register_asc" @if(request()->get('sort') == 'register_asc') selected @endif>{{ trans('admin/main.register_date_ascending') }}</option>
                                    <option value="register_desc" @if(request()->get('sort') == 'register_desc') selected @endif>{{ trans('admin/main.register_date_descending') }}</option>
                                </select>
                            </div>
                        </div>


                        <div class=" js-font-resize col-md-3">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('admin/main.organization') }}</label>
                                <select name="organization_id" data-plugin-selectTwo class=" js-font-resize form-control populate">
                                    <option value="">{{ trans('admin/main.select_organization') }}</option>
                                    @foreach($organizations as $organization)
                                        <option value="{{ $organization->id }}" @if(request()->get('organization_id') == $organization->id) selected @endif>{{ $organization->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class=" js-font-resize col-md-3">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('admin/main.user_group') }}</label>
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
                                <label class=" js-font-resize input-label">{{ trans('admin/main.status') }}</label>
                                <select name="status" data-plugin-selectTwo class=" js-font-resize form-control populate">
                                    <option value="">{{ trans('admin/main.all_status') }}</option>
                                    <option value="active_verified" @if(request()->get('status') == 'active_verified') selected @endif>{{ trans('admin/main.active_verified') }}</option>
                                    <option value="active_notVerified" @if(request()->get('status') == 'active_notVerified') selected @endif>{{ trans('admin/main.active_not_verified') }}</option>
                                    <option value="inactive" @if(request()->get('status') == 'inactive') selected @endif>{{ trans('admin/main.inactive') }}</option>
                                    <option value="ban" @if(request()->get('status') == 'ban') selected @endif>{{ trans('admin/main.banned') }}</option>
                                </select>
                            </div>
                        </div>


                        <div class=" js-font-resize col-md-3">
                            <div class=" js-font-resize form-group mt-1">
                                <label class=" js-font-resize input-label mb-4"> </label>
                                <input type="submit" class=" js-font-resize text-center btn btn-primary w-100" value="{{ trans('admin/main.show_results') }}">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <div class=" js-font-resize card">
        <div class=" js-font-resize card-header">
            @can('admin_users_export_excel')
                <a href="{{ getAdminPanelUrl() }}/instructors/excel?{{ http_build_query(request()->all()) }}" class=" js-font-resize btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
            @endcan
            <div class=" js-font-resize h-10"></div>
        </div>

        <div class=" js-font-resize card-body">
            <div class=" js-font-resize table-responsive text-center">
                <table class=" js-font-resize table table-striped font-14">
                    <tr>
                        <th>{{ trans('admin/main.id') }}</th>
                        <th>{{ trans('admin/main.name') }}</th>
                        <th>{{ trans('admin/main.classes_sales') }}</th>
                        <th>{{ trans('admin/main.appointments_sales') }}</th>
                        <th>{{ trans('admin/main.purchased_classes') }}</th>
                        <th>{{ trans('admin/main.purchased_appointments') }}</th>
                        <th>{{ trans('admin/main.wallet_charge') }}</th>
                        <th>{{ trans('admin/main.user_group') }}</th>
                        <th>كود المعلم </th>
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
                            <td>
                                <div class=" js-font-resize media-body">
                                    <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">{{ $user->classesSalesCount }}</div>
                                    <div class=" js-font-resize text-small font-600-bold">{{ handlePrice($user->classesSalesSum) }}</div>
                                </div>
                            </td>
                            <td>
                                <div class=" js-font-resize media-body">
                                    <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">{{ $user->meetingsSalesCount }}</div>
                                    <div class=" js-font-resize text-small font-600-bold">{{ handlePrice($user->meetingsSalesSum) }}</div>
                                </div>
                            </td>
                            <td>
                                <div class=" js-font-resize media-body">
                                    <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">{{ $user->classesPurchasedsCount }}</div>
                                    <div class=" js-font-resize text-small font-600-bold">{{ handlePrice($user->classesPurchasedsSum) }}</div>
                                </div>
                            </td>
                            <td>
                                <div class=" js-font-resize media-body">
                                    <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">{{ $user->meetingsPurchasedsCount }}</div>
                                    <div class=" js-font-resize text-small font-600-bold">{{ handlePrice($user->meetingsPurchasedsSum) }}</div>
                                </div>
                            </td>

                            <td>{{ handlePrice($user->getAccountingBalance()) }}</td>

                            <td>
                                {{ !empty($user->userGroup) ? $user->userGroup->group->name : '' }}
                            </td>
                            
                            <td>
                                {{ !empty($user->user_code) ? $user->user_code : '' }}
                            </td>

                            <td>{{ dateTimeFormat($user->created_at, 'j M Y - H:i') }}</td>

                            <td>
                                <div class=" js-font-resize media-body">
                                    @if($user->ban and !empty($user->ban_end_at) and $user->ban_end_at > time())
                                        <div class=" js-font-resize mt-0 mb-1 font-weight-bold text-danger">{{ trans('admin/main.banned') }}</div>
                                        <div class=" js-font-resize text-small font-600-bold">{{ trans('admin/main.until') }} {{ dateTimeFormat($user->ban_end_at, 'j M Y') }}</div>
                                    @else
                                        <div class=" js-font-resize mt-0 mb-1 font-weight-bold {{ ($user->status == 'active') ? 'text-success' : 'text-warning' }}">{{ trans('admin/main.'.$user->status) }}</div>
                                        <div class=" js-font-resize text-small font-600-bold {{ ($user->verified ? ' text-success ' : ' text-warning ') }}">({{ trans('admin/main.'.($user->verified ? 'verified' : 'not_verified')) }})</div>
                                    @endif
                                </div>
                            </td>
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


    <section class=" js-font-resize card">
        <div class=" js-font-resize card-body">
            <div class=" js-font-resize section-title ml-0 mt-0 mb-3"><h4>{{trans('admin/main.hints')}}</h4></div>
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-md-4">
                    <div class=" js-font-resize media-body">
                        <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">{{ trans('admin/main.instructors_hint_title_1') }}</div>
                        <div class=" js-font-resize  text-small font-600-bold mb-2">{{ trans('admin/main.instructors_hint_description_1') }}</div>
                    </div>
                </div>

                <div class=" js-font-resize col-md-4">
                    <div class=" js-font-resize media-body">
                        <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">{{ trans('admin/main.instructors_hint_title_2') }}</div>
                        <div class=" js-font-resize  text-small font-600-bold mb-2">{{ trans('admin/main.instructors_hint_description_2') }}</div>
                    </div>
                </div>

                <div class=" js-font-resize col-md-4">
                    <div class=" js-font-resize media-body">
                        <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">{{ trans('admin/main.instructors_hint_title_3') }}</div>
                        <div class=" js-font-resize text-small font-600-bold mb-2">{{ trans('admin/main.instructors_hint_description_3') }}</div>
                    </div>
                </div>


            </div>
        </div>
    </section>
@endsection
