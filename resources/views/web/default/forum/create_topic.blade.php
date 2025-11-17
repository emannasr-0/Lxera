@extends('web.default.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

@section('content')
    <div class=" js-font-resize container">
        <section class=" js-font-resize topics-title-section mt-30 mt-md-50 px-20 px-md-30 py-25 py-md-35 rounded-lg">
            <h1 class=" js-font-resize font-30 font-weight-bold text-white">{{ !empty($topic) ? trans('update.edit_topic') : trans('update.new_topic') }}</h1>
            <p class=" js-font-resize font-14 text-white">{{ trans('update.new_topic_hint') }}</p>

            <div class=" js-font-resize mt-10">
                <nav aria-label="breadcrumb">
                    <ol class=" js-font-resize breadcrumb p-0 m-0">
                        <li class=" js-font-resize breadcrumb-item font-12 text-white"><a href="/" class=" js-font-resize text-white">{{ getGeneralSettings('site_name') }}</a></li>
                        <li class=" js-font-resize breadcrumb-item font-12 text-white"><a href="/forums" class=" js-font-resize text-white">{{ trans('update.forum') }}</a></li>
                        <li class=" js-font-resize breadcrumb-item font-12 text-white font-weight-bold" aria-current="page">{{ !empty($topic) ? trans('update.edit_topic') : trans('update.new_topic') }}</li>
                    </ol>
                </nav>
            </div>
        </section>

        <form action="{{ !empty($topic) ? $topic->getEditUrl() : '/forums/create-topic' }}" method="post">
            {{ csrf_field() }}

            <div class=" js-font-resize rounded-lg px-15 py-20 border bg-white mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 col-md-6">
                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('update.topic_title') }}</label>
                            <input type="text" name="title" value="{{ !empty($topic) ? $topic->title : old('title') }}" class=" js-font-resize form-control @error('title') is-invalid @enderror" placeholder="{{ trans('update.topic_title_placeholder') }}">
                            @error('title')
                            <div class=" js-font-resize invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('update.forums') }}</label>
                            <select name="forum_id" class=" js-font-resize form-control @error('forum_id') is-invalid @enderror">
                                <option selected disabled>{{ trans('admin/main.choose_category') }}</option>

                                @foreach($forums as $forum)
                                    @if(!empty($forum->subForums) and count($forum->subForums))
                                        @php
                                            $showOptgroup = false;

                                            foreach($forum->subForums as $subForum) {
                                                if($subForum->checkUserCanCreateTopic() and !$subForum->close) {
                                                    $showOptgroup = true;
                                                }
                                            }
                                        @endphp

                                        @if($showOptgroup)
                                            <optgroup label="{{ $forum->title }}">
                                                @foreach($forum->subForums as $subForum)
                                                    @if($subForum->checkUserCanCreateTopic() and !$subForum->close)
                                                        <option value="{{ $subForum->id }}" {{ ((!empty($topic) and $topic->forum_id == $subForum->id) or (request()->get('forum_id') == $subForum->id)) ? 'selected' : '' }}>{{ $subForum->title }}</option>
                                                    @endif
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @elseif($forum->checkUserCanCreateTopic() and !$forum->close)
                                        <option value="{{ $forum->id }}" {{ (request()->get('forum_id') == $forum->id) ? 'selected' : '' }}>{{ $forum->title }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('forum_id')
                            <div class=" js-font-resize invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class=" js-font-resize col-12">
                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
                            <textarea id="summernote" name="description" class=" js-font-resize form-control @error('description')  is-invalid @enderror">{!! !empty($topic) ? $topic->description : old('description') !!}</textarea>
                            @error('description')
                            <div class=" js-font-resize invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class=" js-font-resize col-12 col-md-6">
                        <div id="topicImagesInputs" class=" js-font-resize create-topic-attachments form-group mt-15">
                            <label class=" js-font-resize input-label mb-0">{{ trans('update.attachments') }}</label>

                            <div class=" js-font-resize main-row input-group product-images-input-group mt-10">
                                <div class=" js-font-resize input-group-prepend">
                                    <button type="button" class=" js-font-resize input-group-text panel-file-manager" data-input="attachments_record" data-preview="holder">
                                        <i data-feather="upload" width="18" height="18" class=" js-font-resize text-white"></i>
                                    </button>
                                </div>
                                <input type="text" name="attachments[]" id="attachments_record" value="" class=" js-font-resize form-control"/>

                                <button type="button" class=" js-font-resize btn btn-primary btn-sm add-btn">
                                    <i data-feather="plus" width="18" height="18" class=" js-font-resize text-white"></i>
                                </button>
                            </div>

                            @if(!empty($topic) and !empty($topic->attachments) and count($topic->attachments))
                                @foreach($topic->attachments as $topicAttachment)
                                    <div class=" js-font-resize input-group product-images-input-group mt-10">
                                        <div class=" js-font-resize input-group-prepend">
                                            <button type="button" class=" js-font-resize input-group-text panel-file-manager" data-input="attachments_{{ $topicAttachment->id }}" data-preview="holder">
                                                <i data-feather="upload" width="18" height="18" class=" js-font-resize text-white"></i>
                                            </button>
                                        </div>
                                        <input type="text" name="attachments[]" id="attachments_{{ $topicAttachment->id }}" value="{{ $topicAttachment->path }}" class=" js-font-resize form-control" placeholder="{{ trans('update.attachments_size') }}"/>

                                        <button type="button" class=" js-font-resize btn btn-sm btn-danger remove-btn">
                                            <i data-feather="x" width="18" height="18" class=" js-font-resize text-white"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif

                            @error('images')
                            <div class=" js-font-resize invalid-feedback d-block">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize mt-15 p-10 bg-info-light rounded-lg d-flex align-items-center justify-content-between">
                <div class=" js-font-resize py-5">
                    <div class=" js-font-resize font-14 font-weight-bold text-gray">{{ trans('update.terms_and_rules_confirmation') }}</div>
                    <p class=" js-font-resize d-block font-14 text-gray mt-5">{{ trans('update.terms_and_rules_confirmation_hint') }}</p>
                </div>

                <button type="submit" class=" js-font-resize btn btn-primary">
                    <i data-feather="file" class=" js-font-resize text-white" width="16" height="16"></i>
                    <span class=" js-font-resize ml-1">{{ trans('update.publish_topic') }}</span>
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script src="/assets/default/js/parts/create_topics.min.js"></script>
@endpush
