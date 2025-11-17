@php
    $cardUser = !empty($post) ? $post->user : $topic->creator;
    $cardUserBadges = $cardUser->getBadges();
@endphp
<div class=" js-font-resize topics-post-card py-15 rounded-lg border bg-white mt-15">
    <div class=" js-font-resize d-flex flex-wrap">
        <div class=" js-font-resize col-12 col-md-3">
            <div class=" js-font-resize position-relative bg-info-light d-flex flex-column align-items-center justify-content-start rounded-lg w-100 h-100 p-20">
                <div class=" js-font-resize user-avatar rounded-circle {{ ($cardUser->id == $topic->creator_id) ? 'green-ring' : '' }}">
                    <img src="{{ $cardUser->getAvatar(72) }}" class=" js-font-resize img-cover rounded-circle" alt="{{ $cardUser->full_name }}">
                </div>
                <a href="{{ $cardUser->getProfileUrl() }}" target="_blank">
                    <h4 class=" js-font-resize js-post-user-name font-14 text-secondary mt-15 font-weight-bold w-100 text-center">{{ $cardUser->full_name }}</h4>
                </a>

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

                @if(!empty($cardUserBadges) and count($cardUserBadges))
                    <div class=" js-font-resize d-flex flex-wrap align-items-center justify-content-center mt-20 w-100">
                        @foreach($cardUserBadges as $badge)
                            <div class=" js-font-resize mr-10 mt-10" data-toggle="tooltip" data-placement="bottom" data-html="true" title="{{ (!empty($badge->badge_id) ? $badge->badge->description : $badge->description) }}">
                                <img src="{{ !empty($badge->badge_id) ? $badge->badge->image : $badge->image }}" width="32" height="32" alt="{{ !empty($badge->badge_id) ? $badge->badge->title : $badge->title }}">
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class=" js-font-resize mt-20 w-100">
                    @if($cardUser->getTopicsPostsCount() > 0)
                        <div class=" js-font-resize d-flex align-items-center justify-content-between font-12 text-gray">
                            <span class=" js-font-resize ">{{ trans('site.posts') }}:</span>
                            <span class=" js-font-resize ">{{ $cardUser->getTopicsPostsCount() }}</span>
                        </div>
                    @endif

                    @if($cardUser->getTopicsPostsLikesCount() > 0)
                        <div class=" js-font-resize d-flex align-items-center justify-content-between font-12 text-gray mt-10">
                            <span class=" js-font-resize ">{{ trans('update.likes') }}:</span>
                            <span class=" js-font-resize ">{{ $cardUser->getTopicsPostsLikesCount() }}</span>
                        </div>
                    @endif

                    @if(count($cardUser->followers()))
                        <div class=" js-font-resize d-flex align-items-center justify-content-between font-12 text-gray mt-10">
                            <span class=" js-font-resize ">{{ trans('panel.followers') }}:</span>
                            <span class=" js-font-resize ">{{ count($cardUser->followers()) }}</span>
                        </div>
                    @endif

                    <div class=" js-font-resize d-flex align-items-center justify-content-between font-12 text-gray mt-10">
                        <span class=" js-font-resize ">{{ trans('update.member_since') }}:</span>
                        <span class=" js-font-resize ">{{ dateTimeFormat($cardUser->created_at,'j M Y') }}</span>
                    </div>

                    @if(!empty($cardUser->getCountryAndState()))
                        <div class=" js-font-resize d-flex align-items-center justify-content-between font-12 text-gray mt-10">
                            <span class=" js-font-resize ">{{ trans('update.location') }}:</span>
                            <span class=" js-font-resize ">{{ $cardUser->getCountryAndState() }}</span>
                        </div>
                    @endif
                </div>

                @if(!empty($post) and $post->pin)
                    <span class=" js-font-resize pinned-icon d-flex align-items-center justify-content-center">
                        <img src="/assets/default/img/learning/un_pin.svg" alt="pin icon" class=" js-font-resize ">
                    </span>
                @endif
            </div>
        </div>

        <div class=" js-font-resize col-12 col-md-9 mt-15 mt-md-0">
            <div class=" js-font-resize d-flex flex-column justify-content-between h-100">
                <div class=" js-font-resize d-flex flex-column h-100">
                    @if(!empty($post) and !empty($post->parent))
                        <div class=" js-font-resize post-quotation p-15 rounded-sm border mb-15">
                            <div class=" js-font-resize d-flex align-items-center">
                                <div class=" js-font-resize post-quotation-icon rounded-circle">
                                    <img src="/assets/default/img/icons/quote-right.svg" class=" js-font-resize img-cover" alt="quote-right">
                                </div>
                                <div class=" js-font-resize ml-10">
                                    <span class=" js-font-resize d-block font-12 text-gray">{{ trans('update.reply_to') }}</span>
                                    <span class=" js-font-resize font-12 font-weight-bold text-gray">{{ $post->parent->user->full_name }}</span>
                                </div>
                            </div>

                            <div class=" js-font-resize topic-post-description mt-15">{!! truncate($post->parent->description, 200) !!}</div>
                        </div>
                    @endif

                    <div class=" js-font-resize topic-post-description">{!! !empty($post) ? $post->description : $topic->description !!}</div>

                    @if(!empty($post) and !empty($post->attach))
                        <div class=" js-font-resize mt-auto d-inline-flex">
                            <a href="{{ $post->getAttachmentUrl($forum->slug,$topic->slug) }}" target="_blank" class=" js-font-resize d-flex align-items-center text-gray bg-info-light border px-10 py-5 rounded-pill">
                                <i data-feather="paperclip" class=" js-font-resize text-gray" width="16" height="16"></i>
                                <span class=" js-font-resize ml-5">{{ truncate($post->getAttachmentName(),24) }}</span>
                            </a>
                        </div>
                    @elseif(empty($post) and !empty($topic->attachments) and count($topic->attachments))
                        <div class=" js-font-resize mt-auto d-inline-flex align-items-center">
                            @foreach($topic->attachments as $attachment)
                                <a href="{{ $attachment->getDownloadUrl($forum->slug,$topic->slug) }}" target="_blank" class=" js-font-resize d-flex align-items-center text-gray bg-info-light border px-10 py-5 rounded-pill mr-15">
                                    <i data-feather="paperclip" class=" js-font-resize text-gray" width="16" height="16"></i>
                                    <span class=" js-font-resize ml-5 text-gray font-12">{{ truncate($attachment->getName(),24) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class=" js-font-resize d-flex align-items-center justify-content-between mt-15 pt-15 border-top">
                    <span class=" js-font-resize font-14 font-weight-500 text-gray">{{ dateTimeFormat(!empty($post) ? $post->created_at : $topic->created_at,'j M Y | H:i') }}</span>

                    <div class=" js-font-resize d-flex align-items-center">
                        @if(!empty($authUser) and !$topic->close)
                            @if($authUser->id == $cardUser->id)
                                @if(!empty($post))
                                    <button type="button" data-action="{{ $post->getEditUrl($forum->slug,$topic->slug) }}" class=" js-font-resize js-post-edit btn-transparent mr-25 font-14 font-weight-500 text-gray">{{ trans('public.edit') }}</button>
                                @else
                                    <a href="{{ $topic->getEditUrl($forum->slug) }}" class=" js-font-resize mr-25 font-14 font-weight-500 text-gray">{{ trans('public.edit') }}</a>
                                @endif
                            @endif

                            @if(!empty($post) and $authUser->id == $topic->creator_id)
                                @if($post->pin)
                                    <button type="button" data-action="{{ $topic->getPostsUrl() }}/{{ $post->id }}/un_pin" class=" js-font-resize js-btn-post-un-pin btn-transparent font-14 font-weight-500 text-warning mr-25">{{ trans('update.un_pin') }}</button>
                                @else
                                    <button type="button" data-action="{{ $topic->getPostsUrl() }}/{{ $post->id }}/pin" class=" js-font-resize js-btn-post-pin btn-transparent font-14 font-weight-500 text-gray mr-25">{{ trans('update.pin') }}</button>
                                @endif
                            @endif

                            @if(!empty($post))
                                <button type="button" data-id="{{ $post->id }}" class=" js-font-resize js-reply-post-btn btn-transparent mr-25 font-14 font-weight-500 text-gray">{{ trans('panel.reply') }}</button>
                            @endif

                            <button type="button" data-id="{{ !empty($post) ? $post->id : $topic->id }}" data-type="{{ !empty($post) ? 'topic_post' : 'topic' }}" class=" js-font-resize js-topic-post-report btn-transparent mr-25 font-14 font-weight-500 text-gray">{{ trans('panel.report') }}</button>
                        @endif

                        <div class=" js-font-resize topic-post-like-btn d-flex align-items-center">
                            <button type="button" class=" js-font-resize {{ !empty($authUser) ? 'js-topic-post-like' : 'login-to-access' }} badge-icon d-flex align-items-center justify-content-center {{ ((!empty($post) and in_array($post->id,$likedPostsIds)) or (empty($post) and $topic->liked)) ? 'liked' : '' }}" data-action="{{ !empty($post) ? $post->getLikeUrl($forum->slug,$topic->slug) : $topic->getLikeUrl($forum->slug) }}">
                                <i data-feather="heart" width="20" height="20"></i>
                            </button>
                            <div class=" js-font-resize font-12 font-weight-normal">
                                <span class=" js-font-resize js-like-count">{{ !empty($post) ? $post->likes->count() : $topic->likes->count() }}</span>
                                {{ trans('update.likes') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
