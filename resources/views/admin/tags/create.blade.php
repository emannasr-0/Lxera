@extends('admin.layouts.app')

@push('libraries_top')


@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{!empty($tag) ?trans('/admin/main.edit'): trans('admin/main.new') }} {{ trans('admin/main.tag') }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}/tags">{{ trans('admin/main.tags') }}</a>
                </div>
                <div
                    class=" js-font-resize breadcrumb-item">{{!empty($tag) ?trans('/admin/main.edit'): trans('admin/main.new') }}</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-6 col-lg-6">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-body">
                            <form action="{{ getAdminPanelUrl() }}/tags/{{ !empty($tag) ? $tag->id.'/update' : 'store' }}"
                                  method="Post">
                                {{ csrf_field() }}
                                <div class=" js-font-resize form-group">
                                    <label>{{ trans('/admin/main.title') }}</label>
                                    <input type="text" name="title"
                                           class=" js-font-resize form-control  @error('title') is-invalid @enderror"
                                           value="{{ !empty($tag) ? $tag->title : old('title') }}"
                                           placeholder="{{ trans('admin/main.create_field_title_placeholder') }}"/>
                                    @error('title')
                                    <div class=" js-font-resize invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class=" js-font-resize  mt-4">
                                    <button class=" js-font-resize btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
