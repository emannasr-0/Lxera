<li data-id="{{ !empty($faq) ? $faq->id :'' }}" class=" js-font-resize accordion-row bg-white rounded-sm panel-shadow mt-20 py-15 py-lg-30 px-10 px-lg-20">
    <div class=" js-font-resize d-flex align-items-center justify-content-between " role="tab" id="faq_{{ !empty($faq) ? $faq->id :'record' }}">
        <div class=" js-font-resize d-flex align-items-center" href="#collapseFaq{{ !empty($faq) ? $faq->id :'record' }}" aria-controls="collapseFaq{{ !empty($faq) ? $faq->id :'record' }}" data-parent="#faqsAccordion" role="button" data-toggle="collapse" aria-expanded="true">
            <span class=" js-font-resize chapter-icon chapter-content-icon mr-10">
                <i data-feather="help-circle" class=" js-font-resize "></i>
            </span>

            <div class=" js-font-resize font-weight-bold text-dark-blue d-block">{{ !empty($faq) ? $faq->title : trans('webinars.add_new_faqs') }}</div>
        </div>

        <div class=" js-font-resize d-flex align-items-center">
            <i data-feather="move" class=" js-font-resize move-icon mr-10 cursor-pointer" height="20"></i>

            @if(!empty($faq))
                <a href="/panel/store/products/faqs/{{ $faq->id }}/delete" class=" js-font-resize delete-action btn btn-sm btn-transparent text-gray">
                    <i data-feather="trash-2" class=" js-font-resize mr-10 cursor-pointer" height="20"></i>
                </a>
            @endif

            <i class=" js-font-resize collapse-chevron-icon" data-feather="chevron-down" height="20" href="#collapseFaq{{ !empty($faq) ? $faq->id :'record' }}" aria-controls="collapseFaq{{ !empty($faq) ? $faq->id :'record' }}" data-parent="#faqsAccordion" role="button" data-toggle="collapse" aria-expanded="true"></i>
        </div>
    </div>

    <div id="collapseFaq{{ !empty($faq) ? $faq->id :'record' }}" aria-labelledby="faq_{{ !empty($faq) ? $faq->id :'record' }}" class=" js-font-resize  collapse @if(empty($faq)) show @endif" role="tabpanel">
        <div class=" js-font-resize panel-collapse text-gray">
            <div class=" js-font-resize js-content-form faq-form" data-action="/panel/store/products/faqs/{{ !empty($faq) ? $faq->id . '/update' : 'store' }}">
                <input type="hidden" name="ajax[{{ !empty($faq) ? $faq->id : 'new' }}][product_id]" value="{{ !empty($product) ? $product->id :'' }}">

                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 col-lg-6">
                        @if(!empty(getGeneralSettings('content_translate')))
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('auth.language') }}</label>
                                <select name="ajax[{{ !empty($faq) ? $faq->id : 'new' }}][locale]"
                                        class=" js-font-resize form-control {{ !empty($faq) ? 'js-product-content-locale' : '' }}"
                                        data-product-id="{{ !empty($product) ? $product->id : '' }}"
                                        data-id="{{ !empty($faq) ? $faq->id : '' }}"
                                        data-relation="faqs"
                                        data-fields="title,answer"
                                >
                                    @foreach(getUserLanguagesLists() as $lang => $language)
                                        <option value="{{ $lang }}" {{ (!empty($faq) and !empty($faq->locale)) ? (mb_strtolower($faq->locale) == mb_strtolower($lang) ? 'selected' : '') : ($locale == $lang ? 'selected' : '') }}>{{ $language }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="ajax[{{ !empty($faq) ? $faq->id : 'new' }}][locale]" value="{{ $defaultLocale }}">
                        @endif


                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('public.title') }}</label>
                            <input type="text" name="ajax[{{ !empty($faq) ? $faq->id : 'new' }}][title]" class=" js-font-resize js-ajax-title form-control" value="{{ !empty($faq) ? $faq->title : '' }}" placeholder="{{ trans('forms.maximum_255_characters') }}"/>
                            <div class=" js-font-resize invalid-feedback"></div>
                        </div>

                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('public.answer') }}</label>
                            <textarea name="ajax[{{ !empty($faq) ? $faq->id : 'new' }}][answer]" class=" js-font-resize js-ajax-answer form-control" rows="6">{{ !empty($faq) ? $faq->answer : '' }}</textarea>
                            <div class=" js-font-resize invalid-feedback"></div>
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize mt-30 d-flex align-items-center">
                    <button type="button" class=" js-font-resize js-save-faq btn btn-sm btn-primary">{{ trans('public.save') }}</button>

                    @if(empty($faq))
                        <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 cancel-accordion">{{ trans('public.close') }}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</li>
