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

@php
    $type = request()->get('type', 'program');
@endphp

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ !empty($bundle) ? trans('/admin/main.edit') : trans('admin/main.create') }}
                {{ $type == 'bridging' || (!empty($bundle) && $bundle->type == 'bridging') ? trans('update.bridge') : trans('update.bundle') }}
            </h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a
                        href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}/bundles">{{ trans('update.bundles') }}</a>
                </div>
                <div class="breadcrumb-item">{{ !empty($bundle) ? trans('/admin/main.edit') : trans('admin/main.new') }}
                </div>
            </div>
        </div>

        <div class="section-body">

            <div class="row">
                <div class="col-12 ">
                    <div class="card">
                        <div class="card-body">

                            <form method="post"
                                action="{{ getAdminPanelUrl() }}/bundles/{{ !empty($bundle) ? $bundle->id . '/update' : 'store' }}"
                                id="webinarForm" class="webinar-form" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <section>
                                    <h2 class="section-title after-line">{{ trans('public.basic_information') }}</h2>

                                    <div class="row">
                                        <div class="col-12 col-md-5">

                                            @if (!empty($type) and empty($bundle))
                                                <input type="hidden" name="type" value="{{ $type }}">
                                            @endif

                                            @if (!empty($bundle))
                                                <div class="form-group">
                                                    <label class="input-label">نوع البرنامج</label>
                                                    <select name="type" class="form-control">
                                                        <option value="" selected disabled>اختر نوع البرنامج</option>
                                                        <option value="{{ $bundle->type }}" selected>
                                                            {{ $bundle->type == 'program' ? 'عام' : trans('update.bridging') }}
                                                        </option>

                                                    </select>
                                                    @error('type')
                                                        <div class="invalid-feedback d-block">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            @endif

                                            @if (!empty(getGeneralSettings('content_translate')))
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('auth.language') }}</label>
                                                    <select name="locale"
                                                        class="form-control {{ !empty($bundle) ? 'js-edit-content-locale' : '' }}">
                                                        @foreach ($userLanguages as $lang => $language)
                                                            <option value="{{ $lang }}"
                                                                @if (mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>
                                                                {{ $language }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('locale')
                                                        <div class="invalid-feedback d-block">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            @else
                                                <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
                                            @endif


                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.title') }}</label>
                                                <input type="text" name="title"
                                                    value="{{ !empty($bundle) ? $bundle->title : old('title') }}"
                                                    class="form-control @error('title')  is-invalid @enderror"
                                                    placeholder="" />
                                                @error('title')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>



                                            <div class="form-group mt-15">
                                                <label class="input-label">اسم البرنامج فى الشهاده</label>
                                                <input type="text" name="bundle_name_certificate"
                                                    value="{{ !empty($bundle) ? $bundle->bundle_name_certificate : old('bundle_name_certificate') }}"
                                                    class="form-control @error('bundle_name_certificate')  is-invalid @enderror"
                                                    placeholder="" />
                                                @error('bundle_name_certificate')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-15" id="studentDropdown">
                                                <label class="control-label">استثناء طالب من الشهاده</label>
                                                <select name="student_id[]" id="student_id" class="form-control select2"
                                                    multiple data-placeholder="قم بتحديد الطلاب">
                                                    <option value="">-- Select students --</option>
                                                    @foreach ($students as $student)
                                                        <option value="{{ $student->id }}"
                                                            @if (in_array($student->id, old('student_id', [])) ||
                                                                    (!empty($bundle) && $bundle->studentsExcluded->contains($student->id))) selected @endif>
                                                            {{ $student->registeredUser?->email }} -
                                                            {{ $student->ar_name ?? '' }}
                                                            ({{ $student->registeredUser->user_code ?? '' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group mt-15" style="display: none">
                                                <label class="input-label">{{ trans('update.required_points') }}</label>
                                                <input type="text" name="points"
                                                    value="{{ !empty($bundle) ? $bundle->points : old('points') }}"
                                                    class="form-control @error('points')  is-invalid @enderror"
                                                    placeholder="Empty means inactive this mode" />
                                                <div class="text-muted text-small mt-1">
                                                    {{ trans('update.product_points_hint') }}</div>
                                                @error('points')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-15 js-start_date">
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('public.start_date') }}</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="dateInputGroupPrepend">
                                                                <i class="fa fa-calendar-alt "></i>
                                                            </span>
                                                        </div>

                                                        <input type="text" name="start_date"
                                                            value="{{ (!empty($bundle) and $bundle->start_date) ? dateTimeFormat($bundle->start_date, 'Y-m-d H:i', false, false, getTimezone()) : old('start_date') }}"
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




                                            <div class="form-group mt-15 js-start_date">
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('public.end_date') }}</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="dateInputGroupPrepend">
                                                                <i class="fa fa-calendar-alt "></i>
                                                            </span>
                                                        </div>

                                                        <input type="text" name="end_date"
                                                            value="{{ (!empty($bundle) and $bundle->end_date) ? dateTimeFormat($bundle->end_date, 'Y-m-d H:i', false, false, getTimezone()) : old('end_date') }}"
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






                                            <div class="form-group mt-15 d-none">
                                                <label class="input-label">{{ trans('update.bundle_url') }}</label>
                                                <input type="text" name="slug"
                                                    value="{{ !empty($bundle) ? $bundle->slug : old('slug') }}"
                                                    class="form-control @error('slug')  is-invalid @enderror"
                                                    placeholder="" />
                                                <div class="text-muted text-small mt-1">
                                                    {{ trans('update.bundle_url_hint') }}</div>
                                                @error('slug')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-15">
                                                <label class="input-label">طريقة إدخال جدول المحاضرات</label>
                                                <select id="inputMethod" class="form-control" onchange="toggleInput()">
                                                    <option value="url" selected>رابط (URL)</option>
                                                    <option value="file">رفع ملف</option>
                                                </select>
                                            </div>

                                            <div id="urlInput" class="form-group mt-15">
                                                <label class="input-label">عنوان رابط (URL) جدول المحاضرات</label>
                                                <input type="text" name="content_table"
                                                    value="{{ !empty($bundle) ? $bundle->content_table : old('content_table') }}"
                                                    class="form-control @error('content_table') is-invalid @enderror"
                                                    placeholder="" />

                                                @error('content_table')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div id="fileInput" class="form-group mt-15" style="display: none;">
                                                <label class="input-label">رفع جدول المحاضرات</label>
                                                <input type="file" name="content_table"
                                                    value="{{ !empty($bundle) ? $bundle->content_table : old('content_table') }}"
                                                    class="form-control @error('content_table') is-invalid @enderror" />
                                                @error('content_table')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>



                                            @if (!empty($bundle) and $bundle->creator->isOrganization())
                                                <div class="form-group mt-15 ">
                                                    <label
                                                        class="input-label d-block">{{ trans('admin/main.organization') }}</label>

                                                    <select class="form-control" disabled readonly
                                                        data-placeholder="{{ trans('public.search_instructor') }}">
                                                        <option selected>{{ $bundle->creator->full_name }}</option>
                                                    </select>
                                                </div>
                                            @endif


                                            <div class="form-group mt-15 ">
                                                <label
                                                    class="input-label d-block">{{ trans('admin/main.select_a_instructor') }}</label>


                                                <select name="teacher_id" data-search-option="just_teacher_role"
                                                    class="form-control search-user-select2"
                                                    data-placeholder="{{ trans('public.select_a_teacher') }}">
                                                    @if (!empty($bundle))
                                                        <option value="{{ $bundle->teacher->id }}" selected>
                                                            {{ $bundle->teacher->full_name }}</option>
                                                    @else
                                                        <option selected disabled>{{ trans('public.select_a_teacher') }}
                                                        </option>
                                                    @endif
                                                </select>

                                                @error('teacher_id')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                            <div
                                                class="form-group mt-30 d-flex align-items-center justify-content-between">
                                                <label class=""
                                                    for="partnerInstructorSwitch">{{ trans('public.partner_instructor') }}</label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" name="partner_instructor"
                                                        class="custom-control-input" id="partnerInstructorSwitch"
                                                        {{ !empty($bundle) && $bundle->partner_instructor ? 'checked' : '' }}>
                                                    <label class="custom-control-label"
                                                        for="partnerInstructorSwitch"></label>
                                                </div>
                                            </div>

                                            <div id="partnerInstructorInput"
                                                class="form-group mt-15 {{ !empty($bundle) && $bundle->partner_instructor ? '' : 'd-none' }}">
                                                <label
                                                    class="input-label d-block">{{ trans('public.select_a_partner_teacher') }}</label>

                                                <select name="partners[]" multiple data-search-option="just_teacher_role"
                                                    class="js-search-partner-user form-control {{ !empty($bundle) && $bundle->partner_instructor ? 'search-user-select22' : '' }}"
                                                    data-placeholder="{{ trans('public.search_instructor') }}">
                                                    @if (!empty($bundlePartnerTeacher))
                                                        @foreach ($bundlePartnerTeacher as $partner)
                                                            @if (!empty($partner) and $partner->teacher)
                                                                <option value="{{ $partner->teacher->id }}" selected>
                                                                    {{ $partner->teacher->full_name }}</option>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </select>

                                                <div class="text-muted text-small mt-1">
                                                    {{ trans('admin/main.select_a_partner_hint') }}</div>
                                            </div>

                                            <div class="form-group mt-15 d-none">
                                                <label class="input-label">{{ trans('public.seo_description') }}</label>
                                                <input type="text" name="seo_description"
                                                    value="{{ !empty($bundle) ? $bundle->seo_description : old('seo_description') }}"
                                                    class="form-control @error('seo_description')  is-invalid @enderror" />
                                                <div class="text-muted text-small mt-1">
                                                    {{ trans('admin/main.seo_description_hint') }}</div>
                                                @error('seo_description')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-15" style="display:none">
                                                <label class="input-label">{{ trans('public.thumbnail_image') }}</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="input-group-text admin-file-manager"
                                                            data-input="thumbnail" data-preview="holder">
                                                            <i class="fa fa-upload"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" name="thumbnail" id="thumbnail"
                                                        value="bridging"
                                                        class="form-control @error('thumbnail')  is-invalid @enderror" />
                                                    <div class="input-group-append">
                                                        <button type="button" class="input-group-text admin-file-view"
                                                            data-input="thumbnail">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('thumbnail')
                                                        <div class="invalid-feedback d-block">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>


                                            <div class="form-group mt-15" style="display:none">
                                                <label class="input-label">{{ trans('public.cover_image') }}</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="input-group-text admin-file-manager"
                                                            data-input="cover_image" data-preview="holder">
                                                            <i class="fa fa-upload"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" name="image_cover" id="cover_image"
                                                        value="bridging"
                                                        class="form-control @error('image_cover')  is-invalid @enderror" />
                                                    <div class="input-group-append">
                                                        <button type="button" class="input-group-text admin-file-view"
                                                            data-input="cover_image">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('image_cover')
                                                        <div class="invalid-feedback d-block">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group mt-25" style="display:none">
                                                <label class="input-label">{{ trans('public.demo_video') }}
                                                    ({{ trans('public.optional') }})</label>


                                                <div class="d-none">
                                                    <label
                                                        class="input-label font-12">{{ trans('public.source') }}</label>
                                                    <select name="video_demo_source"
                                                        class="js-video-demo-source form-control">
                                                        @foreach (\App\Models\Webinar::$videoDemoSource as $source)
                                                            <option value="{{ $source }}"
                                                                @if (!empty($bundle) and $bundle->video_demo_source == $source) selected @endif>
                                                                {{ trans('update.file_source_' . $source) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group mt-0 d-none">
                                                <label class="input-label font-12">{{ trans('update.path') }}</label>
                                                <div class="input-group js-video-demo-path-input">
                                                    <div class="input-group-prepend">
                                                        <button type="button"
                                                            class="js-video-demo-path-upload input-group-text admin-file-manager {{ (empty($bundle) or empty($bundle->video_demo_source) or $bundle->video_demo_source == 'upload') ? '' : 'd-none' }}"
                                                            data-input="demo_video" data-preview="holder">
                                                            <i class="fa fa-upload"></i>
                                                        </button>

                                                        <button type="button"
                                                            class="js-video-demo-path-links rounded-left input-group-text input-group-text-rounded-left  {{ (empty($bundle) or empty($bundle->video_demo_source) or $bundle->video_demo_source == 'upload') ? 'd-none' : '' }}">
                                                            <i class="fa fa-link"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" name="video_demo" id="demo_video"
                                                        value="{{ !empty($bundle) ? $bundle->video_demo : old('video_demo') }}"
                                                        class="form-control @error('video_demo')  is-invalid @enderror" />
                                                    @error('video_demo')
                                                        <div class="invalid-feedback d-block">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.description') }}</label>
                                                <textarea id="summernote" name="description" class="form-control @error('description')  is-invalid @enderror"
                                                    placeholder="{{ trans('forms.webinar_description_placeholder') }}">{!! !empty($bundle) ? $bundle->description : old('description') !!}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="mt-3">
                                    <h2 class="section-title after-line">{{ trans('public.additional_information') }}</h2>
                                    <div class="row">
                                        <div class="col-12 col-md-6">

                                            <div class="form-group mt-3 d-flex align-items-center justify-content-between">
                                                <label class="" for="subscribeSwitch"
                                                    style="display:none">{{ trans('public.subscribe') }}</label>
                                                <div class="custom-control custom-switch" style="display:none">
                                                    <input type="checkbox" name="subscribe" class="custom-control-input"
                                                        id="subscribeSwitch"
                                                        {{ !empty($bundle) && $bundle->subscribe ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="subscribeSwitch"></label>
                                                </div>
                                            </div>

                                            <div class="form-group mt-15" style="display:none">
                                                <label class="input-label">{{ trans('update.access_days') }}</label>
                                                <input type="text" name="access_days" value="500"
                                                    class="form-control @error('access_days')  is-invalid @enderror" />
                                                @error('access_days')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                                <p class="mt-1">- {{ trans('update.access_days_input_hint') }}</p>
                                            </div>

                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.price') }}
                                                    ({{ $currency }})</label>
                                                <input type="text" name="price"
                                                    value="{{ !empty($bundle) ? $bundle->price : old('price') }}"
                                                    class="form-control @error('price')  is-invalid @enderror"
                                                    placeholder="{{ trans('public.0_for_free') }}" />
                                                @error('price')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-15" style="display:none">
                                                <label class="input-label d-block">{{ trans('public.tags') }}</label>
                                                <input type="text" name="tags" data-max-tag="5"
                                                    value="{{ !empty($bundle) ? implode(',', $bundleTags) : '' }}"
                                                    class="form-control inputtags"
                                                    placeholder="{{ trans('public.type_tag_name_and_press_enter') }} ({{ trans('admin/main.max') }} : 5)" />
                                            </div>


                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.category') }}</label>

                                                <select id="categories"
                                                    class="custom-select @error('category_id')  is-invalid @enderror"
                                                    name="category_id" required>
                                                    <option {{ !empty($bundle) ? '' : 'selected' }} disabled>
                                                        {{ trans('public.choose_category') }}</option>
                                                    @foreach ($categories as $category)
                                                        @if (!empty($category->subCategories) and count($category->subCategories))
                                                            <optgroup label="{{ $category->title }}">
                                                                @foreach ($category->subCategories as $subCategory)
                                                                    <option value="{{ $subCategory->id }}"
                                                                        {{ old('category_id', $bundle->category_id ?? null) == $subCategory->id ? 'selected' : '' }}>
                                                                        {{ $subCategory->title }}</option>
                                                                @endforeach
                                                            </optgroup>
                                                        @else
                                                            <option value="{{ $category->id }}"
                                                                {{ old('category_id', $bundle->category_id ?? null) == $category->id ? 'selected' : '' }}>
                                                                {{ $category->title }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>

                                                @error('category_id')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            @if ($type == 'bridging' or !empty($bundle) and $bundle->type == 'bridging')
                                                <div class="form-group mt-15">
                                                    <label
                                                        class="input-label">{{ 'البرامج المسوح لها بالتجسير' }}</label>

                                                    <select id=""
                                                        class="form-control select2 @error('from_bundle_id')  is-invalid @enderror"
                                                        name="from_bundle_id[]" id='from_bundle_id' required multiple>
                                                        <option disabled>
                                                            {{ trans('public.choose_category') }}</option>
                                                        {{-- Loop through top-level categories --}}
                                                        @foreach ($categories as $category)
                                                            <optgroup label="{{ $category->title }}">

                                                                {{-- Display bundles directly under the current category --}}
                                                                @foreach ($category->programs as $bundleItem)
                                                                    <option value="{{ $bundleItem->id }}"
                                                                        @if (in_array($bundleItem->id, old('from_bundle_id', [])) ||
                                                                                (!empty($bundle) && $bundle->bridgingBundles->contains($bundleItem->id))) selected @endif>
                                                                        {{ $bundleItem->title }}</option>
                                                                @endforeach

                                                                {{-- Display bundles under subcategories --}}
                                                                @foreach ($category->subCategories as $subCategory)
                                                                    @foreach ($subCategory->programs as $bundleItem)
                                                                        <option value="{{ $bundleItem->id }}"
                                                                            @if (in_array($bundleItem->id, old('from_bundle_id', [])) ||
                                                                                    (!empty($bundle) && $bundle->bridgingBundles->contains($bundleItem->id))) selected @endif>
                                                                            {{ $bundleItem->title }}</option>
                                                                    @endforeach
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>

                                                    @error('from_bundle_id')
                                                        <div class="invalid-feedback d-block">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            @endif




                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.batch_number') }}</label>

                                                <select id="study_classes"
                                                    class="custom-select @error('batch_id') is-invalid @enderror"
                                                    name="batch_id" required>
                                                    <option {{ !empty($bundle) ? '' : 'selected' }} disabled>
                                                        {{ trans('public.choose_batch') }}</option>
                                                    @foreach ($study_classes as $studyClass)
                                                        <option value="{{ $studyClass->id }}"
                                                            {{ old('batch_id', $bundle->batch_id ?? null) == $studyClass->id ? 'selected' : '' }}>
                                                            {{ $studyClass->title }}</option>
                                                    @endforeach
                                                </select>

                                                @error('batch_id')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>



                                            <!--certificates-->
                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.certificate') }}</label>

                                                <select id="certificates"
                                                    class="custom-select @error('certificate_template_id')  is-invalid @enderror"
                                                    name="certificate_template_id">
                                                    <option value="" selected>اختر الشهادة
                                                    </option>

                                                    @foreach ($certificates as $certificate)
                                                        @if (!empty($certificate))
                                                            <option value="{{ $certificate->id }}"
                                                                {{ (!empty($bundle) and $bundle->certificate_template_id == $certificate->id) ? 'selected' : '' }}>
                                                                {{ $certificate->title }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>

                                                @error('certificate_template_id')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                            <!---->


                                            {{-- ACP certificate --}}
                                            <div class="form-group mt-15">
                                                <div class="d-flex">
                                                    <input type="hidden" name="has_certificate" value="0">
                                                    <input type="checkbox" name="has_certificate" id="has_certificate"
                                                        style="accent-color:var(--primary)"
                                                        @if (isset($bundle->has_certificate) && $bundle->has_certificate == 1) checked @endif value="1">

                                                    <label for="has_certificate"
                                                        class="form-check-label mr-2 font-weight-bold"> يضم شهادة الشهادة
                                                        المهنيه الاحترافية ACP </label>
                                                </div>

                                                @error('has_certificate')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror

                                            </div>

                                            {{-- has Groups --}}
                                            <div class="form-group mt-15">
                                                <div class="d-flex">
                                                    <input type="hidden" name="hasGroup" value="0">
                                                    <input type="checkbox" name="hasGroup" id="hasGroup"
                                                        style="accent-color:var(--primary)"
                                                        @if (isset($bundle->hasGroup) && $bundle->hasGroup == 1) checked @endif value="1">

                                                    <label for="hasGroup" class="form-check-label mr-2 font-weight-bold">
                                                        يتم تقسيمة لجروبات </label>
                                                </div>

                                                @error('hasGroup')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror

                                            </div>

                                            <div class="form-group">
                                                <label>{{ trans('/admin/main.status') }}</label>
                                                <select class="form-control @error('status') is-invalid @enderror"
                                                    id="status" name="status">
                                                    <option disabled selected>{{ trans('admin/main.select_status') }}
                                                    </option>
                                                    @foreach (\App\Models\Bundle::$statuses as $status)
                                                        <option value="{{ $status }}"
                                                            {{ old('status', $bundle->status ?? null) === $status ? 'selected' : '' }}>
                                                            {{ $status }}</option>
                                                    @endforeach
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                        </div>
                                    </div>

                                    <div class="form-group mt-15 {{ (!empty($bundleCategoryFilters) and count($bundleCategoryFilters)) ? '' : 'd-none' }}"
                                        id="categoriesFiltersContainer">
                                        <span class="input-label d-block">{{ trans('public.category_filters') }}</span>
                                        <div id="categoriesFiltersCard" class="row mt-3">

                                            @if (!empty($bundleCategoryFilters) and count($bundleCategoryFilters))
                                                @foreach ($bundleCategoryFilters as $filter)
                                                    <div class="col-12 col-md-3">
                                                        <div class="webinar-category-filters">
                                                            <strong
                                                                class="category-filter-title d-block">{{ $filter->title }}</strong>
                                                            <div class="py-10"></div>

                                                            @foreach ($filter->options as $option)
                                                                <div
                                                                    class="form-group mt-3 d-flex align-items-center justify-content-between">
                                                                    <label class="text-gray font-14"
                                                                        for="filterOptions{{ $option->id }}">{{ $option->title }}</label>
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" name="filters[]"
                                                                            value="{{ $option->id }}"
                                                                            {{ !empty($bundleFilterOptions) && in_array($option->id, $bundleFilterOptions) ? 'checked' : '' }}
                                                                            class="custom-control-input"
                                                                            id="filterOptions{{ $option->id }}">
                                                                        <label class="custom-control-label"
                                                                            for="filterOptions{{ $option->id }}"></label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif

                                        </div>
                                    </div>
                                </section>

                                @if (!empty($bundle))
                                    <section class="mt-30">
                                        {{-- <div class="d-flex justify-content-between align-items-center">
                                            <h2 class="section-title after-line">{{ trans('admin/main.price_plans') }}</h2>
                                            <button id="webinarAddTicket" type="button" class="btn btn-primary btn-sm mt-3">{{ trans('admin/main.add_price_plan') }}</button>
                                        </div> --}}

                                        {{-- <div class="row mt-10">
                                            <div class="col-12">

                                                @if (!empty($tickets) and !$tickets->isEmpty())
                                                    <div class="table-responsive">
                                                        <table class="table table-striped text-center font-14">

                                                            <tr>
                                                                <th>{{ trans('public.title') }}</th>
                                                                <th>{{ trans('public.discount') }}</th>
                                                                <th>{{ trans('public.capacity') }}</th>
                                                                <th>{{ trans('public.date') }}</th>
                                                                <th></th>
                                                            </tr>

                                                            @foreach ($tickets as $ticket)
                                                                <tr>
                                                                    <th scope="row">{{ $ticket->title }}</th>
                                                                    <td>{{ $ticket->discount }}%</td>
                                                                    <td>{{ $ticket->capacity }}</td>
                                                                    <td>{{ dateTimeFormat($ticket->start_date,'j F Y') }} - {{ (new DateTime())->setTimestamp($ticket->end_date)->format('j F Y') }}</td>
                                                                    <td>
                                                                        <button type="button" data-ticket-id="{{ $ticket->id }}" data-webinar-id="{{ !empty($bundle) ? $bundle->id : '' }}" class="edit-ticket btn-transparent text-primary mt-1" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                                            <i class="fa fa-edit"></i>
                                                                        </button>

                                                                        @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/tickets/'. $ticket->id .'/delete', 'btnClass' => ' mt-1'])
                                                                    </td>
                                                                </tr>
                                                            @endforeach

                                                        </table>
                                                    </div>
                                                @else
                                                    @include('admin.includes.no-result',[
                                                        'file_name' => 'ticket.png',
                                                        'title' => trans('public.ticket_no_result'),
                                                        'hint' => trans('public.ticket_no_result_hint'),
                                                    ])
                                                @endif
                                            </div>
                                        </div> --}}
                                    </section>


                                    <section class="mt-30">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h2 class="section-title after-line">{{ trans('product.courses') }}</h2>
                                            <button id="bundleAddNewCourses" type="button"
                                                class="btn btn-primary btn-sm mt-3">{{ trans('update.add_new_course') }}</button>
                                        </div>

                                        <div class="row mt-10">
                                            <div class="col-12">
                                                @if (!empty($bundleWebinars) and !$bundleWebinars->isEmpty())
                                                    <div class="table-responsive">
                                                        <table class="table table-striped text-center font-14">

                                                            <tr>
                                                                <th>{{ trans('public.title') }}</th>
                                                                <th class="text-left">{{ trans('public.instructor') }}
                                                                </th>
                                                                <th>{{ trans('public.price') }}</th>
                                                                <th>{{ trans('public.publish_date') }}</th>
                                                                <th></th>
                                                            </tr>

                                                            @foreach ($bundleWebinars as $bundleWebinar)
                                                                @if (!empty($bundleWebinar->webinar->title))
                                                                    <tr>
                                                                        <th>{{ $bundleWebinar->webinar->title }}</th>
                                                                        <td class="text-left">
                                                                            {{ $bundleWebinar->webinar->teacher->full_name }}
                                                                        </td>
                                                                        <td>{{ !empty($bundleWebinar->webinar->price) ? handlePrice($bundleWebinar->webinar->price) : trans('public.free') }}
                                                                        </td>
                                                                        <td>{{ dateTimeFormat($bundleWebinar->webinar->created_at, 'j F Y | H:i') }}
                                                                        </td>

                                                                        <td>
                                                                            <button type="button"
                                                                                data-item-id="{{ $bundleWebinar->id }}"
                                                                                data-bundle-id="{{ !empty($bundle) ? $bundle->id : '' }}"
                                                                                class="edit-bundle-webinar btn-transparent text-primary mt-1"
                                                                                data-toggle="tooltip" data-placement="top"
                                                                                title="{{ trans('admin/main.edit') }}">
                                                                                <i class="fa fa-edit"></i>
                                                                            </button>

                                                                            @include(
                                                                                'admin.includes.delete_button',
                                                                                [
                                                                                    'url' =>
                                                                                        getAdminPanelUrl() .
                                                                                        '/bundle-webinars/' .
                                                                                        $bundleWebinar->id .
                                                                                        '/delete',
                                                                                    'btnClass' => ' mt-1',
                                                                                ]
                                                                            )
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach

                                                        </table>
                                                    </div>
                                                @else
                                                    @include('admin.includes.no-result', [
                                                        'file_name' => 'comment.png',
                                                        'title' => trans('update.bundle_webinar_no_result'),
                                                        'hint' => trans('update.bundle_webinar_no_result_hint'),
                                                    ])
                                                @endif
                                            </div>
                                        </div>
                                    </section>

                                    <section class="mt-30">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h2 class="section-title after-line">{{ trans('public.faq') }}</h2>
                                            <button id="webinarAddFAQ" type="button"
                                                class="btn btn-primary btn-sm mt-3">{{ trans('public.add_faq') }}</button>
                                        </div>

                                        <div class="row mt-10">
                                            <div class="col-12">
                                                @if (!empty($faqs) and !$faqs->isEmpty())
                                                    <div class="table-responsive">
                                                        <table class="table table-striped text-center font-14">

                                                            <tr>
                                                                <th>{{ trans('public.title') }}</th>
                                                                <th>{{ trans('public.answer') }}</th>
                                                                <th></th>
                                                            </tr>

                                                            @foreach ($faqs as $faq)
                                                                <tr>
                                                                    <th>{{ $faq->title }}</th>
                                                                    <td>
                                                                        <button type="button"
                                                                            class="js-get-faq-description btn btn-sm btn-gray200">{{ trans('public.view') }}</button>
                                                                        <input type="hidden"
                                                                            value="{{ $faq->answer }}" />
                                                                    </td>

                                                                    <td class="text-right">
                                                                        <button type="button"
                                                                            data-faq-id="{{ $faq->id }}"
                                                                            data-webinar-id="{{ !empty($bundle) ? $bundle->id : '' }}"
                                                                            class="edit-faq btn-transparent text-primary mt-1"
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title="{{ trans('admin/main.edit') }}">
                                                                            <i class="fa fa-edit"></i>
                                                                        </button>

                                                                        @include(
                                                                            'admin.includes.delete_button',
                                                                            [
                                                                                'url' =>
                                                                                    getAdminPanelUrl() .
                                                                                    '/faqs/' .
                                                                                    $faq->id .
                                                                                    '/delete',
                                                                                'btnClass' => ' mt-1',
                                                                            ]
                                                                        )
                                                                    </td>
                                                                </tr>
                                                            @endforeach

                                                        </table>
                                                    </div>
                                                @else
                                                    @include('admin.includes.no-result', [
                                                        'file_name' => 'faq.png',
                                                        'title' => trans('public.faq_no_result'),
                                                        'hint' => trans('public.faq_no_result_hint'),
                                                    ])
                                                @endif
                                            </div>
                                        </div>
                                    </section>
                                @endif

                                <section class="mt-3">
                                    <h2 class="section-title after-line">{{ trans('public.message_to_reviewer') }}</h2>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mt-15">
                                                <textarea name="message_for_reviewer" rows="10" class="form-control">{{ (!empty($bundle) and $bundle->message_for_reviewer) ? $bundle->message_for_reviewer : old('message_for_reviewer') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <input type="hidden" name="draft" value="no" id="forDraft" />

                                <div class="row">
                                    <div class="col-12">
                                        <button type="button" id="saveAndPublish"
                                            class="btn btn-success">{{ !empty($bundle) ? trans('admin/main.save_and_publish') : trans('admin/main.save_and_continue') }}</button>

                                        @if (!empty($bundle))
                                            <button type="button" id="saveReject"
                                                class="btn btn-warning">{{ trans('public.reject') }}</button>

                                            @can('admin_bundles_delete')
                                                @include('admin.includes.delete_button', [
                                                    'url' =>
                                                        getAdminPanelUrl() . '/bundles/' . $bundle->id . '/delete',
                                                    'btnText' => trans('public.delete'),
                                                    'hideDefaultClass' => true,
                                                    'btnClass' => 'btn btn-danger',
                                                ])
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                            </form>

                            @include('admin.bundles.modals.bundle-webinar')
                            @include('admin.bundles.modals.ticket')
                            @include('admin.bundles.modals.faq')
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
        function toggleInput() {
            var method = document.getElementById('inputMethod').value;
            if (method === 'url') {
                document.getElementById('urlInput').style.display = 'block';
                document.getElementById('fileInput').style.display = 'none';
            } else {
                document.getElementById('urlInput').style.display = 'none';
                document.getElementById('fileInput').style.display = 'block';
            }
        }
        $(document).ready(function() {
            $('.select2').select2(); // Make sure select2 is initialized correctly
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
