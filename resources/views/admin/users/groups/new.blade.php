@extends('admin.layouts.app')

@push('styles_top')

@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ !empty($group) ? trans('admin/main.edit') : '' }} {{ trans('admin/main.user_group') }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{ trans('admin/main.new_user_group') }}</div>
            </div>
        </div>


        <div class=" js-font-resize section-body">

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-body">

                            @if(!empty($group))
                                <ul class=" js-font-resize nav nav-pills" id="myTab3" role="tablist">
                                    <li class=" js-font-resize nav-item">
                                        <a class=" js-font-resize nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">{{ trans('admin/main.main_general') }}</a>
                                    </li>

                                    @can('admin_update_group_registration_package')
                                        <li class=" js-font-resize nav-item">
                                            <a class=" js-font-resize nav-link" id="registrationPackage-tab" data-toggle="tab" href="#registrationPackage" role="tab" aria-controls="registrationPackage" aria-selected="true">{{ trans('update.registration_package') }}</a>
                                        </li>
                                    @endcan
                                </ul>
                            @endif

                            <div class=" js-font-resize tab-content" id="myTabContent2">
                                @include('admin.users.groups.tabs.general')

                                @if(!empty($group))
                                    @can('admin_update_group_registration_package')
                                        @include('admin.users.groups.tabs.registration_package')
                                    @endcan
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
