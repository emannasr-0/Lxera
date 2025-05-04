@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/bootstrap-timepicker/bootstrap-timepicker.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/bootstrap-tagsinput/bootstrap-tagsinput.min.css">
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
    <style>
        .bootstrap-timepicker-widget table td input {
            width: 35px !important;
        }

        .select2-container {
            z-index: 1212 !important;
        }
    </style>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ !empty($service) ? trans('/admin/main.edit') : trans('admin/main.new') }} {{ 'خدمة إلكترونية' }}
            </h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a
                        href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}/services">الخدمات الإلكترونية</a>
                </div>
                <div class="breadcrumb-item">{{ !empty($service) ? trans('/admin/main.edit') : trans('admin/main.new') }}
                </div>
            </div>
        </div>

        @if (Session::has('success'))
            <div class="container d-flex justify-content-center mt-80">
                <p class="alert alert-success w-75 text-center"> {{ Session::get('success') }} </p>
            </div>
        @endif

        @if (Session::has('error'))
            <div class="container d-flex justify-content-center mt-80">
                <p class="alert alert-success w-75 text-center"> {{ Session::get('error') }} </p>
            </div>
        @endif

        <div class="section-body">

            <div class="row">
                <div class="col-12 ">
                    <div class="card">
                        <div class="card-body">

                            <form method="post"
                                action="{{ getAdminPanelUrl() }}/services{{ !empty($service) ? '/' . $service->id : '' }}"
                                id="serviceForm" class="service-form">
                                @csrf()
                                @if (!empty($service))
                                    @method('PUT')
                                @endif
                                <section>
                                    <h2 class="section-title after-line">{{ trans('public.basic_information') }}</h2>

                                    <div class="row">
                                        <div class="col-12 col-md-7">
                                            @if (!empty(getGeneralSettings('content_translate')))
                                                <div class="form-group">
                                                    <label class="input-label">
                                                        {{ trans('auth.language') }}
                                                    </label>
                                                    <select name="locale"
                                                        class="form-control {{ !empty($service) ? 'js-edit-content-locale' : '' }}">
                                                        @foreach ($userLanguages as $lang => $language)
                                                            <option value="{{ $lang }}"
                                                                @if (mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>
                                                                {{ $language }}</option>
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

                                            {{-- service title --}}
                                            <div class="form-group mt-15">
                                                <label class="input-label">
                                                    {{ trans('public.title') }}
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="title"
                                                    value="{{ !empty($service) ? $service->title : old('title') }}"
                                                    class="form-control @error('title')  is-invalid @enderror"
                                                    placeholder="ادخل العنوان الخاص بالخدمة" />
                                                @error('title')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            {{-- service description --}}
                                            <div class="form-group mt-15">
                                                <label class="input-label">
                                                    {{ trans('public.description') }}
                                                </label>

                                                <textarea rows="5" name="description" class="form-control @error('description')  is-invalid @enderror"
                                                    placeholder="ادخل وصف للخدمة وماذا هي تقدم">{{ !empty($service) ? $service->description : old('description') }}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            {{-- service target --}}
                                            <div class="form-group">
                                                <label class="control-label" for="targetSelect">هدف الخدمة
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <select id="targetSelect" name="target"
                                                    class="form-control @error('target') is-invalid @enderror">
                                                    <option value="" selected disabled>اختر هدف الخدمة</option>
                                                    <option value="all"
                                                        @if (old('target', $service->target ?? null) == 'all') selected @endif>
                                                        كل الطلاب
                                                    </option>

                                                    <option value="bundles"
                                                        @if (old('target', $service->target ?? null) == 'bundles') selected @endif>
                                                        طلاب كل البرامج
                                                    </option>

                                                    <option value="specific_bundles"
                                                        @if (old('target', $service->target ?? null) == 'specific_bundles') selected @endif>
                                                        طلاب برامج محددة
                                                    </option>

                                                    <option value="webinars"
                                                        @if (old('target', $service->target ?? null) == 'webinars') selected @endif>
                                                        طلاب كل الدورات
                                                    </option>

                                                    <option value="specific_webinars"
                                                        @if (old('target', $service->target ?? null) == 'specific_webinars') selected @endif>
                                                        طلاب دورات محددة
                                                    </option>

                                                </select>
                                                <div class="invalid-feedback">
                                                    @error('target')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>



                                            {{-- Specific Bundles Dropdown --}}
                                            <div class="form-group" id="bundleDropdown" style="display: none;">
                                                <label class="control-label">البرامج
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select name="bundles[]" id="bundles"
                                                    class="form-control select2 @error('bundles')  is-invalid @enderror"
                                                    data-placeholder= 'اختر البرامج' multiple>
                                                    @foreach ($bundles as $bundle)
                                                        <option value="{{ $bundle->id }}"
                                                            {{ in_array($bundle->id, old('bundles', [])) || (isset($service) && $service->bundles->contains($bundle->id)) ? 'selected' : '' }}>
                                                            {{ $bundle->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback">
                                                    @error('bundles')
                                                        {{ $message }}
                                                        @push('styles_top')
                                                            <style>
                                                                .select2-selection {
                                                                    border-color: red !important;
                                                                    padding-right: 10px !important;
                                                                }
                                                            </style>
                                                        @endpush
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Specific Webinars Dropdown --}}
                                            <div class="form-group" id="webinarDropdown" style="display: none;">
                                                <label class="control-label"> الدورات
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select name="courses[]" id="courses"
                                                    class="form-control select2 @error('courses')  is-invalid @enderror"
                                                    data-placeholder= 'اختر الدورات' multiple>
                                                    @foreach ($courses as $course)
                                                        <option value="{{ $course->id }}"
                                                            {{ in_array($course->id, old('courses', [])) || (isset($service) && $service->webinars->contains($course->id)) ? 'selected' : '' }}>
                                                            {{ $course->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback">
                                                    @error('courses')
                                                        {{ $message }}
                                                        @push('styles_top')
                                                            <style>
                                                                .select2-selection {
                                                                    border-color: red !important;
                                                                    padding-right: 10px !important;
                                                                }
                                                            </style>
                                                        @endpush
                                                    @enderror
                                                </div>
                                            </div>


                                            {{-- service price --}}
                                            <div class="form-group mt-15">
                                                <label class="input-label">
                                                    {{ trans('public.price') }}
                                                    ({{ $currency }})
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="price"
                                                    value="{{ !empty($service) ? $service->price : old('price') }}"
                                                    class="form-control @error('price')  is-invalid @enderror"
                                                    placeholder="{{ trans('public.0_for_free') }}" />
                                                @error('price')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            {{-- start date --}}
                                            <div class="form-group mt-15 js-start_date">
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('public.start_date') }} <span
                                                            class="text-danger">*</span> </label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="dateInputGroupPrepend">
                                                                <i class="fa fa-calendar-alt "></i>
                                                            </span>
                                                        </div>

                                                        <input type="text" name="start_date"
                                                            value="{{ (!empty($service) and $service->start_date) ? $service->start_date : old('start_date') }}"
                                                            class="form-control @error('start_date')  is-invalid @enderror datetimepicker"
                                                            aria-describedby="dateInputGroupPrepend" />
                                                        @error('start_date')
                                                            <div class="invalid-feedback d-block">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- end date --}}
                                            <div class="form-group mt-15 js-start_date">
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('public.end_date') }} <span
                                                            class="text-danger">*</span> </label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="dateInputGroupPrepend">
                                                                <i class="fa fa-calendar-alt "></i>
                                                            </span>
                                                        </div>

                                                        <input type="text" name="end_date"
                                                            value="{{ (!empty($service) and $service->end_date) ? $service->end_date : old('end_date') }}"
                                                            class="form-control @error('end_date')  is-invalid @enderror datetimepicker"
                                                            aria-describedby="dateInputGroupPrepend" />
                                                        @error('end_date')
                                                            <div class="invalid-feedback d-block">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>


                                            {{-- status --}}
                                            <div class="form-group  mt-15">
                                                <label>{{ trans('/admin/main.status') }}
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control @error('status') is-invalid @enderror"
                                                    id="status" name="status">
                                                    <option disabled selected>{{ trans('admin/main.select_status') }}
                                                    </option>
                                                    @foreach (\App\User::$statuses as $status)
                                                        @if ($status != 'pending')
                                                            <option value="{{ $status }}"
                                                                {{ old('status', !empty($service) ? $service->status : '') === $status ? 'selected' : '' }}>
                                                                {{ $status }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            {{-- application link --}}

                                            @if (isset($service))
                                                <div class="form-group mt-15">
                                                    <label class="input-label">رابط التقديم (URL)
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="url" name="apply_link"
                                                        value="{{ !empty($service) ? $service->apply_link : old('apply_link') }}"
                                                        class="form-control @error('apply_link')  is-invalid @enderror"
                                                        placeholder="" />
                                                    <div class="text-muted text-small mt-1">
                                                        هذا الحقل خاص لعنوان URL الذي يذهب إليه الطالب لطلب هذة الخدمة
                                                    </div>
                                                    @error('apply_link')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>

                                                {{-- review link --}}

                                                <div class="form-group mt-15">
                                                    <label class="input-label">رابط مراجعة طلب سابق (URL)
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="url" name="review_link"
                                                        value="{{ !empty($service) ? $service->review_link : old('review_link') }}"
                                                        class="form-control @error('review_link')  is-invalid @enderror"
                                                        placeholder="" />
                                                    <div class="text-muted text-small mt-1">
                                                        هذا الحقل خاص لعنوان URL الذي يذهب إليه الطالب ..لمراجعة طلب سابق
                                                        لهذة
                                                        الخدمة
                                                    </div>
                                                    @error('review_link')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            @endif


                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <button
                                                class="btn btn-success">{{ !empty($service) ? trans('admin/main.save_and_publish') : trans('admin/main.save') }}</button>

                                            @if (!empty($service))
                                                @include('admin.includes.delete_button', [
                                                    'url' =>
                                                        getAdminPanelUrl() .
                                                        '/services/' .
                                                        $service->id .
                                                        '/delete',
                                                    'btnText' => trans('public.delete'),
                                                    'hideDefaultClass' => true,
                                                    'btnClass' => 'btn btn-danger',
                                                ])
                                            @endif
                                        </div>
                                    </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var titleLang = '{{ trans('admin/main.title') }}';
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            function resetSelect(selector) {
                selector.selectedIndex = 0; // This sets the first option as selected
                $(selector).val([]).trigger('change'); // Clears the selection
                console.log(selector);
            }

            function serviceTargetToggle() {
                const targetSelect = document.getElementById('targetSelect');
                const bundleDropdown = document.getElementById('bundleDropdown');
                const webinarDropdown = document.getElementById('webinarDropdown');

                const selectedValue = targetSelect.value;

                // Hide both dropdowns initially
                bundleDropdown.style.display = 'none';
                webinarDropdown.style.display = 'none';

                // Show relevant dropdowns based on selected target
                if (selectedValue === 'specific_bundles') {
                    bundleDropdown.style.display = 'block';
                    resetSelect('#courses');

                } else if (selectedValue === 'specific_webinars') {
                    webinarDropdown.style.display = 'block';
                    resetSelect('#bundles');
                } else {
                    resetSelect('#bundles');
                    resetSelect('#courses');
                }
            }

            serviceTargetToggle();
            targetSelect.addEventListener('change', function() {
                serviceTargetToggle();
            });
        });
    </script>

    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/vendors/feather-icons/dist/feather.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/vendors/bootstrap-timepicker/bootstrap-timepicker.min.js"></script>
    <script src="/assets/default/vendors/bootstrap-tagsinput/bootstrap-tagsinput.min.js"></script>
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script src="/assets/admin/js/webinar.min.js"></script>
@endpush
