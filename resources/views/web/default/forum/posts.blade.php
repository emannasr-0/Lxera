@extends('web.default.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

@section('content')
    <div class=" js-font-resize container mt-35 mt-md-50">
        <section class=" js-font-resize d-flex align-items-center justify-content-between px-15 px-md-30 py-15 py-md-25 border rounded-lg">
            <div class=" js-font-resize flex-grow-1">
                <h2 class=" js-font-resize font-20 font-weight-bold text-secondary">{{ $topic->title }}</h2>

                <span class=" js-font-resize d-block font-14 font-weight-500 text-gray mt-5">{{ trans('public.by') }} <span class=" js-font-resize font-weight-bold">{{ $topic->creator->full_name }}</span> {{ trans('public.in') }} {{ dateTimeFormat($topic->created_at, 'j M Y | H:i') }}</span>

                <div class=" js-font-resize mt-15 ">
                    <nav aria-label="breadcrumb">
                        <ol class=" js-font-resize breadcrumb p-0 m-0">
                            <li class=" js-font-resize breadcrumb-item font-12 text-gray"><a href="/">{{ getGeneralSettings('site_name') }}</a></li>
                            <li class=" js-font-resize breadcrumb-item font-12 text-gray"><a href="/forums">{{ trans('update.forum') }}</a></li>
                            <li class=" js-font-resize breadcrumb-item font-12 text-gray"><a href="{{ $topic->forum->getUrl() }}">{{ $topic->forum->title }}</a></li>
                            <li class=" js-font-resize breadcrumb-item font-12 text-gray font-weight-bold" aria-current="page">{{ $topic->title }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <button type="button" data-action="{{ $topic->getBookmarkUrl() }}" class=" js-font-resize {{ !empty($authUser) ? 'js-topic-bookmark' : 'login-to-access' }} d-flex align-items-center flex-column btn-transparent {{ $topic->bookmarked ? 'text-warning' : '' }}">
                <i data-feather="bookmark" class=" js-font-resize text-gray" width="22" height="22"></i>
                <span class=" js-font-resize font-12 mt-5 text-gray">{{ trans('update.bookmark') }}</span>
            </button>
        </section>

        @include('web.default.forum.post_card')

        {{-- Topic Posts --}}
        @if(!empty($topic->posts) and count($topic->posts))
            @foreach($topic->posts as $postRow)
                @include('web.default.forum.post_card',['post' => $postRow])
            @endforeach
        @endif

        {{-- Reply to Topic  --}}
        @if(!auth()->check())
            <div class=" js-font-resize reply-login-close-card d-flex flex-column align-items-center w-100 p-15 rounded-lg border bg-white mt-15 p-40">
                <div class=" js-font-resize icon-card">
                    <img src="/assets/default/img/topics/login.svg" alt="login icon" class=" js-font-resize img-cover">
                </div>

                <h4 class=" js-font-resize font-20 font-weight-bold text-secondary">{{ trans('update.login_to_reply') }}</h4>
                <p class=" js-font-resize font-14 font-weight-500 text-gray mt-5">{{ trans('update.login_to_reply_hint') }}</p>
            </div>
        @elseif($topic->close or $forum->close)
            <div class=" js-font-resize reply-login-close-card d-flex flex-column align-items-center w-100 p-15 rounded-lg border bg-white mt-15 p-40">
                <div class=" js-font-resize icon-card">
                    <img src="/assets/default/img/topics/closed.svg" alt="closed icon" class=" js-font-resize img-cover">
                </div>

                <h4 class=" js-font-resize font-20 font-weight-bold text-secondary">{{ trans('update.topic_closed') }}</h4>
                <p class=" js-font-resize font-14 font-weight-500 text-gray mt-5">{{ trans('update.topic_closed_hint') }}</p>
            </div>
        @else
            <div class=" js-font-resize mt-30">
                <h3 class=" js-font-resize font-16 font-weight-bold text-secondary">{{ trans('update.reply_to_the_topic') }}</h3>

                <div class=" js-font-resize p-15 rounded-lg border bg-white mt-15">
                    <form action="{{ $topic->getPostsUrl() }}" method="post">
                        {{ csrf_field() }}

                        <div class=" js-font-resize topic-posts-reply-card d-none position-relative px-20 py-15 rounded-sm bg-info-light mb-15">
                            <input type="hidden" name="reply_post_id" class=" js-font-resize js-reply-post-id">
                            <div class=" js-font-resize js-reply-post-title font-14 font-weight-500 text-gray">{!! trans('update.you_are_replying_to_the_message') !!}</div>
                            <div class=" js-font-resize js-reply-post-description mt-5 font-14 text-gray"></div>

                            <button type="button" class=" js-font-resize js-close-reply-post btn-transparent">
                                <i data-feather="x" width="22" height="22"></i>
                            </button>
                        </div>


                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
                            <textarea id="summernote" name="description" class=" js-font-resize form-control"></textarea>
                            <div class=" js-font-resize invalid-feedback"></div>
                        </div>

                        <div class=" js-font-resize row">
                            <div class=" js-font-resize col-12 col-md-6">

                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{ trans('update.attach_a_file') }} ({{ trans('public.optional') }})</label>

                                    <div class=" js-font-resize d-flex align-items-center">
                                        <div class=" js-font-resize input-group mr-10">
                                            <div class=" js-font-resize input-group-prepend">
                                                <button type="button" class=" js-font-resize input-group-text panel-file-manager" data-input="postAttachmentInput" data-preview="holder">
                                                    <i data-feather="upload" width="18" height="18" class=" js-font-resize text-white"></i>
                                                </button>
                                            </div>
                                            <input type="text" name="attach" id="postAttachmentInput" value="" class=" js-font-resize form-control" placeholder="{{ trans('update.assignment_attachments_placeholder') }}"/>
                                        </div>

                                        <button type="button" class=" js-font-resize js-save-post btn btn-primary btn-sm">{{ trans('update.send') }}</button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>


    <div id="topicReportModal" class=" js-font-resize d-none">
        <h3 class=" js-font-resize section-title after-line font-20 text-dark-blue">{{ trans('panel.report') }}</h3>

        <form action="{{ $topic->getPostsUrl() }}/report" method="post" class=" js-font-resize mt-25">
            <input type="hidden" name="item_id" class=" js-font-resize js-item-id-input"/>
            <input type="hidden" name="item_type" class=" js-font-resize js-item-type-input"/>

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize text-dark-blue font-14" for="message_to_reviewer">{{ trans('public.message_to_reviewer') }}</label>
                <textarea name="message" id="message_to_reviewer" class=" js-font-resize form-control" rows="10"></textarea>
                <div class=" js-font-resize invalid-feedback"></div>
            </div>
            <p class=" js-font-resize text-gray font-16">{{ trans('product.report_modal_hint') }}</p>

            <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
                <button type="button" class=" js-font-resize js-topic-report-submit btn btn-sm btn-primary">{{ trans('panel.report') }}</button>
                <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts_bottom')
    <script>
        var replyToTopicSuccessfullySubmittedLang = '{{ trans('update.reply_to_topic_successfully_submitted') }}'
        var reportSuccessfullySubmittedLang = '{{ trans('update.report_successfully_submitted') }}';
        var changesSavedSuccessfullyLang = '{{ trans('update.changes_saved_successfully') }}';
        var oopsLang = '{{ trans('update.oops') }}';
        var somethingWentWrongLang = '{{ trans('update.something_went_wrong') }}';
        var reportLang = '{{ trans('panel.report') }}';
        var descriptionLang = '{{ trans('public.description') }}';
        var editAttachmentLabelLang = '{{ trans('update.attach_a_file') }} ({{ trans('public.optional') }})';
        var sendLang = '{{ trans('update.send') }}';
        var notLoginToastTitleLang = '{{ trans('public.not_login_toast_lang') }}';
        var notLoginToastMsgLang = '{{ trans('public.not_login_toast_msg_lang') }}';
        var topicBookmarkedSuccessfullyLang = '{{ trans('update.topic_bookmarked_successfully') }}';
        var topicUnBookmarkedSuccessfullyLang = '{{ trans('update.topic_un_bookmarked_successfully') }}';
        var editPostLang = '{{ trans('update.edit_post') }}';
    </script>

    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script src="/assets/default/js/parts/topic_posts.min.js"></script>
@endpush
