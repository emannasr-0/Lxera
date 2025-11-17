@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ trans('admin/main.testimonials') }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{ trans('admin/main.testimonials') }}</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-12">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-header">
                            @can('admin_testimonials_create')
                                <a href="{{ getAdminPanelUrl() }}/testimonials/create" class=" js-font-resize btn btn-primary">{{ trans('admin/main.add_new') }}</a>
                            @endcan
                        </div>

                        <div class=" js-font-resize card-body">
                            <div class=" js-font-resize table-responsive">
                                <table class=" js-font-resize table table-striped font-14">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ trans('admin/main.user_name') }}</th>
                                        <th>{{ trans('admin/main.rate') }}</th>
                                        <th class=" js-font-resize text-center">{{ trans('admin/main.content') }}</th>
                                        <th class=" js-font-resize text-center">{{ trans('admin/main.status') }}</th>
                                        <th>{{ trans('admin/main.created_at') }}</th>
                                        <th>{{ trans('admin/main.action') }}</th>
                                    </tr>
                                    @foreach($testimonials as $testimonial)
                                        <tr>
                                            <td>
                                                <img src="{{ $testimonial->user_avatar }}" alt="" width="56" height="56" class=" js-font-resize rounded-circle">
                                            </td>
                                            <td>{{ $testimonial->user_name }}</td>
                                            <td>{{ $testimonial->rate }}</td>
                                            <td class=" js-font-resize text-center" width="30%">{{ nl2br(truncate($testimonial->comment, 150, true)) }}</td>

                                            <td class=" js-font-resize text-center">
                                                @if($testimonial->status == 'active')
                                                    <span class=" js-font-resize text-success">{{ trans('admin/main.active') }}</span>
                                                @else
                                                    <span class=" js-font-resize text-warning">{{ trans('admin/main.disable') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ dateTimeFormat($testimonial->created_at, 'j M Y | H:i') }}</td>
                                            <td width="150px">

                                                @can('admin_supports_reply')
                                                    <a href="{{ getAdminPanelUrl() }}/testimonials/{{ $testimonial->id }}/edit" class=" js-font-resize btn-transparent text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                        <i class=" js-font-resize fa fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('admin_supports_delete')
                                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/testimonials/'.$testimonial->id.'/delete' , 'btnClass' => ''])
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <div class=" js-font-resize card-footer text-center">
                            {{ $testimonials->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
