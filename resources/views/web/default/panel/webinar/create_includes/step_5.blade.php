@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <link href="/assets/default/vendors/sortable/jquery-ui.min.css"/>
@endpush


<section class=" js-font-resize mt-50">
    <div class=" js-font-resize ">
        <h2 class=" js-font-resize section-title after-line">{{ trans('public.prerequisites') }} ({{ trans('public.optional') }})</h2>
    </div>

    <button id="webinarAddPrerequisites" data-webinar-id="{{ $webinar->id }}" type="button" class=" js-font-resize btn btn-primary btn-sm mt-15">{{ trans('public.add_prerequisites') }}</button>

    <div class=" js-font-resize row mt-10">
        <div class=" js-font-resize col-12">

            <div class=" js-font-resize accordion-content-wrapper mt-15" id="prerequisitesAccordion" role="tablist" aria-multiselectable="true">
                @if(!empty($webinar->prerequisites) and count($webinar->prerequisites))
                    <ul class=" js-font-resize draggable-lists" data-order-table="prerequisites">
                        @foreach($webinar->prerequisites as $prerequisiteInfo)
                            @include('web.default.panel.webinar.create_includes.accordions.prerequisites',['webinar' => $webinar,'prerequisite' => $prerequisiteInfo])
                        @endforeach
                    </ul>
                @else
                    @include(getTemplate() . '.includes.no-result',[
                        'file_name' => 'comment.png',
                        'title' => trans('public.prerequisites_no_result'),
                        'hint' => trans('public.prerequisites_no_result_hint'),
                    ])
                @endif
            </div>
        </div>
    </div>
</section>

<div id="newPrerequisiteForm" class=" js-font-resize d-none">
    @include('web.default.panel.webinar.create_includes.accordions.prerequisites',['webinar' => $webinar])
</div>


@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>
@endpush