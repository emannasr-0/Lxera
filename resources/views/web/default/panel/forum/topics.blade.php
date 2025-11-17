@extends('web.default.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')
    <section>
        <h2 class=" js-font-resize section-title">{{ trans('update.topics_statistics') }}</h2>

        <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/125.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $publishedTopics }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.published_topics') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/126.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $lockedTopics }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.locked_topics') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/39.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $topicMessages }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.topic_messages') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class=" js-font-resize mt-25">
        <h2 class=" js-font-resize section-title">{{ trans('update.filter_topics') }}</h2>

        <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
            <form action="/panel/forums/topics" method="get" class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-lg-5">
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
                                    <input type="text" name="from" autocomplete="off" class=" js-font-resize form-control @if(!empty(request()->get('from'))) datepicker @else datefilter @endif" aria-describedby="dateInputGroupPrepend" value="{{ request()->get('from','') }}"/>
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
                                    <input type="text" name="to" autocomplete="off" class=" js-font-resize form-control @if(!empty(request()->get('to'))) datepicker @else datefilter @endif" aria-describedby="dateInputGroupPrepend" value="{{ request()->get('to','') }}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" js-font-resize col-12 col-lg-5">
                    <div class=" js-font-resize row">
                        <div class=" js-font-resize col-12 col-lg-6">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('update.forums') }}</label>
                                <select name="forum_id" class=" js-font-resize form-control" data-placeholder="{{ trans('public.all') }}">
                                    <option value="all">{{ trans('public.all') }}</option>

                                    @foreach($forums as $forum)
                                        @if(!empty($forum->subForums) and count($forum->subForums))
                                            <optgroup label="{{ $forum->title }}">
                                                @foreach($forum->subForums as $subForum)
                                                    <option value="{{ $subForum->id }}" {{ (request()->get('forum_id') == $subForum->id) ? 'selected' : '' }}>{{ $subForum->title }}</option>
                                                @endforeach
                                            </optgroup>
                                        @else
                                            <option value="{{ $forum->id }}" {{ (request()->get('forum_id') == $forum->id) ? 'selected' : '' }}>{{ $forum->title }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class=" js-font-resize col-12 col-lg-6">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('public.status') }}</label>
                                <select class=" js-font-resize form-control" id="status" name="status">
                                    <option value="all">{{ trans('public.all') }}</option>
                                    <option value="published" @if(request()->get('status') == 'published') selected @endif >{{ trans('public.published') }}</option>
                                    <option value="closed" @if(request()->get('status') == 'closed') selected @endif >{{ trans('panel.closed') }}</option>
                                </select>
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
            <h2 class=" js-font-resize section-title">{{ trans('update.my_topics') }}</h2>
        </div>

        @if($topics->count() > 0)

            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table text-center custom-table">
                                <thead>
                                <tr>
                                    <th class=" js-font-resize text-left">{{ trans('public.title') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('update.forum') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('site.posts') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.status') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.publish_date') }}</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($topics as $topic)
                                    <tr>
                                        <td class=" js-font-resize text-left align-middle">
                                            <div class=" js-font-resize user-inline-avatar d-flex align-items-center">
                                                <div class=" js-font-resize avatar bg-gray200">
                                                    <img src="{{ $topic->forum->icon }}" class=" js-font-resize img-cover" alt="">
                                                </div>
                                                <a href="{{ $topic->getPostsUrl() }}" target="_blank" class=" js-font-resize ">
                                                    <div class=" js-font-resize  ml-5">
                                                        <span class=" js-font-resize d-block font-14 font-weight-500 text-light">{{ $topic->title }}</span>
                                                    </div>
                                                </a>
                                            </div>
                                        </td>
                                        <td class=" js-font-resize text-center align-middle text-light">{{ $topic->forum->title }}</td>
                                        <td class=" js-font-resize text-center align-middle text-light">{{ $topic->posts_count }}</td>
                                        <td class=" js-font-resize text-center align-middle text-light">
                                            @if($topic->close)
                                                {{ trans('panel.closed') }}
                                            @else
                                                {{ trans('public.published') }}
                                            @endif
                                        </td>
                                        <td class=" js-font-resize text-center align-middle">{{ dateTimeFormat($topic->created_at, 'j M Y H:i') }}</td>
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
                'file_name' => 'quiz.png',
                'title' => trans('update.panel_topics_no_result'),
                'hint' => nl2br(trans('update.panel_topics_no_result_hint')),
                'btn' => ['url' => '/forums','text' => trans('update.forums')]
            ])

        @endif

    </section>

    <div class=" js-font-resize my-30">
        {{ $topics->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
@endpush
