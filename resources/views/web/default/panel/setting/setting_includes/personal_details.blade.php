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

    $nationalities = [
        trans('panel.saudi'),
        trans('panel.emirati'),
        trans('panel.jordanian'),
        trans('panel.bahraini'),
        trans('panel.algerian'),
        trans('panel.iraqi'),
        trans('panel.moroccan'),
        trans('panel.yemeni'),
        trans('panel.sudanese'),
        trans('panel.somali'),
        trans('panel.kuwaiti'),
        trans('panel.syrian'),
        trans('panel.lebanese'),
        trans('panel.egyptian'),
        trans('panel.tunisian'),
        trans('panel.palestinian'),
        trans('panel.djiboutian'),
        trans('panel.omanian'),
        trans('panel.mauritanian'),
        trans('panel.qatari'),
    ];

    $user = auth()->user();
    $student = $user->student;
@endphp

<section>
    <h2 class="section-title after-line">{{ trans('public.personal_information') }}</h2>

    {{-- personal details --}}
    <section class="row mt-20 container">
        <section class="main-container bg-secondary-acadima border-2 border-secondary-subtle rounded-sm p-3 mt-2 mb-25 row mx-0">
            {{-- arabic name --}}
            <div class="form-group col-12 col-sm-6">
                <label for="name">{{ trans('application_form.name') }}<span class="text-danger">*</span></label>
                <input @if (!session()->has('impersonated')) disabled @endif type="text" id="name" name="ar_name"
                    {{-- value="{{ $student ? $student->ar_name : '' }}" --}}
                    value="{{ old('ar_name', $student ? $student->ar_name : $user->full_name ?? '') }}"
                    placeholder="{{ trans('public.ar_name_placeholder') }}" required
                    class="form-control @error('ar_name') is-invalid @enderror">

                @error('ar_name')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- english name --}}
            <div class="form-group col-12 col-sm-6">
                <label for="name_en">{{ trans('application_form.name_en') }}<span class="text-danger">*</span></label>
                <input @if (!session()->has('impersonated')) disabled @endif type="text" id="name_en" name="en_name"
                    {{-- value="{{ $student ? $student->en_name : '' }}" --}} value="{{ old('en_name', $student ? $student->en_name : '') }}"
                    placeholder="{{ trans('public.en_name_placeholder') }}" required
                    class="form-control @error('en_name') is-invalid @enderror">

                @error('en_name')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- identifier number --}}
            <div class="form-group col-12 col-sm-6">
                <label for="identifier_num">{{ trans('public.id_num') }} <span class="text-danger">*</span></label>
                <input type="text" id="identifier_num" name="identifier_num" {{-- value="{{ $student ? $student->identifier_num : '' }}" --}}
                    value="{{ old('identifier_num', $student ? $student->identifier_num : '') }}"
                    placeholder="{{ trans('public.id_num_placeholder') }}" required
                    class="form-control  @error('identifier_num') is-invalid @enderror">

                @error('identifier_num')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

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

                <select id="nationality" name="nationality" required
                    class="form-control  @error('nationality') is-invalid @enderror" onchange="toggleNationality()">
                    <option value="" class="placeholder" disabled>
                        {{ trans('public.nationality_choose') }}
                    </option>
                    @foreach ($nationalities as $nationality)
                        <option value="{{ $nationality }}"
                            {{ old('nationality', $student->nationality ?? null) == $nationality ? 'selected' : '' }}>
                            {{ $nationality }}</option>
                    @endforeach
                    <option value="اخرى" id="anotherNationality"
                        {{ old('nationality') != '' && !in_array(old('nationality'), $nationalities) ? 'selected' : '' }}>
                        {{ trans('public.other') }}</option>
                </select>
                @error('nationality')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- other nationality --}}
            <div class="form-group col-12 col-sm-6" id="other_nationality_section" style="display: none">
                <label for="nationality">{{ trans('public.enter_nationality') }} <span
                        class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nationality') is-invalid @enderror"
                    id="other_nationality" name="" placeholder="{{ trans('public.enter_nationality') }}"
                    {{-- value="{{ $student ? $student->other_nationality : '' }}" --}}
                    value="{{ old('nationality', $student ? $student->other_nationality : '') }}"
                    onkeyup="setNationality()">

                @error('nationality')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- gender --}}
            <div class="form-group col-12 col-sm-6">
                <label for="gender">{{ trans('application_form.gender') }}<span class="text-danger">*</span></label>

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
                            {{ trans('public.female') }}
                        </label>
                    </div>

                    {{-- male --}}
                    <div class="col">
                        <label for="male">
                            <input type="radio" id="male" name="gender" value="male"
                                class=" @error('gender') is-invalid @enderror" required
                                {{ old('gender', $student->gender ?? null) == 'male' ? 'checked' : '' }}>
                            {{ trans('public.male') }}
                        </label>
                    </div>
                </div>
            </div>

            {{-- country --}}
            <div class="form-group col-12 col-sm-6">
                <label for="country">{{ trans('application_form.country') }}<span class="text-danger">*</span></label>

                <select id="mySelect" name="country" required
                    class="form-control @error('country') is-invalid @enderror" onchange="toggleHiddenInputs()">
                    <option value="" class="placeholder" disabled="">{{ trans('public.enter_your_country') }}
                    </option>
                    @foreach ($countries as $country)
                        <option value="{{ $country }}"
                            {{ old('country', $student->country ?? null) == $country ? 'selected' : '' }}>
                            {{ $country }}</option>
                    @endforeach
                    <option value="اخرى" id="anotherCountry"
                        {{ !empty($student->country) && !in_array($student->country, $countries) ? 'selected' : '' }}>
                        {{ trans('public.other') }}</option>

                </select>

                @error('country')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- other country --}}
            <div class="form-group col-12 col-sm-6" id="anotherCountrySection" style="display: none">
                <label for="city" class="form-label">{{ trans('public.enter_country') }}<span
                        class="text-danger">*</span></label>
                <input type="text" id="city" name="city"
                    class="form-control  @error('city') is-invalid @enderror"
                    placeholder="{{ trans('public.enter_your_country') }}"
                    value="{{ old('city', $student ? $student->city : '') }}" onkeyup="setCountry()">

                @error('city')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- region --}}
            <div class="form-group col-12 col-sm-6" id="region" style="display: none">
                <label for="area" class="form-label">{{ trans('public.region') }}<span
                        class="text-danger">*</span></label>
                <input type="text" id="area" name="area"
                    class="form-control  @error('area') is-invalid @enderror"
                    placeholder="{{ trans('public.region') }}"
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
                    <label for="town" id="cityLabel">{{ trans('application_form.city') }}<span
                            class="text-danger">*</span></label>
                    <input type="text" id="town" name="town"
                        placeholder="{{ trans('application_form.city') }}"
                        value="{{ old('town', $student ? $student->town : '') }}" required
                        class="form-control @error('town') is-invalid @enderror">
                </div>
                @error('town')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- identity_img input --}}
            <div class="form-group col-12 col-sm-6">
                <div>
                    <label for="identity_img">{{ trans('public.id_pic') }}</label>
                    <input type="file" id="identity_img" name="identity_img" accept=".jpeg,.jpg,.png"
                        value="{{ old('identity_img', $student ? $student->identity_img : '') }}"
                        class="form-control @error('identity_img') is-invalid @enderror">
                </div>
                @error('identity_img')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            {{-- identity_img display --}}
            <div>
                @if ($student->identity_img)
                    <a href="/store/{{ $student->identity_img }}" target="_blank">
                        <img src="/store/{{ $student->identity_img }}" alt="image" width="100px"
                            style="max-height:100px">
                    </a>
                @endif
            </div>

        </section>
    </section>

</section>

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

    // Show/hide region input based on selected country
    if (select && select.value !== "السعودية") {
        region.style.display = "block";
    } else {
        region.style.display = "none";
    }

    // Handle "Other Country" section
    if (select.value === "اخرى") {
        anotherCountrySection.style.display = "block";
        anotherCountryOption.value = hiddenInput2.value;
    } else {
        anotherCountrySection.style.display = "none";
        anotherCountryOption.value = "اخرى";
    }

    if (select && cityLabel && town) {
        // Get the current value from the existing town input/select
        var previousValue = town.value || "";

        if (select.value === "السعودية") {
            // Build the Saudi cities dropdown
            var cities = [
                { value: "الرياض", label: "{{ trans('panel.riyadh') }}" },
                { value: "جده", label: "{{ trans('panel.jeddah') }}" },
                { value: "مكة المكرمة", label: "{{ trans('panel.mecca') }}" },
                { value: "المدينة المنورة", label: "{{ trans('panel.madinah') }}" },
                { value: "الدمام", label: "{{ trans('panel.damam') }}" },
                { value: "الطائف", label: "{{ trans('panel.taif') }}" },
                { value: "تبوك", label: "{{ trans('panel.tabuk') }}" },
                { value: "الخرج", label: "{{ trans('panel.kharj') }}" },
                { value: "بريدة", label: "{{ trans('panel.buraidah') }}" },
                { value: "خميس مشيط", label: "{{ trans('panel.khames_mishit') }}" },
                { value: "الهفوف", label: "{{ trans('panel.al_hofuf') }}" },
                { value: "المبرز", label: "{{ trans('panel.al_mubara') }}" },
                { value: "حفر الباطن", label: "{{ trans('panel.hafar_al_batin') }}" },
                { value: "حائل", label: "{{ trans('panel.hail') }}" },
                { value: "نجران", label: "{{ trans('panel.najran') }}" },
                { value: "الجبيل", label: "{{ trans('panel.jubail') }}" },
                { value: "أبها", label: "{{ trans('panel.abha') }}" },
                { value: "ينبع", label: "{{ trans('panel.yanbu') }}" },
                { value: "الخبر", label: "{{ trans('panel.khobar') }}" },
                { value: "عنيزة", label: "{{ trans('panel.unaizah') }}" },
                { value: "عرعر", label: "{{ trans('panel.ar_ar') }}" },
                { value: "سكاكا", label: "{{ trans('panel.sakkaka') }}" },
                { value: "جازان", label: "{{ trans('panel.jazan') }}" },
                { value: "القريات", label: "{{ trans('panel.al_qarayat') }}" },
                { value: "الظهران", label: "{{ trans('panel.dhahran') }}" },
                { value: "القطيف", label: "{{ trans('panel.qatif') }}" },
                { value: "الباحة", label: "{{ trans('panel.baha') }}" }
            ];

            // Build select HTML with correct selected value
            var selectHTML = '<select id="town" name="town" class="form-control" required>';
            cities.forEach(function(city) {
                var selected = (city.value === previousValue) ? 'selected' : '';
                selectHTML += `<option value="${city.value}" ${selected}>${city.label}</option>`;
            });
            selectHTML += '</select>';

            // Replace town input with select
            town.outerHTML = selectHTML;

        } else {
            // Replace town with a regular text input for other countries
            town.outerHTML = `
                <input type="text" id="town" name="town"
                    placeholder="{{ trans('panel.current_city_placeholder') }}"
                    class="form-control"
                    value="${previousValue}">
            `;
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
