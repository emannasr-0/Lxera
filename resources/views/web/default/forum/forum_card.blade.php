<div class=" js-font-resize row align-items-center my-15">
    <div class=" js-font-resize col-12 col-md-6">
        <div class=" js-font-resize d-flex align-items-center">
            <div class=" js-font-resize forums-categories-card__icon p-5">
                <img src="{{ $forum->icon }}" alt="{{ $forum->title }}" class=" js-font-resize img-cover">
            </div>
            <div class=" js-font-resize ml-10">
                <a href="{{ $forum->getUrl() }}" class=" js-font-resize d-block">
                    <div class=" js-font-resize font-14 text-secondary font-weight-bold">{{ $forum->title }}</div>
                </a>
                <p class=" js-font-resize font-12 text-gray mt-5">{{ $forum->description }}</p>
            </div>
        </div>
    </div>

    <div class=" js-font-resize col-4 col-md-2 mt-10 mt-md-0 d-flex align-items-center justify-content-around">
        <div class=" js-font-resize text-center">
            <span class=" js-font-resize d-block font-14 text-gray font-weight-bold">{{ $forum->topics_count }}</span>
            <div class=" js-font-resize d-block font-12 text-gray">{{ trans('update.topics') }}</div>
        </div>

        <div class=" js-font-resize text-center">
            <span class=" js-font-resize d-block font-14 text-gray font-weight-bold">{{ $forum->posts_count }}</span>
            <div class=" js-font-resize d-block font-12 text-gray">{{ trans('site.posts') }}</div>
        </div>
    </div>

    <div class=" js-font-resize col-8 col-md-4 mt-10 mt-md-0 forums-categories-card__last-post d-flex align-items-center">
        @if(!empty($forum->lastTopic))
            <div class=" js-font-resize user-avatar rounded-circle">
                <img src="{{ $forum->lastTopic->creator->getAvatar(39) }}" class=" js-font-resize img-cover rounded-circle" alt="{{ $forum->lastTopic->creator->full_name }}">
            </div>

            <div class=" js-font-resize ml-5">
                <a href="{{ $forum->lastTopic->getPostsUrl() }}" class=" js-font-resize d-block">
                    <span class=" js-font-resize font-12 font-weight-500 text-gray text-ellipsis">{{ truncate($forum->lastTopic->title,30) }}</span>
                </a>
                <div class=" js-font-resize text-gray font-12"><span class=" js-font-resize font-weight-bold">{{ $forum->lastTopic->creator->full_name }}</span> {{ trans('public.in') }} {{ dateTimeFormat($forum->lastTopic->created_at,'j M Y | H:i') }}</div>
            </div>
        @endif
    </div>
</div>
