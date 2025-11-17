@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
@endpush

@section('content')
    <section class=" js-font-resize site-top-banner search-top-banner opacity-04 position-relative">
        <img src="{{ getPageBackgroundSettings('categories') }}" class=" js-font-resize img-cover" alt=""/>

        <div class=" js-font-resize container h-100">
            <div class=" js-font-resize row h-100 align-items-center justify-content-center text-center">
                <div class=" js-font-resize col-12 col-md-9 col-lg-7">
                    <div class=" js-font-resize top-search-categories-form">
                        <h1 class=" js-font-resize text-white font-30 mb-15">{{ $pageTitle }}</h1>
                        <span class=" js-font-resize course-count-badge py-5 px-10 text-white rounded">{{ $coursesCount }} {{ trans('product.courses') }}</span>

                        <div class=" js-font-resize search-input bg-white p-10 flex-grow-1">
                            <form action="/search" method="get">
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
            <form action="/classes" method="get" id="filtersForm">

                @include('web.default.pages.includes.top_filters')

                <div class=" js-font-resize row mt-20">
                    <div class=" js-font-resize col-12 col-lg-8">

                        @if(empty(request()->get('card')) or request()->get('card') == 'grid')
                            <div class=" js-font-resize row">
                                @foreach($webinars as $webinar)
                                    <div class=" js-font-resize col-12 col-lg-6 mt-20">
                                        @include('web.default.includes.webinar.grid-card',['webinar' => $webinar])
                                    </div>
                                @endforeach
                            </div>

                        @elseif(!empty(request()->get('card')) and request()->get('card') == 'list')

                            @foreach($webinars as $webinar)
                                @include('web.default.includes.webinar.list-card',['webinar' => $webinar])
                            @endforeach
                        @endif

                    </div>


                    <div class=" js-font-resize col-12 col-lg-4">
                        <div class=" js-font-resize mt-20 p-20 rounded-sm shadow-lg border border-gray300 filters-container">

                            <div class=" js-font-resize ">
                                <h3 class=" js-font-resize category-filter-title font-20 font-weight-bold text-dark-blue">{{ trans('public.type') }}</h3>

                                <div class=" js-font-resize pt-10">
                                    @foreach(['bundle','webinar','course','text_lesson'] as $typeOption)
                                        <div class=" js-font-resize d-flex align-items-center justify-content-between mt-20">
                                            <label class=" js-font-resize cursor-pointer" for="filterLanguage{{ $typeOption }}">
                                                @if($typeOption == 'bundle')
                                                    {{ trans('update.bundle') }}
                                                @else
                                                    {{ trans('webinars.'.$typeOption) }}
                                                @endif
                                            </label>
                                            <div class=" js-font-resize custom-control custom-checkbox">
                                                <input type="checkbox" name="type[]" id="filterLanguage{{ $typeOption }}" value="{{ $typeOption }}" @if(in_array($typeOption, request()->get('type', []))) checked="checked" @endif class=" js-font-resize custom-control-input">
                                                <label class=" js-font-resize custom-control-label" for="filterLanguage{{ $typeOption }}"></label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class=" js-font-resize mt-25 pt-25 border-top border-gray300">
                                <h3 class=" js-font-resize category-filter-title font-20 font-weight-bold text-dark-blue">{{ trans('site.more_options') }}</h3>

                                <div class=" js-font-resize pt-10">
                                    @foreach(['subscribe','certificate_included','with_quiz','featured'] as $moreOption)
                                        <div class=" js-font-resize d-flex align-items-center justify-content-between mt-20">
                                            <label class=" js-font-resize cursor-pointer" for="filterLanguage{{ $moreOption }}">{{ trans('webinars.show_only_'.$moreOption) }}</label>
                                            <div class=" js-font-resize custom-control custom-checkbox">
                                                <input type="checkbox" name="moreOptions[]" id="filterLanguage{{ $moreOption }}" value="{{ $moreOption }}" @if(in_array($moreOption, request()->get('moreOptions', []))) checked="checked" @endif class=" js-font-resize custom-control-input">
                                                <label class=" js-font-resize custom-control-label" for="filterLanguage{{ $moreOption }}"></label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>


                            <button type="submit" class=" js-font-resize btn btn-sm btn-primary btn-block mt-30">{{ trans('site.filter_items') }}</button>
                        </div>
                    </div>
                </div>

            </form>
            <div class=" js-font-resize mt-50 pt-30">
                {{ $webinars->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        </section>
    </div>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>

    <script src="/assets/default/js/parts/categories.min.js"></script>
@endpush
