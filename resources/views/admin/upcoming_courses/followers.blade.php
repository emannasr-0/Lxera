@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <style>
        .select2-container {
            z-index: 1212 !important;
        }
    </style>
@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a></div>
                <div class=" js-font-resize breadcrumb-item"><a href="{{ getAdminPanelUrl('/upcoming_courses') }}">{{ trans('update.upcoming_courses') }}</a></div>
                <div class=" js-font-resize breadcrumb-item"><span>{{ trans('update.followers') }}</span></div>
            </div>
        </div>
    </section>

    <div class=" js-font-resize row">
        <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
            <div class=" js-font-resize card card-statistic-1">
                <div class=" js-font-resize card-icon bg-primary">
                    <i class=" js-font-resize fas fa-users"></i>
                </div>
                <div class=" js-font-resize card-wrap">
                    <div class=" js-font-resize card-header">
                        <h4>{{ trans('update.total_followers') }}</h4>
                    </div>
                    <div class=" js-font-resize card-body">
                        {{ $totalFollowers }}
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
                            <label class=" js-font-resize input-label">{{ trans('admin/main.role') }}</label>
                            <select name="role_id" class=" js-font-resize form-control">
                                <option value="">{{ trans('admin/main.all_roles') }}</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @if($role->id == request()->get('role_id')) selected @endif>{{ $role->caption }}</option>
                                @endforeach
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

    <div class=" js-font-resize card">


        <div class=" js-font-resize card-body">
            <div class=" js-font-resize table-responsive text-center">
                <table class=" js-font-resize table table-striped font-14">
                    <tr>
                        <th class=" js-font-resize text-left">ID</th>
                        <th class=" js-font-resize text-left">{{ trans('admin/main.name') }}</th>
                        <th class=" js-font-resize ">{{ trans('admin/main.role') }}</th>
                        <th>{{ trans('update.followed_at') }}</th>
                        <th width="120">{{ trans('admin/main.actions') }}</th>
                    </tr>

                    @foreach($followers as $follower)
                        @php
                            $user = $follower->user;
                        @endphp

                        <tr>
                            <td class=" js-font-resize text-left">{{ $user->id }}</td>
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

                            <td>{{ $user->role->caption }}</td>

                            <td>{{ dateTimeFormat($follower->created_at, 'j M Y') }}</td>

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

                                @can('admin_upcoming_courses_followers')
                                    @include('admin.includes.delete_button',[
                                                'url' => getAdminPanelUrl('/upcoming_courses/'. $upcomingCourse->id .'/followers/'. $follower->id .'/delete'),
                                                'tooltip' => trans('update.unfollow_course'),
                                            ])
                                @endcan
                            </td>

                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <div class=" js-font-resize card-footer text-center">
            {{ $followers->appends(request()->input())->links() }}
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

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>

    <script>
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
    </script>

    <script src="/assets/default/js/admin/webinar_students.min.js"></script>
@endpush
