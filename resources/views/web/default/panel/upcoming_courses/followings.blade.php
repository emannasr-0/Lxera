@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')

@endpush

@section('content')

    <section>
        <div class=" js-font-resize d-flex align-items-center justify-content-between">
            <h2 class=" js-font-resize section-title">{{ trans('update.following_courses') }}</h2>
        </div>

        @if(!empty($upcomingCourses) and $upcomingCourses->isNotEmpty())

            @foreach($upcomingCourses as $upcomingCourse)
                <div class=" js-font-resize row mt-30">
                    <div class=" js-font-resize col-12">
                        <div class=" js-font-resize webinar-card webinar-list d-flex">
                            <div class=" js-font-resize image-box">
                                <img src="{{ $upcomingCourse->getImage() }}" class=" js-font-resize img-cover" alt="">

                                {{--@if(!empty($upcomingCourse->webinar_id))
                                    <span class=" js-font-resize badge badge-secondary text-dark-blue">{{  trans('update.released') }}</span>
                                @elseif($upcomingCourse->status == \App\Models\UpcomingCourse::$active)
                                    <span class=" js-font-resize badge badge-primary text-dark-blue">{{  trans('public.published') }}</span>
                                @endif--}}

                                @if(!empty($upcomingCourse->course_progress))
                                    <div class=" js-font-resize progress">
                                        <span class=" js-font-resize progress-bar {{ ($upcomingCourse->course_progress < 50) ? 'bg-warning' : '' }}" style="width: {{ $upcomingCourse->course_progress }}%"></span>
                                    </div>
                                @endif
                            </div>

                            <div class=" js-font-resize webinar-card-body w-100 d-flex flex-column">
                                <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                    <a href="{{ $upcomingCourse->getUrl() }}" target="_blank">
                                        <h3 class=" js-font-resize font-16 text-dark-blue font-weight-bold">{{ $upcomingCourse->title }}
                                            <span class=" js-font-resize badge badge-dark ml-10 status-badge-dark text-light">{{ trans('webinars.'.$upcomingCourse->type) }}</span>
                                        </h3>
                                    </a>
                                </div>

                                <div class=" js-font-resize d-flex align-items-center justify-content-between flex-wrap mt-auto">
                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('public.item_id') }}:</span>
                                        <span class=" js-font-resize stat-value">{{ $upcomingCourse->id }}</span>
                                    </div>

                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('public.category') }}:</span>
                                        <span class=" js-font-resize stat-value">{{ !empty($upcomingCourse->category_id) ? $upcomingCourse->category->title : '' }}</span>
                                    </div>

                                    @if(!empty($upcomingCourse->duration))
                                        <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class=" js-font-resize stat-title">{{ trans('webinars.next_session_duration') }}:</span>
                                            <span class=" js-font-resize stat-value">{{ convertMinutesToHourAndMinute($upcomingCourse->duration) }} Hrs</span>
                                        </div>
                                    @endif

                                    @if(!empty($upcomingCourse->publish_date))
                                        <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class=" js-font-resize stat-title">{{ trans('update.estimated_publish_date') }}:</span>
                                            <span class=" js-font-resize stat-value">{{ dateTimeFormat($upcomingCourse->publish_date, 'j M Y H:i') }}</span>
                                        </div>
                                    @endif

                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('public.price') }}:</span>
                                        <span class=" js-font-resize stat-value">{{ (!empty($upcomingCourse->price) and $upcomingCourse->price > 0) ? handlePrice($upcomingCourse->price) : trans('free') }}</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class=" js-font-resize my-30">
                {{ $upcomingCourses->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'student.png',
                'title' => trans('update.no_result_following_course'),
                'hint' =>  trans('update.no_result_following_course_hint') ,
            ])
        @endif

    </section>
@endsection
