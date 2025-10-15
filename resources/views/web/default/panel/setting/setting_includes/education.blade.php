@php
    $countries = [
        trans('panel.saudi_arabia'),
        trans('panel.united_arab_emirates'),
        trans('panel.jordan'),
        trans('panel.bahrain'),
        trans('panel.algeria'),
        trans('panel.iraq'),
        trans('panel.morocco'),
        trans('panel.yemen'),
        trans('panel.sudan'),
        trans('panel.somalia'),
        trans('panel.kuwait'),
        trans('panel.south_sudan'),
        trans('panel.syria'),
        trans('panel.lebanon'),
        trans('panel.egypt'),
        trans('panel.tunisia'),
        trans('panel.palestine'),
        trans('panel.comoros'),
        trans('panel.djibouti'),
        trans('panel.oman'),
        trans('panel.mauritania'),
    ];

    $user = auth()->user();
    $student = $user->student;
@endphp

<section class="mt-30">
    <div class="d-flex justify-content-between align-items-center mb-10">
        <h2 class="section-title after-line">{{ trans('site.education') }}</h2>
        {{-- <button id="userAddEducations" type="button" class="btn btn-primary btn-sm">{{ trans('site.add_education') }}</button> --}}
    </div>

    <div id="userListEducations">

        {{-- @if (!empty($educations) and !$educations->isEmpty())
            @foreach ($educations as $education)
                <div class="row mt-20">
                    <div class="col-12">
                        <div class="education-card py-15 py-lg-30 px-10 px-lg-25 rounded-sm panel-shadow bg-white d-flex align-items-center justify-content-between">
                            <div class="col-8 text-secondary text-left font-weight-500 education-value">{{ $education->value }}</div>
                            <div class="col-2 text-right">
                                <div class="btn-group dropdown table-actions">
                                    <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i data-feather="more-vertical" height="20"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <button type="button" data-education-id="{{ $education->id }}" data-user-id="{{ (!empty($user) and empty($new_user)) ? $user->id : '' }}" class="d-block btn-transparent edit-education">{{ trans('public.edit') }}</button>
                                        <a href="/panel/setting/metas/{{ $education->id }}/delete?user_id={{ (!empty($user) and empty($new_user)) ? $user->id : '' }}" class="delete-action d-block mt-10 btn-transparent">{{ trans('public.delete') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else

            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'edu.png',
                'title' => trans('auth.education_no_result'),
                'hint' => trans('auth.education_no_result_hint'),
            ])
        @endif --}}

        <div class="row mt-20">
            <div class="col-12 ">
                <div
                    class="education-card py-15 py-lg-30 px-10 px-lg-25 rounded-sm panel-shadow bg-secondary-acadima d-flex align-items-center justify-content-between">


                    <div class="accordion col-12" id="accordionExample">

                        {{-- high education --}}
                        <div class="card bg-secondary-acadima">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left text-pink" type="button"
                                        data-toggle="collapse" data-target="#collapseOne" aria-expanded="true"
                                        aria-controls="collapseOne">
                                        {{ trans('public.university_education') }}
                                    </button>
                                </h2>
                            </div>

                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                                data-parent="#accordionExample">
                                <div class="card-body row">

                                    {{-- certificate_type --}}
                                    <div class="form-group col-12">

                                        <label for="certificate_type" class="form-label high_education">
                                            {{ trans('public.university_education') }}
                                            <span class="text-danger">*</span></label>

                                        <div class="row mr-5 mt-5 col-12 col-md-10">
                                            {{-- diploma type --}}
                                            <div class="col-12 col-sm-3">
                                                <label for="diploma">
                                                    <input type="radio" id="diploma" name="certificate_type"
                                                        class="@error('certificate_type') is-invalid @enderror"
                                                        value="diploma" required
                                                        {{ old('certificate_type', $student->certificate_type ?? null) == 'diploma' ? 'checked' : '' }}>
                                                    {{ trans('application_form.diploma') }}
                                                </label>
                                            </div>

                                            {{-- Bachelor type --}}
                                            <div class="col-12 col-sm-3">
                                                <label for="bachelor">
                                                    <input type="radio" id="bachelor" name="certificate_type"
                                                        required class="@error('certificate_type') is-invalid @enderror"
                                                        value="bachelor"
                                                        {{ old('certificate_type', $student->certificate_type ?? null) == 'bachelor' ? 'checked' : '' }}>
                                                    {{ trans('application_form.bachelor') }}
                                                </label>
                                            </div>

                                            {{-- Master type --}}
                                            <div class="col-12 col-sm-3">
                                                <label for="master">
                                                    <input type="radio" id="master" name="certificate_type"
                                                        class="@error('certificate_type') is-invalid @enderror"
                                                        value="master" required
                                                        {{ old('certificate_type', $student->certificate_type ?? null) == 'master' ? 'checked' : '' }}>
                                                    {{ trans('application_form.master') }}
                                                </label>
                                            </div>

                                            {{-- PhD type --}}
                                            <div class="col-12 col-sm-3">
                                                <label for="PhD">
                                                    <input type="radio" id="PhD" name="certificate_type"
                                                        required class="@error('certificate_type') is-invalid @enderror"
                                                        value="PhD"
                                                        {{ old('certificate_type', $student->certificate_type ?? null) == 'PhD' ? 'checked' : '' }}>
                                                    {{ trans('application_form.PhD') }}
                                                </label>
                                            </div>

                                        </div>

                                        @error('certificate_type')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                    </div>

                                    {{-- المؤهل التعليمي --}}
                                    <div class="form-group col-12 col-sm-6">

                                        <label for="educational_qualification_country"
                                            class="form-label high_education">
                                            {{ trans('public.country_source_cert') }}
                                            <span class="text-danger">*</span></label>

                                        <select id="educational_qualification_country"
                                            name="educational_qualification_country"
                                            class="form-control @error('educational_qualification_country') is-invalid @enderror"
                                            onchange="highEducationCountryToggle()">
                                            <option value="" class="placeholder" disabled="">
                                                {{ trans('public.choose_country') }}
                                            </option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country }}"
                                                    {{ old('educational_qualification_country', $student->educational_qualification_country ?? null) == $country ? 'selected' : '' }}>
                                                    {{ $country }}</option>
                                            @endforeach

                                            <option value="اخرى"
                                                {{ !empty($student->educational_qualification_country) && !in_array($student->educational_qualification_country, $countries) ? 'selected' : '' }}
                                                id="anotherEducationCountryOption">{{ trans('public.other') }}</option>
                                        </select>
                                        @error('educational_qualification_country')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                    </div>

                                    {{-- مصدر شهادة البكالوريوس --}}
                                    <div class="form-group col-12 col-sm-6" id="anotherEducationCountrySection"
                                        style="display: none">

                                        <label for="university" class="form-label high_education">
                                            {{ trans('public.enter_bachelor_certificate_source') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input type="text" id="anotherEducationCountry"
                                            class="form-control @error('anotherEducationCountry') is-invalid @enderror"
                                            placeholder="ادخل مصدر الشهادة"
                                            value="{{ old('anotherEducationCountry', $student && !in_array($student->educational_qualification_country, $countries) ? $student->educational_qualification_country : '') }}"
                                            onkeyup="setHighEducationCountry()">

                                        @error('anotherEducationCountry')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- الجامعه --}}
                                    <div class="form-group col-12 col-sm-6 high_education">
                                        <label for="university" class="form-label">
                                            {{trans('public.university')}}<span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="university"
                                            class="form-control @error('university') is-invalid @enderror"
                                            name="university" placeholder="{{ trans('public.university') }}"
                                            value="{{ old('university', $student ? $student->university : '') }}">

                                        @error('university')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- الكليه --}}
                                    <div class="form-group col-12 col-sm-6 high_education">
                                        <label for="faculty" class="form-label">
                                            {{ trans('public.college') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="faculty"
                                            class="form-control @error('faculty') is-invalid @enderror" name="faculty"
                                            placeholder="{{ trans('public.college') }}"
                                            value="{{ old('faculty', $student ? $student->faculty : '') }}">

                                        @error('faculty')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- التخصص  --}}
                                    <div class="form-group col-12 col-sm-6 high_education">
                                        <label for="education_specialization" class="form-label">
                                            {{ trans('public.specialization') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="education_specialization"
                                            class="form-control @error('education_specialization') is-invalid @enderror"
                                            name="education_specialization" placeholder="{{trans('panel.enter_specialization')}}"
                                            value="{{ old('education_specialization', $student ? $student->education_specialization : '') }}">

                                        @error('education_specialization')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- سنة التخرج --}}
                                    <div class="form-group col-12 col-sm-6 high_education">
                                        <label for="graduation_year" class="form-label">
                                            {{ trans('public.graduation_year') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="graduation_year"
                                            class="form-control @error('graduation_year') is-invalid @enderror"
                                            name="graduation_year"
                                            placeholder="{{ trans('public.graduation_year') }}"
                                            value="{{ old('graduation_year', $student ? $student->graduation_year : '') }}">


                                        @error('graduation_year')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- المعدل --}}
                                    <div class="form-group col-12 col-sm-6 high_education">
                                        <label for="gpa" class="form-label">
                                            {{ trans('public.average') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="gpa"
                                            class="form-control @error('gpa') is-invalid @enderror" name="gpa"
                                            placeholder="{{ trans('public.average') }} "
                                            value="{{ old('gpa', $student ? $student->gpa : '') }}">

                                        @error('gpa')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-12 row high_education">
                                        {{-- high_certificate_img input --}}
                                        <div class="form-group col-12 col-sm-6 high_education">
                                            <div>
                                                <label
                                                    for="high_certificate_img">{{ trans('public.graduation_certificate_picture') }}
                                                </label>
                                                <input type="file" id="high_certificate_img"
                                                    name="high_certificate_img" accept=".jpeg,.jpg,.png"
                                                    value="{{ old('high_certificate_img', $student ? $student->high_certificate_img : '') }}"
                                                    class="form-control @error('high_certificate_img') is-invalid @enderror">
                                            </div>
                                            @error('high_certificate_img')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        {{-- high_certificate_img display --}}
                                        <div>
                                            @if ($student->high_certificate_img)
                                                <a href="/store/{{ $student->high_certificate_img }}"
                                                    target="_blank">
                                                    <img src="/store/{{ $student->high_certificate_img }}"
                                                        alt="image" width="100px" style="max-height:100px">
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- secondary education --}}
                        <div class="card bg-secondary-acadima">
                            <div class="card-header" id="headingTwo">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left collapsed text-pink"
                                        type="button" data-toggle="collapse" data-target="#collapseTwo"
                                        aria-expanded="false" aria-controls="collapseTwo">
                                        {{ trans('public.secondary_education') }}
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo"
                                data-parent="#accordionExample">
                                <div class="card-body row">
                                    {{-- المؤهل التعليمي --}}
                                    <div class="form-group col-12 col-sm-6">
                                        <label for="educational_qualification_country"
                                            class="form-label secondary_education">
                                            {{ trans('public.country_source_cert') }}
                                            <span class="text-danger">*</span></label>

                                        <select id="secondary_educational_qualification_country"
                                            name="secondary_educational_qualification_country"
                                            class="form-control @error('secondary_educational_qualification_country') is-invalid @enderror"
                                            onchange="secondaryEducationCountryToggle()">
                                            <option value="" class="placeholder" disabled="">
                                                {{ trans('public.choose_country') }}
                                            </option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country }}"
                                                    {{ old('secondary_educational_qualification_country', $student->secondary_educational_qualification_country ?? null) == $country ? 'selected' : '' }}>
                                                    {{ $country }}</option>
                                            @endforeach

                                            <option value="اخرى"
                                                {{ !empty($student->secondary_educational_qualification_country) && !in_array($student->secondary_educational_qualification_country, $countries) ? 'selected' : '' }}
                                                id="anotherEducationCountryOption2">{{ trans('public.other') }}
                                            </option>
                                        </select>
                                        @error('secondary_educational_qualification_country')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                    </div>


                                    {{-- مصدر شهادة الثانوية --}}
                                    <div class="form-group col-12 col-sm-6" id="anotherEducationCountrySection2"
                                        style="display: none">

                                        <label for="university" class="form-label secondary_education">
                                            {{ trans('public.enter_secondary_certificate_source') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="anotherEducationCountry2"
                                            class="form-control @error('secondary_educational_qualification_country') is-invalid @enderror"
                                            placeholder="ادخل مصدر الشهادة"
                                            value="{{ old('secondary_educational_qualification_country', $student && !in_array($student->secondary_educational_qualification_country, $countries) ? $student->secondary_educational_qualification_country : '') }}"
                                            onkeyup="setSecondaryEducationCountry()">

                                        @error('secondary_educational_qualification_country')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    {{-- المنطقة التعليمية --}}
                                    <div class="form-group col-12 col-sm-6">
                                        <label for="educational_area" class="form-label">
                                            {{ trans('public.education_region') }}
                                            {{-- <span class="text-danger">*</span> --}}
                                        </label>
                                        <input type="text" id="educational_area"
                                            class="form-control @error('educational_area') is-invalid @enderror"
                                            name="educational_area"
                                            placeholder="{{ trans('public.education_region') }}"
                                            value="{{ old('educational_area', $student ? $student->educational_area : '') }}">

                                        @error('educational_area')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- المدرسة --}}
                                    <div class="form-group col-12 col-sm-6 secondary_education">
                                        <label for="school" class="form-label">
                                            {{ trans('public.school') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="school"
                                            class="form-control @error('school') is-invalid @enderror" name="school"
                                            placeholder="{{ trans('public.school') }}"
                                            value="{{ old('school', $student ? $student->school : '') }}">

                                        @error('school')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{--  سنة الحصول على الشهادة الثانوية --}}
                                    <div class="form-group col-12 col-sm-6 secondary_education">
                                        <label for="secondary_graduation_year" class="form-label">
                                            {{ trans('public.secondary_certificate_year') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="secondary_graduation_year"
                                            class="form-control @error('secondary_graduation_year') is-invalid @enderror"
                                            name="secondary_graduation_year"
                                            placeholder="{{ trans('public.secondary_certificate_year') }}"
                                            value="{{ old('secondary_graduation_year', $student ? $student->secondary_graduation_year : '') }}">

                                        @error('secondary_graduation_year')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- معدل المرحلة الثانوية --}}
                                    <div class="form-group col-12 col-sm-6 secondary_education">
                                        <label for="secondary_school_gpa" class="form-label">
                                            {{ trans('public.secondary_school_average') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="secondary_school_gpa"
                                            class="form-control @error('secondary_school_gpa') is-invalid @enderror"
                                            name="secondary_school_gpa" placeholder="{{trans('public.secondary_school_average')}}"
                                            value="{{ old('secondary_school_gpa', $student ? $student->secondary_school_gpa : '') }}">

                                        @error('secondary_school_gpa')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-12 row secondary_education">
                                        {{-- secondary_certificate_img input --}}
                                        <div class="form-group col-12 col-sm-6 secondary_education">
                                            <div>
                                                <label for="secondary_certificate_img">{{trans('public.secondary_certificate_picture')}} </label>
                                                <input type="file" id="secondary_certificate_img"
                                                    name="secondary_certificate_img" accept=".jpeg,.jpg,.png"
                                                    value="{{ old('secondary_certificate_img', $student ? $student->secondary_certificate_img : '') }}"
                                                    class="form-control @error('secondary_certificate_img') is-invalid @enderror">
                                            </div>
                                            @error('secondary_certificate_img')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        {{-- secondary_certificate_img display --}}
                                        <div>
                                            @if ($student->secondary_certificate_img)
                                                <a href="/store/{{ $student->secondary_certificate_img }}"
                                                    target="_blank">
                                                    <img src="/store/{{ $student->secondary_certificate_img }}"
                                                        alt="image" width="100px" style="max-height:100px">
                                                </a>
                                            @endif
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

</section>

<div class="d-none" id="newEducationModal">
    <h3 class="section-title after-line">{{ trans('site.new_education') }}</h3>
    <div class="mt-20 text-center">
        {{-- <img src="/assets/default/img/info.png" width="108" height="96" class="rounded-circle" alt=""> --}}
        <div class="swal2-icon swal2-warning swal2-icon-show" style="display: flex;">
            <div class="swal2-icon-content">!</div>
        </div>
        <h4 class="font-16 mt-20 text-black font-weight-bold">{{ trans('site.new_education_hint') }}</h4>
        <span class="d-block mt-10 text-gray font-14">{{ trans('site.new_education_exam') }}</span>
        <div class="form-group mt-15 px-50">
            <input type="text" id="new_education_val" class="form-control">
            <div class="invalid-feedback">{{ trans('validation.required', ['attribute' => 'value']) }}</div>
        </div>
    </div>

    <div class="mt-30 d-flex align-items-center justify-content-end">
        <button type="button" id="saveEducation" class="btn btn-sm btn-primary">{{ trans('public.save') }}</button>
        <button type="button" class="btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
    </div>
</div>


{{-- education section --}}
<script>
    function highEducationCountryToggle() {
        let anotherEducationCountrySection = document.getElementById("anotherEducationCountrySection");
        let anotherEducationCountry = document.getElementById("anotherEducationCountry");
        let anotherEducationCountryOption = document.getElementById("anotherEducationCountryOption");
        let educationalQualificationCountry = document.getElementById("educational_qualification_country");

        if (educationalQualificationCountry && educationalQualificationCountry.value == "اخرى") {
            anotherEducationCountrySection.style.display = "block";

            anotherEducationCountryOption.value = anotherEducationCountry.value;

            console.log('high toggle set another value');


        } else {
            anotherEducationCountrySection.style.display = "none";
            anotherEducationCountryOption.value = "اخرى";
            console.log('high toggle return another value');

        }

    }


    function secondaryEducationCountryToggle() {

        let anotherEducationCountrySection2 = document.getElementById("anotherEducationCountrySection2");
        let anotherEducationCountry2 = document.getElementById("anotherEducationCountry2");
        let anotherEducationCountryOption2 = document.getElementById("anotherEducationCountryOption2");
        let educationalQualificationCountry2 = document.getElementById("secondary_educational_qualification_country");

        if (educationalQualificationCountry2 && educationalQualificationCountry2.value == "اخرى") {
            anotherEducationCountrySection2.style.display = "block";

            anotherEducationCountryOption2.value = anotherEducationCountry2.value;
            console.log('secondary toggle set another value');

        } else {
            anotherEducationCountrySection2.style.display = "none";
            anotherEducationCountryOption2.value = "اخرى";
            console.log('secondary toggle return another value');
        }

    }

    function setHighEducationCountry() {
        let anotherEducationCountrySection = document.getElementById("anotherEducationCountrySection");
        let anotherEducationCountry = document.getElementById("anotherEducationCountry");
        let anotherEducationCountryOption = document.getElementById("anotherEducationCountryOption");
        let educationalQualificationCountry = document.getElementById("educational_qualification_country");


        if (anotherEducationCountrySection.style.display != "none") {
            anotherEducationCountryOption.value = anotherEducationCountry.value;
            console.log('set another value high section');
        }
    }

    function setSecondaryEducationCountry() {

        let anotherEducationCountrySection2 = document.getElementById("anotherEducationCountrySection2");
        let anotherEducationCountry2 = document.getElementById("anotherEducationCountry2");
        let anotherEducationCountryOption2 = document.getElementById("anotherEducationCountryOption2");
        let educationalQualificationCountry2 = document.getElementById("secondary_educational_qualification_country");


        if (anotherEducationCountrySection2.style.display != "none") {
            anotherEducationCountryOption2.value = anotherEducationCountry2.value;
            console.log('set another value secondary section');
        }

    }

    highEducationCountryToggle();
    secondaryEducationCountryToggle();
</script>
