@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

<div class=" js-font-resize row">
    <div class=" js-font-resize col-12 col-md-4 mt-15">

        @if(!empty(getGeneralSettings('content_translate')))
            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('auth.language') }}</label>
                <select name="locale" class=" js-font-resize custom-select {{ !empty($product) ? 'js-edit-content-locale' : '' }}">
                    @foreach(getUserLanguagesLists() as $lang => $language)
                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }} {{ (!empty($definedLanguage) and is_array($definedLanguage) and in_array(mb_strtolower($lang), $definedLanguage)) ? '('. trans('public.content_defined') .')' : '' }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
        @endif


        <div class=" js-font-resize form-group mt-15 ">
            <label class=" js-font-resize input-label d-block">{{ trans('public.type') }}</label>

            <select name="type" class=" js-font-resize custom-select @error('type')  is-invalid @enderror">
                @if(!empty(getStoreSettings('possibility_create_physical_product')) and getStoreSettings('possibility_create_physical_product'))
                    <option value="physical" @if(!empty($product) and $product->isPhysical()) selected @endif>{{ trans('update.physical') }}</option>
                @endif

                @if(!empty(getStoreSettings('possibility_create_virtual_product')) and getStoreSettings('possibility_create_virtual_product'))
                    <option value="virtual" @if(!empty($product) and $product->isVirtual()) selected @endif>{{ trans('update.virtual') }}</option>
                @endif
            </select>

            @error('type')
            <div class=" js-font-resize invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>


        <div class=" js-font-resize form-group mt-15">
            <label class=" js-font-resize input-label">{{ trans('public.title') }}</label>
            <input type="text" name="title" value="{{ (!empty($product) and !empty($product->translate($locale))) ? $product->translate($locale)->title : old('title') }}" class=" js-font-resize form-control @error('title')  is-invalid @enderror" placeholder=""/>
            @error('title')
            <div class=" js-font-resize invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div class=" js-font-resize form-group mt-15">
            <label class=" js-font-resize input-label">{{ trans('public.seo_description') }}</label>
            <input type="text" name="seo_description" value="{{ (!empty($product) and !empty($product->translate($locale))) ? $product->translate($locale)->seo_description : old('seo_description') }}" class=" js-font-resize form-control @error('seo_description')  is-invalid @enderror " placeholder="{{ trans('forms.50_160_characters_preferred') }}"/>
            @error('seo_description')
            <div class=" js-font-resize invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div class=" js-font-resize form-group mt-15">
            <label class=" js-font-resize input-label">{{ trans('public.summary') }}</label>
            <textarea name="summary" rows="6" class=" js-font-resize form-control @error('summary')  is-invalid @enderror " placeholder="{{ trans('update.product_summary_placeholder') }}">{{ (!empty($product) and !empty($product->translate($locale))) ? $product->translate($locale)->summary : old('summary') }}</textarea>
            @error('summary')
            <div class=" js-font-resize invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

    </div>
</div>

<div class=" js-font-resize row">
    <div class=" js-font-resize col-12">
        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
            <textarea id="summernote" name="description" class=" js-font-resize form-control @error('description')  is-invalid @enderror" placeholder="{{ trans('forms.webinar_description_placeholder') }}">{!! (!empty($product) and !empty($product->translate($locale))) ? $product->translate($locale)->description : old('description')  !!}</textarea>
            @error('description')
            <div class=" js-font-resize invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
    </div>
</div>


<div class=" js-font-resize row">
    <div class=" js-font-resize col-6">

        <div class=" js-font-resize form-group mt-30 d-flex align-items-center">
            <label class=" js-font-resize cursor-pointer mb-0 input-label" for="orderingSwitch">{{ trans('update.enable_ordering') }}</label>
            <div class=" js-font-resize ml-30 custom-control custom-switch">
                <input type="checkbox" name="ordering" class=" js-font-resize custom-control-input" id="orderingSwitch" {{ (!empty($product) and $product->ordering) ? 'checked' :  '' }}>
                <label class=" js-font-resize custom-control-label" for="orderingSwitch"></label>
            </div>
        </div>

        <p class=" js-font-resize text-gray font-12">{{ trans('update.create_product_enable_ordering_hint') }}</p>
    </div>
</div>


@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>

    @push('scripts_bottom')
        <script>
            var videoDemoPathPlaceHolderBySource = {
                upload: '{{ trans('update.file_source_upload_placeholder') }}',
                youtube: '{{ trans('update.file_source_youtube_placeholder') }}',
                vimeo: '{{ trans('update.file_source_vimeo_placeholder') }}',
                external_link: '{{ trans('update.file_source_external_link_placeholder') }}',
            }
        </script>
    @endpush
@endpush
