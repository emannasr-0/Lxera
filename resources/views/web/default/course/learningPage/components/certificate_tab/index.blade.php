@php
    $hasCertificateItem=false;
@endphp

<div class=" js-font-resize content-tab p-15 pb-50 shadow border">
    @if($course->certificate)
        @php
            $hasCertificateItem = true;
        @endphp

        <div class=" js-font-resize course-certificate-item cursor-pointer p-10 border border-gray200 rounded-sm mb-15" data-course-certificate="{{ !empty($courseCertificate) ? $courseCertificate->id : '' }}">
            <div class=" js-font-resize d-flex align-items-center">
                <span class=" js-font-resize chapter-icon bg-acadima-pink mr-10">
                    <i data-feather="award" class=" js-font-resize text-light" width="16" height="16"></i>
                </span>

                <div class=" js-font-resize flex-grow-1">
                    <span class=" js-font-resize font-weight-500 font-14 text-dark d-block">{{ trans('update.course_certificate') }}</span>

                    <div class=" js-font-resize d-flex align-items-center">
                        @if(!empty($courseCertificate))
                            <span class=" js-font-resize font-12 text-gray">{{ trans("public.date") }}: {{ dateTimeFormat($courseCertificate->created_at, 'j F Y') }}</span>
                        @else
                            <span class=" js-font-resize font-12 text-gray">{{ trans("update.not_achieve") }}</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    @endif

    @if(!empty($course->quizzes) and count($course->quizzes))
        @foreach($course->quizzes as $courseQuiz)
            @if($courseQuiz->certificate)
                @php
                    $hasCertificateItem = true;
                @endphp

                <div class=" js-font-resize certificate-item cursor-pointer p-10 border border-gray200 rounded-sm mb-15" data-result="{{ $courseQuiz->result ? $courseQuiz->result->id : '' }}">
                    <div class=" js-font-resize d-flex align-items-center">
                        <span class=" js-font-resize chapter-icon bg-acadima-pink mr-10">
                            <i data-feather="award" class=" js-font-resize text-light" width="16" height="16"></i>
                        </span>

                        <div class=" js-font-resize flex-grow-1">
                            <span class=" js-font-resize font-weight-500 font-14 text-dark d-block">{{ $courseQuiz->title }}</span>

                            <div class=" js-font-resize d-flex align-items-center">
                                <span class=" js-font-resize font-12 text-gray">{{ $courseQuiz->pass_mark }}/{{ $courseQuiz->quizQuestions->sum('grade') }}</span>

                                @if(!empty($courseQuiz->result))
                                    <span class=" js-font-resize font-12 text-gray ml-10">{{ dateTimeFormat($courseQuiz->result->created_at, 'j M Y H:i') }}</span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            @endif
        @endforeach
    @endif

    @if(!$hasCertificateItem)
        <div class=" js-font-resize learning-page-forum-empty d-flex align-items-center justify-content-center flex-column">
            <div class=" js-font-resize learning-page-forum-empty-icon d-flex align-items-center justify-content-center">
                <img src="/assets/default/img/learning/certificate-empty.svg" class=" js-font-resize img-fluid" alt="">
            </div>

            <div class=" js-font-resize d-flex align-items-center flex-column mt-10 text-center">
                <h3 class=" js-font-resize font-20 font-weight-bold text-light text-center">{{ trans('update.learning_page_empty_certificate_title') }}</h3>
                <p class=" js-font-resize font-14 font-weight-500 text-gray mt-5 text-center">{{ trans('update.learning_page_empty_certificate_hint') }}</p>
            </div>
        </div>
    @endif
</div>
