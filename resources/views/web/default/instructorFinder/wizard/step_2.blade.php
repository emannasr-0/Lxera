<div class=" js-font-resize wizard-step-1">
    <h3 class=" js-font-resize font-20 text-dark font-weight-bold">{{ trans('update.meeting_type') }}</h3>

    <span class=" js-font-resize d-block mt-30 text-gray wizard-step-num">
        {{ trans('update.step') }} 2/4
    </span>

    <span class=" js-font-resize d-block font-16 font-weight-500 mt-30">{{ trans('update.choose_the_meeting_type') }}</span>

    <div class=" js-font-resize form-group mt-10">
        <label class=" js-font-resize input-label">{{ trans('update.meeting_type') }}</label>

        <div class=" js-font-resize d-flex align-items-center wizard-custom-radio mt-5">
            <div class=" js-font-resize wizard-custom-radio-item">
                <input type="radio" name="meeting_type" checked value="all" id="all" class=" js-font-resize ">
                <label class=" js-font-resize font-12 cursor-pointer" for="all">{{ trans('public.all') }}</label>
            </div>

            <div class=" js-font-resize wizard-custom-radio-item">
                <input type="radio" name="meeting_type" value="in_person" id="in_person" class=" js-font-resize ">
                <label class=" js-font-resize font-12 cursor-pointer" for="in_person">{{ trans('update.in_person') }}</label>
            </div>

            <div class=" js-font-resize wizard-custom-radio-item">
                <input type="radio" name="meeting_type" value="online" id="online" class=" js-font-resize ">
                <label class=" js-font-resize font-12 cursor-pointer" for="online">{{ trans('update.online') }}</label>
            </div>
        </div>
    </div>

    <div id="regionCard" class=" js-font-resize d-none">
        <div class=" js-font-resize form-group mt-30">
            <label class=" js-font-resize input-label font-weight-500">{{ trans('update.country') }}</label>

            <select name="country_id" class=" js-font-resize form-control">
                <option value="">{{ trans('update.select_country') }}</option>

                @if(!empty($countries))
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->title }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class=" js-font-resize form-group mt-30">
            <label class=" js-font-resize input-label font-weight-500">{{ trans('update.province') }}</label>

            <select name="province_id" class=" js-font-resize form-control" disabled>
                <option value="">{{ trans('update.select_province') }}</option>
            </select>
        </div>

        <div class=" js-font-resize form-group mt-30">
            <label class=" js-font-resize input-label font-weight-500">{{ trans('update.city') }}</label>

            <select name="city_id" class=" js-font-resize form-control" disabled>
                <option value="">{{ trans('update.select_city') }}</option>
            </select>
        </div>

        <div class=" js-font-resize form-group mt-30">
            <label class=" js-font-resize input-label font-weight-500">{{ trans('update.district') }}</label>

            <select name="district_id" class=" js-font-resize form-control" disabled>
                <option value="">{{ trans('update.select_district') }}</option>
            </select>
        </div>
    </div>

    <div class=" js-font-resize ">
        <label class=" js-font-resize input-label">{{ trans('update.population') }}</label>

        <div class=" js-font-resize d-flex align-items-center wizard-custom-radio mt-5">
            <div class=" js-font-resize wizard-custom-radio-item">
                <input type="radio" name="population" value="all" checked id="population_all" class=" js-font-resize ">
                <label class=" js-font-resize font-12 cursor-pointer" for="population_all">{{ trans('public.all') }}</label>
            </div>

            <div class=" js-font-resize wizard-custom-radio-item">
                <input type="radio" name="population" value="single" id="population_single" class=" js-font-resize ">
                <label class=" js-font-resize font-12 cursor-pointer" for="population_single">{{ trans('update.single') }}</label>
            </div>

            <div class=" js-font-resize wizard-custom-radio-item">
                <input type="radio" name="population" value="group" id="population_group" class=" js-font-resize ">
                <label class=" js-font-resize font-12 cursor-pointer" for="population_group">{{ trans('update.group') }}</label>
            </div>
        </div>
    </div>
</div>
