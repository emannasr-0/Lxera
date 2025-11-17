@if(!empty($instructors) and !$instructors->isEmpty())
    <div class=" js-font-resize mt-20 row">

        @foreach($instructors as $instructor)
            <div class=" js-font-resize col-lg-4 mt-20">
                @include('web.default.pages.instructor_card',['instructor' => $instructor])
            </div>
        @endforeach
    </div>
@else
    @include(getTemplate() . '.includes.no-result',[
        'file_name' => 'bio.png',
        'title' => trans('update.this_organization_has_no_instructor'),
        'hint' => '',
    ])
@endif

