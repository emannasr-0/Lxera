<div class="form-group ltr">
    <!-- <label class="input-label" for="email">{{ trans('auth.email') }} {{ !empty($optional) ? "(". trans('public.optional') .")" : '' }}*</label> -->
    <div class="border-radius-lg input-size form-control input-flex">
        <img src="{{ asset('store/Images/Registration/Mail.svg') }}" alt="Mail" class="mb-1">
        <input name="email" type="text" class="form-control @error('email') is-invalid @enderror border-none"
           value="{{ old('email') }}" id="email" aria-describedby="emailHelp" placeholder="Email">
    </div>

    @error('email')
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div>
