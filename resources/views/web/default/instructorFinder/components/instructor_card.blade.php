@php
    $price = (!empty($instructor->meeting)) ? $instructor->meeting->amount : 0;
    $discount = (!empty($price) and !empty($instructor->meeting) and !empty($instructor->meeting->discount) and $instructor->meeting->discount > 0) ? $instructor->meeting->discount : 0;
@endphp

<a href="{{ $instructor->getProfileUrl() }}" class=" js-font-resize ">
    <div class=" js-font-resize position-relative d-flex flex-wrap instructor-finder-card border border-gray300 rounded-sm py-25 mt-20">

        <div class=" js-font-resize col-12 col-md-8 d-flex">
            <div class=" js-font-resize instructor-avatar rounded-circle">
                <img src="{{ $instructor->getAvatar(70) }}" class=" js-font-resize img-cover rounded-circle" alt="{{ $instructor->full_name }}">
            </div>

            <div class=" js-font-resize ml-20">
                <h3 class=" js-font-resize font-16 font-weight-bold text-secondary">{{ $instructor->full_name }}</h3>

                <div>
                    <span class=" js-font-resize d-block font-12 text-gray">{{ $instructor->bio }}</span>

                    @if(!empty($instructor->occupations))
                        <div class=" js-font-resize d-block font-14 text-gray mt-5">
                            @foreach($instructor->occupations as $occupation)
                                @if(!empty($occupation->category))
                                    <span>{{ $occupation->category->title }}{{ !($loop->last) ? ', ' : '' }}</span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <p class=" js-font-resize font-14 text-gray mt-10">{{ truncate($instructor->about, 200) }}</p>
            </div>
        </div>

        <div class=" js-font-resize col-12 col-md-4 mt-10 mt-md-0 pt-10 pt-md-0 instructor-finder-card-right-side position-relative">
            @if(!empty($discount))
                <span class=" js-font-resize off-badge badge badge-danger">{{ trans('public.offer',['off' => $discount]) }}</span>
            @endif

            <div class=" js-font-resize d-flex align-items-start">
                @if(!empty($instructor->meeting) and !empty($instructor->meeting->meetingTimes) and count($instructor->meeting->meetingTimes))
                    @if(!empty($price) and $price > 0)
                        <div class=" js-font-resize d-flex flex-column">
                            <span class=" js-font-resize font-20 font-weight-bold text-primary">{{ handlePrice(!empty($discount) ? ($price - ($price * $discount / 100)) : $price) }}</span>

                            @if(!empty($discount))
                                <span class=" js-font-resize font-14 font-weight-500 text-gray text-decoration-line-through">{{ handlePrice($price) }}</span>
                            @endif
                        </div>

                        <span class=" js-font-resize font-14 font-weight-500 text-gray mt-5">/{{ trans('update.hour') }}</span>
                    @else
                        <span class=" js-font-resize font-weight-bold text-primary font-14">{{ trans('public.free') }}</span>
                    @endif
                @else
                    <span class=" js-font-resize font-weight-bold text-danger font-12">{{ trans('update.not_available_for_meeting') }}</span>
                @endif
            </div>

            @include('web.default.includes.webinar.rate',['rate' => $instructor->rates()])

            <div class=" js-font-resize d-flex align-items-center mt-20">
                <i data-feather="clock" width="18" height="18" class=" js-font-resize text-dark-blue"></i>

                <span class=" js-font-resize font-14 font-weight-500 text-dark-blue ml-10">{{ $instructor->getTotalHoursTutoring() }} {{ trans('update.hours_tutoring') }}</span>
            </div>

            <div class=" js-font-resize d-flex align-items-center flex-wrap mt-15">
                @foreach($instructor->getBadges() as $badge)
                    <div class=" js-font-resize mr-15 mt-10 instructor-badge rounded-circle" data-toggle="tooltip" data-placement="bottom" data-html="true" title="{!! (!empty($badge->badge_id) ? nl2br($badge->badge->description) : nl2br($badge->description)) !!}">
                        <img src="{{ !empty($badge->badge_id) ? $badge->badge->image : $badge->image }}" class=" js-font-resize img-cover rounded-circle" alt="{{ !empty($badge->badge_id) ? $badge->badge->title : $badge->title }}">
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</a>
