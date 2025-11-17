@extends('admin.layouts.app')

@push('libraries_top')

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
            <div class=" js-font-resize h-10"></div>
        </div>

        <div class=" js-font-resize card-body">
            <div class=" js-font-resize table-responsive text-center">
                <table class=" js-font-resize table table-striped font-14">
                    <tr>
                        <th>ID</th>
                        <th class=" js-font-resize text-left">{{ trans('admin/main.name') }}</th>
                        <th>{{ trans('admin/main.register_date') }}</th>
                        <th>{{ trans('admin/main.status') }}</th>
                        <th width="120">{{ trans('admin/main.actions') }}</th>
                    </tr>

                    @foreach($requests as $request)

                        <tr>
                            <td>{{ $request->user->id }}</td>
                            <td class=" js-font-resize text-left">
                                <div class=" js-font-resize d-flex align-items-center">
                                    <figure class=" js-font-resize avatar mr-2">
                                        <img src="{{ $request->user->getAvatar() }}" alt="{{ $request->user->full_name }}">
                                    </figure>
                                    <div class=" js-font-resize media-body ml-1">
                                        <div class=" js-font-resize mt-0 mb-1 font-weight-bold">{{ $request->user->full_name }}</div>

                                        @if($request->user->mobile)
                                            <div class=" js-font-resize text-primary text-small font-600-bold">{{ $request->user->mobile }}</div>
                                        @endif

                                        @if($request->user->email)
                                            <div class=" js-font-resize text-primary text-small font-600-bold">{{ $request->user->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>{{ dateTimeFormat($request->user->created_at, 'j M Y | H:i') }}</td>

                            <td>
                                @if($request->user->ban and !empty($request->user->ban_end_at) and $request->user->ban_end_at > time())
                                    <div class=" js-font-resize mt-0 mb-1 font-weight-bold text-danger">{{ trans('admin/main.ban') }}</div>
                                    <div class=" js-font-resize text-small font-600-bold">Until {{ dateTimeFormat($request->user->ban_end_at, 'Y/m/j') }}</div>
                                @else
                                    <div class=" js-font-resize mt-0 mb-1 font-weight-bold {{ ($request->user->status == 'active') ? 'text-success' : 'text-warning' }}">{{ trans('admin/main.'.$request->user->status) }}</div>
                                @endif
                            </td>

                            <td class=" js-font-resize text-center mb-2" width="120">
                                @can('admin_users_impersonate')
                                    <a href="{{ getAdminPanelUrl() }}/users/{{ $request->user->id }}/impersonate" target="_blank" class=" js-font-resize btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.login') }}">
                                        <i class=" js-font-resize fa fa-user-shield"></i>
                                    </a>
                                @endcan

                                @can('admin_users_edit')
                                    <a href="{{ getAdminPanelUrl() }}/users/{{ $request->user->id }}/edit" class=" js-font-resize btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                        <i class=" js-font-resize fa fa-edit"></i>
                                    </a>
                                @endcan

                                @can('admin_delete_account_requests_confirm')
                                    @include('admin.includes.delete_button',[
                                        'url' => getAdminPanelUrl().'/users/delete-account-requests/'.$request->id.'/confirm' ,
                                        'btnIcon' => 'fa-arrow-up',
                                        'tooltip' => trans('update.confirm')
                                       ])
                                @endcan
                            </td>

                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <div class=" js-font-resize card-footer text-center">
            {{ $requests->appends(request()->input())->links() }}
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
