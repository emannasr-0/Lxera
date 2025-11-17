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

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-12">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-header">
                            @can('admin_store_specifications_create')
                                <a href="{{ getAdminPanelUrl() }}/store/specifications/create" class=" js-font-resize btn btn-primary">{{ trans('admin/main.add_new') }}</a>
                            @endcan
                        </div>

                        <div class=" js-font-resize card-body">
                            <div class=" js-font-resize table-responsive">
                                <table class=" js-font-resize table table-striped font-14">
                                    <tr>
                                        <th class=" js-font-resize text-left">{{ trans('admin/main.title') }}</th>
                                        <th>{{ trans('admin/main.type') }}</th>
                                        <th>{{ trans('admin/main.categories') }}</th>
                                        <th>{{ trans('admin/main.action') }}</th>
                                    </tr>
                                    @foreach($specifications as $specification)

                                        <tr>
                                            <td class=" js-font-resize text-left">{{ $specification->title }}</td>
                                            <td>{{ trans('update.'.$specification->input_type) }}</td>
                                            <td>{{ $specification->categories_count }}</td>
                                            <td>
                                                @can('admin_store_specifications_edit')
                                                    <a href="{{ getAdminPanelUrl() }}/store/specifications/{{ $specification->id }}/edit"
                                                       class=" js-font-resize btn-transparent btn-sm text-primary">
                                                        <i class=" js-font-resize fa fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('admin_store_specifications_delete')
                                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/store/specifications/'.$specification->id.'/delete'])
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <div class=" js-font-resize card-footer text-center">
                            {{ $specifications->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
