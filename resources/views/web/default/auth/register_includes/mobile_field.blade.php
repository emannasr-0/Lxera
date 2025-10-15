<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css">
<style>
    .form-group .iti {
        width: 100% !important;
        border-radius: 20px !important;
    }
</style>
<div class="row">
    <!--<div class="col-5">-->
    <!--    <div class="form-group">-->
    <!--        <label class="input-label" for="mobile">{{ trans('auth.country') }}:</label>-->
    <!--        <select name="country_code" class="form-control select2">-->
    <!--            @foreach (getCountriesMobileCode() as $country => $code)
-->
    <!--                <option value="{{ $code }}" @if ($code == old('country_code')) selected @endif>{{ $country }}</option>-->
    <!--
@endforeach-->
    <!--        </select>-->

    <!--        @error('mobile')
-->
        <!--        <div class="invalid-feedback">-->
        <!--            {{ $message }}-->
        <!--        </div>-->
        <!--
@enderror-->
    <!--    </div>-->
    <!--</div>-->

    <div class="col-12">
        <div class="form-group ltr">
            <!--<input name="mobile" type="text" class="form-control @error('mobile') is-invalid @enderror"-->
            <!--       value="{{ old('mobile') }}" id="mobile" aria-describedby="mobileHelp">-->



            <div class="mb-3 ">
                <!-- <label class="input-label d-block" for="mobile">{{ trans('auth.mobile') }}
                    {{ !empty($optional) ? '(' . trans('public.optional') . ')' : '' }}*</label> -->
                <!-- Phone number input field -->
                <input type="hidden" name="country_code" id="code" class="text-dark"> 

                <div class="border-radius-lg input-size form-control input-flex">
                <img src="{{ asset('store/Images/Registration/Mobile.svg') }}" alt="Mail" class="mb-1">

                <input type="tel" id="phone" name="mobile" value="{{ old('mobile') }}"
                    aria-describedby="mobileHelp" class="form-control @error('mobile') is-invalid @enderror border-none"
                    placeholder="Mobile Phone">
                    </div>

            </div>


            @error('mobile')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<!-- Include intlTelInput JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<!-- Initialize intlTelInput -->
<script>
    // Function to initialize intlTelInput
    function initializeIntlTelInput() {
        var input = document.querySelector("#phone");
        var iti = window.intlTelInput(input, {
            initialCountry: "auto", // Set the default country to Saudi Arabia
            separateDialCode: true, // Add a country code prefix to the input value
            geoIpLookup: function(callback) {
            fetch("https://ipapi.co/json/")
                .then(response => response.json())
                .then(data => callback(data.country_code.toLowerCase()))
                .catch(() => callback("sa")); // Default to US if lookup fails
        },
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js" // Provide the utils.js script
        });
    }

    // Wait for the document to be fully loaded before initializing intlTelInput
    document.addEventListener("DOMContentLoaded", function() {
        initializeIntlTelInput();
    });
</script>
