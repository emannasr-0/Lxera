<section>
    <h2 class=" js-font-resize section-title after-line">{{ trans('public.basic_information') }} </h2>

    <div class=" js-font-resize row mt-20">
        <div class=" js-font-resize col-12 col-lg-4">
            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('public.academic_code') }}</label>
                <input @if(!session()->has('impersonated') || empty($user->student)) disabled @endif type="text" name="user_code" value="{{  old('user_code', $user?->user_code ) }}" class=" js-font-resize form-control @error('user_code')  is-invalid @enderror" placeholder=""/>
                @error('user_code')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('public.email') }}</label>
                <input @if(!session()->has('impersonated')) disabled @endif type="text" name="email" value="{{ (!empty($user) and empty($new_user)) ? $user->email : old('email') }}" class=" js-font-resize form-control @error('email')  is-invalid @enderror" placeholder=""/>
                @error('email')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('auth.name') }}</label>
                <input type="text" name="full_name" value="{{ (!empty($user) and empty($new_user)) ? $user->full_name : old('full_name') }}" class=" js-font-resize form-control @error('full_name')  is-invalid @enderror" placeholder=""/>
                @error('full_name')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('auth.password') }}</label>
                <input type="password" name="password" value="{{ old('password') }}" class=" js-font-resize form-control @error('password')  is-invalid @enderror" placeholder=""/>
                @error('password')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('auth.password_repeat') }}</label>
                <input type="password" name="password_confirmation" value="{{ old('password_confirmation') }}" class=" js-font-resize form-control @error('password_confirmation')  is-invalid @enderror" placeholder=""/>
                @error('password_confirmation')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('public.mobile') }}</label>
                <input @if(!session()->has('impersonated')) disabled @endif type="tel" name="mobile" value="{{ (!empty($user) and empty($new_user)) ? $user->mobile : old('mobile') }}" class=" js-font-resize form-control @error('mobile')  is-invalid @enderror" placeholder=""/>
                @error('mobile')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('auth.language') }}</label>
                <select name="language" class=" js-font-resize form-control">
                    <option value="">{{ trans('auth.language') }}</option>
                    @foreach($userLanguages as $lang => $language)
                        <option value="{{ $lang }}" @if(!empty($user) and mb_strtolower($user->language) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                    @endforeach
                </select>
                @error('language')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('update.timezone') }}</label>
                <select name="timezone" class=" js-font-resize form-control select2" data-allow-clear="false">
                    <option value="" {{ empty($user->timezone) ? 'selected' : '' }} disabled>{{ trans('public.select') }}</option>
                    @foreach(getListOfTimezones() as $timezone)
                        <option value="{{ $timezone }}" @if(!empty($user) and $user->timezone == $timezone) selected @endif>{{ $timezone }}</option>
                    @endforeach
                </select>
                @error('timezone')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            @if(!empty($currencies) and count($currencies))
                @php
                    $userCurrency = currency();
                @endphp

                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">{{ trans('update.currency') }}</label>
                    <select name="currency" class=" js-font-resize form-control select2" data-allow-clear="false">
                        @foreach($currencies as $currencyItem)
                            <option value="{{ $currencyItem->currency }}" {{ ($userCurrency == $currencyItem->currency) ? 'selected' : '' }}>{{ currenciesLists($currencyItem->currency) }} ({{ currencySign($currencyItem->currency) }})</option>
                        @endforeach
                    </select>
                    @error('currency')
                    <div class=" js-font-resize invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            @endif

            <div class=" js-font-resize form-group mt-30 d-flex align-items-center justify-content-between">
                <label class=" js-font-resize cursor-pointer input-label" for="newsletterSwitch">{{ trans('auth.join_newsletter') }}</label>
                <div class=" js-font-resize custom-control custom-switch">
                    <input type="checkbox" name="join_newsletter" class=" js-font-resize custom-control-input" id="newsletterSwitch" {{ (!empty($user) and $user->newsletter) ? 'checked' : '' }}>
                    <label class=" js-font-resize custom-control-label" for="newsletterSwitch"></label>
                </div>
            </div>

            <div class=" js-font-resize form-group mt-30 d-flex align-items-center justify-content-between">
                <label class=" js-font-resize cursor-pointer input-label" for="publicMessagesSwitch">{{ trans('auth.public_messages') }}</label>
                <div class=" js-font-resize custom-control custom-switch">
                    <input type="checkbox" name="public_messages" class=" js-font-resize custom-control-input" id="publicMessagesSwitch" {{ (!empty($user) and $user->public_message) ? 'checked' : '' }}>
                    <label class=" js-font-resize custom-control-label" for="publicMessagesSwitch"></label>
                </div>
            </div>
        </div>
    </div>

</section>
