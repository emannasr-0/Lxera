@extends('admin.layouts.app')

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ trans('admin/main.support_departments') }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{ trans('admin/main.departments') }}</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">
            <div class=" js-font-resize card">
                <div class=" js-font-resize card-body col-12">
                    <div class=" js-font-resize tabs">
                        <ul class=" js-font-resize nav nav-pills">
                            <li class=" js-font-resize nav-item"><a class=" js-font-resize nav-link active" href="#list" data-toggle="tab"> {{ trans('admin/main.departments') }} </a></li>
                            <li class=" js-font-resize nav-item"><a class=" js-font-resize nav-link" href="#newitem" data-toggle="tab">{{ trans('admin/main.new_department') }}</a></li>
                        </ul>
                        <div class=" js-font-resize tab-content">
                            <div id="list" class=" js-font-resize tab-pane active">
                                <div class=" js-font-resize table-responsive">
                                    <table class=" js-font-resize table table-striped font-14">

                                        <tr>
                                            <th>{{ trans('admin/main.department') }}</th>
                                            <th class=" js-font-resize text-center" width="200">{{ trans('admin/main.conversations') }}</th>
                                            <th class=" js-font-resize text-center" width="100">{{ trans('admin/main.actions') }}</th>
                                        </tr>

                                        @foreach($departments as $department)
                                            <tr>
                                                <td>
                                                    <span>{{ $department->title }}</span>
                                                </td>

                                                <td>{{ $department->supports_count }}</td>

                                                <td class=" js-font-resize text-center">
                                                    @can('admin_support_departments_edit')
                                                        <a href="{{ getAdminPanelUrl() }}/supports/departments/{{ $department->id }}/edit" class=" js-font-resize btn-transparent text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                            <i class=" js-font-resize fa fa-edit"></i>
                                                        </a>
                                                    @endcan

                                                    @can('admin_support_departments_delete')
                                                        @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/supports/departments/'. $department->id.'/delete','btnClass' => ''])
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach

                                    </table>
                                </div>

                                <div class=" js-font-resize text-center mt-2">
                                    {{ $departments->appends(request()->input())->links() }}
                                </div>
                            </div>

                            <div id="newitem" class=" js-font-resize tab-pane ">
                                <div class=" js-font-resize row">
                                    <div class=" js-font-resize col-12 col-md-6">
                                        <form action="{{ getAdminPanelUrl() }}/supports/departments/store"
                                              method="Post">
                                            {{ csrf_field() }}

                                           @if(!empty(getGeneralSettings('content_translate')))
                                    <div class=" js-font-resize form-group">
                                        <label class=" js-font-resize input-label">{{ trans('auth.language') }}</label>
                                        <select name="locale" class=" js-font-resize form-control {{ !empty($department) ? 'js-edit-content-locale' : '' }}">
                                            @foreach($userLanguages as $lang => $language)
                                                <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                                            @endforeach
                                        </select>
                                        @error('locale')
                                        <div class=" js-font-resize invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                @else
                                    <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
                                @endif


                                            <div class=" js-font-resize form-group">
                                                <label>{{ trans('admin/main.title') }}</label>
                                                <input type="text" name="title"
                                                       class=" js-font-resize form-control  @error('title') is-invalid @enderror"
                                                       value="{{ old('title') }}"/>
                                                @error('title')
                                                <div class=" js-font-resize invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class=" js-font-resize text-right mt-4">
                                                <button class=" js-font-resize btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
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
