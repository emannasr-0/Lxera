@push('styles_top')
    <link href="/assets/default/vendors/sortable/jquery-ui.min.css"/>
@endpush


<section class=" js-font-resize mt-50">
    <div class=" js-font-resize ">
        <h2 class=" js-font-resize section-title after-line">{{ trans('public.faq') }} ({{ trans('public.optional') }})</h2>
    </div>

    <button id="webinarAddFAQ" data-bundle-id="{{ $bundle->id }}" type="button" class=" js-font-resize btn btn-primary btn-sm mt-15">{{ trans('public.add_faq') }}</button>

    <div class=" js-font-resize row mt-10">
        <div class=" js-font-resize col-12">

            <div class=" js-font-resize accordion-content-wrapper mt-15" id="faqsAccordion" role="tablist" aria-multiselectable="true">
                @if(!empty($bundle->faqs) and count($bundle->faqs))
                    <ul class=" js-font-resize draggable-lists" data-order-table="faqs">
                        @foreach($bundle->faqs as $faqInfo)
                            @include('web.default.panel.bundle.create_includes.accordions.faq',['bundle' => $bundle,'faq' => $faqInfo])
                        @endforeach
                    </ul>
                @else
                    @include(getTemplate() . '.includes.no-result',[
                        'file_name' => 'faq.png',
                        'title' => trans('public.faq_no_result'),
                        'hint' => trans('public.faq_no_result_hint'),
                    ])
                @endif
            </div>
        </div>
    </div>
</section>

<div id="newFaqForm" class=" js-font-resize d-none">
    @include('web.default.panel.bundle.create_includes.accordions.faq',['bundle' => $bundle])
</div>


@push('scripts_bottom')
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>
@endpush
