@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ trans('admin/main.reply_comment') }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{ trans('admin/main.reply_comment') }}</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-12">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-header flex-column align-items-start">
                            <h4>{{ trans('admin/main.main_comment') }}</h4>
                            <p class=" js-font-resize mt-2">{{ nl2br($review->description) }}</p>

                            <hr class=" js-font-resize divider my-2 w-100 border border-gray">

                            @if(!empty($review->comments) and $review->comments->count() > 0)
                                <div class=" js-font-resize mt-1 w-100">
                                    <h4>{{ trans('admin/main.reply_list') }}</h4>

                                    <div class=" js-font-resize table-responsive">
                                        <table class=" js-font-resize table table-striped font-14">
                                            <tr>
                                                <th>{{ trans('admin/main.user') }}</th>
                                                <th>{{ trans('admin/main.comment') }}</th>
                                                <th>{{ trans('public.date') }}</th>
                                                <th>{{ trans('admin/main.status') }}</th>
                                                <th>{{ trans('admin/main.action') }}</th>
                                            </tr>
                                            @foreach($review->comments as $reply)
                                                <tr>
                                                    <td>{{ $reply->user->id .' - '.$reply->user->full_name }}</td>

                                                    <td>
                                                        <button type="button" class=" js-font-resize js-show-description btn btn-outline-primary">{{ trans('admin/main.show') }}</button>
                                                        <input type="hidden" value="{{ nl2br($reply->comment) }}">
                                                    </td>

                                                    <td>{{ dateTimeFormat($reply->created_at, 'Y M j | H:i') }}</td>

                                                    <td>
                                                        <span class=" js-font-resize text-{{ ($reply->status == 'pending') ? 'warning' : 'success' }}">
                                                            {{ ($reply->status == 'pending') ? trans('admin/main.pending') : trans('admin/main.published') }}
                                                        </span>
                                                    </td>

                                                    <td>

                                                        @can("admin_comments_status")
                                                            <a href="{{ getAdminPanelUrl("/comments/product_reviews/{$reply->id}/toggle") }}" class=" js-font-resize btn-transparent text-primary">
                                                                @if($reply->status == 'pending')
                                                                    <i class=" js-font-resize fa fa-arrow-up"></i>
                                                                @else
                                                                    <i class=" js-font-resize fa fa-arrow-down"></i>
                                                                @endif
                                                            </a>
                                                        @endcan

                                                        @can("admin_comments_edit")
                                                            <a href="{{ getAdminPanelUrl("/comments/product_reviews/{$reply->id}/edit") }}" class=" js-font-resize btn-transparent text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                                <i class=" js-font-resize fa fa-edit"></i>
                                                            </a>
                                                        @endcan

                                                        @can("admin_comments_delete")
                                                            @include('admin.includes.delete_button',['url' => getAdminPanelUrl("/comments/product_reviews/{$reply->id}/delete"), 'btnClass' => 'btn-sm mt-2'])
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @can("admin_comments_reply")
                            <div class=" js-font-resize card-body ">
                                <form action="{{ getAdminPanelUrl("/comments/product_reviews/{$review->id}/reply") }}" method="post">
                                    {{ csrf_field() }}

                                    <div class=" js-font-resize form-group mt-15">
                                        <label class=" js-font-resize input-label">{{ trans('admin/main.reply_comment') }}</label>
                                        <textarea id="summernote" name="comment" class=" js-font-resize summernote form-control @error('comment')  is-invalid @enderror">{!! old('comment')  !!}</textarea>

                                        @error('comment')
                                        <div class=" js-font-resize invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    <button type="submit" class=" js-font-resize mt-3 btn btn-primary">{{ trans('admin/main.save_change') }}</button>
                                </form>
                            </div>
                        @endcan

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class=" js-font-resize modal fade" id="contactMessage" tabindex="-1" aria-labelledby="contactMessageLabel" aria-hidden="true">
        <div class=" js-font-resize modal-dialog modal-dialog-centered">
            <div class=" js-font-resize modal-content">
                <div class=" js-font-resize modal-header">
                    <h5 class=" js-font-resize modal-title" id="contactMessageLabel">{{ trans('admin/main.comment') }}</h5>
                    <button type="button" class=" js-font-resize close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class=" js-font-resize modal-body">

                </div>
                <div class=" js-font-resize modal-footer">
                    <button type="button" class=" js-font-resize btn btn-secondary" data-dismiss="modal">{{ trans('admin/main.close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script src="/assets/default/js/admin/comments.min.js"></script>
@endpush
