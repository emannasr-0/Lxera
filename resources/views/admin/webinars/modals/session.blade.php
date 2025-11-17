<!-- Modal -->
<div class=" js-font-resize d-none" id="webinarSessionModal">
    <h3 class=" js-font-resize section-title after-line font-20 text-dark-blue mb-25">{{ trans('public.add_session') }}</h3>

    <form action="{{ getAdminPanelUrl() }}/sessions/store" method="post" class=" js-font-resize session-form">
        <input type="hidden" name="webinar_id" value="{{ !empty($webinar) ? $webinar->id :''  }}">

        @if(!empty(getGeneralSettings('content_translate')))
            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('auth.language') }}</label>
                <select name="locale" class=" js-font-resize form-control ">
                    @foreach($userLanguages as $lang => $language)
                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                    @endforeach
                </select>
                @error('locale')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        @else
            <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
        @endif


        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('webinars.select_session_api') }}</label>

            <div class=" js-font-resize js-session-api">
                <div class=" js-font-resize custom-control custom-radio custom-control-inline">
                    <input type="radio" name="session_api" id="localApi_record" value="local" checked class=" js-font-resize js-api-input custom-control-input">
                    <label class=" js-font-resize custom-control-label" for="localApi_record">{{ trans('webinars.session_local_api') }}</label>
                </div>

                <div class=" js-font-resize custom-control custom-radio custom-control-inline">
                    <input type="radio" name="session_api" id="bigBlueButton_record" value="big_blue_button" class=" js-font-resize js-api-input custom-control-input">
                    <label class=" js-font-resize custom-control-label" for="bigBlueButton_record">{{ trans('webinars.session_big_blue_button') }}</label>
                </div>

                <div class=" js-font-resize custom-control custom-radio custom-control-inline">
                    <input type="radio" name="session_api" id="zoomApi_record" value="zoom" class=" js-font-resize js-api-input custom-control-input">
                    <label class=" js-font-resize custom-control-label" for="zoomApi_record">{{ trans('webinars.session_zoom') }}</label>
                </div>

                <div class=" js-font-resize custom-control custom-radio custom-control-inline">
                    <input type="radio" name="session_api" id="agoraApi_record" value="agora" class=" js-font-resize js-api-input custom-control-input">
                    <label class=" js-font-resize custom-control-label" for="agoraApi_record">{{ trans('update.agora') }}</label>
                </div>
            </div>

            <div class=" js-font-resize invalid-feedback"></div>

            <div class=" js-font-resize js-zoom-not-complete-alert mt-10 text-danger d-none">
                {{ trans('admin/main.teacher_zoom_jwt_token_invalid') }}
            </div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.chapter') }}</label>
            <select class=" js-font-resize custom-select" name="chapter_id">
                <option value="">{{ trans('admin/main.no_chapter') }}</option>

                @if(!empty($chapters))
                    @foreach($chapters as $chapter)
                        <option value="{{ $chapter->id }}">{{ $chapter->title }}</option>
                    @endforeach
                @endif
            </select>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group js-api-secret">
            <label class=" js-font-resize input-label">{{ trans('auth.password') }}</label>
            <input type="text" name="api_secret" class=" js-font-resize js-ajax-api_secret form-control" value=""/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group js-moderator-secret d-none">
            <label class=" js-font-resize input-label">{{ trans('public.moderator_password') }}</label>
            <input type="text" name="moderator_secret" class=" js-font-resize js-ajax-moderator_secret form-control" value=""/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>


        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.title') }}</label>
            <input type="text" name="title" class=" js-font-resize form-control" placeholder="{{ trans('forms.maximum_255_characters') }}"/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.date') }}</label>
            <div class=" js-font-resize input-group">
                <div class=" js-font-resize input-group-prepend">
                    <span class=" js-font-resize input-group-text" id="dateRangeLabel">
                        <i class=" js-font-resize fa fa-calendar"></i>
                    </span>
                </div>
                <input type="text" name="date" class=" js-font-resize js-ajax-date form-control datetimepicker" aria-describedby="dateRangeLabel"/>
                <div class=" js-font-resize invalid-feedback"></div>
            </div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.duration') }} <span class=" js-font-resize braces">({{ trans('public.minutes') }})</span></label>
            <input type="text" name="duration" class=" js-font-resize js-ajax-duration form-control" placeholder=""/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('update.extra_time_to_join') }} <span class=" js-font-resize braces">({{ trans('public.minutes') }})</span></label>
            <input type="text" name="extra_time_to_join" class=" js-font-resize js-ajax-extra_time_to_join form-control" placeholder=""/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group js-local-link">
            <label class=" js-font-resize input-label">{{ trans('public.link') }}</label>
            <input type="text" name="link" class=" js-font-resize js-ajax-link form-control" placeholder=""/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
            <textarea name="description" class=" js-font-resize form-control" rows="6"></textarea>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize js-session-status form-group mt-3">
            <div class=" js-font-resize d-flex align-items-center justify-content-between">
                <label class=" js-font-resize cursor-pointer input-label" for="sessionStatusSwitch_record">{{ trans('admin/main.active') }}</label>
                <div class=" js-font-resize custom-control custom-switch">
                    <input type="checkbox" name="status" checked class=" js-font-resize custom-control-input" id="sessionStatusSwitch_record">
                    <label class=" js-font-resize custom-control-label" for="sessionStatusSwitch_record"></label>
                </div>
            </div>
        </div>

        <div class=" js-font-resize js-agora-chat-and-rec d-none">
            @if(getFeaturesSettings('agora_chat'))
                <div class=" js-font-resize form-group mt-20">
                    <div class=" js-font-resize d-flex align-items-center justify-content-between">
                        <label class=" js-font-resize cursor-pointer input-label" for="sessionAgoraChatSwitch_record">{{ trans('update.chat') }}</label>
                        <div class=" js-font-resize custom-control custom-switch">
                            <input type="checkbox" name="agora_chat" class=" js-font-resize custom-control-input" id="sessionAgoraChatSwitch_record">
                            <label class=" js-font-resize custom-control-label" for="sessionAgoraChatSwitch_record"></label>
                        </div>
                    </div>
                </div>
            @endif

            {{--
                <div class=" js-font-resize form-group mt-20">
                    <div class=" js-font-resize d-flex align-items-center justify-content-between">
                        <label class=" js-font-resize cursor-pointer input-label" for="sessionAgoraRecordSwitch_record">{{ trans('update.record') }}</label>
                        <div class=" js-font-resize custom-control custom-switch">
                            <input type="checkbox" name="agora_record" class=" js-font-resize custom-control-input" id="sessionAgoraRecordSwitch_record" >
                            <label class=" js-font-resize custom-control-label" for="sessionAgoraRecordSwitch_record"></label>
                        </div>
                    </div>
                </div>
            --}}

        </div>

        @if(getFeaturesSettings('sequence_content_status'))
            <div class=" js-font-resize form-group mb-1">
                <div class=" js-font-resize d-flex align-items-center justify-content-between">
                    <label class=" js-font-resize cursor-pointer input-label" for="SequenceContentSwitch_record">{{ trans('update.sequence_content') }}</label>
                    <div class=" js-font-resize custom-control custom-switch">
                        <input type="checkbox" name="sequence_content" class=" js-font-resize js-sequence-content-switch custom-control-input" id="SequenceContentSwitch_record">
                        <label class=" js-font-resize custom-control-label" for="SequenceContentSwitch_record"></label>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize js-sequence-content-inputs pl-2 d-none">
                <div class=" js-font-resize form-group mb-1">
                    <div class=" js-font-resize d-flex align-items-center justify-content-between">
                        <label class=" js-font-resize cursor-pointer input-label" for="checkPreviousPartsSwitch_record">{{ trans('update.check_previous_parts') }}</label>
                        <div class=" js-font-resize custom-control custom-switch">
                            <input type="checkbox" checked name="check_previous_parts" class=" js-font-resize custom-control-input" id="checkPreviousPartsSwitch_record">
                            <label class=" js-font-resize custom-control-label" for="checkPreviousPartsSwitch_record"></label>
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">{{ trans('update.access_after_day') }}</label>
                    <input type="number" name="access_after_day" value="" class=" js-font-resize js-ajax-access_after_day form-control" placeholder="{{ trans('update.access_after_day_placeholder') }}"/>
                    <div class=" js-font-resize invalid-feedback"></div>
                </div>
            </div>
        @endif

        <div class=" js-font-resize mt-3 d-flex align-items-center justify-content-end">
            <button type="button" id="saveSession" class=" js-font-resize btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class=" js-font-resize btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </form>
</div>
