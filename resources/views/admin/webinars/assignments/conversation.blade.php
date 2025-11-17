@extends('admin.layouts.app')

@push('styles_top')

@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ $pageTitle }}</h1>

            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class=" js-font-resize breadcrumb-item"><a href="{{ getAdminPanelUrl() }}/assignments">{{ trans('update.assignments') }}</a></div>
                <div class=" js-font-resize breadcrumb-item"><a href="{{ getAdminPanelUrl() }}/assignments/{{ $assignment->id }}/students">{{ trans('public.students') }}</a></div>
                <div class=" js-font-resize breadcrumb-item">{{ trans('admin/main.conversation') }}</div>
            </div>
        </div>


        <div class=" js-font-resize section-body">

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 ">
                    <div class=" js-font-resize card chat-box" id="mychatbox2">

                        <div class=" js-font-resize card-body chat-content">

                            @foreach($conversations as $conversation)
                                <div class=" js-font-resize chat-item chat-{{ !empty($conversation->sender_id == $assignment->creator_id) ? 'right' : 'left' }}">
                                    <img src="{{ $conversation->sender->getAvatar(50) }}">

                                    <div class=" js-font-resize chat-details">

                                        <div class=" js-font-resize chat-time">{{ $conversation->sender->full_name }}</div>

                                        <div class=" js-font-resize chat-text">{!! $conversation->message !!}</div>
                                        <div class=" js-font-resize chat-time">
                                            <span class=" js-font-resize mr-2">{{ dateTimeFormat($conversation->created_at,'Y M j | H:i') }}</span>

                                            @if(!empty($conversation->file_path))
                                                <a href="{{ $conversation->getDownloadUrl($assignment->id) }}" target="_blank" class=" js-font-resize text-success">
                                                    <i class=" js-font-resize fa fa-paperclip"></i>
                                                    {{ trans('admin/main.open_attach') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')


@endpush
