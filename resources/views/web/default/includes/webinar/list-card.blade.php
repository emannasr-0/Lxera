<div class=" js-font-resize webinar-card webinar-list webinar-list-2 d-flex mt-30">
    <div class=" js-font-resize image-box">
        @if($webinar->bestTicket() < $webinar->price)
            <span class=" js-font-resize badge badge-danger text-light">{{ trans('public.offer',['off' => $webinar->bestTicket(true)['percent']]) }}</span>
        @elseif(empty($isFeature) and !empty($webinar->feature))
            <span class=" js-font-resize badge badge-warning text-dark-blue">{{ trans('home.featured') }}</span>
        @elseif($webinar->type == 'webinar')
            @if($webinar->start_date > time())
                <span class=" js-font-resize badge badge-primary text-dark-blue">{{  trans('panel.not_conducted') }}</span>
            @elseif($webinar->isProgressing())
                <span class=" js-font-resize badge badge-secondary text-dark-blue">{{ trans('webinars.in_progress') }}</span>
            @else
                <span class=" js-font-resize badge badge-secondary text-dark-blue">{{ trans('public.finished') }}</span>
            @endif
        @else 
            <span class=" js-font-resize badge badge-primary text-dark-blue">{{ trans('webinars.'.$webinar->type) }}</span>
        @endif

        <a href="{{ $webinar->getUrl() }}">
            <img src="{{ $webinar->getImage() }}" class=" js-font-resize img-cover" alt="{{ $webinar->title }}">
        </a>

        <div class=" js-font-resize progress-and-bell d-flex align-items-center">

            @if($webinar->type == 'webinar')
                <a href="{{ $webinar->addToCalendarLink() }}" target="_blank" class=" js-font-resize webinar-notify d-flex align-items-center justify-content-center">
                    <i data-feather="bell" width="20" height="20" class=" js-font-resize webinar-icon"></i>
                </a>
            @endif

            @if($webinar->type == 'webinar')
                <div class=" js-font-resize progress ml-10">
                    <span class=" js-font-resize progress-bar" style="width: {{ $webinar->getProgress() }}%"></span>
                </div>
            @endif
        </div>
    </div>

    <div class=" js-font-resize webinar-card-body w-100 d-flex flex-column">
        <div class=" js-font-resize d-flex align-items-center justify-content-between">
            <a href="{{ $webinar->getUrl() }}">
                <h3 class=" js-font-resize mt-15 webinar-title font-weight-bold font-16 text-dark-blue">{{ clean($webinar->title,'title') }}</h3>
            </a>
        </div>

        @if(!empty($webinar->category))
            <span class=" js-font-resize d-block font-14 mt-10">{{ trans('public.in') }} <a href="{{ $webinar->category->getUrl() }}" target="_blank" class=" js-font-resize text-decoration-underline">{{ $webinar->category->title }}</a></span>
        @endif

        <div class=" js-font-resize user-inline-avatar d-flex align-items-center mt-10">
            <div class=" js-font-resize avatar bg-gray200">
                <img src="{{ $webinar->teacher->getAvatar() }}" class=" js-font-resize img-cover" alt="{{ $webinar->teacher->full_name }}">
            </div>
            <a href="{{ $webinar->teacher->getProfileUrl() }}" target="_blank" class=" js-font-resize user-name ml-5 font-14">{{ $webinar->teacher->full_name }}</a>
        </div>

        @include(getTemplate() . '.includes.webinar.rate',['rate' => $webinar->getRate()])

        <div class=" js-font-resize d-flex justify-content-between mt-auto">
            <div class=" js-font-resize d-flex align-items-center">
                <div class=" js-font-resize d-flex align-items-center">
                    <i data-feather="clock" width="20" height="20" class=" js-font-resize webinar-icon"></i>
                    <span class=" js-font-resize duration ml-5 font-14">{{ convertMinutesToHourAndMinute($webinar->duration) }} {{ trans('home.hours') }}</span>
                </div>

                <div class=" js-font-resize vertical-line h-25 mx-15"></div>

                <div class=" js-font-resize d-flex align-items-center">
                    <i data-feather="calendar" width="20" height="20" class=" js-font-resize webinar-icon"></i>
                    <span class=" js-font-resize date-published ml-5 font-14">{{ dateTimeFormat(!empty($webinar->start_date) ? $webinar->start_date : $webinar->created_at,'j M Y') }}</span>
                </div>
            </div>

            <!--<div class=" js-font-resize webinar-price-box d-flex flex-column justify-content-center align-items-center">-->
            <!--@if(!empty($webinar->price) and $webinar->price > 0)-->
            <!--        @if($webinar->bestTicket() < $webinar->price)-->
            <!--            <span class=" js-font-resize off">{{ handlePrice($webinar->price, true, true, false, null, true) }}</span>-->
            <!--            <span class=" js-font-resize real">{{ handlePrice($webinar->bestTicket(), true, true, false, null, true) }}</span>-->
            <!--        @else-->
            <!--            <span class=" js-font-resize real">{{ handlePrice($webinar->price, true, true, false, null, true) }}</span>-->
            <!--        @endif-->
            <!--    @else-->
            <!--        <span class=" js-font-resize real font-14">{{ trans('public.free') }}</span>-->
            <!--    @endif-->
            <!--</div>-->
        </div>
    </div>
</div>
