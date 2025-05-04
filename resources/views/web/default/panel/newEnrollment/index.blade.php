@extends(getTemplate() . '.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/owl-carousel2/owl.carousel.min.css">
    <style>
        .container_form {
            margin-top: 20px;
            /* border: 1px solid #ddd; */
            /* Add border to the container */
            padding: 20px;
            /* Optional: Add padding for spacing */
            border-radius: 10px !important;
            /* box-shadow: 2px 5px 10px #ddd; */
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
            color: #5F2B80;
        }

        a {
            color: #CCF5FF;
        }

        #formSubmit {
            /* background: #5F2B80 !important; */

            /* background-color: var(--acadima-cyan) !important; */

        }


        .form-main-title {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 400;
            font-size: 32px;
            line-height: 39px;
            color: #5E0A83;
        }

        .form-title {


            font-family: 'IBM Plex Sans';
            font-style: normal;
            font-weight: 700;
            font-size: 32px;
            line-height: 42px;
            color: #000000;

        }

        input {
            text-align: right;
        }

        .main-section {
            background-color: #F6F7F8;
        }

        .main-container {
            border-width: 2px !important;
        }

        .secondary_education,
        .high_education,
        #education {
            display: none;
        }

        .hero {
            width: 100%;
            height: 80vh;
            /* background-color: #ED1088; */
            background-image: linear-gradient(90deg, #5E0A83 19%, #F70387 100%);
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
                font-size: 25px;
            }


        }
    </style>
@endpush

@section('content')
    <div class="application container-fluid">
        <div class="col-12 px-0">
            <div class="col-lg-12 px-0">
                <Section class="section1 main-section p-lg-40 pt-40 p-0 bg-transparent shadow border">
                    <h2 class="section-title text-pink">
                        <!-- {{ trans('panel.new_rgstr') }} -->
                          Enrol to a new program registration request
                    </h2>
                    <div class="container_form bg-secondary-acadima">
                        <form action="/apply" method="POST" id="myForm">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">

                            {{-- application type --}}
                            {{-- <div class="form-group col-12 col-sm-6 text-light">
                                <label class="form-label">{{ trans('panel.select_application_type') }}<span
                                        class="text-danger">*</span></label>
                                <select id="typeSelect" name="type" required
                                    class="form-control @error('type') is-invalid @enderror" onchange="toggleHiddenType()">
                                    <option selected hidden value="">
                                        {{ trans('panel.choose_application_type') }}
                                    </option>
                                    @if (count($categories) > 0)
                                        <option value="programs" @if (old('type', request()->type) == 'programs') selected @endif>
                                            {{trans('panel.professional_programs')}}
                                        </option>
                                    @endif
                                    <option value="courses" @if (old('type', request()->type) == 'courses') selected @endif>
                                        {{trans('panel.training_courses')}}
                                    </option>
                                </select>

                                @error('type')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div> --}}

                            {{-- course --}}
                            {{-- <div class="form-group col-12 col-sm-6 text-light">
                                <label for="application2" class="form-label" id="all_course">{{trans('panel.training_courses')}}<span
                                        class="text-danger">*</span></label>
                                <select id="mySelect2" name="webinar_id" onchange="coursesToggle()"
                                    class="form-control @error('webinar_id') is-invalid @enderror">
                                    <option selected hidden value="">
                                        {{ trans('panel.choose_training_course') }}
                                    </option>

                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}"
                                            @if (old('webinar_id', request()->webinar) == $course->id) selected @endif>
                                            {{ $course->title }} </option>
                                    @endforeach

                                </select>

                                @error('webinar_id')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div> --}}

                            {{-- course endorsement --}}
                            <div class="col-12 d-none">
                                <input type="checkbox" id="course_endorsement" name="course_endorsement">
                                <span class="text-light">
                                    {{ trans('panel.acknowledge_experience') }}
                                </span>
                                @error('course_endorsement')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="mt-3">
                                    <input type="checkbox" id="course_endorsement2">
                                    <span class="text-light">
                                        {{ trans('panel.acknowledge_time_limit') }}
                                    </span>
                                </div>
                            </div>

                            {{-- diplomas --}}
                            <section id="diplomas_section">
                                {{-- diploma --}}

                                {{-- specialization --}}
                                <div class="form-group col-12 col-sm-6 text-dark">
                                    <label for="bundle_id">
                                        {{ trans('panel.program') }}<span class="text-danger">*</span>
                                    </label>
                                    {{-- <input type="text" id="bundle_id" name="bundle_id"
                                        class="form-control @error('bundle_id') is-invalid @enderror"
                                        value="{{ old('bundle_id', $bundle ? $bundle->id : '') }}"> --}}

                                    <select id="bundle_id" class="custom-select  @error('bundle_id')  is-invalid @enderror"
                                        name="bundle_id" onchange="CertificateSectionToggle()">
                                        <option selected hidden value="">
                                            {{ trans('panel.choose_program') }}
                                        </option>

                                        {{-- Loop through top-level categories --}}
                                        @foreach ($categories as $category)
                                            <optgroup label="{{ $category->title }}">

                                                {{-- Display bundles directly under the current category --}}
                                                @foreach ($category->activeBundles as $bundleItem)
                                                    <option value="{{ $bundleItem->id }}"
                                                        has_certificate="{{ $bundleItem->has_certificate }}"
                                                        early_enroll="{{ $bundleItem->early_enroll }}"
                                                        @if (old('bundle_id', $bundle->id ?? null) == $bundleItem->id) selected @endif>
                                                        {{ $bundleItem->title }}</option>
                                                @endforeach

                                                {{-- Display bundles under subcategories --}}
                                                @foreach ($category->activeSubCategories as $subCategory)
                                                    @foreach ($subCategory->activeBundles as $bundleItem)
                                                        <option value="{{ $bundleItem->id }}"
                                                            has_certificate="{{ $bundleItem->has_certificate }}"
                                                            early_enroll="{{ $bundleItem->early_enroll }}"
                                                            @if (old('bundle_id', $bundle->id ?? null) == $bundleItem->id) selected @endif>
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
                                    {{ trans('panel.registration_available') }}
                                </div>

                                {{-- certificate --}}
                                <div class="form-group col-12  d-none" id="certificate_section">
                                    <label class="text-light">{{ trans('application_form.want_certificate') }} ؟ <span
                                            class="text-danger">*</span></label>
                                    <span class="text-danger font-12 font-weight-bold text-light" id="certificate_message">
                                    </span>
                                    @error('certificate')
                                        <div class="invalid-feedback d-block text-light">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    <div class="row mr-5 mt-5">
                                        {{-- want certificate --}}
                                        <div class="col-sm-4 col">
                                            <label for="want_certificate">
                                                <input type="radio" id="want_certificate" name="certificate"
                                                    value="1" onchange="showCertificateMessage()"
                                                    class=" @error('certificate') is-invalid @enderror"
                                                    {{ old('certificate', $student->certificate ?? null) === '1' ? 'checked' : '' }}>
                                                <span class="text-light">{{ trans('public.yes') }} (
                                                    {{ trans('panel.pay_later') }} )</span>
                                            </label>
                                        </div>

                                        {{-- does not want certificate --}}
                                        <div class="col">
                                            <label for="doesn't_want_certificate">
                                                <input type="radio" id="doesn't_want_certificate" name="certificate"
                                                    onchange="showCertificateMessage()" value="0"
                                                    class="@error('certificate') is-invalid @enderror"
                                                    {{ old('certificate', $student->certificate ?? null) === '0' ? 'checked' : '' }}>
                                                <span class="text-light">{{ trans('public.none') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-none col-12">
                                    <!--
                                    <input type="checkbox" id="requirement_endorsement" name="requirement_endorsement">
                                    <span class="text-light">
                                        {{ trans('panel.acknowledge_terms') }}
                                        <a class="text-cyan" href="https://anasacademy.uk/admission/" target="_blank">
                                            {{ trans('panel.registration_requirements') }}
                                        </a>
                                        {{ trans('panel.commit_to_requirements') }}

                                    </span>

                                    @error('requirement_endorsement')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    -->
                                </div>
                            </section>

                            <label class="mt-30 col-12 text-dark">
                                <input type="checkbox" id="terms" name="terms" required>
                                <!--{{ trans('application_form.agree_terms_conditions') }}-->
                                <!-- {{ trans('panel.confirm_registration_data') }} -->
                                {{ trans('panel.confirmation_agreement') }}
                                <!-- <a target="_blank" class="text-cyan"
                                                href="https://anasacademy.uk/wp-content/uploads/2024/02/Contract.pdf">
                                                {{ trans('panel.click_here_to_view') }}
                                            </a> -->

                            </label>
                            <div class="col-12 mt-3">
                                <input type="hidden" id="direct_register" name="direct_register" value="">
                                <button type="button" id="form_button"
                                    class="btn btn-acadima-primary ">{{ trans('panel.register') }} </button>

                               <!-- <button type="submit" class="btn btn-acadima-primary mr-3" id="formSubmit">
                                    {{ trans('panel.seat_reservation_label') }}
                                </button>  -->

                            </div>
                        </form>
                    </div>


                    {{-- display errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </Section>
            </div>


        </div>
    </div>
@endsection

@php
    $bundlesByCategory = [];
    foreach ($categories as $item) {
        $bundlesByCategory[$item->id] = $item->bundles;
    }
@endphp

@push('scripts_bottom')
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script>
        var undefinedActiveSessionLang = '{{ trans('webinars.undefined_active_session') }}';
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var selectChapterLang = '{{ trans('update.select_chapter') }}';
    </script>

    <script src="/assets/default/js/panel/make_next_session.min.js"></script>

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
                        var isSelected = bundle.id == "{{ old('bundle_id', $student->bundle_id ?? null) }}" ?
                            'selected' : '';
                        return `<option value="${bundle.id}" ${isSelected} has_certificate="${bundle.has_certificate}" early_enroll="${bundle.early_enroll}">${bundle.title}</option>`;
                    }).join('');

                    hiddenInput.outerHTML =
                        '<select id="bundle_id" name="bundle_id"  class="form-control" onchange="CertificateSectionToggle()" >' +
                        '<option value="" class="placeholder" selected hidden >{{ trans('panel.choose_specialization') }}</option>' +
                        options +
                        '</select>';
                    hiddenLabel.style.display = "block";
                    hiddenLabel.closest('div').classList.remove('d-none');
                } else {
                    hiddenInput.outerHTML =
                        '<select id="bundle_id" name="bundle_id"  class="form-control" onchange="CertificateSectionToggle()" >' +
                        '<option value="" class="placeholder" selected hidden >{{ trans('panel.choose_specialization') }}</option> </select>';
                    hiddenLabel.style.display = "none";
                    hiddenLabel.closest('div').classList.add('d-none');
                }
            } else {
                hiddenInput.outerHTML =
                    '<select id="bundle_id" name="bundle_id"  class="form-control" onchange="CertificateSectionToggle()" >' +
                    '<option value="" class="placeholder" selected hidden >{{ trans('panel.choose_specialization') }}</option> </select>';
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
            var hiddenDiplomaInput = document.getElementById("mySelect1");
            var hiddenDiplomaLabel = document.getElementById("degree");
            var hiddenBundleInput = document.getElementById("bundle_id");
            var hiddenDiplomaLabel1 = document.getElementById("hiddenLabel1");
            let certificateSection = document.getElementById("certificate_section");
            let diplomasSection = document.getElementById("diplomas_section");
            //let RequirementEndorsementInput = document.getElementById("requirement_endorsement");

            var hiddenCourseInput = document.getElementById("mySelect2");
            var hiddenCourseLabel = document.getElementById("all_course");

            let formButton = document.getElementById('form_button');
            let formSubmit = document.getElementById('formSubmit');

            if (select) {
                var type = select.value;
                console.log(type);

                if (type == 'programs') {
                    diplomasSection.classList.remove('d-none');
                    hiddenCourseInput.closest('div').classList.add('d-none');
                    formSubmit.innerHTML = " {{ trans('panel.reserve_seat') }}";
                    formButton.classList.remove('d-none');
                    resetSelect(hiddenCourseInput);
                } else if (type == 'courses') {
                    hiddenCourseInput.closest('div').classList.remove('d-none');
                    diplomasSection.classList.add('d-none');
                    formSubmit.innerHTML = "{{ trans('panel.register') }}";
                    formButton.classList.add('d-none');
                    // resetSelect(hiddenDiplomaInput);
                    resetSelect(hiddenBundleInput);

                } else {
                    diplomasSection.classList.add('d-none');
                    hiddenCourseInput.closest('div').classList.add('d-none');
                    formSubmit.innerHTML = "{{ trans('panel.register') }}";
                    formButton.classList.add('d-none');
                    // resetSelect(hiddenDiplomaInput);
                    resetSelect(hiddenBundleInput);
                    resetSelect(hiddenCourseInput);
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

            // Get the selected option
            // var selectedOption = bundleSelect.options ? bundleSelect.options[bundleSelect.selectedIndex] :  bundleSelect.options[0];
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

            /*let RequirementEndorsementInput = document.getElementById("requirement_endorsement");
            let RequirementEndorsementSection = RequirementEndorsementInput.closest("div");
            if (bundleSelect.selectedIndex != 0) {
                RequirementEndorsementSection.classList.remove("d-none");
                RequirementEndorsementInput.setAttribute("required", "required");
            } else {
                RequirementEndorsementSection.classList.add("d-none");
                RequirementEndorsementInput.removeAttribute("required");
            }
            */

        }

        CertificateSectionToggle();
    </script>
    {{-- certificate message  --}}
    <script>
        function showCertificateMessage(event) {

            let messageSection = document.getElementById("certificate_message");
            let certificateOption = document.querySelector("input[name='certificate']:checked");
            if (certificateOption.value === "1") {

                messageSection.innerHTML = "{{ trans('panel.you_will_get_23_discount') }}"
            } else if (certificateOption.value === "0") {
                messageSection.innerHTML = "{{ trans('panel.you_may_get_23_discount') }}"

            } else {
                messageSection.innerHTML = ""

            }
        }



        showCertificateMessage();
    </script>
@endpush
