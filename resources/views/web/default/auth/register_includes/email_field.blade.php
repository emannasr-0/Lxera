<div class=" js-font-resize form-group ltr">
    <!-- <label class=" js-font-resize input-label" for="email">{{ trans('auth.email') }} {{ !empty($optional) ? "(". trans('public.optional') .")" : '' }}*</label> -->
    <div class=" js-font-resize border-radius-lg input-size form-control input-flex">
        <img src="{{ asset('store/Images/Registration/Mail.svg') }}" alt="Mail" class=" js-font-resize mb-1">
        <input name="email" type="text" class=" js-font-resize form-control @error('email') is-invalid @enderror border-none"
           value="{{ old('email') }}" id="email" aria-describedby="emailHelp" placeholder="Email">
    </div>

    @error('email')
    <div class=" js-font-resize invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div>
