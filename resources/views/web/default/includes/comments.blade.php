<div class=" js-font-resize mt-35">
    <h2 class=" js-font-resize section-title after-line">{{ trans('panel.comments') }} <span class=" js-font-resize ml-5">({{ $comments->count() }})</span></h2>

    <div class=" js-font-resize mt-20">
        <form action="/comments/store" method="post">

            <input type="hidden" name="_token" value=" {{ csrf_token() }}">
            <input type="hidden" id="commentItemId" name="item_id" value="{{ $inputValue }}">
            <input type="hidden" id="commentItemName" name="item_name" value="{{ $inputName }}">

            <div class=" js-font-resize form-group">
                <textarea name="comment" class=" js-font-resize form-control @error('comment') is-invalid @enderror" rows="10"></textarea>
                <div class=" js-font-resize invalid-feedback">@error('comment') {{ $message }} @enderror</div>
            </div>
            <button type="submit" class=" js-font-resize btn btn-sm btn-primary">{{ trans('product.post_comment') }}</button>
        </form>
    </div>

    @if(!empty(session()->has('msg')))
        <div class=" js-font-resize alert alert-success my-25">
            {{ session()->get('msg') }}
        </div>
    @endif

    @if($comments)
        @foreach($comments as $comment)
            <div class=" js-font-resize comments-card shadow-lg rounded-sm border px-20 py-15 mt-30" data-address="/comments/{{ $comment->id }}/reply" data-csrf="{{ csrf_token() }}" data-id="{{ $comment->id }}">
                <div class=" js-font-resize d-flex align-items-center justify-content-between">
                    <div class=" js-font-resize user-inline-avatar d-flex align-items-center mt-10">
                        <div class=" js-font-resize avatar bg-gray200">
                            <img src="{{ $comment->user->getAvatar() }}" class=" js-font-resize img-cover" alt="">
                        </div>
                        <div class=" js-font-resize d-flex flex-column ml-5">
                            <span class=" js-font-resize font-weight-500 text-secondary">{{ $comment->user->full_name }}</span>
                            <span class=" js-font-resize font-12 text-gray">
                                @if($comment->user->isUser() or !empty($course) and $course->checkUserHasBought($comment->user))
                                    {{ trans('quiz.student') }}
                                @elseif(!$comment->user->isUser() and !empty($course) and ($course->creator_id == $comment->user->id or $course->teacher_id == $comment->user->id))
                                    {{ trans('panel.teacher') }}
                                @elseif($comment->user->isAdmin())
                                    {{ trans('panel.staff') }}
                                @else
                                    {{ trans('panel.user') }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class=" js-font-resize d-flex align-items-center">
                        <span class=" js-font-resize font-12 text-gray mr-10">{{ dateTimeFormat($comment->created_at, 'j M Y | H:i') }}</span>

                        <div class=" js-font-resize btn-group dropdown table-actions">
                            <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i data-feather="more-vertical" height="20"></i>
                            </button>
                            <div class=" js-font-resize dropdown-menu">
                                <button type="button" class=" js-font-resize btn-transparent webinar-actions d-block text-hover-primary reply-comment">{{ trans('panel.reply') }}</button>
                                <button type="button" data-item-id="{{ $inputValue }}" data-comment-id="{{ $comment->id }}" class=" js-font-resize btn-transparent webinar-actions d-block mt-10 text-hover-primary report-comment">{{ trans('panel.report') }}</button>

                                @if(auth()->check() and auth()->user()->id == $comment->user_id)
                                    <a href="/comments/{{ $comment->id }}/delete" class=" js-font-resize webinar-actions d-block mt-10 text-hover-primary">{{ trans('public.delete') }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize font-14 mt-20 text-gray">
                    {!! nl2br(clean($comment->comment)) !!}
                </div>

                @if(!empty($comment->replies) and $comment->replies->count() > 0)
                    @foreach($comment->replies as $reply)
                        <div class=" js-font-resize rounded-sm border px-20 py-15 mt-30">
                            <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                <div class=" js-font-resize user-inline-avatar d-flex align-items-center mt-10">
                                    <div class=" js-font-resize avatar bg-gray200">
                                        <img src="{{ $reply->user->getAvatar() }}" class=" js-font-resize img-cover" alt="">
                                    </div>
                                    <div class=" js-font-resize d-flex flex-column ml-5">
                                        <span class=" js-font-resize font-weight-500 text-secondary">{{ $reply->user->full_name }}</span>
                                        <span class=" js-font-resize font-12 text-gray">
                                            @if($reply->user->isUser() or !empty($course) and $course->checkUserHasBought($reply->user))
                                                {{ trans('quiz.student') }}
                                            @elseif(!$reply->user->isUser() and !empty($course) and ($course->creator_id == $reply->user->id or $course->teacher_id == $reply->user->id))
                                                {{ trans('panel.teacher') }}
                                            @elseif($reply->user->isAdmin())
                                                {{ trans('panel.staff') }}
                                            @else
                                                {{ trans('panel.user') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <div class=" js-font-resize d-flex align-items-center">
                                    <span class=" js-font-resize font-12 text-gray mr-10">{{ dateTimeFormat($reply->created_at, 'j M Y | H:i') }}</span>

                                    <div class=" js-font-resize btn-group dropdown table-actions">
                                        <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i data-feather="more-vertical" height="20"></i>
                                        </button>
                                        <div class=" js-font-resize dropdown-menu">
                                            <button type="button" class=" js-font-resize btn-transparent webinar-actions d-block text-hover-primary reply-comment">{{ trans('panel.reply') }}</button>
                                            <button type="button" data-item-id="{{ $inputValue }}" data-comment-id="{{ $reply->id }}" class=" js-font-resize btn-transparent webinar-actions d-block mt-10 text-hover-primary report-comment">{{ trans('panel.report') }}</button>

                                            @if(auth()->check() and auth()->user()->id == $reply->user_id)
                                                <a href="/comments/{{ $reply->id }}/delete" class=" js-font-resize webinar-actions d-block mt-10 text-hover-primary">{{ trans('public.delete') }}</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class=" js-font-resize font-14 mt-20 text-gray">
                                {!! nl2br(clean($reply->comment)) !!}
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach
    @endif
</div>

