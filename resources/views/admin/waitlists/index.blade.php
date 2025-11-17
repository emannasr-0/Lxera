@extends('admin.layouts.app')

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{trans('update.waitlists')}}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{trans('update.waitlists')}}</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">


            <div class=" js-font-resize card">
                <div class=" js-font-resize card-header">
                    @can('admin_waitlists_exports')
                        <a href="{{ getAdminPanelUrl('/waitlists/export') }}" class=" js-font-resize btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
                    @endcan
                </div>

                <div class=" js-font-resize card-body">
                    <table class=" js-font-resize table table-striped font-14" id="datatable-details">
                        <thead>
                        <tr>
                            <th class=" js-font-resize text-left">{{ trans('admin/main.course') }}</th>
                            <th class=" js-font-resize ">{{ trans('update.members') }}</th>
                            <th class=" js-font-resize ">{{ trans('update.registered_members') }}</th>
                            <th class=" js-font-resize ">{{ trans('update.last_submission') }}</th>
                            <th class=" js-font-resize text-left">{{ trans('admin/main.actions') }}</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($waitlists as $waitlist)
                            <tr>
                                <td class=" js-font-resize text-left">
                                    <a class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold" href="{{ $waitlist->getUrl() }}">{{ $waitlist->title }}</a>
                                    @if(!empty($waitlist->category->title))
                                        <div class=" js-font-resize text-small">{{ $waitlist->category->title }}</div>
                                    @else
                                        <div class=" js-font-resize text-small text-warning">{{trans('admin/main.no_category')}}</div>
                                    @endif
                                </td>

                                <td>{{ $waitlist->members }}</td>

                                <td>{{ $waitlist->registered_members }}</td>

                                <td>
                                    {{ !empty($waitlist->last_submission) ? dateTimeFormat($waitlist->last_submission, 'j M Y H:i') : '-' }}
                                </td>

                                <td class=" js-font-resize text-left">
                                    <div class=" js-font-resize btn-group dropdown table-actions">
                                        <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class=" js-font-resize fa fa-ellipsis-v"></i>
                                        </button>
                                        <div class=" js-font-resize dropdown-menu webinars-lists-dropdown">

                                            @can('admin_waitlists_clear_list')
                                                @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl("/waitlists/{$waitlist->id}/clear_list"),
                                                    'btnClass' => 'd-flex align-items-center text-warning text-decoration-none btn-transparent btn-sm mt-1',
                                                    'btnText' => '<i class=" js-font-resize fa fa-times"></i><span class=" js-font-resize ml-2">'. trans("update.clear_list") .'</span>'
                                                ])
                                            @endcan

                                            @can('admin_waitlists_users')
                                                <a href="{{ getAdminPanelUrl("/waitlists/{$waitlist->id}/view_list") }}" class=" js-font-resize d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm mt-1">
                                                    <i class=" js-font-resize fa fa-eye"></i>
                                                    <span class=" js-font-resize ml-2">{{ trans("update.view_list") }}</span>
                                                </a>
                                            @endcan

                                            @can('admin_waitlists_exports')
                                                <a href="{{ getAdminPanelUrl("/waitlists/{$waitlist->id}/export_list") }}" class=" js-font-resize d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm mt-1">
                                                    <i class=" js-font-resize fa fa-download"></i>
                                                    <span class=" js-font-resize ml-2">{{ trans("update.export_list") }}</span>
                                                </a>
                                            @endcan

                                            @can('admin_waitlists_disable')
                                                @include('admin.includes.delete_button',[
                                                        'url' => getAdminPanelUrl("/waitlists/{$waitlist->id}/disable"),
                                                        'btnClass' => 'd-flex align-items-center text-danger text-decoration-none btn-transparent btn-sm mt-1',
                                                        'btnText' => '<i class=" js-font-resize fa fa-lock"></i><span class=" js-font-resize ml-2">'. trans("update.disable_waitlist") .'</span>'
                                                    ])
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                    </table>
                </div>

                <div class=" js-font-resize card-footer text-center">
                    {{ $waitlists->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection
