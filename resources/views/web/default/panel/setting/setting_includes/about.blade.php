<section>
    <h3 class=" js-font-resize section-title after-line mt-35">{{ trans('site.about') }}</h3>

    <div class=" js-font-resize row mt-20">
        <div class=" js-font-resize col-12 col-lg-6">
            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('panel.bio') }}</label>
                <textarea name="about" rows="9" class=" js-font-resize form-control @error('about')  is-invalid @enderror">{!! (!empty($user) and empty($new_user)) ? $user->about : old('about')  !!}</textarea>
                @error('about')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('panel.job_title') }}</label>
                <textarea name="bio" rows="3" class=" js-font-resize form-control @error('bio') is-invalid @enderror">{{ $user->bio }}</textarea>
                @error('bio')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror

                <div class=" js-font-resize mt-15">
                     <p class=" js-font-resize font-12 text-gray">- {{ trans('panel.bio_hint_1') }}</p>
                     <p class=" js-font-resize font-12 text-gray">- {{ trans('panel.bio_hint_2') }}</p>
                </div>

            </div>
        </div>
    </div>
</section>
