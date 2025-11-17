@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
@endpush


@section('content')
    <section class=" js-font-resize site-top-banner search-top-banner opacity-04 position-relative">
        <img src="{{ getPageBackgroundSettings('upcoming_courses_lists') }}" class=" js-font-resize img-cover" alt=""/>

        <div class=" js-font-resize container h-100">
            <div class=" js-font-resize row h-100 align-items-center justify-content-center text-center">
                <div class=" js-font-resize col-12 col-md-9 col-lg-7">
                    <div class=" js-font-resize top-search-categories-form">
                        <h1 class=" js-font-resize text-white font-30 mb-15">{{ trans('update.upcoming_courses') }}</h1>
                        <span class=" js-font-resize course-count-badge py-5 px-10 text-white rounded">{{ $upcomingCoursesCount }} {{ trans('product.courses') }}</span>

                        <div class=" js-font-resize search-input bg-white p-10 flex-grow-1">
                            <form action="/upcoming_courses" method="get">
                                <div class=" js-font-resize form-group d-flex align-items-center m-0">
                                    <input type="text" name="search" class=" js-font-resize form-control border-0" placeholder="{{ trans('home.slider_search_placeholder') }}"/>
                                    <button type="submit" class=" js-font-resize btn btn-primary rounded-pill">{{ trans('home.find') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class=" js-font-resize container mt-30">

        <section class=" js-font-resize mt-lg-50 pt-lg-20 mt-md-40 pt-md-40">
            <form action="/upcoming_courses" method="get" id="filtersForm">

                @include('web.default.upcoming_courses.includes.top_filters')

                <div class=" js-font-resize row mt-20">
                    <div class=" js-font-resize col-12 col-lg-8">

                        @if(empty(request()->get('card')) or request()->get('card') == 'grid')
                            <div class=" js-font-resize row">
                                @foreach($upcomingCourses as $upcomingCourse)
                                    <div class=" js-font-resize col-12 col-lg-6 mt-20">
                                        @include('web.default.includes.webinar.upcoming_course_grid_card',['upcomingCourse' => $upcomingCourse])
                                    </div>
                                @endforeach
                            </div>

                        @elseif(!empty(request()->get('card')) and request()->get('card') == 'list')

                            @foreach($upcomingCourses as $upcomingCourse)
                                @include('web.default.includes.webinar.upcoming_course_list_card',['upcomingCourse' => $upcomingCourse])
                            @endforeach
                        @endif

                    </div>


                    <div class=" js-font-resize col-12 col-lg-4">
                        @include('web.default.upcoming_courses.includes.right_filters')
                    </div>
                </div>

            </form>
            <div class=" js-font-resize mt-50 pt-30">
                {{ $upcomingCourses->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        </section>
    </div>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>

    <script src="/assets/default/js/parts/categories.min.js"></script>
@endpush
