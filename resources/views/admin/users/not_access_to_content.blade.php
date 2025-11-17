@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a>{{ trans('admin/main.students') }}</a></div>
                <div class=" js-font-resize breadcrumb-item"><a href="#">{{ $pageTitle }}</a></div>
            </div>
        </div>
    </section>

    <div class=" js-font-resize section-body">
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
            @can('admin_users_not_access_content_toggle')
                <button type="button" id="addNewUserToNotaccess" class=" js-font-resize btn btn-primary">{{ trans('admin/main.add_new') }}</button>
            @endcan
        </div>

        <div class=" js-font-resize card-body">
            <div class=" js-font-resize table-responsive text-center">
                <table class=" js-font-resize table table-striped font-14">
                    <tr>
                        <th>ID</th>
                        <th class=" js-font-resize text-left">{{ trans('admin/main.name') }}</th>
                        <th>{{ trans('admin/main.register_date') }}</th>
                        <th>{{ trans('admin/main.status') }}</th>
                        <th>{{ trans('update.access_to_content') }}</th>
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

                            <td>{{ dateTimeFormat($user->created_at, 'j M Y | H:i') }}</td>

                            <td>
                                @if($user->ban and !empty($user->ban_end_at) and $user->ban_end_at > time())
                                    <div class=" js-font-resize mt-0 mb-1 font-weight-bold text-danger">{{ trans('admin/main.ban') }}</div>
                                    <div class=" js-font-resize text-small font-600-bold">Until {{ dateTimeFormat($user->ban_end_at, 'Y/m/j') }}</div>
                                @else
                                    <div class=" js-font-resize mt-0 mb-1 font-weight-bold {{ ($user->status == 'active') ? 'text-success' : 'text-warning' }}">{{ trans('admin/main.'.$user->status) }}</div>
                                @endif
                            </td>

                            <td>
                                <div class=" js-font-resize mt-0 mb-1 font-weight-bold text-danger">{{ trans('admin/main.no') }}</div>
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

                                @can('admin_users_not_access_content_toggle')
                                    <a href="{{ getAdminPanelUrl() }}/users/not-access-to-content/{{ $user->id }}/active" class=" js-font-resize btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.active') }}">
                                        <i class=" js-font-resize fa fa-arrow-up"></i>
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


    <section class=" js-font-resize card">
        <div class=" js-font-resize card-body">
            <div class=" js-font-resize section-title ml-0 mt-0 mb-3"><h5>{{trans('admin/main.hints')}}</h5></div>
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-md-4">
                    <div class=" js-font-resize media-body">
                        <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">{{trans('admin/main.students_hint_title_1')}}</div>
                        <div class=" js-font-resize  text-small font-600-bold">{{trans('admin/main.students_hint_description_1')}}</div>
                    </div>
                </div>

                <div class=" js-font-resize col-md-4">
                    <div class=" js-font-resize media-body">
                        <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">{{trans('admin/main.students_hint_title_2')}}</div>
                        <div class=" js-font-resize  text-small font-600-bold">{{trans('admin/main.students_hint_description_2')}}</div>
                    </div>
                </div>


                <div class=" js-font-resize col-md-4">
                    <div class=" js-font-resize media-body">
                        <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">{{trans('admin/main.students_hint_title_3')}}</div>
                        <div class=" js-font-resize text-small font-600-bold">{{trans('admin/main.students_hint_description_3')}}</div>
                    </div>
                </div>


            </div>
        </div>
    </section>

    <div id="addUserToNotAccessModal" class=" js-font-resize d-none">
        <h3 class=" js-font-resize section-title after-line">{{ trans('update.add_to_not_access') }}</h3>
        <div class=" js-font-resize mt-25">
            <form action="{{ getAdminPanelUrl() }}/users/not-access-to-content/store" method="post">

                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label d-block">{{ trans('admin/main.user') }}</label>
                    <select name="user_id" class=" js-font-resize form-control user-search" data-placeholder="{{ trans('public.search_user') }}">

                    </select>
                    <div class=" js-font-resize invalid-feedback"></div>
                </div>

                <div class=" js-font-resize d-flex align-items-center justify-content-end mt-3">
                    <button type="button" class=" js-font-resize js-save-add-user-to-not-access btn btn-sm btn-primary">{{ trans('public.save') }}</button>
                    <button type="button" class=" js-font-resize close-swl btn btn-sm btn-danger ml-2">{{ trans('public.close') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>

    <script>
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
    </script>

    <script src="/assets/default/js/admin/not_access_to_content.min.js"></script>
@endpush
