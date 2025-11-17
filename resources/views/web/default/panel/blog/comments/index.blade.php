@extends('web.default.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')
    <section class=" js-font-resize mt-25">
        <h2 class=" js-font-resize section-title">{{ trans('panel.filter_comments') }}</h2>

        <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
            <form action="/panel/blog/comments" method="get" class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-lg-6">
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
                                            <i data-feather="calendar" width="18" height="18" class=" js-font-resize text-black"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="to" autocomplete="off" value="{{ request()->get('to') }}" class=" js-font-resize form-control {{ !empty(request()->get('to')) ? 'datepicker' : 'datefilter' }}" aria-describedby="dateInputGroupPrepend"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" js-font-resize col-12 col-lg-4">
                    <div class=" js-font-resize form-group">
                        <label class=" js-font-resize input-label">{{ trans('admin/main.post') }}</label>
                        <select name="blog_id" class=" js-font-resize form-control select2" data-placeholder="{{ trans('update.select_post') }}">
                            <option {{ empty($selectedPost) ? 'selected' : '' }} value="">{{ trans('public.all') }}</option>

                            @foreach($posts as $post)
                                <option value="{{ $post->id }}" {{ (!empty($selectedPost) and $selectedPost->id == $post->id) ? 'selected' : '' }}>{{ $post->title }}</option>
                            @endforeach
                        </select>
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
            <h2 class=" js-font-resize section-title">{{ trans('update.blog_comments_list') }}</h2>
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
                                    <th class=" js-font-resize text-left">{{ trans('admin/main.post') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('panel.comment') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.status') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.date') }}</th>
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
                                            <a href="{{ $comment->blog->getUrl() }}" target="_blank" class=" js-font-resize text-light font-weight-500">{{ $comment->blog->title }}</a>
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            <input type="hidden" id="commentDescription{{ $comment->id }}" value="{{ nl2br($comment->comment) }}">
                                            <button type="button" data-comment-id="{{ $comment->id }}" class=" js-font-resize js-view-comment btn btn-sm btn-gray200">{{ trans('public.view') }}</button>
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            @if($comment->status == 'active')
                                                <span class=" js-font-resize text-primary font-weight-500">{{ trans('public.active') }}</span>
                                            @else
                                                <span class=" js-font-resize text-light font-weight-500">{{ trans('public.pending') }}</span>
                                            @endif
                                        </td>
                                        <td class=" js-font-resize align-middle">{{ dateTimeFormat($comment->created_at,'j M Y | H:i') }}</td>
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
    <script>
        var commentLang = '{{ trans('panel.comment') }}';
    </script>

    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/js/panel/blog_comments.min.js"></script>
@endpush
