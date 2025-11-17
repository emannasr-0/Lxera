<div class=" js-font-resize tab-pane mt-3 fade" id="topics" role="tabpanel" aria-labelledby="topics-tab">
    <div class=" js-font-resize row">

        <div class=" js-font-resize col-12">
            <h5 class=" js-font-resize section-title after-line">{{ trans('update.forum_topics') }}</h5>

            <div class=" js-font-resize table-responsive mt-5">
                <table class=" js-font-resize table table-striped table-md">
                    <tr>
                        <th>{{ trans('public.topic') }}</th>
                        <th>{{ trans('admin/main.category') }}</th>
                        <th>{{ trans('site.posts') }}</th>
                        <th>{{ trans('admin/main.created_at') }}</th>
                        <th>{{ trans('admin/main.updated_at') }}</th>
                        <th class=" js-font-resize text-right">{{ trans('admin/main.actions') }}</th>
                    </tr>

                    @if(!empty($topics))
                        @foreach($topics as $topic)

                            <tr>
                                <td width="25%">
                                    <a href="{{ $topic->getPostsUrl() }}" target="_blank" class=" js-font-resize ">{{ $topic->title }}</a>
                                </td>

                                <td>
                                    {{ $topic->forum->title }}
                                </td>
                                <td>{{ $topic->posts_count }}</td>
                                <td class=" js-font-resize text-center">{{ dateTimeFormat($topic->created_at,'j M Y | H:i') }}</td>
                                <td class=" js-font-resize text-center">{{ (!empty($topic->posts) and count($topic->posts)) ? dateTimeFormat($topic->posts->first()->created_at,'j M Y | H:i') : '-' }}</td>
                                <td class=" js-font-resize text-right">

                                    @can('admin_forum_topics_lists')
                                        @if(!$topic->close)
                                            @include('admin.includes.delete_button',[
                                                'url' => "/admin/forums/{$topic->forum_id}/topics/{$topic->id}/close",
                                                'tooltip' => trans('public.close'),
                                                'btnClass' => 'mr-1',
                                                'btnIcon' => 'fa-lock'
                                            ])
                                        @else
                                            @include('admin.includes.delete_button',[
                                                'url' => "/admin/forums/{$topic->forum_id}/topics/{$topic->id}/open",
                                                'tooltip' => trans('public.open'),
                                                'btnClass' => 'mr-1',
                                                'btnIcon' => 'fa-unlock'
                                            ])
                                        @endif
                                    @endcan

                                    @can('admin_forum_topics_posts')
                                        <a href="{{ getAdminPanelUrl() }}/forums/{{ $topic->forum_id }}/topics/{{ $topic->id }}/posts"
                                           class=" js-font-resize btn-transparent btn-sm text-primary mr-1"
                                           data-toggle="tooltip" data-placement="top" title="{{ trans('site.posts') }}"
                                        >
                                            <i class=" js-font-resize fa fa-eye"></i>
                                        </a>
                                    @endcan

                                    @can('admin_enrollment_block_access')
                                        @include('admin.includes.delete_button',[
                                                'url' => "/admin/forums/{$topic->forum_id}/topics/{$topic->id}/delete?no_redirect=true",
                                            ])
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
