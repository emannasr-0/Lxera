@if(!empty($forumTopics) and !$forumTopics->isEmpty())
    <div class=" js-font-resize px-15 py-20">

        @foreach($forumTopics as $topic)
            <div class=" js-font-resize topics-lists-card row align-items-center py-10">
                <div class=" js-font-resize col-12 col-md-6">
                    <div class=" js-font-resize d-flex align-items-center">
                        <div class=" js-font-resize topic-user-avatar rounded-circle">
                            <img src="{{ $user->getAvatar() }}" class=" js-font-resize img-cover rounded-circle" alt="{{ $user->full_name }}">
                        </div>
                        <div class=" js-font-resize ml-10 mw-100">
                            <a href="{{ $topic->getPostsUrl() }}" class=" js-font-resize ">
                                <h4 class=" js-font-resize font-16 font-weight-bold text-secondary text-ellipsis">{{ $topic->title }}</h4>
                            </a>
                            <span class=" js-font-resize d-block font-14 text-gray">{{ trans('public.by') }} {{ $user->full_name }} {{ trans('public.in') }} {{ dateTimeFormat($topic->created_at,'j M Y | H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize col-12 col-md-6">
                    <div class=" js-font-resize row">
                        <div class=" js-font-resize col-3 text-center">
                            <span class=" js-font-resize d-block font-14 text-gray font-weight-bold">{{ $topic->posts_count }}</span>
                            <span class=" js-font-resize d-block font-12 text-gray">{{ trans('site.posts') }}</span>
                        </div>
                        <div class=" js-font-resize col-3 d-flex align-items-center">
                            @if($topic->pin)
                                <div class=" js-font-resize topics-lists-card__icons rounded-circle mr-10">
                                    <img src="/assets/default/img/learning/un_pin.svg" alt="" class=" js-font-resize img-cover rounded-circle">
                                </div>
                            @endif

                            @if($topic->close)
                                <div class=" js-font-resize topics-lists-card__icons rounded-circle">
                                    <img src="/assets/default/img/learning/lock.svg" alt="" class=" js-font-resize img-cover rounded-circle">
                                </div>
                            @endif
                        </div>
                        <div class=" js-font-resize col-12 col-md-6">
                            @if(!empty($topic->lastPost))
                                <div class=" js-font-resize d-flex align-items-center">
                                    <div class=" js-font-resize topic-last-post-user-avatar rounded-circle">
                                        <img src="{{ $topic->lastPost->user->getAvatar(30) }}" class=" js-font-resize img-cover rounded-circle" alt="{{ $topic->lastPost->user->full_name }}">
                                    </div>
                                    <div class=" js-font-resize ml-10">
                                        <h4 class=" js-font-resize font-14 font-weight-500 text-gray">{{ $topic->lastPost->user->full_name }}</h4>
                                        <span class=" js-font-resize d-block font-12 font-weight-500 text-gray">{{ trans('public.in') }} {{ dateTimeFormat($topic->lastPost->created_at,'j M Y | H:i') }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    @include(getTemplate() . '.includes.no-result',[
        'file_name' => 'webinar.png',
        'title' => trans('update.instructor_not_have_topics'),
        'hint' => '',
    ])
@endif

