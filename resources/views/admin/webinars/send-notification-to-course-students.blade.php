@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">
            <div class=" js-font-resize card">
                <div class=" js-font-resize card-body">
                    <p class=" js-font-resize ">
                        <span class=" js-font-resize font-weight-bold">{{ trans('admin/main.course_title') }}</span>:
                        {{ $webinar->title }}
                    </p>

                    <form method="post" action="{{ getAdminPanelUrl() }}/webinars/{{ $webinar->id }}/sendNotification" class=" js-font-resize form-horizontal form-bordered mt-4">
                        {{ csrf_field() }}

                        <div class=" js-font-resize row">
                            <div class=" js-font-resize col-lg-6">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize control-label" for="inputDefault">{!! trans('admin/main.title') !!}</label>
                                    <input type="text" name="title" class=" js-font-resize form-control @error('title') is-invalid @enderror" value="{{ !empty($notification) ? $notification->title : old('title') }}">
                                    <div class=" js-font-resize invalid-feedback">@error('title') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <div class=" js-font-resize form-group ">
                            <label class=" js-font-resize control-label">{{ trans('admin/main.message') }}</label>
                            <textarea name="message" class=" js-font-resize summernote form-control text-left  @error('message') is-invalid @enderror">{{ (!empty($notification)) ? $notification->message :'' }}</textarea>
                            <div class=" js-font-resize invalid-feedback">@error('message') {{ $message }} @enderror</div>
                        </div>


                        <div class=" js-font-resize form-group">
                            <div class=" js-font-resize col-md-12">
                                <button class=" js-font-resize btn btn-primary" type="submit">{{ trans('notification.send_notification') }}</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
@endpush
