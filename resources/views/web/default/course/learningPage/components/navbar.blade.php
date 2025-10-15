@php
    $percent = $course->getProgress(true);
@endphp

<style>
    @media(max-width:993px) {
        .learning-page-logo-card .navbar-brand {
        width: 130px !important;
    }
    }
</style>

<div class="learning-page-navbar d-flex align-items-lg-center justify-content-between flex-column flex-lg-row px-15 px-lg-35 border-bottom">
    <div class="d-flex align-items-lg-center flex-column flex-lg-row col-8 col-lg-6">

        <div class="">
            <a class="navbar-brand d-flex align-items-center justify-content-center mr-0" href="/">
                @if(!empty($generalSettings['logo']))
                    <img src="{{ asset('store/Acadima/acadima-logo.webp') }}" class="img-cover" alt="site logo">
                @endif
            </a>

            <div class="d-flex align-items-center d-lg-none ml-20">
                {{-- <a href="{{ $course->getUrl() }}" class="btn learning-page-navbar-btn btn-sm border-gray200 d-none d-md-block">{{ trans('update.course_page') }}</a> --}}

                <a href="/panel/webinars/purchases" class="btn btn-sm ml-10  btn-acadima-primary">{{trans('panel.course_schedule')}}</a>
                </div>
        </div>

        <div class="learning-page-progress-card d-flex flex-column">
            <a href="" class="learning-page-navbar-title">
                <span class="font-weight-bold text-pink">{{ $course->title }}</span>
            </a>

            @if ($user->isUser())
                <div class="d-flex align-items-center">
                    <div class="progress course-progress d-flex align-items-center flex-grow-1 bg-light rounded-sm shadow-none">
                        <span class="progress-bar rounded-sm bg-gray" style="width: {{ $percent }}%"></span>
                    </div>

                    <span class="ml-10 font-weight-500 font-14 text-gray">{{ $percent }}% {{ trans('update.learnt') }}</span>
                </div>
            @endif
        </div>
    </div>

    <div class="d-flex align-items-center mt-5 mt-md-0">

        @if(!empty($course->noticeboards_count) and $course->noticeboards_count > 0)
            <a href="{{ $course->getNoticeboardsPageUrl() }}" target="_blank" class="btn  btn-acadima-primary noticeboard-btn btn-sm border-gray200 mr-10">
                <i data-feather="bell" class="" width="16" height="16"></i>

                <span class="noticeboard-btn-badge d-flex align-items-center justify-content-center text-white bg-danger rounded-circle font-12">{{ $course->noticeboards_count }}</span>
            </a>
        @endif

        {{-- @if($course->forum)
            <a href="{{ $course->getForumPageUrl() }}" class="btn  btn-acadima-primary btn-sm mr-10">{{ trans('update.course_forum') }}</a>
        @endif --}}

        <div class="d-none align-items-center d-lg-flex">
            {{-- <a href="{{ $course->getUrl() }}" class="btn  btn-acadima-primary btn-sm ">{{ trans('update.course_page') }}</a> --}}

            <a href="/panel/webinars/purchases" class="btn btn-sm ml-10  btn-acadima-primary">{{trans('panel.course_schedule')}}</a>
        </div>

        <button id="collapseBtn" type="button" class="btn-transparent ml-auto ml-lg-20">
            <i data-feather="menu" width="20" height="20" class="text-black"></i>
        </button>
    </div>
</div>
