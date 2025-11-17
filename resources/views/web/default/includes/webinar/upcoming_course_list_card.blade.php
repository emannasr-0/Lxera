<div class=" js-font-resize webinar-card webinar-list webinar-list-2 d-flex mt-30">
    <div class=" js-font-resize image-box">
        @if(!empty($upcomingCourse->webinar_id))
            <span class=" js-font-resize badge badge-secondary">{{ trans('update.released') }}</span>
        @endif

        <a href="{{ $upcomingCourse->getUrl() }}">
            <img src="{{ $upcomingCourse->getImage() }}" class=" js-font-resize img-cover" alt="{{ $upcomingCourse->title }}">
        </a>
    </div>

    <div class=" js-font-resize webinar-card-body w-100 d-flex flex-column">

        @if(empty($upcomingCourse->webinar_id))
            <a href="{{ $upcomingCourse->addToCalendarLink() }}" target="_blank" class=" js-font-resize upcoming-bell d-flex align-items-center justify-content-center">
                <i data-feather="bell" width="20" height="20"></i>
            </a>
        @endif

        <div class=" js-font-resize d-flex align-items-center justify-content-between">
            <a href="{{ $upcomingCourse->getUrl() }}">
                <h3 class=" js-font-resize mt-15 webinar-title font-weight-bold font-16 text-dark-blue">{{ clean($upcomingCourse->title,'title') }}</h3>
            </a>
        </div>

        @if(!empty($upcomingCourse->category))
            <span class=" js-font-resize d-block font-14 mt-10">{{ trans('public.in') }} <a href="{{ $upcomingCourse->category->getUrl() }}" target="_blank" class=" js-font-resize text-decoration-underline">{{ $upcomingCourse->category->title }}</a></span>
        @endif

        <div class=" js-font-resize user-inline-avatar d-flex align-items-center mt-10">
            <div class=" js-font-resize avatar bg-gray200">
                <img src="{{ $upcomingCourse->teacher->getAvatar() }}" class=" js-font-resize img-cover" alt="{{ $upcomingCourse->teacher->full_name }}">
            </div>
            <a href="{{ $upcomingCourse->teacher->getProfileUrl() }}" target="_blank" class=" js-font-resize user-name ml-5 font-14">{{ $upcomingCourse->teacher->full_name }}</a>
        </div>


        <div class=" js-font-resize d-flex justify-content-between mt-auto">
            <div class=" js-font-resize d-flex align-items-center">

                @if(!empty($upcomingCourse->duration))
                    <div class=" js-font-resize d-flex align-items-center">
                        <i data-feather="clock" width="20" height="20" class=" js-font-resize webinar-icon"></i>
                        <span class=" js-font-resize duration ml-5 font-14">{{ convertMinutesToHourAndMinute($upcomingCourse->duration) }} {{ trans('home.hours') }}</span>
                    </div>
                @endif

                @if(!empty($upcomingCourse->published_date))

                    @if(!empty($upcomingCourse->duration))
                        <div class=" js-font-resize vertical-line h-25 mx-15"></div>
                    @endif

                    <div class=" js-font-resize d-flex align-items-center">
                        <i data-feather="calendar" width="20" height="20" class=" js-font-resize webinar-icon"></i>
                        <span class=" js-font-resize date-published ml-5 font-14">{{ dateTimeFormat($upcomingCourse->published_date, 'j M Y') }}</span>
                    </div>
                @endif
            </div>

            <div class=" js-font-resize webinar-price-box d-flex flex-column justify-content-center align-items-center">
                @if(!empty($upcomingCourse->price))
                    <span class=" js-font-resize real">{{ handlePrice($upcomingCourse->price) }}</span>
                @else
                    <span class=" js-font-resize real font-14">{{ trans('public.free') }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
