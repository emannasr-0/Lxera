<div class="row mt-15">
    <div class="col-12 col-md-6">
        <div class="form-group ">
            <label class="input-label">{{ trans('update.upfront') }}</label>
            <input type="number" name="upfront"
                value="{{ !empty($installment) ? $installment->upfront : old('upfront') }}"
                class="form-control @error('upfront')  is-invalid @enderror" />
            @error('upfront')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group ">
            <label class="input-label">{{ trans('update.upfront_type') }}</label>
            <select name="upfront_type" class="form-control">
                <option value="fixed_amount"
                    {{ (!empty($installment) and $installment->upfront_type == 'fixed_amount') ? 'selected' : '' }}>
                    {{ trans('update.fixed_amount') }}</option>
                <option value="percent"
                    {{ (!empty($installment) and $installment->upfront_type == 'percent') ? 'selected' : '' }}>
                    {{ trans('update.percent') }}</option>
            </select>
            @error('upfront_type')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    {{-- deadline type input --}}
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label class="input-label">نوع ميعاد الحد الاقصي للأقساط</label>
            <select name="deadline_type" id='deadline_type'
                class="form-control  @error('deadline_type') is-invalid  @enderror">
                <option value="date" @if ((!empty($installment) and $installment->deadline_type == 'date') || old('deadline_type') == 'date') selected @endif>بالتواريخ
                </option>
                <option value="days"
                    {{ (!empty($installment) and $installment->deadline_type == 'days') || old('deadline_type') == 'days' ? 'selected' : '' }}>
                    بالأيام
                </option>
            </select>
            @error('deadline_type')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
</div>

<div class="row mt-20">
    <div class="col-12">

        {{-- Installment Steps --}}
        <div id="installmentStepsCard" class="mt-3">
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="font-16 text-dark">{{ trans('update.payment_steps') }}</h5>

                        <button type="button" class="js-add-btn btn btn-success ml-3">
                            <i class="fa fa-plus"></i>
                            {{ trans('update.add_step') }}
                        </button>
                    </div>
                </div>
            </div>

            @if (!empty($installment) and !empty($installment->steps))
                @php
                    $installmentSteps = explode(',', $installment->options);
                @endphp
                @foreach ($installment->steps as $stepRow)
                    @include('admin.financial.installments.create.includes.installment_step_inputs', [
                        'step' => $stepRow,
                    ])
                @endforeach
            @endif

            <div id="installmentStepsMainRow" class="d-none">
                @include('admin.financial.installments.create.includes.installment_step_inputs')
            </div>

        </div>



    </div>
</div>

@push('scripts_bottom')
    <script>
        // var selectedValue = $('#deadline_type').val();
        // console.log(selectedValue);

        $('#deadline_type').change(function() {
            // Get the selected value
            let newContent = '';
            var selectedValue = $(this).val();
            console.log(selectedValue);
            $('.step_deadline').each(function() {
                let stepId = $(this).data('step-id');
                console.log(stepId);
                // Skip steps with stepId 'record'
                if (stepId === 'record') {
                    return true; // Skip this iteration (continue to the next step)
                }

                if (selectedValue == 'date') {
                    newContent = `
                <div class="input-group-prepend date_type">
                        <span class="input-group-text" id="dateRangeLabel">
                            <i class="fa fa-calendar"></i>
                        </span>
                    </div>

                    <input type="text" name="steps[${stepId}][deadline]"
                        class="form-control text-center datetimepicker date_type" aria-describedby="dateRangeLabel"
                        autocomplete="off"
                        value="{{ (!empty($step) and !empty($step->deadline)) ? dateTimeFormat($step->deadline, 'Y-m-d H:i', false) : '' }}" />
                `;

                } else if (selectedValue == 'days') {
                    $('.date_type').remove();
                    newContent = `
                <input type="number" name="steps[${stepId}][deadline]"
                        value="{{ !empty($step) ? $step->deadline : '' }}" class="form-control days_type" />
                `;
                }

                newContent = newContent.replace(/\[record\]/g, '[' + randomString() + ']');

                $(this).html(newContent);
            });

            // Reset the date picker (reinitialize it for the newly added field)
            resetDatePickers();
            // $('.step_deadline').html(newContent);
            // resetDatePickers();

        });
    </script>
@endpush
