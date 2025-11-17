@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')
    <section>
        <h2 class=" js-font-resize section-title">{{ trans('panel.comments_statistics') }}</h2>

        <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/39.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 font-weight-bold mt-5">{{ $comments->count() }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('panel.comments') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/41.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 font-weight-bold mt-5">{{ $repliedCommentsCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('panel.replied') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/40.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 font-weight-bold mt-5">{{ ($comments->count() - $repliedCommentsCount) }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('panel.not_replied') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class=" js-font-resize mt-25">
        <h2 class=" js-font-resize section-title">{{ trans('panel.filter_comments') }}</h2>

        <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
            <form action="/panel/store/products/comments" method="get" class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-lg-4">
                    <div class=" js-font-resize row">
                        <div class=" js-font-resize col-12 col-md-6">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('public.from') }}</label>
                                <div class=" js-font-resize input-group">
                                    <div class=" js-font-resize input-group-prepend">
                                        <span class=" js-font-resize input-group-text" id="dateInputGroupPrepend">
                                            <i data-feather="calendar" width="18" height="18" class=" js-font-resize text-light"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="from" autocomplete="off" value="{{ request()->get('from') }}" class=" js-font-resize form-control {{ !empty(request()->get('from')) ? 'datepicker' : 'datefilter' }}" aria-describedby="dateInputGroupPrepend"/>
                                </div>
                            </div>
                        </div>
                        <div class=" js-font-resize col-12 col-md-6">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('public.to') }}</label>
                                <div class=" js-font-resize input-group">
                                    <div class=" js-font-resize input-group-prepend">
                                        <span class=" js-font-resize input-group-text" id="dateInputGroupPrepend">
                                            <i data-feather="calendar" width="18" height="18" class=" js-font-resize text-light"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="to" autocomplete="off" value="{{ request()->get('to') }}" class=" js-font-resize form-control {{ !empty(request()->get('to')) ? 'datepicker' : 'datefilter' }}" aria-describedby="dateInputGroupPrepend"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" js-font-resize col-12 col-lg-6">
                    <div class=" js-font-resize row">
                        <div class=" js-font-resize col-12 col-lg-5">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('panel.user') }}</label>
                                <input type="text" name="user" value="{{ request()->get('user') }}" class=" js-font-resize form-control"/>
                            </div>
                        </div>
                        <div class=" js-font-resize col-12 col-lg-7">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('update.product') }}</label>
                                <input type="text" name="product" value="{{ request()->get('product') }}" class=" js-font-resize form-control"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" js-font-resize col-12 col-lg-2 d-flex align-items-center justify-content-end">
                    <button type="submit" class=" js-font-resize btn btn-sm btn-acadima-primary w-100 mt-2">{{ trans('public.show_results') }}</button>
                </div>
            </form>
        </div>
    </section>

    <section class=" js-font-resize mt-35">
        <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class=" js-font-resize section-title">{{ trans('update.product_comments_list') }}</h2>
        </div>

        @if(!empty($comments) and !$comments->isEmpty())

            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table custom-table text-center ">
                                <thead>
                                <tr>
                                    <th class=" js-font-resize text-left">{{ trans('panel.user') }}</th>
                                    <th class=" js-font-resize text-left">{{ trans('update.product') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('panel.comment') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.status') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.date') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($comments as $comment)
                                    <tr>
                                        <th class=" js-font-resize text-left">
                                            <div class=" js-font-resize user-inline-avatar d-flex align-items-center">
                                                <div class=" js-font-resize avatar bg-gray200">
                                                    <img src="{{ $comment->user->getAvatar() }}" class=" js-font-resize img-cover" alt="">
                                                </div>
                                                <span class=" js-font-resize user-name ml-5 text-light font-weight-500">{{ $comment->user->full_name }}</span>
                                            </div>
                                        </th>
                                        <td class=" js-font-resize  text-left align-middle" width="35%">
                                            <a href="{{ $comment->product->getUrl() }}" target="_blank" class=" js-font-resize text-light font-weight-500">{{ $comment->product->title }}</a>
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            <button type="button" data-comment-id="{{ $comment->id }}" class=" js-font-resize js-view-comment btn btn-sm btn-gray200">{{ trans('public.view') }}</button>
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            @if(empty($comment->reply_id))
                                                <span class=" js-font-resize text-primary font-weight-500">{{ trans('public.open') }}</span>
                                            @else
                                                <span class=" js-font-resize text-light font-weight-500">{{ trans('panel.replied') }}</span>
                                            @endif
                                        </td>
                                        <td class=" js-font-resize align-middle">{{ dateTimeFormat($comment->created_at,'j M Y | H:i') }}</td>
                                        <td class=" js-font-resize align-middle text-right">
                                            <input type="hidden" id="commentDescription{{ $comment->id }}" value="{{ nl2br($comment->comment) }}">
                                            <div class=" js-font-resize btn-group dropdown table-actions">
                                                <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>
                                                <div class=" js-font-resize dropdown-menu">
                                                    <button type="button" data-comment-id="{{ $comment->id }}" class=" js-font-resize js-reply-comment btn-transparent">{{ trans('panel.reply') }}</button>
                                                    <button type="button" data-item-id="{{ $comment->product_id }}" data-comment-id="{{ $comment->id }}" class=" js-font-resize btn-transparent webinar-actions d-block mt-10 text-hover-primary report-comment">{{ trans('panel.report') }}</button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'comment.png',
                'title' => trans('panel.comments_no_result'),
                'hint' =>  nl2br(trans('panel.comments_no_result_hint')) ,
            ])
        @endif
    </section>

    <div class=" js-font-resize my-30">
        {{ $comments->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script>
        var commentLang = '{{ trans('panel.comment') }}';
        var replyToCommentLang = '{{ trans('panel.reply_to_the_comment') }}';
        var saveLang = '{{ trans('public.save') }}';
        var closeLang = '{{ trans('public.close') }}';
        var reportLang = '{{ trans('panel.report') }}';
        var reportSuccessLang = '{{ trans('panel.report_success') }}';
        var messageToReviewerLang = '{{ trans('public.message_to_reviewer') }}';
    </script>
    <script src="/assets/default/js/panel/comments.min.js"></script>
@endpush
