<div class=" js-font-resize product-show-description-tab mt-20">
    @if($product->description)
        <div class=" js-font-resize course-description">
            {!! $product->description !!}
        </div>
    @endif

    {{-- FAQ --}}
    @if(!empty($product->faqs) and $product->faqs->count() > 0)
        <div class=" js-font-resize mt-20 mt-lg-30">
            <h2 class=" js-font-resize section-title after-line">{{ trans('public.faq') }}</h2>

            <div class=" js-font-resize accordion-content-wrapper mt-15" id="accordion" role="tablist" aria-multiselectable="true">
                @foreach($product->faqs as $faq)
                    <div class=" js-font-resize accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">
                        <div class=" js-font-resize font-weight-bold font-14 text-secondary" role="tab" id="faq_{{ $faq->id }}">
                            <div href="#collapseFaq{{ $faq->id }}" aria-controls="collapseFaq{{ $faq->id }}" class=" js-font-resize d-flex align-items-center justify-content-between" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true">
                                <span>{{ clean($faq->title,'title') }}?</span>
                                <i class=" js-font-resize collapse-chevron-icon" data-feather="chevron-down" width="25" class=" js-font-resize text-gray"></i>
                            </div>
                        </div>
                        <div id="collapseFaq{{ $faq->id }}" aria-labelledby="faq_{{ $faq->id }}" class=" js-font-resize  collapse" role="tabpanel">
                            <div class=" js-font-resize panel-collapse text-gray">
                                {{ clean($faq->answer,'answer') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    {{-- ./ FAQ --}}

    @if(!empty(getStoreSettings('activate_comments')) and getStoreSettings('activate_comments'))
        {{-- product Comments --}}
        @include('web.default.includes.comments',[
                'comments' => $product->comments,
                'inputName' => 'product_id',
                'inputValue' => $product->id
            ])
        {{-- ./ product Comments --}}
    @endif
</div>
