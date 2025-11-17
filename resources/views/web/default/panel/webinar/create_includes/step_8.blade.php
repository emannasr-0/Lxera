@push('styles_top')

@endpush


<section class=" js-font-resize mt-20">
    <h2 class=" js-font-resize section-title after-line">{{ trans('public.message_to_reviewer') }}</h2>
    <div class=" js-font-resize row">
        <div class=" js-font-resize col-12">
            <div class=" js-font-resize form-group mt-15">
                <textarea name="message_for_reviewer" rows="10" class=" js-font-resize form-control">{{ (!empty($webinar) and $webinar->message_for_reviewer) ? $webinar->message_for_reviewer : old('message_for_reviewer') }}</textarea>
            </div>
        </div>
    </div>

    <div class=" js-font-resize row">
        <div class=" js-font-resize col-12 col-md-4">
            <div class=" js-font-resize form-group mt-10">
                <div class=" js-font-resize d-flex align-items-center justify-content-between">
                    <label class=" js-font-resize cursor-pointer input-label" for="rulesSwitch">{{ trans('public.agree_rules') }}</label>
                    <div class=" js-font-resize custom-control custom-switch">
                        <input type="checkbox" name="rules" class=" js-font-resize custom-control-input " id="rulesSwitch">
                        <label class=" js-font-resize custom-control-label" for="rulesSwitch"></label>
                    </div>
                </div>

                @error('rules')
                <div class=" js-font-resize text-danger mt-10">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>
    </div>
</section>

@push('scripts_bottom')

@endpush
