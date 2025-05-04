@extends('admin.layouts.app')

@push('styles_top')
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.new_template') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.new_template') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-body">


                    <hr class="my-4">

                    <form method="post" action="" id="templateForm" class="form-horizontal form-bordered">
                        {{ csrf_field() }}

                        <div class="row">
                            <div class="col-lg-6">
                                @if (!empty(getGeneralSettings('content_translate')))
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('auth.language') }}</label>
                                        <select name="locale"
                                            class="form-control {{ !empty($template) ? 'js-edit-content-locale' : '' }}">
                                            @foreach ($userLanguages as $lang => $language)
                                                <option value="{{ $lang }}"
                                                    @if (mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('locale')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                @else
                                    <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
                                @endif

                                <div class="form-group">
                                    <label class="control-label" for="inputDefault">{!! trans('public.type') !!}</label>
                                    <select id="typeSelect" name="type"
                                        class="form-control @error('type') is-invalid @enderror">
                                        <option value="">{{ trans('admin/main.select_type') }}</option>
                                        <option value="quiz"
                                            {{ (!empty($template) and $template->type == 'quiz') ? 'selected' : '' }}>
                                            {{ trans('update.quiz_related') }}</option>
                                        <option value="course"
                                            {{ (!empty($template) and $template->type == 'course') ? 'selected' : '' }}>
                                            {{ trans('update.course_completion') }}</option>
                                        <option value="bundle"
                                            {{ (!empty($template) and $template->type == 'bundle') ? 'selected' : '' }}>
                                            إتمام حزمة</option>
                                            <option value="attendance"
                                            {{ (!empty($template) and $template->type == 'attendance') ? 'selected' : '' }}>
                                               شهاده حضور</option>   
                                    </select>
                                    <div class="invalid-feedback">
                                        @error('type')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>

                                <!-- Form Group for Bundle Selection -->
                                <div class="form-group" id="bundleDropdown" style="display: none;">
                                    <label class="control-label">دبلومات الشهادة</label>
                                    <select name="bundles[]" id="bundles" class="form-control" multiple>
                                        @foreach ($bundles as $bundle)
                                            <option value="{{ $bundle->id }}"
                                                {{ isset($template) && $template->bundle->contains($bundle->id) ? 'selected' : '' }}>
                                                {{ $bundle->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        @error('bundles')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>

                                <!-- Form Group for Course Selection (Similar to Bundle Dropdown) -->
                                 <div class="form-group" id="courseDropdown" style="display: none;">
                                    <label class="control-label">اختيار الدورة</label>


                                    <select name="webinars[]" id="courses" class="form-control" multiple>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ (isset($template) && $template->webinar->contains($course->id)) ? 'selected' : '' }}>

                                                {{ $course->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">@error('courses') {{ $message }} @enderror</div>

                                </div> 
                               
             

                                <div class="form-group">
                                    <label class="control-label" for="inputDefault">{!! trans('public.title') !!}</label>
                                    <input type="text" name="title"
                                        class="form-control @error('title') is-invalid @enderror"
                                        value="{{ !empty($template) ? $template->title : old('title') }}">
                                    <div class="invalid-feedback">
                                        @error('title')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="inputDefault">{!! trans('public.price') !!}</label>
                                    <input type="text" name="price" class="form-control"
                                        value="{{ !empty($template) ? $template->price : old('price') }}">
                                </div>


                                <div class="form-group">
                                    <label class="control-label" for="student_name">اسم الطالب</label>
                                    <input type="text" name="student_name" class="form-control"
                                        value="{{ old('student_name', !empty($template) ? $template->student_name : '') }}">

                                    <label class="control-label" for="position_x_student">{!! trans('admin/main.position_x') !!}</label>
                                    <input type="text" name="position_x_student"
                                        class="form-control @error('position_x_student') is-invalid @enderror"
                                        value="{{ old('position_x_student', !empty($template) ? $template->position_x_student : '835') }}">
                                    <div class="invalid-feedback">
                                        @error('position_x_student')
                                            {{ $message }}
                                        @enderror
                                    </div>

                                    <label class="control-label" for="position_y_student">{!! trans('admin/main.position_y') !!}</label>
                                    <input type="text" name="position_y_student"
                                        class="form-control @error('position_y_student') is-invalid @enderror"
                                        value="{{ old('position_y_student', !empty($template) ? $template->position_y_student : '1250') }}">
                                    <div class="invalid-feedback">
                                        @error('position_y_student')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                    <label class="control-label" for="font_size_student">{!! trans('admin/main.font_size') !!}</label>
                                    <input type="text" name="font_size_student"
                                        class="form-control @error('font_size_student') is-invalid @enderror"
                                        value="{{ old('font_size_student', !empty($template) ? $template->font_size_student : '40') }}">
                                    <div class="invalid-feedback">
                                        @error('font_size_student')
                                            {{ $message }}
                                        @enderror
                                    </div>


                                </div>


                                <div class="form-group">
                                    <label class="control-label" for="text">النص</label>
                                    <input type="text" name="text"
                                        class="form-control @error('text') is-invalid @enderror"
                                        value="{{ old('text', !empty($template) ? $template->text : 'HAS BEEN AWARDED AN ONLINE DIPLOMA DEGREE OF') }}">
                                    <div class="invalid-feedback">
                                        @error('text')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                    <label class="control-label" for="position_x_text">{!! trans('admin/main.position_x') !!}</label>
                                    <input type="text" name="position_x_text"
                                        class="form-control @error('position_x_text') is-invalid @enderror"
                                        value="{{ old('position_x_text', !empty($template) ? $template->position_x_text : '835') }}">
                                    <div class="invalid-feedback">
                                        @error('position_x_text')
                                            {{ $message }}
                                        @enderror
                                    </div>

                                    <label class="control-label" for="position_y_text">{!! trans('admin/main.position_y') !!}</label>
                                    <input type="text" name="position_y_text"
                                        class="form-control @error('position_y_text') is-invalid @enderror"
                                        value="{{ old('position_y_text', !empty($template) ? $template->position_y_text : '1400') }}">
                                    <div class="invalid-feedback">
                                        @error('position_y_text')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                    <label class="control-label" for="font_size_text">{!! trans('admin/main.font_size') !!}</label>
                                    <input type="text" name="font_size_text"
                                        class="form-control @error('font_size_text') is-invalid @enderror"
                                        value="{{ old('font_size_text', !empty($template) ? $template->font_size_text : '30') }}">
                                    <div class="invalid-feedback">
                                        @error('font_size_text')
                                            {{ $message }}
                                        @enderror
                                    </div>

                                </div>



                                <div class="form-group">
                                    <label class="control-label" for="course_name">اسم الكورس</label>
                                    <input type="text" name="course_name" class="form-control"
                                        value="{{ old('course_name', !empty($template) ? $template->course_name : '') }}">

                                    <label class="control-label" for="position_x_course">{!! trans('admin/main.position_x') !!}</label>
                                    <input type="text" name="position_x_course"
                                        class="form-control @error('position_x_course') is-invalid @enderror"
                                        value="{{ old('position_x_course', !empty($template) ? $template->position_x_course : '835') }}">
                                    <div class="invalid-feedback">
                                        @error('position_x_course')
                                            {{ $message }}
                                        @enderror
                                    </div>

                                    <label class="control-label" for="position_y_course">{!! trans('admin/main.position_y') !!}</label>
                                    <input type="text" name="position_y_course"
                                        class="form-control @error('position_y_course') is-invalid @enderror"
                                        value="{{ old('position_y_course', !empty($template) ? $template->position_y_course : ' 1450') }}">
                                    <div class="invalid-feedback">
                                        @error('position_y_course')
                                            {{ $message }}
                                        @enderror
                                    </div>

                                    <label class="control-label" for="font_size_course">{!! trans('admin/main.font_size') !!}</label>
                                    <input type="text" name="font_size_course"
                                        class="form-control @error('font_size_course') is-invalid @enderror"
                                        value="{{ old('font_size_course', !empty($template) ? $template->font_size_course : '40') }}">
                                    <div class="invalid-feedback">
                                        @error('font_size_course')
                                            {{ $message }}
                                        @enderror
                                    </div>

                                </div>


                                <div class="form-group">
                                    <label class="control-label" for="graduation_date">تاريخ التخرج</label>
                                    <input type="date" id="graduation_date" name="graduation_date"
                                        class="form-control @error('graduation_date') is-invalid @enderror"
                                        value="{{ old('date', !empty($template) ? $template->graduation_date : '') }}">
                                    @error('graduation_date')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    <label class="control-label" for="position_x_date">{!! trans('admin/main.position_x') !!}</label>
                                    <input type="text" name="position_x_date"
                                        class="form-control @error('position_x_date') is-invalid @enderror"
                                        value="{{ old('position_x_date', !empty($template) ? $template->position_x_date : '835') }}">
                                    <div class="invalid-feedback">
                                        @error('position_x_date')
                                            {{ $message }}
                                        @enderror
                                    </div>

                                    <label class="control-label" for="position_y_date">{!! trans('admin/main.position_y') !!}</label>
                                    <input type="text" name="position_y_date"
                                        class="form-control @error('position_y_date') is-invalid @enderror"
                                        value="{{ old('position_y_date', !empty($template) ? $template->position_y_date : '1510') }}">
                                    <div class="invalid-feedback">
                                        @error('position_y_date')
                                            {{ $message }}
                                        @enderror
                                    </div>

                                    <label class="control-label" for="font_size_date">{!! trans('admin/main.font_size') !!}</label>
                                    <input type="text" name="font_size_date"
                                        class="form-control @error('font_size_date') is-invalid @enderror"
                                        value="{{ old('font_size_date', !empty($template) ? $template->font_size_date : '40') }}">
                                    <div class="invalid-feedback">
                                        @error('font_size_date')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="graduation_date">سكشن ال GPA</label>
                                    <input type="text" id="graduation_date" name="gpa"
                                        class="form-control @error('gpa') is-invalid @enderror"
                                        value="{{ old('gpa', !empty($template) ? $template->gpa : '') }}">
                                    @error('gpa')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    <label class="control-label" for="position_x_gpa">{!! trans('admin/main.position_x') !!}</label>
                                    <input type="text" name="position_x_gpa"
                                        class="form-control @error('position_x_gpa') is-invalid @enderror"
                                        value="{{ old('position_x_gpa', !empty($template) ? $template->position_x_gpa : '835') }}">
                                    <div class="invalid-feedback">
                                        @error('position_x_gpa')
                                            {{ $message }}
                                        @enderror
                                    </div>

                                    <label class="control-label" for="position_y_gpa">{!! trans('admin/main.position_y') !!}</label>
                                    <input type="text" name="position_y_gpa"
                                        class="form-control @error('position_y_gpa') is-invalid @enderror"
                                        value="{{ old('position_y_gpa', !empty($template) ? $template->position_y_gpa : '1510') }}">
                                    <div class="invalid-feedback">
                                        @error('position_y_gpa')
                                            {{ $message }}
                                        @enderror
                                    </div>

                                    <label class="control-label" for="font_size_gpa">{!! trans('admin/main.font_size') !!}</label>
                                    <input type="text" name="font_size_gpa"
                                        class="form-control @error('font_size_gpa') is-invalid @enderror"
                                        value="{{ old('font_size_gpa', !empty($template) ? $template->font_size_gpa : '40') }}">
                                    <div class="invalid-feedback">
                                        @error('font_size_gpa')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>




                                <div class="form-group">
                                    <label class="control-label" for="graduation_date">كود الشهاده</label>

                                    <label class="control-label"
                                        for="position_x_certificate_code">{!! trans('admin/main.position_x') !!}</label>
                                    <input type="text" name="position_x_certificate_code"
                                        class="form-control @error('position_x_certificate_code') is-invalid @enderror"
                                        value="{{ old('position_x_certificate_code', !empty($template) ? $template->position_x_certificate_code : '600') }}">
                                    <div class="invalid-feedback">
                                        @error('position_x_certificate_code')
                                            {{ $message }}
                                        @enderror
                                    </div>

                                    <label class="control-label"
                                        for="position_y_certificate_code">{!! trans('admin/main.position_y') !!}</label>
                                    <input type="text" name="position_y_certificate_code"
                                        class="form-control @error('position_y_certificate_code') is-invalid @enderror"
                                        value="{{ old('position_y_certificate_code', !empty($template) ? $template->position_y_certificate_code : '2236') }}">
                                    <div class="invalid-feedback">
                                        @error('position_y_certificate_code')
                                            {{ $message }}
                                        @enderror
                                    </div>

                                    <label class="control-label"
                                        for="font_size_certificate_code">{!! trans('admin/main.font_size') !!}</label>
                                    <input type="text" name="font_size_certificate_code"
                                        class="form-control @error('font_size_certificate_code') is-invalid @enderror"
                                        value="{{ old('font_size_certificate_code', !empty($template) ? $template->font_size_certificate_code : '20') }}">
                                    <div class="invalid-feedback">
                                        @error('font_size_certificate_code')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>





                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.template_image') }}</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <button type="button" class="input-group-text admin-file-manager "
                                                data-input="image" data-preview="holder">
                                                <i class="fa fa-upload"></i>
                                            </button>
                                        </div>
                                        <input type="text" name="image" id="image"
                                            value="{{ !empty($template) ? $template->image : old('image') }}"
                                            class="js-certificate-image form-control @error('image') is-invalid @enderror" />
                                        <div class="invalid-feedback">
                                            @error('image')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">
                                        @error('image')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                    <div class="text-muted text-small mt-1">{{ trans('update.certificate_image_hint') }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="inputDefault">{!! trans('admin/main.text_color') !!}</label>
                                    <input type="text" name="text_color"
                                        class="form-control @error('text_color') is-invalid @enderror"
                                        value="{{ !empty($template) ? $template->text_color : old('text_color') }}">
                                    <div class="invalid-feedback">
                                        @error('text_color')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                    <div>Example: #e1e1e1</div>
                                </div>



                                <div class="form-group custom-switches-stacked">
                                    <label class="custom-switch pl-0">
                                        <input type="hidden" name="rtl" value="0">
                                        <input type="checkbox" id="rtl" name="rtl" value="1"
                                            {{ (!empty($template) and $template->rtl) ? 'checked="checked"' : '' }}
                                            class="custom-switch-input" />
                                        <span class="custom-switch-indicator"></span>
                                        <label class="custom-switch-description mb-0 cursor-pointer"
                                            for="rtl">{{ trans('admin/main.rtl') }}</label>
                                    </label>
                                </div>

                                <div class="form-group custom-switches-stacked">
                                    <label class="custom-switch pl-0">
                                        <input type="hidden" name="status" value="draft">
                                        <input type="checkbox" id="status" name="status" value="publish"
                                            {{ (!empty($template) and $template->status == 'publish') ? 'checked="checked"' : '' }}
                                            class="custom-switch-input" />
                                        <span class="custom-switch-indicator"></span>
                                        <label class="custom-switch-description mb-0 cursor-pointer"
                                            for="status">{{ trans('admin/main.active') }}</label>
                                    </label>
                                </div>

                            </div>
                        </div>
                </div>



                <div class="form-group">
                    <div class="col-md-12">
                        <button class="btn btn-success pull-left" id="submiter" type="button"
                            data-action="{{ !empty($template) ? getAdminPanelUrl("/certificates/templates/{$template->id}/update") : getAdminPanelUrl('/certificates/templates/store') }}">{{ trans('public.save') }}</button>
                        <button class="btn btn-info pull-left" id="preview" type="button"
                            data-action="{{ getAdminPanelUrl() }}/certificates/templates/preview">{{ trans('admin/main.preview_certificate') }}</button>
                    </div>
                </div>

                </form>
            </div>
        </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>


document.addEventListener('DOMContentLoaded', function () {
    // Get references to the dropdowns and type select element
    var typeSelect = document.getElementById('typeSelect');
    var bundleDropdown = document.getElementById('bundleDropdown');
    var courseDropdown = document.getElementById('courseDropdown');
    
    // Function to handle visibility based on selected type
    function handleTypeChange() {
        var selectedType = typeSelect.value;
        
        // Show/Hide dropdowns based on selected type
        if (selectedType === 'bundle') {
            bundleDropdown.style.display = 'block';
            courseDropdown.style.display = 'none';
        } else if (selectedType === 'course') {
            bundleDropdown.style.display = 'none';
            courseDropdown.style.display = 'block';
        } else if (selectedType === 'attendance') {
            bundleDropdown.style.display = 'block';
            courseDropdown.style.display = 'none';
        }
        else {
            bundleDropdown.style.display = 'none';
            courseDropdown.style.display = 'none';
        }
    }

    // Attach change event listener to the type select element
    typeSelect.addEventListener('change', handleTypeChange);

    // Initial call to set the correct state based on the current value
    handleTypeChange();
});
    $(document).ready(function() {
       
        $('#bundles').select2({
            placeholder: '',
           
        });

        $('#courses').select2({
            placeholder: '',
           
        });
    });
</script>
    <script src="/assets/default/js/admin/certificates.min.js"></script>
@endpush
