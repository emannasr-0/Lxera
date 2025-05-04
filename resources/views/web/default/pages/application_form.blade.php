@extends(getTemplate() . '.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/owl-carousel2/owl.carousel.min.css">
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-WSVP27XBX1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-WSVP27XBX1');
    </script>
    <style>
        .container_form {
            margin-top: 20px;
            /* border: 1px solid #ddd; */
            /* Add border to the container */
            padding: 20px;
            /* Optional: Add padding for spacing */
            border-radius: 16px !important;
            /* box-shadow: 2px 5px 10px #CCF5FF; */
            border:none;
            margin: 60px auto;
        }

        .hidden-element {
            display: none;
        }

        .application {
            display: flex;
            flex-direction: column;
            align-content: stretch;
            justify-content: flex-start;
            align-items: center;
            flex-wrap: wrap;
        }

        .section1 .form-title {
            text-align: center !important;
            padding: 10px;
            color: #CCF5FF;
        }

        a {
            color: #CCF5FF;
        }

        .form-main-title {
            font-family: sans-serif !important;
            font-style: normal;
            font-weight: 400;
            font-size: 22px;
            line-height: 39px;
            color: #CCF5FF;
        }

        .form-title {
            font-family: sans-serif !important;
            font-style: normal;
            font-weight: 700;
            /* font-size: 36px; */
            line-height: 42px;
            color: #fff;
        }

        input {
            text-align: right;
        }

        .main-section {
            background-color: #F6F7F8;
            border-radius: 16px !important;
        }

        .main-container {
            border-width: 2px !important;
            border-radius: 16px !important;
        }

        .secondary_education,
        .high_education,
        #education {
            display: none;
        }

        .hero {
            width: 100%;
            height: 50vh;
            /* background-color: #ED1088; */
            /* background-image: URL('https://lh3.googleusercontent.com/pw/AM-JKLXva4P7RlMWEJD_UMf699iZq37WokzlPBAqpkLcxYqgkUi3YzPTP5fuglzL3els1W36mjlBVmMNcqjGJMGNtQREe3THVN9pMkRZGNazhM3F5iQSuC4Z435gIA_0xrrPQWa1DGvsV02rmdJBJQxU0XM=w1400-h474-no'); */
            /* background-color: #141F25; */
            background-color: #fff !important;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            display: flex;
            flex-direction: column;
            flex-wrap: nowrap;
            justify-content: center;
            align-items: stretch;
        }

        @media(max-width:768px) {
            .hero {
                height: 50vh;
            }

            footer img {
                width: 150px !important;
            }

            .img-cover {
                width: 100% !important;
            }
        }

        @media(max-width:576px) {
            .form-main-title {
                font-size: 20px;
            }


        }
    </style>
@endpush

@php
    $siteGeneralSettings = getGeneralSettings();
@endphp
@php
    $registerMethod = getGeneralSettings('register_method') ?? 'mobile';
    $showOtherRegisterMethod = getFeaturesSettings('show_other_register_method') ?? false;
    $showCertificateAdditionalInRegister = getFeaturesSettings('show_certificate_additional_in_register') ?? false;
    $selectRolesDuringRegistration = getFeaturesSettings('select_the_role_during_registration') ?? null;
@endphp


@section('content')
    {{-- hero section --}}
    @include('web.default.includes.hero_section', [
        'inner' => "<h1 class='form-title font-36'>نموذج قبول طلب جديد وحجز مقعد دراسي</h1>",
    ])


    <div class="application container">
        <div class="col-12 col-lg-10 col-md-11 px-0">
            <div class="col-lg-12 col-md-12 px-0">
                <Section class="section1 main-section">
                    <div class="container_form bg-secondary-acadima">
                        <!--Form Title-->

                        <p style="padding: 40px 0;font-size:18px;font-weight:600;line-height:1.5em">
                            يجب الاطلاع على متطلبات القبول في البرامج قبل تقديم طلب قبول جديد
                            <a href="https://anasacademy.uk/admission/" style="color:#CCF5FF !important;" target="_blank">
                                اضغط هنا
                            </a>
                        </p>
                        <form action="/apply" method="POST" id="myForm">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id ?? '' }}">

                            {{-- application type --}}
                            <div class="form-group col-12 col-sm-6">
                                <label class="form-label">حدد نوع التقديم<span class="text-danger">*</span></label>
                                <select id="typeSelect" name="type" required
                                    class="form-control @error('type') is-invalid @enderror" onchange="toggleHiddenType()">
                                    <option selected hidden value="">ختر الدورة التدربيه التي تريد دراستها
                                        في
                                        اكاديما</option>
                                    @if (count($categories) > 0)
                                        <option value="programs" @if (old('type', request()->type ?? $user->application_type) == 'programs') selected @endif>
                                            البرامج المهنية</option>
                                    @endif
                                    <option value="courses" @if (old('type', request()->type ?? $user->application_type) == 'courses') selected @endif>الدورات التدريبيه</option>
                                </select>

                                @error('type')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- course --}}
                            <div class="form-group col-12 col-sm-6">
                                <label for="application2" class="form-label" id="all_course">الدورات التدربيه<span
                                        class="text-danger">*</span></label>
                                <select id="mySelect2" name="webinar_id" onchange="coursesToggle()"
                                    class="form-control @error('webinar_id') is-invalid @enderror">
                                    <option selected hidden value="">اختر الدورة التدربيه التي تريد دراستها
                                        في
                                        اكاديما</option>

                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}"
                                            @if (old('webinar_id', request()->webinar ?? ($user->application_type == 'courses' ? $user->program_id: null)) == $course->id) selected @endif>
                                            {{ $course->title }} </option>
                                    @endforeach

                                </select>

                                @error('webinar_id')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- course endorsement --}}
                            <div class="col-12 text-light d-none">
                                <input type="checkbox" id="course_endorsement" name="course_endorsement">
                                <span  class="text-light">
                                أقر بأن لدي خبرة عملية ومعرفة جيدة بالبرامج التي سأتقدم للاختبار بها، وأفهم أن الدورة تؤهل
                                للاختبار فقط ولا تعلم البرامج من الصفر.
                                </span>
                                @error('course_endorsement')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <div class="mt-3 text-light">
                                    <input type="checkbox" id="course_endorsement2" >
                                    إقرار بعدم تجاوز المتدرب فترة 30 يوم للتقدم للاختبار متضمنة فترة التأهيل وعند التجاوز يتطلب من المتدرب دفع غرامة مالية تحددها الأكاديمية ليتمكن من تمديد الدورة التأهيلية ومدة الاختبار
                                </div>
                            </div>


                            {{-- diplomas --}}
                            <section class="" id="diplomas_section">

                                {{-- diploma --}}
                                {{-- <div class="form-group col-12 col-sm-6">
                                    <label for="application" class="form-label"
                                        id="degree">{{ trans('application_form.application') }}<span
                                            class="text-danger">*</span></label>
                                    <select id="mySelect1" name="category_id"
                                        class="form-control @error('category_id') is-invalid @enderror"
                                        onchange="toggleHiddenInput()">
                                        <option selected hidden value="">اختر البرنامج الذي تريد
                                            دراسته في
                                            اكاديمية انس للفنون </option>
                                        @foreach ($category as $item)
                                            <option value="{{ $item->id }}" education= "{{ $item->education }}"
                                                {{ old('category_id', $bundle->category_id ?? null) == $item->id ? 'selected' : '' }}>
                                                {{ $item->title }} </option>
                                        @endforeach
                                    </select>

                                    @error('category_id')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div> --}}

                                {{-- specialization --}}
                                <div class="form-group col-12 col-sm-6">
                                    <label for="bundle_id">
                                        البرنامج<span class="text-danger">*</span>
                                    </label>

                                    <select id="bundle_id" class="custom-select @error('bundle_id')  is-invalid @enderror"
                                        name="bundle_id" onchange="CertificateSectionToggle()">
                                        <option selected hidden value="">اختر الدورة التدربيه التي تريد دراستها
                                            في
                                            اكاديما</option>

                                        {{-- Loop through top-level categories --}}
                                        @foreach ($categories as $category)
                                            <optgroup label="{{ $category->title }}">

                                                {{-- Display bundles directly under the current category --}}
                                                @foreach ($category->activeBundles as $bundleItem)
                                                    <option value="{{ $bundleItem->id }}"
                                                        has_certificate="{{ $bundleItem->has_certificate }}"
                                                        early_enroll="{{ $bundleItem->early_enroll }}"
                                                        @if (old('bundle_id', request()->bundle ?? ($user->application_type == 'programs' ? $user->program_id: null)) == $bundleItem->id) selected @endif>
                                                        {{ $bundleItem->title }}</option>
                                                @endforeach

                                                {{-- Display bundles under subcategories --}}
                                                @foreach ($category->activeSubCategories as $subCategory)
                                                    @foreach ($subCategory->activeBundles as $bundleItem)
                                                        <option value="{{ $bundleItem->id }}"
                                                            has_certificate="{{ $bundleItem->has_certificate }}"
                                                            early_enroll="{{ $bundleItem->early_enroll }}"
                                                            @if (old('bundle_id', request()->bundle ?? ($user->application_type == 'programs' ? $user->program_id: null)) == $bundleItem->id) selected @endif>
                                                            {{ $bundleItem->title }}</option>
                                                    @endforeach
                                                @endforeach

                                            </optgroup>
                                        @endforeach

                                    </select>


                                    @error('bundle_id')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="d-none font-14 font-weight-bold mb-10 col-12" id="early_enroll"
                                    style="color: #5F2B80;">
                                    التسجيل متاح لهذا البرنامج للدفعة التاسعة، علمًا أن الدراسة في هذا البرنامج ستبدأ في يناير 2025 بإذن الله تعالى
                                </div>

                                {{-- certificate --}}
                                <div class="form-group col-12 text-light d-none" id="certificate_section">
                                    <label><span class="text-light">{{ trans('application_form.want_certificate') }} ؟ </span><span
                                            class="text-danger">*</span></label>
                                    <span class="text-danger font-12 font-weight-bold" id="certificate_message"> </span>
                                    @error('certificate')
                                        <div class="invalid-feedback d-block text-light">
                                            <span class="text-light">{{ $message }}</span>
                                        </div>
                                    @enderror
                                    <div class="row mr-5 mt-5">
                                        {{-- want certificate --}}
                                        <div class="col-sm-4 col text-light">
                                            <label for="want_certificate" class="text-light">
                                                <input type="radio" id="want_certificate" name="certificate"
                                                    value="1" onchange="showCertificateMessage()"
                                                    class=" @error('certificate') is-invalid @enderror"
                                                    {{ old('certificate', $student->certificate ?? null) === '1' ? 'checked' : '' }}>
                                                    <span class="text-light"> نعم ( ادفع الرسوم لاحقاً ) </span>
                                            </label>
                                        </div>

                                        {{-- does not want certificate --}}
                                        <div class="col">
                                            <label for="doesn't_want_certificate" >
                                                <input type="radio" id="doesn't_want_certificate" name="certificate"
                                                    onchange="showCertificateMessage()" value="0"
                                                    class="@error('certificate') is-invalid @enderror"
                                                    {{ old('certificate', $student->certificate ?? null) === '0' ? 'checked' : '' }}>
                                                <span class="text-light">لا</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-12 d-none text-light">
                                    <input type="checkbox" id="requirement_endorsement" name="requirement_endorsement">
                                    أقر بأني اطلعت على <a href="https://anasacademy.uk/admission/" target="_blank">متطلبات
                                        التسجيل</a> في البرنامج التدريبي الذي اخترته وأتعهد بتقديم كافة
                                    المتطلبات قبل التخرج.

                                    @error('requirement_endorsement')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                {{-- <div class="col-12 d-none mt-3">
                                    <input type="checkbox" id="register_endorsement" name="register_endorsement">

                                    أقر بأنني سألتزم بتسديد قيمة البرنامج المسجل به، في حال عدم التسديد فإن أكاديمية أنس
                                    للفنون البصرية تحتفظ بالحق في اتخاذ الإجراءات المناسبة التي قد تشمل إلغاء التسجيل أو فرض
                                    رسوم تأخير إضافية.

                                    @error('register_endorsement')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div> --}}


                            </section>

                            <h1 class=" mt-50 mb-25">بيانات المتدرب الأساسية</h1>


                            {{-- personal details --}}
                            <section>
                                <h2 class="form-main-title text-cyan">البيانات الشخصية</h2>
                                <section
                                    class="main-container border border-2 border-secondary-subtle rounded p-3 mt-2 mb-25 row mx-0">
                                    {{-- arabic name --}}
                                    <div class="form-group col-12 col-sm-6">
                                        <label for="name">{{ trans('application_form.name') }}<span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="name" name="ar_name" {{-- value="{{ $student ? $student->ar_name : '' }}" --}}
                                            value="{{ old('ar_name', $student ? $student->ar_name : $user->full_name ?? '') }}"
                                            placeholder="ادخل الإسم باللغه العربية فقط" required
                                            class="form-control @error('ar_name') is-invalid @enderror">

                                        @error('ar_name')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- english name --}}
                                    <div class="form-group col-12 col-sm-6">
                                        <label for="name_en">{{ trans('application_form.name_en') }}<span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="name_en" name="en_name" {{-- value="{{ $student ? $student->en_name : '' }}" --}}
                                            value="{{ old('en_name', $student ? $student->en_name : $user->en_name) }}"
                                            placeholder="ادخل الإسم باللغه الإنجليزيه فقط" required
                                            class="form-control @error('en_name') is-invalid @enderror">

                                        @error('en_name')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- identifier number --}}
                                    {{-- <div class="form-group col-12 col-sm-6">
                                        <label for="identifier_num">رقم الهوية الوطنية أو جواز السفر <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="identifier_num" name="identifier_num"

                                            value="{{ old('identifier_num', $student ? $student->identifier_num : '') }}"
                                            placeholder="الرجاء إدخال الرقم كامًلا والمكون من 10 أرقام للهوية أو 6 أرقام للجواز"
                                            required class="form-control  @error('identifier_num') is-invalid @enderror">

                                        @error('identifier_num')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div> --}}

                                    {{-- birthday --}}
                                    <div class="form-group col-12 col-sm-6">
                                        <label for="birthday">{{ trans('application_form.birthday') }}<span
                                                class="text-danger">*</span></label>
                                        <input type="date" id="birthday" name="birthdate" {{-- value="{{ $student ? $student->birthdate : '' }}" --}}
                                            value="{{ old('birthdate', $student ? $student->birthdate : '') }}" required
                                            class="form-control @error('birthdate') is-invalid @enderror">
                                        @error('birthdate')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                    </div>


                                    {{-- nationality --}}
                                    <div class="form-group col-12 col-sm-6">
                                        <label for="nationality">{{ trans('application_form.nationality') }}<span
                                                class="text-danger">*</span></label>
                                        @php
                                            $nationalities = [
                                                'سعودي/ة',
                                                'اماراتي/ة',
                                                'اردني/ة',
                                                'بحريني/ة',
                                                'جزائري/ة',
                                                'عراقي/ة',
                                                'مغربي/ة',
                                                'يمني/ة',
                                                'سوداني/ة',
                                                'صومالي/ة',
                                                'كويتي/ة',
                                                'سوري/ة',
                                                'لبناني/ة',
                                                'مصري/ة',
                                                'تونسي/ة',
                                                'فلسطيني/ة',
                                                'جيبوتي/ة',
                                                'عماني/ة',
                                                'موريتاني/ة',
                                                'قطري/ة',
                                            ];
                                        @endphp
                                        <select id="nationality" name="nationality" required
                                            class="form-control  @error('nationality') is-invalid @enderror"
                                            onchange="toggleNationality()">
                                            <option value="" class="placeholder" disabled>
                                                اختر جنسيتك</option>
                                            @foreach ($nationalities as $nationality)
                                                <option value="{{ $nationality }}"
                                                    {{ old('nationality', $student->nationality ?? null) == $nationality ? 'selected' : '' }}>
                                                    {{ $nationality }}</option>
                                            @endforeach
                                            <option value="اخرى" id="anotherNationality"
                                                {{ old('nationality') != '' && !in_array(old('nationality'), $nationalities) ? 'selected' : '' }}>
                                                اخرى</option>
                                        </select>
                                        @error('nationality')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>


                                    {{-- other nationality --}}
                                    <div class="form-group col-12 col-sm-6" id="other_nationality_section"
                                        style="display: none">
                                        <label for="nationality">ادخل الجنسية <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('other_nationality') is-invalid @enderror"
                                            id="other_nationality" name="other_nationality" placeholder="اكتب الجنسية"
                                            {{-- value="{{ $student ? $student->other_nationality : '' }}" --}}
                                            value="{{ old('other_nationality', $student ? $student->other_nationality : '') }}"
                                            onkeyup="setNationality()">

                                        @error('other_nationality')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- country --}}
                                    <div class="form-group col-12 col-sm-6">
                                        <label for="country">{{ trans('application_form.country') }}<span
                                                class="text-danger">*</span></label>
                                        @php
                                            $countries = [
                                                'السعودية',
                                                'الامارات العربية المتحدة',
                                                'الاردن',
                                                'البحرين',
                                                'الجزائر',
                                                'العراق',
                                                'المغرب',
                                                'اليمن',
                                                'السودان',
                                                'الصومال',
                                                'الكويت',
                                                'جنوب السودان',
                                                'سوريا',
                                                'لبنان',
                                                'مصر',
                                                'تونس',
                                                'فلسطين',
                                                'جزرالقمر',
                                                'جيبوتي',
                                                'عمان',
                                                'موريتانيا',
                                            ];
                                        @endphp
                                        <select id="mySelect" name="country" required
                                            class="form-control @error('country') is-invalid @enderror"
                                            onchange="toggleHiddenInputs()">
                                            <option value="" class="placeholder" disabled="">اختر دولتك</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country }}"
                                                    {{ old('country', $student->country ?? null) == $country ? 'selected' : '' }}>
                                                    {{ $country }}</option>
                                            @endforeach
                                            <option value="اخرى" id="anotherCountry"
                                                {{ old('country') != '' && !in_array(old('country'), $countries) ? 'selected' : '' }}>
                                                اخرى</option>

                                        </select>

                                        @error('country')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- other country --}}
                                    <div class="form-group col-12 col-sm-6" id="anotherCountrySection"
                                        style="display: none">
                                        <label for="city" class="form-label">ادخل البلد<span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="city" name="city"
                                            class="form-control  @error('city') is-invalid @enderror"
                                            placeholder="ادخل دولتك"
                                            value="{{ old('city', $student ? $student->city : '') }}"
                                            onkeyup="setCountry()">

                                        @error('city')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- region --}}
                                    <div class="form-group col-12 col-sm-6" id="region" style="display: none">
                                        <label for="area" class="form-label">المنطقة<span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="area" name="area"
                                            class="form-control  @error('area') is-invalid @enderror"
                                            placeholder="اكتب المنطقة"
                                            value="{{ old('area', $student ? $student->area : '') }}">

                                        @error('area')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- city --}}
                                    <div class="form-group col-12 col-sm-6">
                                        <div id="cityContainer">
                                            <label for="town"
                                                id="cityLabel">{{ trans('application_form.city') }}<span
                                                    class="text-danger">*</span></label>
                                            <input type="text" id="town" name="town"
                                                placeholder="اكتب مدينه السكن الحاليه"
                                                value="{{ old('town', $student ? $student->town : '') }}" required
                                                class="form-control @error('town') is-invalid @enderror">
                                        </div>
                                        @error('town')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- gender --}}
                                    <div class="form-group col-12 col-sm-6">
                                        <label for="gender">{{ trans('application_form.gender') }}<span
                                                class="text-danger">*</span></label>

                                        @error('gender')
                                            <div class="invalid-feedback d-inline">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                        <div class="row mr-5 mt-5">
                                            {{-- female --}}
                                            <div class="col-sm-4 col">
                                                <label for="female">
                                                    <input type="radio" id="female" name="gender" value="female"
                                                        class=" @error('gender') is-invalid @enderror" required
                                                        {{ old('gender', $student->gender ?? null) == 'female' ? 'checked' : '' }}>
                                                    انثي
                                                </label>
                                            </div>

                                            {{-- male --}}
                                            <div class="col">
                                                <label for="male">
                                                    <input type="radio" id="male" name="gender" value="male"
                                                        class=" @error('gender') is-invalid @enderror" required
                                                        {{ old('gender', $student->gender ?? null) == 'male' ? 'checked' : '' }}>
                                                    ذكر
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </section>


                            {{-- about us --}}
                            <div class="form-group col-12">
                                <label class="text-light">{{ trans('application_form.heard_about_us') }}<span
                                        class="text-danger">*</span></label>

                                @error('about_us')
                                    <div class="invalid-feedback d-inline text-light">
                                        {{ $message }}
                                    </div>
                                @enderror


                                <br>

                                <label for="snapchat" class="text-light">
                                    <input type="radio" id="snapchat" name="about_us" required value="snapchat"
                                        class=" @error('about_us') is-invalid @enderror"
                                        {{ old('about_us', $student->about_us ?? null) == 'snapchat' ? 'checked' : '' }}>
                                    {{ trans('application_form.snapchat') }}
                                </label><br>
                                <label for="twitter" class="text-light">
                                    <input type="radio" id="twitter" name="about_us" required value="twitter"
                                        class=" @error('about_us') is-invalid @enderror"
                                        {{ old('about_us', $student->about_us ?? null) == 'twitter' ? 'checked' : '' }}>
                                    {{ trans('application_form.twitter') }}
                                </label><br>
                                <label for="friend" class="text-light">
                                    <input type="radio" id="friend" name="about_us" required value="friend"
                                        class=" @error('about_us') is-invalid @enderror"
                                        {{ old('about_us', $student->about_us ?? null) == 'friend' ? 'checked' : '' }}>
                                    {{ trans('application_form.friend') }}
                                </label><br>
                                <label for="instagram">
                                    <input type="radio" id="instagram" name="about_us" required value="instagram"
                                        class=" @error('about_us') is-invalid @enderror"
                                        {{ old('about_us', $student->about_us ?? null) == 'instagram' ? 'checked' : '' }}>
                                    {{ trans('application_form.instagram') }}
                                </label><br>
                                <label for="facebook">
                                    <input type="radio" id="facebook" name="about_us" required value="facebook"
                                        class=" @error('about_us') is-invalid @enderror"
                                        {{ old('about_us', $student->about_us ?? null) == 'facebook' ? 'checked' : '' }}>
                                    {{ trans('application_form.facebook') }}
                                </label><br>
                                <label for="other">
                                    <input type="radio" id="other" name="about_us" required value="other"
                                        class=" @error('about_us') is-invalid @enderror"
                                        {{ old('about_us', $student->about_us ?? null) == 'other' ? 'checked' : '' }}>
                                    {{ trans('application_form.other') }}
                                </label><br>
                                <label id="otherLabel"style="display:none">أدخل المصدر <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="otherInput" placeholder="" name="other_about_us"
                                    class="form-control @error('about_us') is-invalid @enderror"
                                    style="display:none"><br>


                                <label>
                                    <input type="checkbox" id="terms" name="terms" required
                                        class="@error('terms') is-invalid @enderror">

                                    اقر أنا المسجل بياناتي اعلاه بموافقتي على لائحة الحقوق والوجبات واحكام وشروط
                                    القبول
                                    والتسجيل، كما أقر بالتزامي التام بمضمونها، وبمسؤوليتي التامة عن أية مخالفات قد
                                    تصدر مني لها
                                    ، مما يترتب عليه كامل الأحقية للاكاديمية في مسائلتي عن تلك المخالفات والتصرفات
                                    المخالفة
                                    للوائح المشار إليها في عقد اتفاقية التحاق متدربـ/ـة <a target="_blank"
                                        href="https://anasacademy.uk/wp-content/uploads/2024/05/contract.pdf">انقر
                                        هنا
                                        لمشاهدة</a>

                                </label>

                                @error('terms')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- display errors --}}
                            {{-- @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif --}}

                            <input type="hidden" id="direct_register" name="direct_register" value="">
                            <button type="button" id="form_button" class="btn btn-primary">تسجيل </button>

                            <button type="submit" class="btn btn-acadima-primary mr-3 " id="formSubmit">
                                تسجيل
                            </button>
                        </form>
                    </div>
                </Section>
            </div>


        </div>
    </div>
@endsection
@php
    $bundlesByCategory = [];
    foreach ($categories as $item) {
        $bundlesByCategory[$item->id] = $item->activeBundles;
    }
@endphp
@push('scripts_bottom')
    <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="/assets/default/vendors/owl-carousel2/owl.carousel.min.js"></script>
    <script src="/assets/default/vendors/parallax/parallax.min.js"></script>
    <script src="/assets/default/js/parts/home.min.js"></script>

    {{-- submit form --}}
    <script>
        let form = document.getElementById('myForm');
        let formButton = document.getElementById('form_button');
        let directRegisterInput = document.getElementById('direct_register');
        directRegisterInput.value = "";
        formButton.onclick = function() {
            directRegisterInput.value = true;
            if (form.checkValidity()) {
                form.submit();
            } else {
                console.log("form failed");
                var invalidFields = form.querySelectorAll(':invalid');
                if (invalidFields.length > 0) {
                    console.log(invalidFields[0]);
                    // Focus on the first invalid field
                    invalidFields[0].focus();
                    // Optionally scroll the field into view
                    invalidFields[0].scrollIntoView({
                        behavior: 'smooth'
                    });
                    invalidFields[0].reportValidity(); // Triggers the display of the built-in validation message

                }
            }
        }
    </script>



    {{-- about us script --}}
    <script>
        var otherLabel = document.getElementById("otherLabel");
        var otherInput = document.getElementById("otherInput");

        var radioButtons = document.querySelectorAll('input[name="about_us"]');

        radioButtons.forEach(function(radioButton) {
            radioButton.addEventListener("change", function() {
                if (radioButton.id === "other" && radioButton.checked) {

                    otherLabel.style.display = "block";
                    otherInput.style.display = "block";
                    otherInput.setAttribute('required', 'required');
                    radioButton.value = otherInput.value;
                } else {
                    otherLabel.style.display = "none";
                    otherInput.style.display = "none";
                    otherInput.removeAttribute('required');
                }
            });
        });

        otherInput.addEventListener("change", function() {
            let radioButton = document.getElementById('other');
            radioButton.value = otherInput.value;
        })
    </script>

    {{-- bundle toggle and education section toggle --}}
    <script>
        function toggleHiddenInput() {
            var bundles = @json($bundlesByCategory);
            var select = document.getElementById("mySelect1");
            var hiddenInput = document.getElementById("bundle_id");
            var hiddenLabel = document.getElementById("hiddenLabel1");
            let education = document.getElementById("education");
            let high_education = document.getElementsByClassName("high_education");
            let secondary_education = document.getElementsByClassName("secondary_education");


            if (select.value && hiddenLabel && hiddenInput) {

                var categoryId = select.value;
                var categoryBundles = bundles[categoryId];

                if (categoryBundles) {
                    var options = categoryBundles.map(function(bundle) {
                        var isSelected = bundle.id == "{{ old('bundle_id', request()->bundle) }}" ?
                            'selected' : '';
                        return `<option value="${bundle.id}" ${isSelected} has_certificate="${bundle.has_certificate}" early_enroll="${bundle.early_enroll}">${bundle.title}</option>`;
                    }).join('');

                    hiddenInput.outerHTML =
                        '<select id="bundle_id" name="bundle_id"  class="form-control" onchange="CertificateSectionToggle()" >' +
                        '<option value="" class="placeholder" selected hidden>اختر التخصص الذي تود دراسته في اكاديما</option>' +
                        options +
                        '</select>';
                    hiddenLabel.style.display = "block";
                    hiddenLabel.closest('div').classList.remove('d-none');
                } else {
                    hiddenInput.outerHTML =
                        '<select id="bundle_id" name="bundle_id"  class="form-control" onchange="CertificateSectionToggle()" >' +
                        '<option value="" class="placeholder" selected hidden >اختر التخصص الذي تود دراسته في اكاديما</option> </select>';
                    hiddenLabel.style.display = "none";
                    hiddenLabel.closest('div').classList.add('d-none');
                }
                var selectedOption = select.options[select.selectedIndex];
                var selectedText = selectedOption.textContent;
                if (!isNaN(select.value) && !isNaN(parseInt(select.value))) {
                    education.style.display = "block";
                    document.getElementById('educational_area').setAttribute('required', 'required');
                }



                if (selectedOption.getAttribute('education') == "0") {

                    secondary_education.forEach(function(element) {
                        element.style.display = "block";
                    });

                    // Select all inputs within elements having the class 'secondary_education'
                    var inputs = document.querySelectorAll('.secondary_education input');
                    inputs.forEach(function(input) {
                        input.setAttribute('required', 'required');
                    });

                    // hidding high education field
                    high_education.forEach(function(element) {
                        element.style.display = "none";
                    });

                    var inputs = document.querySelectorAll('.high_education input');
                    inputs.forEach(function(input) {
                        input.removeAttribute('required');
                    });

                } else if (selectedOption.getAttribute('education') == "1") {
                    secondary_education.forEach(function(element) {
                        element.style.display = "none";
                    });

                    var inputs = document.querySelectorAll('.secondary_education input');
                    inputs.forEach(function(input) {
                        input.removeAttribute('required');
                    });

                    high_education.forEach(function(element) {
                        element.style.display = "block";
                    });

                    var inputs = document.querySelectorAll('.high_education input');
                    inputs.forEach(function(input) {
                        input.setAttribute('required', 'required');
                    });



                }

            } else {
                hiddenInput.outerHTML =
                    '<select id="bundle_id" name="bundle_id"  class="form-control" onchange="CertificateSectionToggle()" >' +
                    '<option value="" class="placeholder" selected hidden >اختر التخصص الذي تود دراسته في اكاديما</option> </select>';
                hiddenLabel.style.display = "none";
                hiddenLabel.closest('div').classList.add('d-none');

                // CertificateSectionToggle();
            }
        }


        // toggleHiddenInput();
    </script>

    {{-- type toggle --}}
    <script>
        function toggleHiddenType() {
            var select = document.getElementById("typeSelect");
            // var hiddenDiplomaInput = document.getElementById("mySelect1");
            var hiddenDiplomaLabel = document.getElementById("degree");
            var hiddenBundleInput = document.getElementById("bundle_id");
            var hiddenDiplomaLabel1 = document.getElementById("hiddenLabel1");
            let certificateSection = document.getElementById("certificate_section");
            let diplomasSection = document.getElementById("diplomas_section");
            let education = document.getElementById("education");

            var hiddenCourseInput = document.getElementById("mySelect2");
            var hiddenCourseLabel = document.getElementById("all_course");

            let formButton = document.getElementById('form_button');
            let formSubmit = document.getElementById('formSubmit');


            if (select) {
                var type = select.value;
                if (type == 'programs') {
                    diplomasSection.classList.remove('d-none');
                    hiddenCourseInput.closest('div').classList.add('d-none');
                    formSubmit.innerHTML = " حجز مقعد";
                    formButton.classList.remove('d-none');
                    resetSelect(hiddenCourseInput);

                } else if (type == 'courses') {
                    hiddenCourseInput.closest('div').classList.remove('d-none');
                    diplomasSection.classList.add('d-none');
                    // resetSelect(hiddenDiplomaInput);
                    resetSelect(hiddenBundleInput);
                    formSubmit.innerHTML = "تسجيل";
                    formButton.classList.add('d-none');

                } else {
                    diplomasSection.classList.add('d-none');
                    hiddenCourseInput.closest('div').classList.add('d-none');
                    formSubmit.innerHTML = "تسجيل";
                    formButton.classList.add('d-none');
                    // resetSelect(hiddenDiplomaInput);
                    resetSelect(hiddenBundleInput);
                    resetSelect(hiddenCourseInput);
                    // education.classList.add('d-none');
                }

                // toggleHiddenInput();
                coursesToggle();
                CertificateSectionToggle();
            }
        }

        toggleHiddenType();

        function resetSelect(selector) {
            selector.selectedIndex = 0; // This sets the first option as selected
        }

        function coursesToggle() {
            console.log('course');
            let courseEndorsementInput = document.getElementById("course_endorsement");
            let courseEndorsementInput2 = document.getElementById("course_endorsement2");
            let courseEndorsementSection = courseEndorsementInput.closest("div");
            var courseSelect = document.getElementById("mySelect2");
            if (courseSelect.selectedIndex == 1) {
                courseEndorsementSection.classList.remove("d-none");
                courseEndorsementInput.setAttribute("required", "required");
                courseEndorsementInput2.setAttribute("required", "required");
            } else {
                courseEndorsementSection.classList.add("d-none");
                courseEndorsementInput.removeAttribute("required");
                courseEndorsementInput2.removeAttribute("required");
            }
        }
    </script>



    {{-- Certificate Section Toggle --}}
    <script>
        function CertificateSectionToggle() {
            let certificateSection = document.getElementById("certificate_section");
            let earlyEnroll = document.getElementById("early_enroll");
            let bundleSelect = document.getElementById("bundle_id");
            let certificateInputs = document.querySelectorAll("input[name='certificate']");

            console.log('index: ', bundleSelect.selectedIndex);
            console.log(bundleSelect.options[bundleSelect.selectedIndex]);
            console.log(bundleSelect);
            // Get the selected option
            var selectedOption = bundleSelect.options[bundleSelect.selectedIndex];
            if (selectedOption.getAttribute('has_certificate') == 1) {
                certificateSection.classList.remove("d-none");


                certificateInputs.forEach(function(element) {
                    element.setAttribute("required", "required");
                });
            } else {
                certificateSection.classList.add("d-none");

                certificateInputs.forEach(function(element) {
                    element.removeAttribute("required", "required");
                });

            }

            if (selectedOption.getAttribute('early_enroll') == 1) {

                earlyEnroll.classList.remove("d-none");

            } else {
                earlyEnroll.classList.add("d-none");
            }

            let RequirementEndorsementInput = document.getElementById("requirement_endorsement");
            let RequirementEndorsementSection = RequirementEndorsementInput.closest("div");
            if (bundleSelect.selectedIndex != 0) {
                RequirementEndorsementSection.classList.remove("d-none");
                RequirementEndorsementInput.setAttribute("required", "required");
            } else {
                RequirementEndorsementSection.classList.add("d-none");
                RequirementEndorsementInput.removeAttribute("required");
            }

            // let registerEndorsementInput = document.getElementById("register_endorsement");
            // let registerEndorsementSection = registerEndorsementInput.closest("div");
            // if (bundleSelect.selectedIndex != 0) {
            //     registerEndorsementSection.classList.remove("d-none");
            //     registerEndorsementInput.setAttribute("required", "required");
            // } else {
            //     registerEndorsementSection.classList.add("d-none");
            //     registerEndorsementInput.removeAttribute("required");
            // }
        }

        CertificateSectionToggle();
    </script>

    {{-- certificate message  --}}
    <script>
        function showCertificateMessage(event) {

            let messageSection = document.getElementById("certificate_message");
            let certificateOption = document.querySelector("input[name='certificate']:checked");
            if (certificateOption.value === "1") {

                messageSection.innerHTML = "سوف تحصل على خصم 23%"
            } else if (certificateOption.value === "0") {
                messageSection.innerHTML = "بيفوتك الحصول على خصم 23%"

            } else {
                messageSection.innerHTML = ""

            }
        }



        showCertificateMessage();
    </script>



    {{-- city and country toggle --}}
    <script>
        function toggleHiddenInputs() {
            var select = document.getElementById("mySelect");
            var hiddenInput = document.getElementById("area");
            var hiddenLabel = document.getElementById("hiddenLabel");
            var hiddenInput2 = document.getElementById("city");
            var hiddenLabel2 = document.getElementById("hiddenLabel2");
            var cityLabel = document.getElementById("cityLabel");
            var town = document.getElementById("town");
            var anotherCountrySection = document.getElementById("anotherCountrySection");
            var region = document.getElementById("region");
            let anotherCountryOption = document.getElementById("anotherCountry");

            if (select && select.value !== "السعودية") {
                region.style.display = "block";
            } else {
                region.style.display = "none";
            }

            if (select.value === "اخرى") {
                anotherCountrySection.style.display = "block";
                anotherCountryOption.value = hiddenInput2.value;
            } else {
                anotherCountrySection.style.display = "none";
                anotherCountryOption.value = "اخرى";

            }
            if (select && cityLabel && town) {
                if (select.value === "السعودية") {
                    town.outerHTML = '<select id="town" name="town"  class="form-control" required>' +
                        '<option value="الرياض" selected="selected">الرياض</option>' +
                        '<option value="جده">جده </option>' +
                        '<option value="مكة المكرمة">مكة المكرمة</option>' +
                        '<option value="المدينة المنورة">المدينة المنورة</option>' +
                        '<option value="الدمام">الدمام</option>' +
                        '<option value="الطائف">الطائف</option>' +
                        '<option value="تبوك">تبوك</option>' +
                        '<option value="الخرج">الخرج</option>' +
                        '<option value="بريدة">بريدة</option>' +
                        '<option value="خميس مشيط">خميس مشيط</option>' +
                        '<option value="الهفوف">الهفوف</option>' +
                        '<option value="المبرز">المبرز</option>' +
                        '<option value="حفر الباطن">حفر الباطن</option>' +
                        '<option value="حائل">حائل</option>' +
                        '<option value="نجران">نجران</option>' +
                        '<option value="الجبيل">الجبيل</option>' +
                        '<option value="أبها">أبها</option>' +
                        '<option value="ينبع">ينبع</option>' +
                        '<option value="الخبر">الخبر</option>' +
                        '<option value="عنيزة">عنيزة</option>' +
                        '<option value="عرعر">عرعر</option>' +
                        '<option value="سكاكا">سكاكا</option>' +
                        '<option value="جازان">جازان</option>' +
                        '<option value="القريات">القريات</option>' +
                        '<option value="الظهران">الظهران</option>' +
                        '<option value="القطيف">القطيف</option>' +
                        '<option value="الباحة">الباحة</option>' +
                        '</select>';
                } else {

                    town.outerHTML =
                        `<input type="text" id="town" name="town" placeholder="اكتب مدينه السكن الحاليه" class="form-control" value="{{ old('town', $student ? $student->town : '') }}" >`;
                }
            }
        }


        function setCountry() {
            let anotherCountrySection = document.getElementById("anotherCountrySection");
            let anotherCountryOption = document.getElementById("anotherCountry");
            let another_country = document.getElementById("city");

            if (anotherCountrySection.style.display != "none") {
                // nationality.value = other_nationality.value;
                anotherCountryOption.value = another_country.value;

            }
        }
        toggleHiddenInputs();
    </script>


    {{-- nationality toggle --}}
    <script>
        function toggleNationality() {
            let other_nationality_section = document.getElementById("other_nationality_section");
            let nationality = document.getElementById("nationality");
            let other_nationality = document.getElementById("other_nationality");
            let anotherNationalityOption = document.getElementById("anotherNationality");
            if (nationality && nationality.value == "اخرى") {
                other_nationality_section.style.display = "block";

                // nationality.value = other_nationality.value;
                anotherNationalityOption.value = other_nationality.value;
            } else {
                other_nationality_section.style.display = "none";
                anotherNationalityOption.value = "اخرى";
            }
        }

        function setNationality() {
            let other_nationality_section = document.getElementById("other_nationality_section");
            let nationality = document.getElementById("nationality");
            let other_nationality = document.getElementById("other_nationality");
            let anotherNationalityOption = document.getElementById("anotherNationality");
            if (other_nationality_section.style.display != "none") {
                // nationality.value = other_nationality.value;
                anotherNationalityOption.value = other_nationality.value;

            }
        }
    </script>
@endpush
