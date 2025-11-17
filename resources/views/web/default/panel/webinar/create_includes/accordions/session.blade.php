@php
    if (!empty($session->agora_settings)) {
        $session->agora_settings = json_decode($session->agora_settings);
    }
@endphp

<li data-id="{{ !empty($chapterItem) ? $chapterItem->id :'' }}" class=" js-font-resize accordion-row bg-white rounded-sm border border-gray300 mt-20 py-15 py-lg-30 px-10 px-lg-20">
    <div class=" js-font-resize d-flex align-items-center justify-content-between " role="tab" id="session_{{ !empty($session) ? $session->id :'record' }}">
        <div class=" js-font-resize d-flex align-items-center" href="#collapseSession{{ !empty($session) ? $session->id :'record' }}" aria-controls="collapseSession{{ !empty($session) ? $session->id :'record' }}" data-parent="#chapterContentAccordion{{ !empty($chapter) ? $chapter->id :'' }}" role="button" data-toggle="collapse" aria-expanded="true">
            <span class=" js-font-resize chapter-icon chapter-content-icon mr-10">
                <i data-feather="file-text" class=" js-font-resize "></i>
            </span>

            <div class=" js-font-resize font-weight-bold text-dark-blue d-block">{{ !empty($session) ? $session->title : trans('public.add_new_sessions') }}</div>
        </div>

        <div class=" js-font-resize d-flex align-items-center">

            @if(!empty($session) and $session->status != \App\Models\WebinarChapter::$chapterActive)
                <span class=" js-font-resize disabled-content-badge mr-10">{{ trans('public.disabled') }}</span>
            @endif

            {{-- @if(!empty($session))
                <button type="button" data-item-id="{{ $session->id }}" data-item-type="{{ \App\Models\WebinarChapterItem::$chapterSession }}" data-chapter-id="{{ !empty($chapter) ? $chapter->id : '' }}" class=" js-font-resize js-change-content-chapter btn btn-sm btn-transparent text-gray mr-10">
                    <i data-feather="grid" class=" js-font-resize " height="20"></i>
                </button>
            @endif --}}

            <i data-feather="move" class=" js-font-resize move-icon mr-10 cursor-pointer" height="20"></i>

            {{-- @if(!empty($session))
                <a href="/panel/sessions/{{ $session->id }}/delete" class=" js-font-resize delete-action btn btn-sm btn-transparent text-gray">
                    <i data-feather="trash-2" class=" js-font-resize mr-10 cursor-pointer" height="20"></i>
                </a>
            @endif --}}

            <i class=" js-font-resize collapse-chevron-icon" data-feather="chevron-down" height="20" href="#collapseSession{{ !empty($session) ? $session->id :'record' }}" aria-controls="collapseSession{{ !empty($session) ? $session->id :'record' }}" data-parent="#chapterContentAccordion{{ !empty($chapter) ? $chapter->id :'' }}" role="button" data-toggle="collapse" aria-expanded="true"></i>
        </div>
    </div>

    <div id="collapseSession{{ !empty($session) ? $session->id :'record' }}" aria-labelledby="session_{{ !empty($session) ? $session->id :'record' }}" class=" js-font-resize  collapse @if(empty($session)) show @endif" role="tabpanel">
        <div class=" js-font-resize panel-collapse text-gray">
            <div class=" js-font-resize js-content-form session-form" data-action="/panel/sessions/{{ !empty($session) ? $session->id . '/update' : 'store' }}">
                <input readonly type="hidden" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][webinar_id]" value="{{ !empty($webinar) ? $webinar->id :'' }}">

                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">{{ trans('webinars.select_session_api') }}</label>

                    <div class=" js-font-resize js-session-api">
                        @foreach(getFeaturesSettings("available_session_apis") as $sessionApi)
                            <div class=" js-font-resize custom-control custom-radio custom-control-inline">
                                <input readonly type="radio" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][session_api]" id="{{ $sessionApi }}_api_{{ !empty($session) ? $session->id : '' }}" value="{{ $sessionApi }}" @if((!empty($session) and $session->session_api == $sessionApi) or (empty($session) and $sessionApi == 'local')) checked @endif class=" js-font-resize js-api-input custom-control-input" {{ (!empty($session) and $session->session_api != 'local') ? 'disabled' :'' }}>
                                <label class=" js-font-resize custom-control-label" for="{{ $sessionApi }}_api_{{ !empty($session) ? $session->id : '' }}">{{ trans('update.session_api_'.$sessionApi) }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class=" js-font-resize invalid-feedback"></div>

                    <div class=" js-font-resize js-zoom-not-complete-alert mt-10 text-danger d-none">
                        {{ trans('webinars.your_zoom_settings_are_not_complete') }}
                        <a href="/panel/setting/step/8" class=" js-font-resize text-primary" target="_blank">{{ trans('public.go_to_settings') }}</a>
                    </div>
                </div>


                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 col-lg-6">

                        @if(!empty(getGeneralSettings('content_translate')))
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('auth.language') }}</label>
                                <select disabled name="ajax[{{ !empty($session) ? $session->id : 'new' }}][locale]"
                                        class=" js-font-resize form-control {{ !empty($session) ? 'js-webinar-content-locale' : '' }}"
                                        data-webinar-id="{{ !empty($webinar) ? $webinar->id : '' }}"
                                        data-id="{{ !empty($session) ? $session->id : '' }}"
                                        data-relation="sessions"
                                        data-fields="title,description"
                                >
                                    @foreach($userLanguages as $lang => $language)
                                        <option value="{{ $lang }}" {{ (!empty($session) and !empty($session->locale)) ? (mb_strtolower($session->locale) == mb_strtolower($lang) ? 'selected' : '') : ($locale == $lang ? 'selected' : '') }}>{{ $language }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input readonly type="hidden" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][locale]" value="{{ $defaultLocale }}">
                        @endif

                        <div class=" js-font-resize form-group js-api-secret {{ (!empty($session) and in_array($session->session_api, ['zoom', 'agora', 'jitsi'])) ? 'd-none' :'' }}">
                            <label class=" js-font-resize input-label">{{ trans('auth.password') }}</label>
                            <input readonly type="text" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][api_secret]" class=" js-font-resize js-ajax-api_secret form-control" value="{{ !empty($session) ? $session->api_secret : '' }}" {{ (!empty($session) and $session->session_api != 'local') ? 'disabled' :'' }}/>
                            <div class=" js-font-resize invalid-feedback"></div>
                        </div>

                        <div class=" js-font-resize form-group js-moderator-secret {{ (empty($session) or $session->session_api != 'big_blue_button') ? 'd-none' :'' }}">
                            <label class=" js-font-resize input-label">{{ trans('public.moderator_password') }}</label>
                            <input readonly type="text" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][moderator_secret]" class=" js-font-resize js-ajax-moderator_secret form-control" value="{{ !empty($session) ? $session->moderator_secret : '' }}" {{ (!empty($session) and $session->session_api == 'big_blue_button') ? 'disabled' :'' }}/>
                            <div class=" js-font-resize invalid-feedback"></div>
                        </div>

                        @if(!empty($session))
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('public.chapter') }}</label>
                                <select disabled name="ajax[{{ !empty($session) ? $session->id : 'new' }}][chapter_id]" class=" js-font-resize js-ajax-chapter_id form-control">
                                    @foreach($webinar->chapters as $ch)
                                        <option value="{{ $ch->id }}" {{ ($session->chapter_id == $ch->id) ? 'selected' : '' }}>{{ $ch->title }}</option>
                                    @endforeach
                                </select>
                                <div class=" js-font-resize invalid-feedback"></div>
                            </div>
                        @else
                            <input readonly type="hidden" name="ajax[new][chapter_id]" value="" class=" js-font-resize chapter-input">
                        @endif

                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('public.title') }}</label>
                            <input readonly type="text" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][title]" class=" js-font-resize js-ajax-title form-control" value="{{ !empty($session) ? $session->title : '' }}" placeholder="{{ trans('forms.maximum_255_characters') }}"/>
                            <div class=" js-font-resize invalid-feedback"></div>
                        </div>

                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('public.date') }}</label>
                            <div class=" js-font-resize input-group">
                                <div class=" js-font-resize input-group-prepend">
                                    <span class=" js-font-resize input-group-text" id="dateRangeLabel">
                                        <i data-feather="calendar" width="18" height="18" class=" js-font-resize text-white"></i>
                                    </span>
                                </div>
                                <input readonly type="text" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][date]" class=" js-font-resize js-ajax-date form-control datetimepicker" value="{{ !empty($session) ? dateTimeFormat($session->date, 'Y-m-d H:i', false) : '' }}" aria-describedby="dateRangeLabel" {{ (!empty($session) and $session->session_api != 'local') ? 'disabled' :'' }} autocomplete="off"/>
                                <div class=" js-font-resize invalid-feedback"></div>
                            </div>
                        </div>

                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('public.duration') }} <span class=" js-font-resize braces">({{ trans('public.minutes') }})</span></label>
                            <input readonly type="text" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][duration]" class=" js-font-resize js-ajax-duration form-control" value="{{ !empty($session) ? $session->duration : '' }}" {{ (!empty($session) and $session->session_api != 'local') ? 'disabled' :'' }}/>
                            <div class=" js-font-resize invalid-feedback"></div>
                        </div>

                        <div class=" js-font-resize form-group js-local-link {{ (!empty($session) and in_array($session->session_api, ['agora', 'jitsi'])) ? 'd-none' : '' }}">
                            <label class=" js-font-resize input-label">{{ trans('public.link') }}</label>
                            <input readonly type="text" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][link]" class=" js-font-resize js-ajax-link form-control" value="{{ !empty($session) ? $session->getJoinLink() : '' }}" {{ (!empty($session) and $session->session_api != 'local') ? 'disabled' :'' }}/>
                            <div class=" js-font-resize invalid-feedback"></div>
                        </div>

                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
                            <textarea name="ajax[{{ !empty($session) ? $session->id : 'new' }}][description]" class=" js-font-resize js-ajax-description form-control" rows="6">{{ !empty($session) ? $session->description : '' }}</textarea>
                            <div class=" js-font-resize invalid-feedback"></div>
                        </div>

                        @if(!empty(getFeaturesSettings('extra_time_to_join_status')) and getFeaturesSettings('extra_time_to_join_status'))
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('update.extra_time_to_join') }} <span class=" js-font-resize braces">({{ trans('public.minutes') }})</span></label>
                                <input readonly type="text" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][extra_time_to_join]" value="{{ (!empty($session) and $session->extra_time_to_join) ? $session->extra_time_to_join : getFeaturesSettings('extra_time_to_join_default_value') }}" class=" js-font-resize js-ajax-extra_time_to_join form-control" placeholder=""/>
                                <div class=" js-font-resize invalid-feedback"></div>
                            </div>
                        @elseif(!empty(getFeaturesSettings('extra_time_to_join_default_value')))
                            <input readonly type="hidden" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][extra_time_to_join]" value="{{ (!empty($session) and $session->extra_time_to_join) ? $session->extra_time_to_join : getFeaturesSettings('extra_time_to_join_default_value') }}" class=" js-font-resize js-ajax-extra_time_to_join form-control" placeholder=""/>
                        @endif

                        <div class=" js-font-resize form-group mt-20">
                            <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                <label class=" js-font-resize cursor-pointer input-label" for="sessionStatusSwitch{{ !empty($session) ? $session->id : '_record' }}">{{ trans('public.active') }}</label>
                                <div class=" js-font-resize custom-control custom-switch">
                                    <input readonly type="checkbox" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][status]" class=" js-font-resize custom-control-input" id="sessionStatusSwitch{{ !empty($session) ? $session->id : '_record' }}" {{ (empty($session) or $session->status == \App\Models\Session::$Active) ? 'checked' : ''  }}>
                                    <label class=" js-font-resize custom-control-label" for="sessionStatusSwitch{{ !empty($session) ? $session->id : '_record' }}"></label>
                                </div>
                            </div>
                        </div>

                        <div class=" js-font-resize js-agora-chat-and-rec  {{ (empty($session) or $session->session_api !== 'agora') ? 'd-none' : '' }}">
                            @if(getFeaturesSettings('agora_chat'))
                                <div class=" js-font-resize form-group mt-20">
                                    <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                        <label class=" js-font-resize cursor-pointer input-label" for="sessionAgoraChatSwitch{{ !empty($session) ? $session->id : '_record' }}">{{ trans('update.chat') }}</label>
                                        <div class=" js-font-resize custom-control custom-switch">
                                            <input readonly type="checkbox" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][agora_chat]" class=" js-font-resize custom-control-input" id="sessionAgoraChatSwitch{{ !empty($session) ? $session->id : '_record' }}" {{ (!empty($session) and !empty($session->agora_settings) and $session->agora_settings->chat) ? 'checked' : ''  }}>
                                            <label class=" js-font-resize custom-control-label" for="sessionAgoraChatSwitch{{ !empty($session) ? $session->id : '_record' }}"></label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{--
                                                        <div class=" js-font-resize form-group mt-20">
                                                            <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                                                <label class=" js-font-resize cursor-pointer input-label" for="sessionAgoraRecordSwitch{{ !empty($session) ? $session->id : '_record' }}">{{ trans('update.record') }}</label>
                                                                <div class=" js-font-resize custom-control custom-switch">
                                                                    <input readonly type="checkbox" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][agora_record]" class=" js-font-resize custom-control-input" id="sessionAgoraRecordSwitch{{ !empty($session) ? $session->id : '_record' }}" {{ (!empty($session) and !empty($session->agora_settings) and $session->agora_settings->record) ? 'checked' : ''  }}>
                                                                    <label class=" js-font-resize custom-control-label" for="sessionAgoraRecordSwitch{{ !empty($session) ? $session->id : '_record' }}"></label>
                                                                </div>
                                                            </div>
                                                        </div>
                            --}}

                        </div>

                        @if(getFeaturesSettings('sequence_content_status'))
                            <div class=" js-font-resize form-group mt-20">
                                <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                    <label class=" js-font-resize cursor-pointer input-label" for="SequenceContentSwitch{{ !empty($session) ? $session->id : '_record' }}">{{ trans('update.sequence_content') }}</label>
                                    <div class=" js-font-resize custom-control custom-switch">
                                        <input readonly type="checkbox" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][sequence_content]" class=" js-font-resize js-sequence-content-switch custom-control-input" id="SequenceContentSwitch{{ !empty($session) ? $session->id : '_record' }}" {{ (!empty($session) and ($session->check_previous_parts or !empty($session->access_after_day))) ? 'checked' : ''  }}>
                                        <label class=" js-font-resize custom-control-label" for="SequenceContentSwitch{{ !empty($session) ? $session->id : '_record' }}"></label>
                                    </div>
                                </div>
                            </div>

                            <div class=" js-font-resize js-sequence-content-inputs pl-5 {{ (!empty($session) and ($session->check_previous_parts or !empty($session->access_after_day))) ? '' : 'd-none' }}">
                                <div class=" js-font-resize form-group">
                                    <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                        <label class=" js-font-resize cursor-pointer input-label" for="checkPreviousPartsSwitch{{ !empty($session) ? $session->id : '_record' }}">{{ trans('update.check_previous_parts') }}</label>
                                        <div class=" js-font-resize custom-control custom-switch">
                                            <input readonly type="checkbox" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][check_previous_parts]" class=" js-font-resize custom-control-input" id="checkPreviousPartsSwitch{{ !empty($session) ? $session->id : '_record' }}" {{ (empty($session) or $session->check_previous_parts) ? 'checked' : ''  }}>
                                            <label class=" js-font-resize custom-control-label" for="checkPreviousPartsSwitch{{ !empty($session) ? $session->id : '_record' }}"></label>
                                        </div>
                                    </div>
                                </div>

                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{ trans('update.access_after_day') }}</label>
                                    <input readonly type="number" name="ajax[{{ !empty($session) ? $session->id : 'new' }}][access_after_day]" value="{{ (!empty($session)) ? $session->access_after_day : '' }}" class=" js-font-resize js-ajax-access_after_day form-control" placeholder="{{ trans('update.access_after_day_placeholder') }}"/>
                                    <div class=" js-font-resize invalid-feedback"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class=" js-font-resize mt-30 d-flex align-items-center">
                   {{-- <button type="button" class=" js-font-resize js-save-session btn btn-sm btn-primary">{{ trans('public.save') }}</button>  --}}

                    @if(!empty($session))
                        @if(!$session->isFinished())
                            <a href="{{ $session->getJoinLink(true) }}" target="_blank" class=" js-font-resize ml-10 btn btn-sm btn-secondary">{{ trans('footer.join') }}</a>
                        @else
                            <button type="button" class=" js-font-resize js-session-has-ended ml-10 btn btn-sm btn-secondary disabled">{{ trans('footer.join') }}</button>
                        @endif
                    @endif

                    @if(empty($session))
                        <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 cancel-accordion">{{ trans('public.close') }}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</li>
