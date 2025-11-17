@push('styles_top')
    <link href="/assets/default/vendors/sortable/jquery-ui.min.css"/>
@endpush


<section class=" js-font-resize mt-50">
    <div class=" js-font-resize ">
        <h2 class=" js-font-resize section-title after-line">{{ trans('public.faq') }} ({{ trans('public.optional') }})</h2>
    </div>

    <button id="webinarAddFAQ" data-webinar-id="{{ $webinar->id }}" type="button" class=" js-font-resize btn btn-primary btn-sm mt-15">{{ trans('public.add_faq') }}</button>

    <div class=" js-font-resize row mt-10">
        <div class=" js-font-resize col-12">

            <div class=" js-font-resize accordion-content-wrapper mt-15" id="faqsAccordion" role="tablist" aria-multiselectable="true">
                @if(!empty($webinar->faqs) and count($webinar->faqs))
                    <ul class=" js-font-resize draggable-lists" data-order-table="faqs">
                        @foreach($webinar->faqs as $faqInfo)
                            @include('web.default.panel.webinar.create_includes.accordions.faq',['webinar' => $webinar,'faq' => $faqInfo])
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
    @include('web.default.panel.webinar.create_includes.accordions.faq',['webinar' => $webinar])
</div>

@foreach(\App\Models\WebinarExtraDescription::$types as $webinarExtraDescriptionType)
    <section class=" js-font-resize mt-50">
        <div class=" js-font-resize ">
            <h2 class=" js-font-resize section-title after-line">{{ trans('update.'.$webinarExtraDescriptionType) }} ({{ trans('public.optional') }})</h2>
        </div>

        <button id="add_new_{{ $webinarExtraDescriptionType }}" data-webinar-id="{{ $webinar->id }}" type="button" class=" js-font-resize btn btn-primary btn-sm mt-15">{{ trans('update.add_'.$webinarExtraDescriptionType) }}</button>

        <div class=" js-font-resize row mt-10">
            <div class=" js-font-resize col-12">

                @php
                    $webinarExtraDescriptionValues = $webinar->webinarExtraDescription->where('type',$webinarExtraDescriptionType);
                @endphp

                <div class=" js-font-resize accordion-content-wrapper mt-15" id="{{ $webinarExtraDescriptionType }}_accordion" role="tablist" aria-multiselectable="true">
                    @if(!empty($webinarExtraDescriptionValues) and count($webinarExtraDescriptionValues))
                        <ul class=" js-font-resize draggable-content-lists draggable-lists-{{ $webinarExtraDescriptionType }}" data-drag-class=" js-font-resize draggable-lists-{{ $webinarExtraDescriptionType }}" data-order-table="webinar_extra_descriptions_{{ $webinarExtraDescriptionType }}">
                            @foreach($webinarExtraDescriptionValues as $learningMaterialInfo)
                                @include('web.default.panel.webinar.create_includes.accordions.extra_description',
                                    [
                                        'webinar' => $webinar,
                                        'extraDescription' => $learningMaterialInfo,
                                        'extraDescriptionType' => $webinarExtraDescriptionType,
                                        'extraDescriptionParentAccordion' => $webinarExtraDescriptionType.'_accordion',
                                    ]
                                )
                            @endforeach
                        </ul>
                    @else
                        @include(getTemplate() . '.includes.no-result',[
                            'file_name' => 'faq.png',
                            'title' => trans("update.{$webinarExtraDescriptionType}_no_result"),
                            'hint' => trans("update.{$webinarExtraDescriptionType}_no_result_hint"),
                        ])
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div id="new_{{ $webinarExtraDescriptionType }}_html" class=" js-font-resize d-none">
        @include('web.default.panel.webinar.create_includes.accordions.extra_description',
            [
                'webinar' => $webinar,
                'extraDescriptionType' => $webinarExtraDescriptionType,
                'extraDescriptionParentAccordion' => $webinarExtraDescriptionType.'_accordion',
            ]
        )
    </div>
@endforeach


@push('scripts_bottom')
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>
@endpush
