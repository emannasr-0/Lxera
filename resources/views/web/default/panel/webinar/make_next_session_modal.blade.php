<div class=" js-font-resize d-none" id="webinarNextSessionModal">
    <form action="/panel/sessions/store" method="post">
        {{ csrf_field() }}

        <input type="hidden" name="ajax[new][webinar_id]">
        <input type="hidden" name="ajax[new][chapter_id]">
        <input type="hidden" name="ajax[new][locale]">
        <input type="hidden" name="ajax[new][status]" value="on">
        <input type="hidden" name="ajax[new][agora_chat]" value="on">
        <input type="hidden" name="ajax[new][agora_rec]" value="on">

        <h3 class=" js-font-resize section-title after-line font-16 text-dark-blue mb-25">{{ trans('webinars.next_session_info') }}</h3>

        <div class=" js-font-resize mt-25">

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-7">
                    @if(!empty(getGeneralSettings('content_translate')))
                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('auth.language') }}</label>
                            <select name="ajax[new][locale]"
                                    class=" js-font-resize form-control"
                                    data-bundle-id=""
                                    data-id=""
                                    data-relation=""
                                    data-fields=""
                            >
                                @foreach(getUserLanguagesLists() as $lang => $language)
                                    <option value="{{ $lang }}" {{ app()->getLocale() == $lang ? 'selected' : '' }}>{{ $language }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="ajax[new][locale]" value="{{ mb_strtolower(getDefaultLocale()) }}">
                    @endif
                </div>
                <div class=" js-font-resize col-12 col-md-5">
                    <div class=" js-font-resize form-group">
                        <label class=" js-font-resize input-label">{{ trans('public.chapter') }}</label>

                        <select name="ajax[new][chapter_id]" class=" js-font-resize js-ajax-chapter_id form-control">

                        </select>

                        <div class=" js-font-resize invalid-feedback"></div>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-7">
                    <div class=" js-font-resize form-group">
                        <label class=" js-font-resize input-label">{{ trans('webinars.session_title') }}</label>
                        <input type="text" name="ajax[new][title]" class=" js-font-resize js-ajax-title form-control" value=""/>
                        <div class=" js-font-resize invalid-feedback"></div>
                    </div>
                </div>

                <div class=" js-font-resize col-12 col-md-5">
                    <div class=" js-font-resize form-group">
                        <label class=" js-font-resize input-label">{{ trans('public.date') }}</label>
                        <div class=" js-font-resize input-group">
                            <div class=" js-font-resize input-group-prepend">
                            <span class=" js-font-resize input-group-text">
                                <i data-feather="calendar" width="18" height="18" class=" js-font-resize text-white"></i>
                            </span>
                            </div>
                            <input type="text" name="ajax[new][date]" value="" class=" js-font-resize js-ajax-date form-control datetimepicker"/>
                            <div class=" js-font-resize invalid-feedback"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12">
                    <div class=" js-font-resize form-group">
                        <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
                        <textarea name="ajax[new][description]" class=" js-font-resize js-ajax-description form-control" rows="5"></textarea>
                        <div class=" js-font-resize invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        <h3 class=" js-font-resize section-title after-line font-16 text-dark-blue mb-25">{{ trans('webinars.join_information') }}</h3>

        <div class=" js-font-resize row">
            <div class=" js-font-resize col-6 js-local-link">
                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">{{ trans('public.link') }}</label>
                    <div class=" js-font-resize input-group">
                        <div class=" js-font-resize input-group-prepend">
                            <button type="button" class=" js-font-resize input-group-text js-copy" data-input="ajax[new][link]" data-toggle="tooltip" data-placement="top" title="{{ trans('public.copy') }}" data-copy-text="{{ trans('public.copy') }}" data-done-text="{{ trans('public.copied') }}">
                                <i data-feather="copy" width="18" height="18" class=" js-font-resize text-white"></i>
                            </button>
                        </div>
                        <input type="text" name="ajax[new][link]" value="" class=" js-font-resize js-ajax-link form-control"/>
                        <div class=" js-font-resize invalid-feedback"></div>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize col-6">
                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">{{ trans('public.duration') }}</label>
                    <input type="text" name="ajax[new][duration]" value="" class=" js-font-resize js-ajax-duration form-control"/>
                    <div class=" js-font-resize invalid-feedback"></div>
                </div>
            </div>

            <div class=" js-font-resize col-12 col-md-6">
                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">{{ trans('webinars.system') }}</label>

                    <select name="ajax[new][session_api]" class=" js-font-resize js-ajax-session_api form-control">
                        @foreach(getFeaturesSettings("available_session_apis") as $sessionApi)
                            <option value="{{ $sessionApi }}">{{ trans('update.session_api_'.$sessionApi) }}</option>
                        @endforeach
                    </select>
                    <div class=" js-font-resize invalid-feedback"></div>
                </div>
            </div>

            <div class=" js-font-resize col-12 col-md-6 js-api-secret">
                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">{{ trans('auth.password') }}</label>
                    <input type="text" name="ajax[new][api_secret]" class=" js-font-resize js-ajax-api_secret form-control" value=""/>
                    <div class=" js-font-resize invalid-feedback"></div>
                </div>
            </div>

            <div class=" js-font-resize col-12 col-md-6 js-moderator-secret d-none">
                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">{{ trans('public.moderator_password') }}</label>
                    <input type="text" name="ajax[new][moderator_secret]" class=" js-font-resize js-ajax-moderator_secret form-control" value=""/>
                    <div class=" js-font-resize invalid-feedback"></div>
                </div>
            </div>
        </div>

        <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
            <button type="button" class=" js-font-resize js-save-next-session btn btn-sm btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
        </div>
    </form>
</div>
