@push('styles_top')
    <link href="/assets/default/vendors/sortable/jquery-ui.min.css"/>
@endpush


<section class=" js-font-resize mt-50">
    <div class=" js-font-resize ">
        <h2 class=" js-font-resize section-title after-line">{{ trans('public.faq') }} ({{ trans('public.optional') }})</h2>
    </div>

    <button id="upcomingCourseAddFAQ" type="button" class=" js-font-resize btn btn-primary btn-sm mt-15">{{ trans('public.add_faq') }}</button>

    <div class=" js-font-resize row mt-10">
        <div class=" js-font-resize col-12">

            <div class=" js-font-resize accordion-content-wrapper mt-15" id="faqsAccordion" role="tablist" aria-multiselectable="true">
                @if(!empty($upcomingCourse->faqs) and count($upcomingCourse->faqs))
                    <ul class=" js-font-resize draggable-lists draggable-content-lists js-draggable-faq-lists" data-order-table="faqs" data-drag-class=" js-font-resize js-draggable-faq-lists">
                        @foreach($upcomingCourse->faqs as $faqInfo)
                            @include('web.default.panel.upcoming_courses.create_includes.accordions.faq',['upcomingCourse' => $upcomingCourse, 'faq' => $faqInfo])
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
    @include('web.default.panel.upcoming_courses.create_includes.accordions.faq',['upcomingCourse' => $upcomingCourse])
</div>

@foreach(\App\Models\WebinarExtraDescription::$types as $upcomingCourseExtraDescriptionType)
    <section class=" js-font-resize mt-50">
        <div class=" js-font-resize ">
            <h2 class=" js-font-resize section-title after-line">{{ trans('update.'.$upcomingCourseExtraDescriptionType) }} ({{ trans('public.optional') }})</h2>
        </div>

        <button id="add_new_{{ $upcomingCourseExtraDescriptionType }}" data-webinar-id="{{ $upcomingCourse->id }}" type="button" class=" js-font-resize btn btn-primary btn-sm mt-15">{{ trans('update.add_'.$upcomingCourseExtraDescriptionType) }}</button>

        <div class=" js-font-resize row mt-10">
            <div class=" js-font-resize col-12">

                @php
                    $upcomingCourseExtraDescriptionValues = $upcomingCourse->extraDescriptions->where('type',$upcomingCourseExtraDescriptionType);
                @endphp

                <div class=" js-font-resize accordion-content-wrapper mt-15" id="{{ $upcomingCourseExtraDescriptionType }}_accordion" role="tablist" aria-multiselectable="true">
                    @if(!empty($upcomingCourseExtraDescriptionValues) and count($upcomingCourseExtraDescriptionValues))
                        <ul class=" js-font-resize draggable-content-lists draggable-lists-{{ $upcomingCourseExtraDescriptionType }}" data-drag-class=" js-font-resize draggable-lists-{{ $upcomingCourseExtraDescriptionType }}" data-order-table="webinar_extra_descriptions_{{ $upcomingCourseExtraDescriptionType }}">
                            @foreach($upcomingCourseExtraDescriptionValues as $learningMaterialInfo)
                                @include('web.default.panel.upcoming_courses.create_includes.accordions.extra_description',
                                    [
                                        'upcomingCourse' => $upcomingCourse,
                                        'extraDescription' => $learningMaterialInfo,
                                        'extraDescriptionType' => $upcomingCourseExtraDescriptionType,
                                        'extraDescriptionParentAccordion' => $upcomingCourseExtraDescriptionType.'_accordion',
                                    ]
                                )
                            @endforeach
                        </ul>
                    @else
                        @include(getTemplate() . '.includes.no-result',[
                            'file_name' => 'faq.png',
                            'title' => trans("update.{$upcomingCourseExtraDescriptionType}_no_result"),
                            'hint' => trans("update.{$upcomingCourseExtraDescriptionType}_no_result_hint"),
                        ])
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div id="new_{{ $upcomingCourseExtraDescriptionType }}_html" class=" js-font-resize d-none">
        @include('web.default.panel.upcoming_courses.create_includes.accordions.extra_description',
            [
                'upcomingCourse' => $upcomingCourse,
                'extraDescriptionType' => $upcomingCourseExtraDescriptionType,
                'extraDescriptionParentAccordion' => $upcomingCourseExtraDescriptionType.'_accordion',
            ]
        )
    </div>
@endforeach


@push('scripts_bottom')
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>
@endpush
