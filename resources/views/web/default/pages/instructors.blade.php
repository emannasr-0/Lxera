@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
@endpush


@section('content')
    <section class=" js-font-resize site-top-banner search-top-banner opacity-04 position-relative">
        <img src="{{ getPageBackgroundSettings($page) }}" class=" js-font-resize img-cover" alt=""/>

        <div class=" js-font-resize container h-100">
            <div class=" js-font-resize row h-100 align-items-center justify-content-center text-center">
                <div class=" js-font-resize col-12 col-md-9 col-lg-7">
                    <div class=" js-font-resize top-search-categories-form">
                        <h1 class=" js-font-resize text-white font-30 mb-15">{{ $title }}</h1>
                        <span class=" js-font-resize course-count-badge py-5 px-10 text-white rounded">{{ $instructorsCount }} {{ $title }}</span>

                        <div class=" js-font-resize search-input bg-white p-10 flex-grow-1">
                            <form action="/{{ $page }}" method="get">
                                <div class=" js-font-resize form-group d-flex align-items-center m-0">
                                    <input type="text" name="search" class=" js-font-resize form-control border-0" value="{{ request()->get('search') }}" placeholder="{{ trans('public.search') }} {{ $title }}"/>
                                    <button type="submit" class=" js-font-resize btn btn-primary rounded-pill">{{ trans('home.find') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class=" js-font-resize container">

        <form id="filtersForm" action="/{{ $page }}" method="get">

            <div id="topFilters" class=" js-font-resize mt-25 shadow-lg border border-gray300 rounded-sm p-10 p-md-20">
                <div class=" js-font-resize row align-items-center">
                    <div class=" js-font-resize col-lg-9 d-block d-md-flex align-items-center justify-content-start my-25 my-lg-0">
                        <div class=" js-font-resize d-flex align-items-center justify-content-between justify-content-md-center">
                            <label class=" js-font-resize mb-0 mr-10 cursor-pointer" for="available_for_meetings">{{ trans('public.available_for_meetings') }}</label>
                            <div class=" js-font-resize custom-control custom-switch">
                                <input type="checkbox" name="available_for_meetings" class=" js-font-resize custom-control-input" id="available_for_meetings" @if(request()->get('available_for_meetings',null) == 'on') checked="checked" @endif>
                                <label class=" js-font-resize custom-control-label" for="available_for_meetings"></label>
                            </div>
                        </div>

                        <div class=" js-font-resize d-flex align-items-center justify-content-between justify-content-md-center mx-0 mx-md-20 my-20 my-md-0">
                            <label class=" js-font-resize mb-0 mr-10 cursor-pointer" for="free_meetings">{{ trans('public.free_meetings') }}</label>
                            <div class=" js-font-resize custom-control custom-switch">
                                <input type="checkbox" name="free_meetings" class=" js-font-resize custom-control-input" id="free_meetings" @if(request()->get('free_meetings',null) == 'on') checked="checked" @endif>
                                <label class=" js-font-resize custom-control-label" for="free_meetings"></label>
                            </div>
                        </div>

                        <div class=" js-font-resize d-flex align-items-center justify-content-between justify-content-md-center">
                            <label class=" js-font-resize mb-0 mr-10 cursor-pointer" for="discount">{{ trans('public.discount') }}</label>
                            <div class=" js-font-resize custom-control custom-switch">
                                <input type="checkbox" name="discount" class=" js-font-resize custom-control-input" id="discount" @if(request()->get('discount',null) == 'on') checked="checked" @endif>
                                <label class=" js-font-resize custom-control-label" for="discount"></label>
                            </div>
                        </div>

                    </div>

                    <div class=" js-font-resize col-lg-3 d-flex align-items-center">
                        <select name="sort" class=" js-font-resize form-control">
                            <option disabled selected>{{ trans('public.sort_by') }}</option>
                            <option value="">{{ trans('public.all') }}</option>
                            <option value="top_rate" @if(request()->get('sort',null) == 'top_rate') selected="selected" @endif>{{ trans('site.top_rate') }}</option>
                            <option value="top_sale" @if(request()->get('sort',null) == 'top_sale') selected="selected" @endif>{{ trans('site.top_sellers') }}</option>
                        </select>
                    </div>

                </div>
            </div>

            <div class=" js-font-resize mt-30 px-20 py-15 rounded-sm shadow-lg border border-gray300">
                <h3 class=" js-font-resize category-filter-title font-20 font-weight-bold">{{ trans('categories.categories') }}</h3>

                <div class=" js-font-resize p-10 mt-20 d-flex  flex-wrap">

                    @foreach($categories as $category)
                        @if(!empty($category->subCategories) and count($category->subCategories))
                            @foreach($category->subCategories as $subCategory)
                                <div class=" js-font-resize checkbox-button bordered-200 mt-5 mr-15">
                                    <input type="checkbox" name="categories[]" id="checkbox{{ $subCategory->id }}" value="{{ $subCategory->id }}" @if(in_array($subCategory->id,request()->get('categories',[]))) checked="checked" @endif>
                                    <label for="checkbox{{ $subCategory->id }}">{{ $subCategory->title }}</label>
                                </div>
                            @endforeach
                        @else
                            <div class=" js-font-resize checkbox-button bordered-200 mr-20">
                                <input type="checkbox" name="categories[]" id="checkbox{{ $category->id }}" value="{{ $category->id }}" @if(in_array($category->id,request()->get('categories',[]))) checked="checked" @endif>
                                <label for="checkbox{{ $category->id }}">{{ $category->title }}</label>
                            </div>
                        @endif
                    @endforeach

                </div>
            </div>
        </form>

        <section>
            <div id="instructorsList" class=" js-font-resize row mt-20">

                @foreach($instructors as $instructor)
                    <div class=" js-font-resize col-12 col-md-6 col-lg-4">
                        @include('web.default.pages.instructor_card',['instructor' => $instructor])
                    </div>
                @endforeach

            </div>

            <div class=" js-font-resize text-center">
                <button type="button" id="loadMoreInstructors" data-page="{{ ($page == 'instructors') ? \App\Models\Role::$teacher : \App\Models\Role::$organization }}" class=" js-font-resize btn btn-border-white mt-50 {{ ($instructors->lastPage() <= $instructors->currentPage()) ? ' d-none' : '' }}">{{ trans('site.load_more_instructors') }}</button>
            </div>
        </section>


        @if(!empty($bestRateInstructors) and !$bestRateInstructors->isEmpty() and (empty(request()->get('sort')) or !in_array(request()->get('sort'),['top_rate','top_sale'])))
            <section class=" js-font-resize mt-30 pt-30">
                <div class=" js-font-resize d-flex justify-content-between">
                    <div>
                        <h2 class=" js-font-resize font-24 text-dark-blue">{{ trans('site.best_rated_instructors') }}</h2>
                        <span class=" js-font-resize font-14 text-gray">{{ trans('site.best_rated_instructors_subtitle') }}</span>
                    </div>

                    <a href="/{{ $page }}?sort=top_rate" class=" js-font-resize btn btn-border-white">{{ trans('home.view_all') }}</a>
                </div>

                <div class=" js-font-resize position-relative mt-20">
                    <div id="bestRateInstructorsSwiper" class=" js-font-resize swiper-container px-12">
                        <div class=" js-font-resize swiper-wrapper pb-20">

                            @foreach($bestRateInstructors as $bestRateInstructor)
                                <div class=" js-font-resize swiper-slide">
                                    @include('web.default.pages.instructor_card',['instructor' => $bestRateInstructor])
                                </div>
                            @endforeach

                        </div>
                    </div>

                    <div class=" js-font-resize d-flex justify-content-center">
                        <div class=" js-font-resize swiper-pagination best-rate-swiper-pagination"></div>
                    </div>
                </div>

            </section>
        @endif

        @if(!empty($bestSalesInstructors) and !$bestSalesInstructors->isEmpty() and (empty(request()->get('sort')) or !in_array(request()->get('sort'),['top_rate','top_sale'])))
            <section class=" js-font-resize mt-50 pt-50">
                <div class=" js-font-resize d-flex justify-content-between">
                    <div>
                        <h2 class=" js-font-resize font-24 text-dark-blue">{{ trans('site.top_sellers') }}</h2>
                        <span class=" js-font-resize font-14 text-gray">{{ trans('site.top_sellers_subtitle') }}</span>
                    </div>

                    <a href="/{{ $page }}?sort=top_sale" class=" js-font-resize btn btn-border-white">{{ trans('home.view_all') }}</a>
                </div>

                <div class=" js-font-resize position-relative mt-20">
                    <div id="topSaleInstructorsSwiper" class=" js-font-resize swiper-container px-12">
                        <div class=" js-font-resize swiper-wrapper pb-20">

                            @foreach($bestSalesInstructors as $bestSalesInstructor)
                                <div class=" js-font-resize swiper-slide">
                                    @include('web.default.pages.instructor_card',['instructor' => $bestSalesInstructor])
                                </div>
                            @endforeach

                        </div>
                    </div>

                    <div class=" js-font-resize d-flex justify-content-center">
                        <div class=" js-font-resize swiper-pagination best-sale-swiper-pagination"></div>
                    </div>
                </div>

            </section>
        @endif
    </div>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>

    <script src="/assets/default/js/parts/instructors.min.js"></script>
@endpush
