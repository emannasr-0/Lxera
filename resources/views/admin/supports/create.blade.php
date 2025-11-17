@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ trans('admin/main.new_ticket') }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{ trans('admin/main.supports') }}</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-body">
                            <div class=" js-font-resize row">
                                <div class=" js-font-resize col-12 col-md-6">
                                    <form action="{{ getAdminPanelUrl() }}/supports/{{ !empty($support) ? $support->id.'/update' : 'store' }}" method="Post">
                                        {{ csrf_field() }}

                                        <div class=" js-font-resize form-group">
                                            <label>{{ trans('admin/main.title') }}</label>
                                            <input type="text" name="title" class=" js-font-resize form-control  @error('title') is-invalid @enderror"
                                                   value="{{ !empty($support) ? $support->title : old('title') }}"/>
                                            @error('title')
                                            <div class=" js-font-resize invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class=" js-font-resize form-group">
                                            <label>{{ trans('admin/main.department') }}</label>
                                            <select name="department_id" class=" js-font-resize form-control  @error('department_id') is-invalid @enderror">
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->id }}" @if(!empty($support) and $support->department_id == $department->id) selected @endif>{{ $department->title }}</option>
                                                @endforeach
                                            </select>
                                            @error('department_id')
                                            <div class=" js-font-resize invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class=" js-font-resize form-group">
                                            <label class=" js-font-resize input-label d-block">{{ trans('admin/main.users') }}</label>
                                            <select name="user_id" class=" js-font-resize form-control search-user-select2"
                                                    data-search-option="for_user_group"
                                                    data-placeholder="{{ trans('public.search_user') }}">
                                                @if(!empty($toUser))
                                                    <option value="{{ $toUser->id }}">{{ $toUser->full_name }}</option>
                                                @endif
                                            </select>
                                        </div>


                                        <div class=" js-font-resize form-group mt-15">
                                            <label class=" js-font-resize input-label">{{ trans('admin/main.description') }}</label>
                                            <textarea name="message" rows="6" class=" js-font-resize form-control @error('message')  is-invalid @enderror">{!! !empty($support) ? $support->message : old('message')  !!}</textarea>
                                            @error('message')
                                            <div class=" js-font-resize invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class=" js-font-resize row align-items-center">
                                            <div class=" js-font-resize col-12 col-md-8">
                                                <div class=" js-font-resize form-group mt-15">
                                                    <label class=" js-font-resize input-label">{{ trans('admin/main.attach') }}</label>
                                                    <div class=" js-font-resize input-group">
                                                        <div class=" js-font-resize input-group-prepend">
                                                            <button type="button" class=" js-font-resize input-group-text admin-file-manager" data-input="attach" data-preview="holder">
                                                                Browse
                                                            </button>
                                                        </div>
                                                        <input type="text" name="attach" id="attach" value="{{ old('image_cover') }}" class=" js-font-resize form-control"/>
                                                        <div class=" js-font-resize input-group-append">
                                                            <button type="button" class=" js-font-resize input-group-text admin-file-view" data-input="attach">
                                                                <i class=" js-font-resize fa fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class=" js-font-resize col-12 col-md-4 mt-2 mt-md-0">
                                                <button class=" js-font-resize btn btn-primary w-100">{{ trans('admin/main.send') }}</button>
                                            </div>
                                        </div>

                                    </form>
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
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>

@endpush
