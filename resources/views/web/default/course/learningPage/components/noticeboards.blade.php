<section class=" js-font-resize px-15 pb-15 my-15 mx-lg-15 bg-white rounded-lg">

    @if(!empty($course->noticeboards) and count($course->noticeboards))
        @foreach($course->noticeboards as $noticeboard)
            <div class=" js-font-resize course-noticeboards noticeboard-{{ $noticeboard->color }} p-15 mt-15 rounded-sm w-100">
                <div class=" js-font-resize d-flex align-items-center">
                    <div class=" js-font-resize course-noticeboard-icon d-flex align-items-center justify-content-center rounded-circle">
                        <i data-feather="{{ $noticeboard->getIcon() }}" class=" js-font-resize " width="24" height="24"></i>
                    </div>

                    <div class=" js-font-resize ml-10">
                        <h3 class=" js-font-resize font-14 font-weight-bold">{{ $noticeboard->title }}</h3>
                        <span class=" js-font-resize d-block font-12">{{ $noticeboard->creator->full_name }} {{ trans('public.in') }} {{ dateTimeFormat($noticeboard->created_at,'j M Y') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize mt-10 font-14">{!! $noticeboard->message !!}</div>
            </div>
        @endforeach
    @endif

</section>
