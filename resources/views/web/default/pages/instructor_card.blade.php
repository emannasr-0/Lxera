@php
    $canReserve = false;
    if(!empty($instructor->meeting) and !$instructor->meeting->disabled and !empty($instructor->meeting->meetingTimes) and $instructor->meeting->meeting_times_count > 0) {
        $canReserve = true;
    }
@endphp

<div class=" js-font-resize rounded-lg shadow-sm mt-25 p-20 course-teacher-card instructors-list text-center d-flex align-items-center flex-column position-relative">
    @if(!empty($instructor->meeting) and $instructor->meeting->disabled)
        <span class=" js-font-resize px-15 py-10 bg-gray off-label text-white font-12">{{ trans('public.unavailable') }}</span>
    @elseif(!empty($instructor->meeting) and !empty($instructor->meeting->discount))
        <span class=" js-font-resize px-15 py-10 bg-danger off-label text-white font-12">{{ $instructor->meeting->discount }}% {{ trans('public.off') }}</span>
    @endif

    <a href="{{ $instructor->getProfileUrl() }}{{ ($canReserve) ? '?tab=appointments' : '' }}" class=" js-font-resize text-center d-flex flex-column align-items-center justify-content-center">
        <div class=" js-font-resize  teacher-avatar mt-5 position-relative">
            <img src="{{ $instructor->getAvatar(190) }}" class=" js-font-resize img-cover" alt="{{ $instructor->full_name }}">

            @if($instructor->offline)
                <span class=" js-font-resize user-circle-badge unavailable d-flex align-items-center justify-content-center">
                <i data-feather="slash" width="20" height="20" class=" js-font-resize text-white"></i>
                </span>
            @elseif($instructor->verified)
                <span class=" js-font-resize user-circle-badge has-verified d-flex align-items-center justify-content-center">
                    <i data-feather="check" width="20" height="20" class=" js-font-resize text-white"></i>
                </span>
            @endif
        </div>

        <h3 class=" js-font-resize mt-20 font-16 font-weight-bold text-dark-blue text-center">{{ $instructor->full_name }}</h3>
    </a>

    <div class=" js-font-resize mt-5 font-14 text-gray">
        @if(!empty($instructor->bio))
            {{ $instructor->bio }}
        @endif
    </div>

    <div class=" js-font-resize stars-card d-flex align-items-center mt-10">
        @include('web.default.includes.webinar.rate',['rate' => $instructor->rates()])
    </div>

    <div class=" js-font-resize d-flex align-items-center mt-20">
        @foreach($instructor->getBadges() as $badge)
            <div class=" js-font-resize mr-15 mt-5" data-toggle="tooltip" data-placement="bottom" data-html="true" title="{!! (!empty($badge->badge_id) ? nl2br($badge->badge->description) : nl2br($badge->description)) !!}">
                <img src="{{ !empty($badge->badge_id) ? $badge->badge->image : $badge->image }}" width="32" height="32" alt="{{ !empty($badge->badge_id) ? $badge->badge->title : $badge->title }}">
            </div>
        @endforeach
    </div>

    <div class=" js-font-resize mt-15">
        @if(!empty($instructor->meeting) and !$instructor->meeting->disabled and !empty($instructor->meeting->amount))
            @if(!empty($instructor->meeting->discount))
                <span class=" js-font-resize font-20 text-primary font-weight-bold">{{ handlePrice($instructor->meeting->amount - (($instructor->meeting->amount * $instructor->meeting->discount) / 100)) }}</span>
                <span class=" js-font-resize font-14 text-gray text-decoration-line-through ml-10">{{ handlePrice($instructor->meeting->amount) }}</span>
            @else
                <span class=" js-font-resize font-20 text-primary font-weight-500">{{ handlePrice($instructor->meeting->amount) }}</span>
            @endif
        @else
            <span class=" js-font-resize py-10">&nbsp;</span>
        @endif
    </div>

    <div class=" js-font-resize mt-20 d-flex flex-row align-items-center justify-content-center w-100">
        <a href="{{ $instructor->getProfileUrl() }}{{ ($canReserve) ? '?tab=appointments' : '' }}" class=" js-font-resize btn btn-primary btn-block">
            @if($canReserve)
                {{ trans('public.reserve_a_meeting') }}
            @else
                {{ trans('public.view_profile') }}
            @endif
        </a>
    </div>
</div>
