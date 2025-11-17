@extends(getTemplate() .'.panel.layouts.panel_layout')
<style>
.notification-message {
    display: none;
}

</style>
@section('content')
    <section>
        <div class=" js-font-resize d-flex align-items-center justify-content-between ">
            <h2 class=" js-font-resize section-title mt-20">{{ trans('panel.notifications') }}</h2>

            <a href="/panel/notifications/mark-all-as-read" class=" js-font-resize delete-action d-flex align-items-center cursor-pointer text-hover-primary" data-title="{{ trans('update.convert_unread_messages_to_read') }}" data-confirm="{{ trans('update.yes_convert') }}">
                <i data-feather="check" width="20" height="20"></i>
                <span class=" js-font-resize ml-5 font-16">{{ trans('update.mark_all_notifications_as_read') }}</span>
            </a>
        </div>

        @if(!empty($notifications) and !$notifications->isEmpty())
            @foreach($notifications as $notification)
                <div class=" js-font-resize notification-card rounded-sm panel-shadow bg-secondary-acadima py-15 py-lg-20 px-20 px-lg-40 mt-20">
                <div class=" js-font-resize row row-cols-1 row-cols-lg-3 align-items-center justify-content-between gap-3">
    <!-- Left: Badge & Title -->
    <div class=" js-font-resize mt-10 mt-lg-0 p-0 d-flex align-items-start g-3 mb-3">
                            @if(empty($notification->notificationStatus))
                                <span class=" js-font-resize notification-badge badge badge-circle-danger mr-5 mt-5 d-flex align-items-center justify-content-center"></span>
                            @endif

                            <div class=" js-font-resize ">
                                <h3 class=" js-font-resize notification-title font-16 font-weight-bold text-dark">{{ $notification->title }}</h3>
                                <span class=" js-font-resize notification-time d-block font-12 text-gray mt-5">{{ dateTimeFormat($notification->created_at,'j M Y | H:i') }}</span>
                            </div>
                        </div>

    <!-- Middle: Message -->
    <div class=" js-font-resize text-wrap text-break col text-lg-center mb-3">
        <span class=" js-font-resize font-weight-bold text-black font-14 d-block">
            {!! truncate($notification->message, 150, true) !!}
        </span>
    </div>

    <!-- Right: Button -->
    <div class=" js-font-resize w-100 w-lg-80 d-flex justify-content-start justify-content-lg-end">
        <button type="button" data-id="{{ $notification->id }}" id="showNotificationMessage{{ $notification->id }}"
            class=" js-font-resize js-show-message btn btn-acadima-primary w-50 w-lg-80 align-right 
            @if(!empty($notification->notificationStatus)) seen-at @endif">
        {{ trans('panel.click_here') }}
        </button>
    <input type="hidden" class=" js-font-resize notification-message" value="{{$notification->message}}">
</div>

</div>

                </div>
            @endforeach

            <div class=" js-font-resize my-30">
                {{ $notifications->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        @else
            @include(getTemplate() . '.includes.no-result',[
               'file_name' => 'webinar.png',
               'title' => trans('panel.notification_no_result'),
               'hint' => nl2br(trans('panel.notification_no_result_hint')),
           ])
        @endif
    </section>

    <div class=" js-font-resize mt-5 d-none" id="messageModal">
        <div class=" js-font-resize text-center">
            <h3 class=" js-font-resize modal-title font-16 font-weight-bold text-pink"></h3>
            <span class=" js-font-resize modal-time d-block font-12 text-gray mt-5"></span>
            <span class=" js-font-resize modal-message text-dark mt-20"></span>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script>
        (function ($) {
            "use strict";

            @if(!empty(request()->get('notification')))
            setTimeout(() => {
                $('body #showNotificationMessage{{ request()->get('notification') }}').trigger('click');

                let url = window.location.href;
                url = url.split('?')[0];
                window.history.pushState("object or string", "Title", url);
            }, 400);
            @endif
        })(jQuery)
    </script>

    <script src="/assets/default/js/panel/notifications.min.js"></script>
@endpush
