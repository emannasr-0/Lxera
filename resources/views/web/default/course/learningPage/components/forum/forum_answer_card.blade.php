@php
    $cardUser = !empty($answer) ? $answer->user : $courseForum->user;
@endphp

<div class=" js-font-resize course-forum-answer-card py-15 m-15 rounded-lg {{ (!empty($answer) and $answer->resolved) ? 'resolved' : '' }}">
    <div class=" js-font-resize d-flex flex-wrap">
        <div class=" js-font-resize col-12 col-md-3">
            <div class=" js-font-resize position-relative bg-info-light d-flex flex-column align-items-center justify-content-center rounded-lg w-100 h-100 p-20">
                <div class=" js-font-resize user-avatar rounded-circle {{ (!empty($answer) and $cardUser->isTeacher()) ? 'is-instructor' : '' }}">
                    <img src="{{ $cardUser->getAvatar(72) }}" class=" js-font-resize img-cover rounded-circle" alt="{{ $cardUser->full_name }}">
                </div>
                <h4 class=" js-font-resize font-14 text-secondary mt-15 font-weight-bold">{{ $cardUser->full_name }}</h4>

                <span class=" js-font-resize px-10 py-5 mt-5 rounded-lg border bg-info-light text-center font-12 text-gray">
                    @if($cardUser->isUser())
                        {{ trans('quiz.student') }}
                    @elseif($cardUser->isTeacher())
                        {{ trans('public.instructor') }}
                    @elseif($cardUser->isOrganization())
                        {{ trans('home.organization') }}
                    @elseif($cardUser->isAdmin())
                        {{ trans('panel.staff') }}
                    @endif
                </span>

                @if(!empty($answer) and $answer->pin)
                    <span class=" js-font-resize pinned-icon d-flex align-items-center justify-content-center">
                        <img src="/assets/default/img/learning/un_pin.svg" alt="pin icon" class=" js-font-resize ">
                    </span>
                @endif
            </div>
        </div>

        <div class=" js-font-resize col-12 col-md-9 mt-15 mt-md-0">
            <div class=" js-font-resize d-flex flex-column justify-content-between h-100">
                <div class=" js-font-resize ">
                    <p class=" js-font-resize font-14 text-gray d-block white-space-pre-wrap">{{ !empty($answer) ? $answer->description : $courseForum->description }}</p>

                    @if(empty($answer) and !empty($courseForum->attach))
                        <div class=" js-font-resize mt-25 d-inline-block">
                            <a href="{{ $course->getForumPageUrl() }}/{{ $courseForum->id }}/downloadAttach" target="_blank" class=" js-font-resize d-flex align-items-center text-gray bg-info-light border px-10 py-5 rounded-pill">
                                <i data-feather="paperclip" class=" js-font-resize text-gray" width="16" height="16"></i>
                                <span class=" js-font-resize ml-5 font-12 text-gray">{{ trans('update.attachment') }}</span>
                            </a>
                        </div>
                    @endif
                </div>

                <div class=" js-font-resize d-flex align-items-center justify-content-between mt-15 pt-15 border-top">
                    <span class=" js-font-resize font-12 font-weight-500 text-gray">{{ dateTimeFormat(!empty($answer) ? $answer->created_at : $courseForum->created_at,'j M Y | H:i') }}</span>

                    <div class=" js-font-resize d-flex align-items-center">
                        @if(empty($answer) and $user->id == $courseForum->user_id)
                            <button type="button" data-action="{{ $course->getForumPageUrl() }}/{{ $courseForum->id }}/edit" class=" js-font-resize js-edit-forum btn-transparent font-12 font-weight-500 text-gray">{{ trans('public.edit') }}</button>
                        @elseif(!empty($answer))
                            @if($course->isOwner($user->id))
                                @if($answer->pin)
                                    <button type="button" data-action="{{ $course->getForumPageUrl() }}/{{ $courseForum->id }}/answers/{{ $answer->id }}/un_pin" class=" js-font-resize js-btn-answer-un_pin btn-transparent font-12 font-weight-500 text-warning">{{ trans('update.un_pin') }}</button>
                                @else
                                    <button type="button" data-action="{{ $course->getForumPageUrl() }}/{{ $courseForum->id }}/answers/{{ $answer->id }}/pin" class=" js-font-resize js-btn-answer-pin btn-transparent font-12 font-weight-500 text-gray">{{ trans('update.pin') }}</button>
                                @endif
                            @endif

                            @if($course->isOwner($user->id) or $user->id == $courseForum->user_id)
                                @if($answer->resolved)
                                    <button type="button" data-action="{{ $course->getForumPageUrl() }}/{{ $courseForum->id }}/answers/{{ $answer->id }}/mark_as_not_resolved" class=" js-font-resize js-btn-answer-mark_as_not_resolved btn-transparent font-12 font-weight-500 text-gray ml-20">{{ trans('update.mark_as_not_resolved') }}</button>
                                @else
                                    <button type="button" data-action="{{ $course->getForumPageUrl() }}/{{ $courseForum->id }}/answers/{{ $answer->id }}/mark_as_resolved" class=" js-font-resize js-btn-answer-mark_as_resolved btn-transparent font-12 font-weight-500 text-gray ml-20">{{ trans('update.mark_as_resolved') }}</button>
                                @endif
                            @endif

                            @if($user->id == $answer->user_id)
                                <button type="button" data-action="{{ $course->getForumPageUrl() }}/{{ $courseForum->id }}/answers/{{ $answer->id }}/edit" class=" js-font-resize js-edit-forum-answer btn-transparent font-12 font-weight-500 text-gray ml-20">{{ trans('public.edit') }}</button>
                            @endif

                            @if($answer->resolved)
                                <div class=" js-font-resize resolved-answer-badge d-flex align-items-center ml-25 text-primary font-12">
                                    <span class=" js-font-resize badge-icon d-flex align-items-center justify-content-center">
                                        <i data-feather="check" width="20" height="20"></i>
                                    </span>
                                    {{ trans('update.resolved') }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
