@extends(getTemplate().'.layouts.app')

@section('content')
    <section class=" js-font-resize cart-banner position-relative text-center">
        <div class=" js-font-resize container h-100">
            <div class=" js-font-resize row h-100 align-items-center justify-content-center text-center">
                <div class=" js-font-resize col-12 col-md-9 col-lg-7">

                    <h1 class=" js-font-resize font-30 text-white font-weight-bold">{{ $textLesson->title }}</h1>

                    <div class=" js-font-resize mt-20 font-16 font-weight-500 text-white">
                        <span>{{ trans('public.lesson') }} {{ $textLesson->order }}/{{ count($course->textLessons) }} </span> | <span>{{ trans('public.study_time') }}: {{ $textLesson->study_time }} {{ trans('public.min') }}</span>
                    </div>

                    <div class=" js-font-resize mt-20 font-16 font-weight-500 text-white">
                        <span>{{ trans('product.course') }}: <a href="{{ $course->getUrl() }}" class=" js-font-resize font-16 font-weight-500 text-white text-decoration-underline">{{ $course->title }}</a></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class=" js-font-resize container mt-10 mt-md-40">
        <div class=" js-font-resize row">
            <div class=" js-font-resize col-12 col-lg-8">
                <div class=" js-font-resize post-show mt-30">

                    <div class=" js-font-resize post-img pb-30">
                        <img src="{{ url($textLesson->image) }}" alt="{{ $textLesson->title }}"/>
                    </div>

                    {!! nl2br($textLesson->content) !!}
                </div>


                <div class=" js-font-resize mt-30 row align-items-center">
                    <div class=" js-font-resize col-12 col-md-5">
                        @if(auth()->check())
                            <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                <label class=" js-font-resize cursor-pointer font-weight-500" for="readLessonSwitch">{{ trans('public.i_passed_this_lesson') }}</label>
                                <div class=" js-font-resize custom-control custom-switch">
                                    <input type="checkbox" name="read" class=" js-font-resize custom-control-input" id="readLessonSwitch" data-course-id="{{ $course->id }}" data-lesson-id="{{ $textLesson->id }}" {{ !empty($textLesson->checkPassedItem()) ? 'checked' : ''  }}>
                                    <label class=" js-font-resize custom-control-label" for="readLessonSwitch"></label>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class=" js-font-resize col-12 col-md-7 text-right">
                        @if(!empty($course->textLessons) and count($course->textLessons))
                            <a href="{{ (!empty($previousLesson)) ? $course->getUrl() .'/lessons/'. $previousLesson->id .'/read' : '#' }}" class=" js-font-resize btn btn-sm {{ (!empty($previousLesson)) ? 'btn-primary' : 'btn-gray disabled' }}">{{ trans('public.previous_lesson') }}</a>

                            @if(!empty($nextLesson))
                                <a href="{{ (!$nextLesson->not_purchased) ? $course->getUrl() .'/lessons/'. $nextLesson->id .'/read' : '#' }}" class=" js-font-resize btn btn-sm {{ (!$nextLesson->not_purchased) ? 'btn-primary' : 'btn-gray disabled' }} {{ ($nextLesson->not_purchased) ? 'js-not-purchased' : '' }}">{{ trans('public.next_lesson') }}</a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <div class=" js-font-resize col-12 col-lg-4">

                <div class=" js-font-resize rounded-lg shadow-sm mt-35 p-20 course-teacher-card d-flex align-items-center flex-column">
                    <div class=" js-font-resize teacher-avatar mt-5">
                        <img src="{{ $course->teacher->getAvatar(100) }}" class=" js-font-resize img-cover" alt="{{ $course->teacher->full_name }}">
                    </div>
                    <h3 class=" js-font-resize mt-10 font-20 font-weight-bold text-secondary">{{ $course->teacher->full_name }}</h3>
                    <span class=" js-font-resize mt-5 font-weight-500 text-gray">{{ trans('product.product_designer') }}</span>

                    @include('web.default.includes.webinar.rate',['rate' => $course->teacher->rates()])

                    <div class=" js-font-resize user-reward-badges d-flex flex-wrap align-items-center mt-20">
                        @foreach($course->teacher->getBadges() as $userBadge)
                            <div class=" js-font-resize mr-15 mt-10" data-toggle="tooltip" data-placement="bottom" data-html="true" title="{!! (!empty($userBadge->badge_id) ? nl2br($userBadge->badge->description) : nl2br($userBadge->description)) !!}">
                                <img src="{{ !empty($userBadge->badge_id) ? $userBadge->badge->image : $userBadge->image }}" width="32" height="32" alt="{{ !empty($userBadge->badge_id) ? $userBadge->badge->title : $userBadge->title }}">
                            </div>
                        @endforeach
                    </div>

                    <div class=" js-font-resize mt-25 d-flex flex-row align-items-center justify-content-center w-100">
                        <a href="{{ $course->teacher->getProfileUrl() }}" target="_blank" class=" js-font-resize btn btn-sm btn-primary teacher-btn-action">{{ trans('public.profile') }}</a>

                        @if(!empty($course->teacher->hasMeeting()))
                            <a href="{{ $course->teacher->getProfileUrl() }}" class=" js-font-resize btn btn-sm btn-primary teacher-btn-action ml-15">{{ trans('public.book_a_meeting') }}</a>
                        @else
                            <button type="button" class=" js-font-resize btn btn-sm btn-primary disabled teacher-btn-action ml-15">{{ trans('public.book_a_meeting') }}</button>
                        @endif
                    </div>
                </div>

                @if(!empty($textLesson->attachments) and count($textLesson->attachments))
                    <div class=" js-font-resize shadow-sm rounded-lg bg-white px-15 px-md-25 py-20 mt-30">
                        <h3 class=" js-font-resize category-filter-title font-16 font-weight-bold text-dark-blue">{{ trans('public.attachments') }}</h3>

                        <ul class=" js-font-resize p-0 m-0 pt-10">
                            @foreach($textLesson->attachments as $attachment)
                                <li class=" js-font-resize mt-10 p-10 rounded bg-info-light font-14 font-weight-500 text-dark-blue d-flex align-items-center justify-content-between text-ellipsis">
                                    <span class=" js-font-resize ">{{ $attachment->file->title }}</span>

                                    <a href="{{ $course->getLearningPageUrl() }}?type=file&item={{ $attachment->file->id }}" target="_blank">
                                        <i data-feather="download-cloud" width="20" class=" js-font-resize text-secondary"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($course->textLessons) and count($course->textLessons))
                    <div class=" js-font-resize shadow-sm rounded-lg bg-white px-15 px-md-25 py-20 mt-30">
                        <h3 class=" js-font-resize category-filter-title font-16 font-weight-bold text-dark-blue">{{ trans('public.course_sessions') }}</h3>

                        <div class=" js-font-resize p-0 m-0 pt-10">
                            @foreach($course->textLessons as $lesson)
                                <a href="{{ $course->getUrl() }}/lessons/{{ $lesson->id }}/read"
                                   class=" js-font-resize d-block mt-10 px-10 py-15 rounded font-14 font-weight-500 text-ellipsis @if($lesson->id == $textLesson->id) bg-primary text-white @else bg-info-light text-dark-blue @endif">
                                    {{ $loop->iteration .'- '. $lesson->title }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        var learningToggleLangSuccess = '{{ trans('public.course_learning_change_status_success') }}';
        var learningToggleLangError = '{{ trans('public.course_learning_change_status_error') }}';
    </script>

    <script src="/assets/default/js/parts/text_lesson.min.js"></script>
@endpush
