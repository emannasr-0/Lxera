@extends('admin.layouts.app')

@push('styles_top')

@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{ trans('admin/main.new_department') }}</div>
            </div>
        </div>


        <div class=" js-font-resize section-body">

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-8 col-lg-6">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-body">
                            <form action="{{ getAdminPanelUrl() }}/supports/departments/{{ !empty($department) ? $department->id.'/update' : 'store' }}"
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
                                           value="{{ !empty($department) ? $department->title : old('title') }}"/>
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
    </section>
@endsection

@push('scripts_bottom')

@endpush
