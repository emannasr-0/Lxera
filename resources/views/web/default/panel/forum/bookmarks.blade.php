@extends('web.default.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')

    <section class=" js-font-resize mt-35">
        <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class=" js-font-resize section-title">{{ trans('update.bookmarks') }}</h2>
        </div>

        @if($topics->count() > 0)

            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table text-center custom-table">
                                <thead>
                                <tr>
                                    <th class=" js-font-resize text-left">{{ trans('public.topic') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('update.forum') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('update.replies') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.publish_date') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($topics as $topic)
                                    <tr>
                                        <td class=" js-font-resize text-left align-middle">
                                            <div class=" js-font-resize user-inline-avatar d-flex align-items-center">
                                                <div class=" js-font-resize avatar bg-gray200">
                                                    <img src="{{ $topic->creator->getAvatar(48) }}" class=" js-font-resize img-cover" alt="">
                                                </div>
                                                <a href="{{ $topic->getPostsUrl() }}" target="_blank" class=" js-font-resize ">
                                                    <div class=" js-font-resize  ml-5">
                                                        <span class=" js-font-resize d-block font-16 font-weight-500 text-light">{{ $topic->title }}</span>
                                                        <span class=" js-font-resize font-12 text-gray mt-5">{{ trans('public.by') }} {{ $topic->creator->full_name }}</span>
                                                    </div>
                                                </a>
                                            </div>
                                        </td>
                                        <td class=" js-font-resize text-center align-middle text-light">{{ $topic->forum->title }}</td>
                                        <td class=" js-font-resize text-center align-middle text-light">{{ $topic->posts_count }}</td>
                                        <td class=" js-font-resize text-center align-middle text-light">{{ dateTimeFormat($topic->created_at, 'j M Y H:i') }}</td>
                                        <td class=" js-font-resize text-center align-middle text-light">
                                            <a
                                                href="/panel/forums/topics/{{ $topic->id }}/removeBookmarks"
                                                data-title="{{ trans('update.this_topic_will_be_removed_from_your_bookmark') }}"
                                                data-confirm="{{ trans('update.confirm') }}"
                                                class=" js-font-resize panel-remove-bookmark-btn delete-action d-flex align-items-center justify-content-center p-5 rounded-circle">
                                                <i data-feather="bookmark" width="18" height="18" class=" js-font-resize text-danger"></i>
                                            </a>
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
                'title' => trans('update.panel_topics_bookmark_no_result'),
                'hint' => nl2br(trans('update.panel_topics_bookmark_no_result_hint')),
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
