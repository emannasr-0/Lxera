@extends('admin.layouts.app')

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{ $pageTitle}}</div>
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
                                        <th>{{ trans('admin/main.user') }}</th>
                                        <th class=" js-font-resize text-center">{{ trans('update.package') }}</th>
                                        <th class=" js-font-resize text-center">{{ trans('admin/main.status') }}</th>
                                        <th class=" js-font-resize text-center">{{ trans('admin/main.created_at') }}</th>
                                        <th>{{ trans('admin/main.actions') }}</th>
                                    </tr>

                                    @foreach($becomeInstructors as $become)
                                        <tr>

                                        @if(!empty($become->user->full_name))
                                        <td>{{ $become->user->full_name }}</td>
                                               @else
                                                <td class=" js-font-resize text-danger">User Deleted</td>
                                                @endif


                                           

                                            <td>
                                                @if(!empty($become->registrationPackage))
                                                    {{ $become->registrationPackage->title }}
                                                @else
                                                    ---
                                                @endif
                                            </td>

                                            <td class=" js-font-resize text-center">
                                                <span class=" js-font-resize {{ ($become->status == 'accept' ? 'text-success' : ($become->status == 'pending' ? 'text-warning' : 'text-danger')) }}">
                                                    @if($become->status == 'accept')
                                                        {{ trans('admin/main.accepted') }}
                                                    @elseif($become->status == 'pending')
                                                        {{ trans('admin/main.waiting') }}
                                                    @else
                                                        {{ trans('public.rejected') }}
                                                    @endif
                                                </span>
                                            </td>

                                            <td class=" js-font-resize text-center">{{ dateTimeFormat($become->created_at, 'Y M j | H:i') }}</td>

                                            <td>
                                                @can('admin_become_instructors_reject')
                                                    @if($become->status != 'accept')
                                                        @include('admin.includes.delete_button',[
                                                             'url' => getAdminPanelUrl("/users/{$become->user_id}/acceptRequestToInstructor") ,
                                                             'btnClass' => 'mr-1',
                                                             'btnIcon' => 'fa-check',
                                                             'tooltip' => trans('admin/main.accept_request')
                                                        ])

                                                        @include('admin.includes.delete_button',[
                                                         'url' => getAdminPanelUrl("/users/become-instructors/{$become->id}/reject") ,
                                                         'btnClass' => 'mr-1',
                                                         'btnIcon' => 'fa-times',
                                                         'tooltip' => trans('admin/main.reject_request')
                                                    ])
                                                    @endif
                                                @endcan

                                                @can('admin_users_edit')
                                                    <a href="{{ getAdminPanelUrl() }}/users/{{ $become->user_id }}/edit?type=check_instructor_request" class=" js-font-resize btn-transparent text-primary mr-1" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.check') }}">
                                                        <i class=" js-font-resize fa fa-id-card" aria-hidden="true"></i>
                                                    </a>
                                                @endcan

                                                @can('admin_become_instructors_delete')
                                                    @include('admin.includes.delete_button',[
                                                             'url' => getAdminPanelUrl().'/users/become-instructors/'. $become->id .'/delete' ,
                                                             'btnClass' => '',
                                                             'btnIcon' => 'fa-trash',
                                                             'tooltip' => trans('admin/main.delete')
                                                         ])
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach

                                </table>
                            </div>
                        </div>

                        <div class=" js-font-resize card-footer text-center">
                            {{ $becomeInstructors->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class=" js-font-resize card">
        <div class=" js-font-resize card-body">
            <div class=" js-font-resize section-title ml-0 mt-0 mb-3"><h5>{{trans('admin/main.hints')}}</h5></div>
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-md-6">
                    <div class=" js-font-resize media-body">
                        <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">{{trans('admin/main.become_instructor_hint_title_1')}}</div>
                        <div class=" js-font-resize  text-small font-600-bold">{{trans('admin/main.become_instructor_hint_description_1')}}</div>
                    </div>
                </div>

                <div class=" js-font-resize col-md-6">
                    <div class=" js-font-resize media-body">
                        <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">{{trans('admin/main.become_instructor_hint_title_2')}}</div>
                        <div class=" js-font-resize  text-small font-600-bold">{{trans('admin/main.become_instructor_hint_description_2')}}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
