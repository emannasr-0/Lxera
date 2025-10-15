@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/leaflet/leaflet.css">
@endpush

@php
    $user = auth()->user();
    $student = $user->student;
@endphp

<h2 class="section-title after-line mt-30">{{ trans('public.additional_information') }}</h2>
<section class="container p-0">
    {{-- working --}}
    <section class="mt-30 mb-30 bg-secondary-acadima p-20 rounded-sm">
        <h2 class="form-main-title">{{ trans('public.job_data') }}</h2>
        <section
            class="main-container border-2 border-secondary-subtle rounded row mx-0 workingSection">
            {{-- work status --}}
            <div class="form-group col-12 col-sm-6">
                <label>{{ trans('application_form.status') }}<span class="text-danger">*</span></label>

                @error('workStatus')
                    <div class="invalid-feedback d-inline">
                        {{ $message }}
                    </div>
                @enderror

                <div class="row mr-5 mt-5">
                    {{-- working status --}}
                    <div class="col-sm-4 col">
                        <label for="working">
                            <input type="radio" id="working" name="workStatus"
                                class="@error('workStatus') is-invalid @enderror" value="1" required
                                {{ old('workStatus', $student->job ?? null) != false ? 'checked' : '' }}>
                            {{ trans('application_form.working') }}
                        </label>
                    </div>

                    {{-- not working Status --}}
                    <div class="col">
                        <label for="not_working">
                            <input type="radio" id="not_working" name="workStatus" required
                                class="@error('workStatus') is-invalid @enderror" value="0"
                                {{ old('workStatus', $student->job ?? null) == false ? 'checked' : '' }}>
                            {{ trans('application_form.not_working') }}
                        </label>
                    </div>
                </div>
            </div>

            {{-- job details --}}
            <div class="col-12" id="job" style="display: none">
                <div class="row">
                    <div class="form-group col-12 col-sm-6">
                        <label for="job_title">{{ trans('public.job_title') }}<span class="text-danger">*</span></label>
                        <input type="text" id="job_title" name="job"
                            class="form-control @error('job') is-invalid @enderror"
                            placeholder="{{ trans('public.job_title') }}"
                            value="{{ old('job', $student ? $student->job : '') }}">


                        @error('job')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group col-12 col-sm-6">
                        <label for="employment_type">{{ trans('public.employer') }}<span
                                class="text-danger">*</span></label>
                        <select id="employment_type" name="job_type"
                            class="form-control @error('job_type') is-invalid @enderror">
                            <option value="" selected disabled>{{ trans('public.employer') }}</option>
                            <option value="governmental"
                                {{ old('job_type', $student->job_type ?? null) == 'governmental' ? 'selected' : '' }}>
                                {{ trans('public.government') }}
                            </option>
                            <option value="private"
                                {{ old('job_type', $student->job_type ?? null) == 'private' ? 'selected' : '' }}>
                                {{ trans('public.private') }}
                            </option>
                        </select>

                        @error('job_type')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>
        </section>
    </section>

    {{-- healthy --}}
    <section class="bg-secondary-acadima rounded-sm p-20">
        <h2 class="form-main-title">{{ trans('public.health_status') }}</h2>
        <section class="main-container border-2 border-secondary-subtle  row mx-0">

            {{-- deaf status --}}
            <div class="col-12 row">
                {{-- deaf --}}
                <div class="form-group col-12 col-sm-6">
                    <label for="deaf">{{ trans('application_form.deaf_patient') }}؟ <span
                            class="text-danger">*</span></label>

                    @error('deaf')
                        <div class="invalid-feedback d-inline">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="row mr-5 mt-5">
                        {{-- deaf --}}
                        <div class="col-sm-4 col">
                            <label for="deaf">
                                <input type="radio" id="deaf" name="deaf"
                                    class="@error('deaf') is-invalid @enderror" value="1" required
                                    {{ old('deaf', $student->deaf ?? null) == 1 ? 'checked' : '' }}>
                                    {{ trans('public.yes') }}
                            </label>
                        </div>

                        {{-- not deaf --}}
                        <div class="col">
                            <label for="not_deaf">
                                <input type="radio" id="not_deaf" name="deaf"
                                    class="@error('deaf') is-invalid @enderror" value="0" required
                                    {{ old('deaf', $student->deaf ?? null) == 0 ? 'checked' : '' }}>
                                    {{ trans('public.none') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- disabled --}}
            <div class="col-12 row">

                {{-- disabled --}}
                <div class="form-group col-12 col-sm-6">
                    <label>{{ trans('application_form.deaf_patient') }}؟<span class="text-danger">*</span></label>

                    @error('disabled')
                        <div class="invalid-feedback d-inline">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="row mr-5 mt-5">
                        {{-- disabled --}}
                        <div class="col-sm-4 col">
                            <label for="disabled">
                                <input type="radio" id="disabled" name="disabled"
                                    class="@error('disabled') is-invalid @enderror" value="1" required
                                    {{ old('disabled', $student->disabled_type ?? null) != false ? 'checked' : '' }}>
                                {{ trans('public.yes') }}
                            </label>
                        </div>

                        {{-- not disabled --}}
                        <div class="col">
                            <label for="not_disabled">
                                <input type="radio" id="not_disabled" name="disabled"
                                    class="@error('disabled') is-invalid @enderror" value="0" required
                                    {{ old('disabled', $student->disabled_type ?? null) == false ? 'checked' : '' }}>
                                {{ trans('public.none') }}
                            </label>
                        </div>
                    </div>

                </div>

                {{-- disabled type --}}
                <div class="form-group col-12 col-sm-6" id="disabled_type_section" style="display: none">
                    <label for="disabled_type">{{ 'حدد نوع الإعاقة' }} <span class="text-danger">*</span></label>
                    <select id="disabled_type" name="disabled_type"
                        class="form-control @error('disabled_type') is-invalid @enderror">
                        <option value="" class="placeholder" disabled="" selected>
                            {{ trans('public.choose_disability_type') }}
                        </option>
                        <option value="option1"
                            {{ old('disabled_type', $student->disabled_type ?? null) == 'option1' ? 'selected' : '' }}>
                            {{ trans('public.mental_disability') }}
                        </option>
                        <option value="option2"
                            {{ old('disabled_type', $student->disabled_type ?? null) == 'option2' ? 'selected' : '' }}>
                            {{ trans('public.physical_disability') }}
                        </option>
                    </select>

                    @error('disabled_type')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

            </div>

            {{-- healthy problem --}}
            <div class="col-12 row">
                {{-- healthy status --}}
                <div class="form-group col-12 col-sm-6">
                    <label for="healthy">{{ trans('application_form.health_proplem') }}؟<span
                            class="text-danger">*</span></label>


                    @error('healthy')
                        <div class="invalid-feedback d-inline">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="row mr-5 mt-5">
                        {{-- healthy --}}
                        <div class="col-sm-4 col">
                            <label for="healthy">
                                <input type="radio" id="healthy" name="healthy"
                                    class=" @error('healthy') is-invalid @enderror" value="1" required
                                    {{ old('healthy', $student->healthy_problem ?? null) != false ? 'checked' : '' }}>
                                    {{ trans('public.yes') }}
                            </label>
                        </div>

                        {{-- not healthy --}}
                        <div class="col">
                            <label for="not_healthy">
                                <input type="radio" id="not_healthy" name="healthy"
                                    class=" @error('healthy') is-invalid @enderror" value="0" required
                                    {{ old('healthy', $student->healthy_problem ?? null) == false ? 'checked' : '' }}>
                                    {{ trans('public.none') }}
                            </label>
                        </div>
                    </div>

                </div>

                {{-- healthy problem --}}
                <div class="form-group col-12 col-sm-6" id="healthy_problem_section" style="display: none">
                    <label for="healthy_problem">{{ trans('public.enter_health_problem') }}<span class="text-danger">*</span></label>
                    <input type="text" id="healthy_problem"
                        class="form-control @error('healthy_problem') is-invalid @enderror" name="healthy_problem"
                        placeholder="{{ trans('public.enter_health_problem') }}"
                        value="{{ old('healthy_problem', $student ? $student->healthy_problem : '') }}">

                    @error('healthy_problem')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror


                </div>
            </div>
        </section>
    </section>
</section>

@push('scripts_bottom')
    <script src="/assets/vendors/leaflet/leaflet.min.js"></script>

    <script>
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
        var leafletApiPath = '{{ getLeafletApiPath() }}';
    </script>

    <script src="/assets/default/js/panel/user_settings_tab.min.js"></script>

    {{-- job script --}}
    <script>
        var working = document.getElementById("working");
        var notWorking = document.getElementById("not_working");
        var job = document.getElementById("job");

        function toggleJobFields() {
            if (working.checked) {
                job.style.display = "block";
                var inputs = document.querySelectorAll('#job input');
                inputs.forEach(function(input) {
                    input.setAttribute('required', 'required');
                });

            } else {
                job.style.display = "none";
                var inputs = document.querySelectorAll('#job input');
                inputs.forEach(function(input) {
                    input.removeAttribute('required');
                });
            }
        }

        working.addEventListener("change", toggleJobFields);
        notWorking.addEventListener("change", toggleJobFields);
        toggleJobFields();
    </script>


    {{--  healthy section toggle --}}
    <script>
        // healthy section display
        function toggleHealthyProblemSection() {
            let healthyProblemSection = document.getElementById("healthy_problem_section");
            let healthyStatus = document.getElementById("healthy");
            if (healthyStatus.checked) {
                healthyProblemSection.style.display = "block";
                var inputs = document.querySelectorAll('#healthy_problem_section input');
                inputs.forEach(function(input) {
                    input.setAttribute('required', 'required');
                });
            } else {
                healthyProblemSection.style.display = "none";
                var inputs = document.querySelectorAll('#healthy_problem_section input');
                inputs.forEach(function(input) {
                    input.removeAttribute('required');
                });
            }

        }

        let healthy = document.getElementById("healthy");
        let notHealthy = document.getElementById("not_healthy");
        healthy.addEventListener("change", toggleHealthyProblemSection);
        notHealthy.addEventListener("change", toggleHealthyProblemSection);
        toggleHealthyProblemSection();
    </script>


    {{-- disabled section toggle --}}
    <script>
        // disabled section display
        function toggleDisabledSection() {
            let disabledTypeSection = document.getElementById("disabled_type_section");
            let disabledStatus = document.getElementById("disabled");
            if (disabledStatus.checked) {
                disabledTypeSection.style.display = "block";
                var inputs = document.querySelectorAll('#disabled_type_section select');
                inputs.forEach(function(input) {
                    input.setAttribute('required', 'required');
                });
            } else {
                disabledTypeSection.style.display = "none";
                var inputs = document.querySelectorAll('#disabled_type_section select');
                inputs.forEach(function(input) {
                    input.removeAttribute('required');
                });
            }

        }
        let disabled = document.getElementById("disabled");
        let notDisabled = document.getElementById("not_disabled");
        disabled.addEventListener("change", toggleDisabledSection);
        notDisabled.addEventListener("change", toggleDisabledSection);
        toggleDisabledSection();
    </script>
@endpush
