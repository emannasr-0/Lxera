@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <div class=" js-font-resize tickets-list">
                <a class=" js-font-resize ticket-item">
                    <div class=" js-font-resize ticket-title">
                        <h4 class=" js-font-resize text-primary">{{ $support->title }}</h4>
                    </div>
                    <div class=" js-font-resize ticket-info">
                        <div class=" js-font-resize font-weight-bold">{{ $support->user->full_name }}</div>
                        <div class=" js-font-resize bullet"></div>
                        <div class=" js-font-resize font-weight-bold">
                            @if($support->status == 'open')
                                <span class=" js-font-resize text-success">{{ trans('admin/main.open') }}</span>
                            @elseif($support->status == 'close')
                                <span class=" js-font-resize text-danger">{{ trans('admin/main.close') }}</span>
                            @elseif($support->status == 'replied')
                                <span class=" js-font-resize text-warning">{{ trans('admin/main.pending_reply') }}</span>
                            @else
                                <span class=" js-font-resize text-primary">{{ trans('admin/main.replied') }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>

            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class=" js-font-resize breadcrumb-item">{{ trans('admin/main.conversation') }}</div>
            </div>
        </div>


        <div class=" js-font-resize section-body">

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 ">
                    <div class=" js-font-resize card chat-box" id="mychatbox2">

                        <div class=" js-font-resize card-body chat-content">

                            @foreach($support->conversations as $conversations)
                                <div class=" js-font-resize chat-item chat-{{ !empty($conversations->sender_id) ? 'right' : 'left' }}">
                                    <img src="{{ !empty($conversations->sender_id) ? $conversations->sender->getAvatar() : $conversations->supporter->getAvatar() }}">

                                    <div class=" js-font-resize chat-details">

                                        <div class=" js-font-resize chat-time">{{ !empty($conversations->sender_id) ? $conversations->sender->full_name : $conversations->supporter->full_name }}</div>

                                        <div class=" js-font-resize chat-text white-space-pre-wrap">{{ $conversations->message }}</div>
                                        <div class=" js-font-resize chat-time">
                                            <span class=" js-font-resize mr-2">{{ dateTimeFormat($conversations->created_at,'Y M j | H:i') }}</span>

                                            @if(!empty($conversations->attach))
                                                <a href="{{ url($conversations->attach) }}" target="_blank" class=" js-font-resize text-success"><i class=" js-font-resize fa fa-paperclip"></i> {{ trans('admin/main.open_attach') }}</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 ">
                    <div class=" js-font-resize card">

                        <div class=" js-font-resize card-body">
                            <form action="{{ getAdminPanelUrl() }}/supports/{{ $support->id }}/conversation" method="post">
                                {{ csrf_field() }}

                                <div class=" js-font-resize form-group mt-15">
                                    <label class=" js-font-resize input-label">{{ trans('site.message') }}</label>
                                    <textarea name="message" rows="6" class=" js-font-resize  form-control @error('message')  is-invalid @enderror">{!! old('message')  !!}</textarea>
                                    @error('message')
                                    <div class=" js-font-resize invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class=" js-font-resize row">
                                    <div class=" js-font-resize col-12 col-md-8">
                                        <div class=" js-font-resize form-group">
                                            <label class=" js-font-resize input-label">{{ trans('admin/main.attach') }}</label>
                                            <div class=" js-font-resize input-group">
                                                <div class=" js-font-resize input-group-prepend">
                                                    <button type="button" class=" js-font-resize input-group-text admin-file-manager" data-input="attach" data-preview="holder">
                                                        Browse
                                                    </button>
                                                </div>
                                                <input type="text" name="attach" id="attach" value="{{ old('image_cover') }}" class=" js-font-resize form-control"/>
                                                <div class=" js-font-resize input-group-append">
                                                    <button type="button" class=" js-font-resize input-group-text admin-file-view" data-input="attach">
                                                        <i class=" js-font-resize fa fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class=" js-font-resize col-12 col-md-4 text-right mt-4">
                                        <button type="submit" class=" js-font-resize btn btn-primary">{{ trans('site.send_message') }}</button>

                                        @if($support->status != 'close')
                                            <a href="{{ getAdminPanelUrl() }}/supports/{{ $support->id }}/close" class=" js-font-resize btn btn-danger ml-1">{{ trans('admin/main.close_conversation') }}</a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>

@endpush
