<section class=" js-font-resize p-15 m-15 border rounded-lg bg-secondary-acadima">
    <div class=" js-font-resize course-forum-top-stats d-flex flex-wrap flex-md-nowrap align-items-center justify-content-around">
        <div class=" js-font-resize d-flex align-items-center justify-content-center pb-5 pb-md-0">
            <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                <img src="/assets/default/img/activity/47.svg" class=" js-font-resize course-forum-top-stats__icon" alt="">
                <strong class=" js-font-resize font-20 text-light font-weight-bold mt-5">{{ $questionsCount }}</strong>
                <span class=" js-font-resize font-14 text-gray font-weight-500">{{ trans('public.questions') }}</span>
            </div>
        </div>

        <div class=" js-font-resize d-flex align-items-center justify-content-center pb-5 pb-md-0">
            <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                <img src="/assets/default/img/activity/120.svg" class=" js-font-resize course-forum-top-stats__icon" alt="">
                <strong class=" js-font-resize font-20 text-light font-weight-bold mt-5">{{ $resolvedCount }}</strong>
                <span class=" js-font-resize font-14 text-gray font-weight-500">{{ trans('update.resolved') }}</span>
            </div>
        </div>

        <div class=" js-font-resize d-flex align-items-center justify-content-center pb-5 pb-md-0">
            <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                <img src="/assets/default/img/activity/119.svg" class=" js-font-resize course-forum-top-stats__icon" alt="">
                <strong class=" js-font-resize font-20 text-light font-weight-bold mt-5">{{ $openQuestionsCount }}</strong>
                <span class=" js-font-resize font-14 text-gray font-weight-500">{{ trans('update.open_questions') }}</span>
            </div>
        </div>

        <div class=" js-font-resize d-flex align-items-center justify-content-center pb-5 pb-md-0">
            <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                <img src="/assets/default/img/activity/39.svg" class=" js-font-resize course-forum-top-stats__icon" alt="">
                <strong class=" js-font-resize font-20 text-light font-weight-bold mt-5">{{ $commentsCount }}</strong>
                <span class=" js-font-resize font-14 text-gray font-weight-500">{{ trans('update.answers') }}</span>
            </div>
        </div>

        <div class=" js-font-resize d-flex align-items-center justify-content-center pb-5 pb-md-0">
            <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                <img src="/assets/default/img/activity/49.svg" class=" js-font-resize course-forum-top-stats__icon" alt="">
                <strong class=" js-font-resize font-20 text-light font-weight-bold mt-5">{{ $activeUsersCount }}</strong>
                <span class=" js-font-resize font-14 text-gray font-weight-500">{{ trans('update.active_users') }}</span>
            </div>
        </div>
    </div>

    <div class=" js-font-resize container-fluid p-15 rounded-lg bg-info-light font-14 text-gray mt-20">
        <div class=" js-font-resize row align-items-center">
            <div class=" js-font-resize col-12 col-lg-4">
                <div class=" js-font-resize ">
                    <h3 class=" js-font-resize font-16 font-weight-bold text-light">{{ trans('update.course_forum') }}</h3>
                    <span class=" js-font-resize d-block font-14 font-weight-500 text-gray mt-1">{{ trans('update.communicate_others_and_ask_your_questions') }}</span>
                </div>
            </div>
            <div class=" js-font-resize col-12 col-lg-5 mt-15 mt-lg-0">
                <form action="{{ request()->url() }}" method="get">
                    <div class=" js-font-resize d-flex align-items-center">
                        <input type="text" name="search" class=" js-font-resize form-control flex-grow-1" value="{{ request()->get('search') }}" placeholder="{{ trans('update.search_in_this_forum') }}">
                        <button type="submit" class=" js-font-resize btn btn-primary btn-sm ml-10 course-forum-search-btn">
                            <i data-feather="search" class=" js-font-resize text-white" width="16" height="16"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class=" js-font-resize col-12 col-lg-3 mt-15 mt-lg-0 text-right">
                <button type="button" id="askNewQuestion" class=" js-font-resize btn btn-primary btn-sm course-forum-search-btn">
                    <i data-feather="file" class=" js-font-resize text-white" width="16" height="16"></i>
                    <span class=" js-font-resize ml-1">{{ trans('update.ask_new_question') }}</span>
                </button>
            </div>
        </div>
    </div>
</section>

@if($forums and count($forums))
    @foreach($forums as $forum)
        <div class=" js-font-resize course-forum-question-card p-15 m-15 border rounded-lg">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-lg-6">
                    <div class=" js-font-resize d-flex align-items-start">
                        <div class=" js-font-resize question-user-avatar">
                            <img src="{{ $forum->user->getAvatar(64) }}" class=" js-font-resize img-cover rounded-circle" alt="{{ $forum->user->full_name }}">
                        </div>
                        <div class=" js-font-resize ml-10">
                            <a href="{{ $course->getForumPageUrl() }}/{{ $forum->id }}/answers" class=" js-font-resize ">
                                <h4 class=" js-font-resize font-16 font-weight-bold text-light">{{ $forum->title }}</h4>
                            </a>

                            <span class=" js-font-resize d-block font-12 text-gray mt-5">{{ trans('public.by') }} {{ $forum->user->full_name }} {{ trans('public.in') }} {{ dateTimeFormat($forum->created_at, 'j M Y | H:i') }}</span>

                            <p class=" js-font-resize d-block font-14 text-gray mt-10 white-space-pre-wrap">{{ $forum->description }}</p>
                        </div>
                    </div>
                </div>
                <div class=" js-font-resize col-12 col-lg-6 mt-15 mt-lg-0 border-left">
                    @if($course->isOwner($user->id))
                        <button type="button" data-action="{{ $course->getForumPageUrl() }}/{{ $forum->id }}/pinToggle" class=" js-font-resize question-forum-pin-btn d-flex align-items-center justify-content-center">
                            <img src="/assets/default/img/learning/{{ $forum->pin ? 'un_pin' : 'pin' }}.svg" alt="pin icon" class=" js-font-resize ">
                        </button>
                    @endif


                    @if(!empty($forum->answers) and count($forum->answers))
                        <div class=" js-font-resize py-15 row">
                            <div class=" js-font-resize col-3">
                                <span class=" js-font-resize d-block font-12 text-gray">{{ trans('public.answers') }}</span>
                                <span class=" js-font-resize d-block font-14 text-dark mt-10">{{ $forum->answer_count }}</span>
                            </div>

                            <div class=" js-font-resize col-3">
                                <span class=" js-font-resize d-block font-12 text-gray">{{ trans('panel.users') }}</span>
                                <div class=" js-font-resize answers-user-icons d-flex align-items-center">
                                    @if(!empty($forum->usersAvatars))
                                        @foreach($forum->usersAvatars as $userAvatar)
                                            <div class=" js-font-resize user-avatar-card rounded-circle">
                                                <img src="{{ $userAvatar->getAvatar(32) }}" class=" js-font-resize img-cover rounded-circle" alt="{{ $userAvatar->full_name }}">
                                            </div>
                                        @endforeach
                                    @endif

                                    @if(($forum->answers->groupBy('user_id')->count() - count($forum->usersAvatars)) > 0)
                                        <span class=" js-font-resize answer-count d-flex align-items-center justify-content-center font-12 text-gray rounded-circle">+{{ $forum->answer_count - count($forum->usersAvatars) }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class=" js-font-resize col-6 position-relative">
                                <span class=" js-font-resize d-block font-12 text-gray">{{ trans('update.last_activity') }}</span>
                                <span class=" js-font-resize d-block font-14 text-dark mt-10">{{ dateTimeFormat($forum->lastAnswer->created_at,'j M Y | H:i') }}</span>
                            </div>
                        </div>

                        <div class=" js-font-resize py-15 border-top position-relative">
                            <span class=" js-font-resize d-block font-12 text-gray">{{ trans('update.last_answer') }}</span>

                            <div class=" js-font-resize d-flex align-items-start mt-20">
                                <div class=" js-font-resize last-answer-user-avatar">
                                    <img src="{{ $forum->lastAnswer->user->getAvatar(30) }}" class=" js-font-resize img-cover rounded-circle" alt="{{ $forum->lastAnswer->user->full_name }}">
                                </div>
                                <div class=" js-font-resize ml-10">
                                    <h4 class=" js-font-resize font-14 text-dark font-weight-bold">{{ $forum->lastAnswer->user->full_name }}</h4>
                                    <p class=" js-font-resize font-12 font-weight-500 text-gray mt-5">{!! truncate($forum->lastAnswer->description, 160) !!}</p>
                                </div>
                            </div>

                            @if(!empty($forum->resolved))
                                <div class=" js-font-resize resolved-answer-badge d-flex align-items-center font-12 text-primary">
                            <span class=" js-font-resize badge-icon d-flex align-items-center justify-content-center">
                                <i data-feather="check" width="20" height="20"></i>
                            </span>
                                    {{ trans('update.resolved') }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class=" js-font-resize d-flex flex-column justify-content-center text-center py-15 h-100">
                            <p class=" js-font-resize text-gray font-14 font-weight-bold">{{ trans('update.be_the_first_to_answer_this_question') }}</p>

                            <div class=" js-font-resize ">
                                <a href="{{ $course->getForumPageUrl() }}/{{ $forum->id }}/answers" class=" js-font-resize btn btn-primary btn-sm mt-15">{{ trans('public.answer') }}</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class=" js-font-resize learning-page-forum-empty d-flex align-items-center justify-content-center flex-column">
        <div class=" js-font-resize learning-page-forum-empty-icon d-flex align-items-center justify-content-center">
            <img src="/assets/default/img/learning/forum-empty.svg" class=" js-font-resize img-fluid" alt="">
        </div>

        <div class=" js-font-resize d-flex align-items-center flex-column mt-10 text-center">
            <h3 class=" js-font-resize font-20 font-weight-bold text-light text-center"></h3>
            <p class=" js-font-resize font-14 font-weight-500 text-gray mt-5 text-center">{{ trans('update.learning_page_empty_content_title_hint') }}</p>
        </div>
    </div>
@endif

@include('web.default.course.learningPage.components.forum.ask_question_modal')
