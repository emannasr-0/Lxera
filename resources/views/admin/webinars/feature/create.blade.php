@extends('admin.layouts.app')

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
                <div class=" js-font-resize col-12">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-body">
                            @php
                                $pages = \App\Models\FeatureWebinar::$pages;
                            @endphp

                            <div class=" js-font-resize row">
                                <div class=" js-font-resize col-12 col-md-6">
                                    <form action="{{ getAdminPanelUrl() }}/webinars/features/{{ !empty($feature) ? $feature->id.'/update' : 'store'  }}" method="post">
                                        {{ csrf_field() }}

                                        @if(!empty(getGeneralSettings('content_translate')))
                                            <div class=" js-font-resize form-group">
                                                <label class=" js-font-resize input-label">{{ trans('auth.language') }}</label>
                                                <select name="locale" class=" js-font-resize form-control {{ !empty($feature) ? 'js-edit-content-locale' : '' }}">
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
                                            <label class=" js-font-resize input-label">{{ trans('admin/main.position') }}</label>
                                            <select name="page" class=" js-font-resize form-control">
                                                @foreach($pages as $page)
                                                    <option value="{{ $page }}" @if(!empty($feature) and $feature->page == $page) selected @endif>{{ trans('admin/main.page_'.$page) }}</option>
                                                @endforeach
                                            </select>
                                            @error('locale')
                                            <div class=" js-font-resize invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class=" js-font-resize form-group">
                                            <label class=" js-font-resize input-label d-block">{{ trans('admin/main.webinar') }}</label>
                                            <select name="webinar_id" class=" js-font-resize form-control search-webinar-select2 @error('webinar_id') is-invalid @enderror" data-placeholder="{{ trans('admin/main.search_webinar') }}">
                                                @if(!empty($feature))
                                                    <option value="{{ $feature->webinar->id }}">{{ $feature->webinar->title }}</option>
                                                @endif
                                            </select>

                                            @error('webinar_id')
                                            <div class=" js-font-resize invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class=" js-font-resize form-group">
                                            <label class=" js-font-resize input-label d-block">{{ trans('public.description') }}</label>
                                            <textarea name="description" class=" js-font-resize form-control @error('description') is-invalid @enderror" rows="6">{{ !empty($feature) ? $feature->description : '' }}</textarea>

                                            @error('description')
                                            <div class=" js-font-resize invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class=" js-font-resize form-group">
                                            <label class=" js-font-resize input-label">{{ trans('admin/main.status') }}</label>
                                            <select class=" js-font-resize custom-select" name="status">
                                                <option value="pending" {{ (!empty($feature) and $feature->status == 'pending') ? 'selected' : '' }}>{{ trans('admin/main.pending') }}</option>
                                                <option value="publish" {{ (!empty($feature) and $feature->status == 'publish') ? 'selected' : '' }}>{{ trans('admin/main.published') }}</option>
                                            </select>
                                        </div>

                                        <button type="submit" class=" js-font-resize btn btn-primary">{{ trans('admin/main.save_change') }}</button>
                                    </form>
                                </div>
                            </div>

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
                        <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">{{trans('admin/main.new_featured_hint_title_1')}}</div>
                        <div class=" js-font-resize  text-small font-600-bold mb-2">{{trans('admin/main.new_featured_hint_description_1')}}</div>
                    </div>
                </div>

                <div class=" js-font-resize col-md-6">
                    <div class=" js-font-resize media-body">
                        <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">{{trans('admin/main.new_featured_hint_title_2')}}</div>
                        <div class=" js-font-resize  text-small font-600-bold mb-2">{{trans('admin/main.new_featured_hint_description_2')}}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('scripts_bottom')

@endpush
