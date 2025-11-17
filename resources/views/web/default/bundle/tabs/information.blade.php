
{{-- Installments --}}
@if(!empty($installments) and count($installments) and getInstallmentsSettings('installment_plans_position') == 'top_of_page')
    @foreach($installments as $installmentRow)
        @include('web.default.installment.card',['installment' => $installmentRow, 'itemPrice' => $bundle->getPrice(), 'itemId' => $bundle->id, 'itemType' => 'bundles'])
    @endforeach
@endif

{{--course description--}}
@if($bundle->description)
    <div class=" js-font-resize mt-20">
        <h2 class=" js-font-resize section-title after-line">{{ trans('update.bundle_description') }}</h2>
        <div class=" js-font-resize mt-15 course-description">
            {!! clean($bundle->description) !!}
        </div>
    </div>
@endif
{{-- ./ course description--}}


{{-- course FAQ --}}
@if(!empty($bundle->faqs) and $bundle->faqs->count() > 0)
    <div class=" js-font-resize mt-20">
        <h2 class=" js-font-resize section-title after-line">{{ trans('public.faq') }}</h2>

        <div class=" js-font-resize accordion-content-wrapper mt-15" id="accordion" role="tablist" aria-multiselectable="true">
            @foreach($bundle->faqs as $faq)
                <div class=" js-font-resize accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">
                    <div class=" js-font-resize font-weight-bold font-14 text-secondary" role="tab" id="faq_{{ $faq->id }}">
                        <div href="#collapseFaq{{ $faq->id }}" aria-controls="collapseFaq{{ $faq->id }}" class=" js-font-resize d-flex align-items-center justify-content-between" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true">
                            <span>{{ clean($faq->title,'title') }}</span>
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
{{-- ./ course FAQ --}}


{{-- Installments --}}
@if(!empty($installments) and count($installments) and getInstallmentsSettings('installment_plans_position') == 'bottom_of_page')
    @foreach($installments as $installmentRow)
        @include('web.default.installment.card',['installment' => $installmentRow, 'itemPrice' => $bundle->getPrice(), 'itemId' => $bundle->id, 'itemType' => 'bundles'])
    @endforeach
@endif


{{-- course Comments --}}
@include('web.default.includes.comments',[
        'comments' => $bundle->comments,
        'inputName' => 'bundle_id',
        'inputValue' => $bundle->id
    ])
{{-- ./ course Comments --}}
