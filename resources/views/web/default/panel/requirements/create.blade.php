@extends(getTemplate() . '.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')
    @include('web.default.panel.requirements.requirements_includes.progress')
    @if (Session::has('success'))
        <div class="container d-flex justify-content-center mt-80">
            <p class="alert alert-success w-75 text-center"> {{ Session::get('success') }} </p>
        </div>
    @else
        @if (!$requirementUploaded || $requirementStatus=="rejected")
            @include('web.default.panel.requirements.requirements_includes.basic_information')
        @else
        <div class="container mt-80 text-center ">
            <p class="alert alert-success text-center">
        لقد تم بالفعل رفع متطلبات القبول يرجي الذهاب لصفحة المتطلبات لرؤية حالة الطلب
            </p>
            <a href="/panel/requirements"
                class="btn btn-primary p-5 mt-20">للذهاب لصفحة متطلبات القبول برجي الضغط هنا</a>
        </div>
        @endif
    @endif
@endsection
@push('scripts_bottom')
    <script src="/assets/vendors/cropit/jquery.cropit.js"></script>
    <script src="/assets/default/js/parts/img_cropit.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>

    <script>
        var editEducationLang = '{{ trans('site.edit_education') }}';
        var editExperienceLang = '{{ trans('site.edit_experience') }}';
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var saveErrorLang = '{{ trans('site.store_error_try_again') }}';
        var notAccessToLang = '{{ trans('public.not_access_to_this_content') }}';
    </script>

    <script src="/assets/default/js/panel/user_setting.min.js"></script>
@endpush
