@extends('admin.layouts.app')

@push('styles_top')

@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}/store/specifications">{{ trans('update.specifications') }}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{ $pageTitle  }}</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-12">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-body">
                            <form action="{{ getAdminPanelUrl() }}/store/specifications/{{ !empty($specification) ? $specification->id.'/update' : 'store' }}"
                                  method="Post">
                                {{ csrf_field() }}

                                <div class=" js-font-resize row">
                                    <div class=" js-font-resize col-12 col-md-6 col-lg-6">
                                        @if(!empty(getGeneralSettings('content_translate')))
                                            <div class=" js-font-resize form-group">
                                                <label class=" js-font-resize input-label">{{ trans('auth.language') }}</label>
                                                <select name="locale" class=" js-font-resize form-control {{ !empty($specification) ? 'js-edit-content-locale' : '' }}">
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
                                            <label>{{ trans('/admin/main.title') }}</label>
                                            <input type="text" name="title"
                                                   class=" js-font-resize form-control  @error('title') is-invalid @enderror"
                                                   value="{{ !empty($specification) ? $specification->title : old('title') }}"
                                                   placeholder="{{ trans('admin/main.choose_title') }}"/>
                                            @error('title')
                                            <div class=" js-font-resize invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class=" js-font-resize form-group mb-0">
                                    <label class=" js-font-resize ">{{ trans('admin/main.categories') }}</label>
                                    @error('category')
                                    <div class=" js-font-resize invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class=" js-font-resize row">
                                    @foreach($categories as $category)
                                        <div class=" js-font-resize col-12 col-md-4 col-lg-3 mt-3">
                                            @if(!empty($category->subCategories) and count($category->subCategories))
                                                <div class=" js-font-resize form-group mb-1">
                                                    <label class=" js-font-resize ">{{ $category->title }}</label>
                                                </div>

                                                @foreach($category->subCategories as $subCategory)
                                                    <div class=" js-font-resize col-12 col-md-4 col-lg-3">
                                                        <div class=" js-font-resize form-group mb-0">
                                                            <div class=" js-font-resize custom-control custom-checkbox">
                                                                <input id="category{{ $subCategory->id }}" value="{{ $subCategory->id }}" type="checkbox" name="category[]"
                                                                       class=" js-font-resize custom-control-input" {{ (!empty($selectedCategories) and in_array($subCategory->id,$selectedCategories)) ? 'checked' : '' }}>
                                                                <label class=" js-font-resize custom-control-label"
                                                                       for="category{{ $subCategory->id }}">{{ $subCategory->title }}</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach

                                            @else
                                                <div class=" js-font-resize form-group">
                                                    <div class=" js-font-resize custom-control custom-checkbox">
                                                        <input id="category{{ $category->id }}" value="{{ $category->id }}" type="checkbox" name="category[]"
                                                               class=" js-font-resize custom-control-input" {{ (!empty($selectedCategories) and in_array($category->id,$selectedCategories)) ? 'checked' : '' }}>
                                                        <label class=" js-font-resize custom-control-label"
                                                               for="category{{ $category->id }}">{{ $category->title }}</label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class=" js-font-resize form-group mt-4">
                                    <label class=" js-font-resize input-label">{{ trans('update.input_type') }}:</label>

                                    <div class=" js-font-resize d-flex align-items-center" id="inputTypes">
                                        <div class=" js-font-resize custom-control mr-2 custom-radio">
                                            <input type="radio" name="input_type" value="textarea" {{ (!empty($specification->input_type) and $specification->input_type == 'textarea') ? 'checked="checked"' : ''}} id="textarea" class=" js-font-resize custom-control-input">
                                            <label class=" js-font-resize custom-control-label cursor-pointer" for="textarea">{{ trans('update.textarea') }}</label>
                                        </div>

                                        <div class=" js-font-resize custom-control mr-2 custom-radio ml-15">
                                            <input type="radio" name="input_type" value="multi_value" id="multi_value" {{ (!empty($specification->input_type) and $specification->input_type == 'multi_value') ? 'checked="checked"' : ''}} class=" js-font-resize custom-control-input">
                                            <label class=" js-font-resize custom-control-label cursor-pointer" for="multi_value">{{ trans('update.multi_value') }}</label>
                                        </div>
                                    </div>

                                    @error('input_type')
                                    <div class=" js-font-resize invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class=" js-font-resize row">
                                    <div class=" js-font-resize col-12 col-md-6 col-lg-6">
                                        <div id="multiValues" class=" js-font-resize ml-0 {{ (!empty($multiValues) and !$multiValues->isEmpty()) ? '' : ' d-none' }}">
                                            <div class=" js-font-resize d-flex align-items-center justify-content-between mb-4">
                                                <strong class=" js-font-resize d-block">{{ trans('update.multi_value') }}</strong>

                                                <button type="button" class=" js-font-resize btn btn-success add-btn"><i class=" js-font-resize fa fa-plus"></i> Add</button>
                                            </div>

                                            <div class=" js-font-resize multi-values-card">

                                                @if((!empty($multiValues) and !$multiValues->isEmpty()))
                                                    @foreach($multiValues as $key => $multiValue)
                                                        <div class=" js-font-resize form-group">

                                                            <div class=" js-font-resize input-group">
                                                                <input type="text" name="multi_values[{{ $multiValue->id }}][title]"
                                                                       class=" js-font-resize form-control w-auto flex-grow-1"
                                                                       value="{{ !empty($multiValue->translate($selectedLocale)) ? $multiValue->translate($selectedLocale)->title : '' }}"
                                                                       placeholder="{{ trans('admin/main.choose_title') }}"/>

                                                                <div class=" js-font-resize input-group-append">
                                                                    <button type="button" class=" js-font-resize btn remove-btn btn-danger"><i class=" js-font-resize fa fa-times"></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
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

    <div class=" js-font-resize form-group main-row d-none">
        <div class=" js-font-resize input-group">
            <input type="text" name="multi_values[record][title]"
                   class=" js-font-resize form-control w-auto flex-grow-1"
                   placeholder="{{ trans('admin/main.choose_title') }}"/>

            <div class=" js-font-resize input-group-append">
                <button type="button" class=" js-font-resize btn remove-btn btn-danger"><i class=" js-font-resize fa fa-times"></i></button>
            </div>
        </div>
    </div>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/admin/store/specification.min.js"></script>
@endpush
