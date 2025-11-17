@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ trans('admin/main.tags') }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{ trans('admin/main.tag') }}</div>
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
                                        <th>{{ trans('admin/main.title') }}</th>
                                        <th>{{ trans('admin/main.action') }}</th>
                                    </tr>
                                    @foreach($tags as $tag)
                                        <tr>
                                            <td>{{ $tag->id }}</td>
                                            <td>{{ $tag->title }}</td>

                                            <td>
                                                @can('admin_tags_edit')
                                                    <a href="{{ getAdminPanelUrl() }}/tags/{{ $tag->id }}/edit"
                                                       class=" js-font-resize btn-transparent btn-sm text-primary">
                                                        <i class=" js-font-resize fa fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('admin_tags_delete')
                                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/tags/'.$tag->id.'/delete'])
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <div class=" js-font-resize card-footer text-center">
                            {{ $tags->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
