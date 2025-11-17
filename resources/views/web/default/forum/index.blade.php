@extends('web.default.layouts.app')

@section('content')
    <section class=" js-font-resize forum-hero-section mt-50 position-relative">
        <div class=" js-font-resize container forum-hero-section__container">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-6">
                    <h1 class=" js-font-resize font-36 text-secondary">
                        <span>{{ trans('update.need_help?') }}</span><br/>
                        <span>{{ trans('update.create_a_topic_in_forum') }}</span>
                    </h1>
                    <p class=" js-font-resize font-14 text-gray mt-15">{{ trans('update.forum_top_section_hint') }}</p>

                    <div class=" js-font-resize search-input bg-white p-10 flex-grow-1 mt-25">
                        <form action="/forums/search" method="get">
                            <div class=" js-font-resize form-group d-flex align-items-center m-0">
                                <input type="text" name="search" class=" js-font-resize form-control border-0" placeholder="{{ trans('update.search_discussions') }}"/>
                                <button type="submit" class=" js-font-resize btn btn-primary rounded-pill">{{ trans('public.search') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class=" js-font-resize forum-hero-section__image">
            <img src="/assets/default/img/forum/hero.png" class=" js-font-resize img-cover" alt="forum hero">
        </div>
    </section>

    <div class=" js-font-resize container mt-40">
        <div class=" js-font-resize forum-stat-section rounded-lg bg-white p-20">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-6 col-md-3">
                    <div class=" js-font-resize d-flex align-items-center justify-content-center flex-column">
                        <img src="/assets/default/img/forum/1.svg" alt="{{ trans('update.forums') }}" class=" js-font-resize forum-stat-icon"/>
                        <span class=" js-font-resize font-30 font-weight-bold text-secondary">{{ $forumsCount }}</span>
                        <span class=" js-font-resize font-16 font-weight-500 text-gray">{{ trans('update.forums') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3">
                    <div class=" js-font-resize d-flex align-items-center justify-content-center flex-column">
                        <img src="/assets/default/img/forum/2.svg" alt="{{ trans('update.topics') }}" class=" js-font-resize forum-stat-icon"/>
                        <span class=" js-font-resize font-30 font-weight-bold text-secondary">{{ $topicsCount }}</span>
                        <span class=" js-font-resize font-16 font-weight-500 text-gray">{{ trans('update.topics') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3">
                    <div class=" js-font-resize d-flex align-items-center justify-content-center flex-column">
                        <img src="/assets/default/img/forum/3.svg" alt="{{ trans('site.posts') }}" class=" js-font-resize forum-stat-icon"/>
                        <span class=" js-font-resize font-30 font-weight-bold text-secondary">{{ $postsCount }}</span>
                        <span class=" js-font-resize font-16 font-weight-500 text-gray">{{ trans('site.posts') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3">
                    <div class=" js-font-resize d-flex align-items-center justify-content-center flex-column">
                        <img src="/assets/default/img/forum/4.svg" alt="{{ trans('update.members') }}" class=" js-font-resize forum-stat-icon"/>
                        <span class=" js-font-resize font-30 font-weight-bold text-secondary">{{ $membersCount }}</span>
                        <span class=" js-font-resize font-16 font-weight-500 text-gray">{{ trans('update.members') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($featuredTopics) and count($featuredTopics))
        <section class=" js-font-resize container forums-featured-section mt-30 mt-md-50">

            <div class=" js-font-resize text-center mb-30">
                <h2 class=" js-font-resize font-30 font-weight-bold text-secondary">{{ trans('update.featured_topics') }}</h2>
                <p class=" js-font-resize font-14 text-gray">{{ trans('update.featured_topics_hint') }}</p>
            </div>

            @foreach($featuredTopics as $featuredTopic)
                <div class=" js-font-resize forums-featured-card d-flex align-items-center bg-white p-20 p-md-35 rounded-lg mt-15">
                    <div class=" js-font-resize forums-featured-card-icon">
                        <img src="{{ $featuredTopic->icon }}" alt="{{ $featuredTopic->topic->title }}" class=" js-font-resize img-cover">
                    </div>

                    <div class=" js-font-resize ml-15">
                        <a href="{{ $featuredTopic->topic->getPostsUrl() }}" class=" js-font-resize ">
                            <h4 class=" js-font-resize font-16 font-weight-bold text-dark">{{ $featuredTopic->topic->title }}</h4>
                        </a>
                        <p class=" js-font-resize font-14 text-gray">{!! truncate(strip_tags($featuredTopic->topic->description),100) !!}</p>
                        <div class=" js-font-resize mt-15 d-flex align-items-end">
                            @if($featuredTopic->topic->posts_count > 0 or (!empty($featuredTopic->usersAvatars) and count($featuredTopic->usersAvatars)))
                                <div class=" js-font-resize forums-featured-card-users-avatar d-flex align-items-center mr-10">
                                    @foreach($featuredTopic->usersAvatars as $userAvatar)
                                        <div class=" js-font-resize user-avatar-card rounded-circle">
                                            <img src="{{ $userAvatar->getAvatar(32) }}" class=" js-font-resize img-cover rounded-circle" alt="{{ $userAvatar->full_name }}">
                                        </div>
                                    @endforeach

                                    @if(($featuredTopic->topic->posts_count - count($featuredTopic->usersAvatars)) > 0)
                                        <span class=" js-font-resize topics-count d-flex align-items-center justify-content-center font-12 text-gray rounded-circle">+{{ ($featuredTopic->topic->posts_count - count($featuredTopic->usersAvatars)) }}</span>
                                    @endif
                                </div>
                            @endif

                            <div class=" js-font-resize d-flex align-items-center">
                                <div class=" js-font-resize text-gray font-12">{{ trans('public.created_by') }} <span class=" js-font-resize font-weight-bold">{{ $featuredTopic->topic->creator->full_name }}</span></div>

                                <div class=" js-font-resize text-gray font-12 ml-5 pl-5 border-left">{{ trans('update.n_posts',['count' => $featuredTopic->topic->posts_count]) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class=" js-font-resize forums-featured-bg-box"></div>
        </section>
    @endif

    @if(!empty($forums) and count($forums))
        <section class=" js-font-resize container forums-categories-section mt-30">
            <div class=" js-font-resize text-center">
                <h2 class=" js-font-resize font-30 text-secondary font-weight-bold">{{ trans('update.forums') }}</h2>
                <p class=" js-font-resize font-14 text-gray mt-5">{{ trans('update.forums_categories_hints') }}</p>
            </div>

            @foreach($forums as $forum)
                <div class=" js-font-resize forums-categories-card mt-30 rounded-lg border bg-white p-15">
                    <h3 class=" js-font-resize forums-categories-card__title text-dark font-16 font-weight-bold mb-15">{{ $forum->title }}</h3>

                    @if(!empty($forum->subForums) and count($forum->subForums))
                        @foreach($forum->subForums as $subForum)
                            @include('web.default.forum.forum_card',['forum' => $subForum])
                        @endforeach
                    @else
                        @include('web.default.forum.forum_card',['forum' => $forum])
                    @endif
                </div>
            @endforeach
        </section>
    @endif

    @if(!empty($recommendedTopics) and count($recommendedTopics))
        <section class=" js-font-resize container forum-recommended-topics-section position-relative">
            <div class=" js-font-resize text-center">
                <h2 class=" js-font-resize font-30 font-weight-bold text-secondary">{{ trans('update.recommended_topics') }}</h2>
                <p class=" js-font-resize font-14 text-gray">{{ trans('update.recommended_topics_hint') }}</p>
            </div>

            <div class=" js-font-resize row mt-20 position-relative">
                @foreach($recommendedTopics as $recommendedTopic)
                    <div class=" js-font-resize col-12 col-md-3 mt-15">
                        <div class=" js-font-resize forum-recommended-topics__card position-relative rounded-lg bg-white px-20 py-30">
                            <div class=" js-font-resize forum-recommended-topics__icon">
                                <img src="{{ $recommendedTopic->icon }}" alt="{{ $recommendedTopic->title }}" class=" js-font-resize img-cover">
                            </div>

                            <h4 class=" js-font-resize font-16 font-weight-bold text-secondary mt-10">{{ $recommendedTopic->title }}</h4>

                            <div class=" js-font-resize forum-recommended-topics__lists mt-5">
                                @foreach($recommendedTopic->topics as $topic)
                                    <a href="{{ $topic->getPostsUrl() }}" class=" js-font-resize d-flex align-items-center text-gray font-14 font-weight-500 mt-15">
                                        <i data-feather="chevron-right" class=" js-font-resize mr-5 text-primary" width="16" height="16"></i>
                                        <span>{{ truncate($topic->title,25) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class=" js-font-resize forums-recommended-topics-bg-box"></div>
        </section>
    @endif

    <section class=" js-font-resize container forum-question-section bg-info-light rounded-lg mt-25 mt-md-45">
        <div class=" js-font-resize row">
            <div class=" js-font-resize col-12 col-md-7">
                <div class=" js-font-resize px-10 px-md-25 py-25 p-md-50">
                    <h1 class=" js-font-resize font-36 font-weight-bold text-secondary">
                        <span class=" js-font-resize d-block">{{ trans('update.have_a_question?') }}</span>
                        <span class=" js-font-resize d-block">{{ trans('update.ask_it_in_forum_and_get_answer') }}</span>
                    </h1>

                    <p class=" js-font-resize mt-15 text-gray font-14">{{ trans('update.have_a_question_hint') }}</p>

                    <div class=" js-font-resize d-flex flex-column flex-md-row align-items-stretch align-items-md-center mt-15">
                        <a href="/forums/create-topic" class=" js-font-resize btn btn-primary">
                            <i data-feather="file" class=" js-font-resize mr-5 text-white" width="16" height="16"></i>
                            {{ trans('update.create_a_new_topic') }}
                        </a>

                        <a href="" class=" js-font-resize btn btn-outline-primary ml-0 ml-md-20 mt-15 mt-md-0">
                            {{ trans('update.browse_forums') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize col-12 col-md-5 d-none d-md-block position-relative">
                <div class=" js-font-resize forum-question-section__img">
                    <img src="/assets/default/img/forum/question-section.png" class=" js-font-resize img-fluid" alt="">
                </div>
            </div>
        </div>
    </section>
@endsection
