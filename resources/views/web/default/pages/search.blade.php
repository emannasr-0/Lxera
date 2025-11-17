@extends(getTemplate().'.layouts.app')

@push('styles_top')

@endpush


@section('content')
    @if((!empty($webinars) and count($webinars)) or (!empty($products) and count($products)) or (!empty($teachers) and count($teachers)) or (!empty($organizations) and count($organizations)))
        <section class=" js-font-resize site-top-banner search-top-banner opacity-04 position-relative">
            <img src="{{ getPageBackgroundSettings('search') }}" class=" js-font-resize img-cover" alt=""/>

            <div class=" js-font-resize container h-100">
                <div class=" js-font-resize row h-100 align-items-center justify-content-center text-center">
                    <div class=" js-font-resize col-12 col-md-9 col-lg-7">
                        <div class=" js-font-resize top-search-form">
                            <h1 class=" js-font-resize text-white font-30 white-space-pre-wrap">{{ trans('site.result_find',['count' => $resultCount , 'search' => request()->get('search')]) }}</h1>

                            <div class=" js-font-resize search-input bg-white p-10 flex-grow-1">
                                <form action="/search" method="get">
                                    <div class=" js-font-resize form-group d-flex align-items-center m-0">
                                        <input type="text" name="search" class=" js-font-resize form-control border-0" value="{{ request()->get('search','') }}" placeholder="{{ trans('home.slider_search_placeholder') }}"/>
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
            @if(!empty($webinars) and count($webinars))
                <section class=" js-font-resize mt-50">
                    <h2 class=" js-font-resize font-24 font-weight-bold text-secondary">{{ trans('webinars.webinars') }}</h2>

                    <div class=" js-font-resize row">
                        @foreach($webinars as $webinar)
                            <div class=" js-font-resize col-md-6 col-lg-4 mt-30">
                                @include(getTemplate().'.includes.webinar.grid-card',['webinar' => $webinar])
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if(!empty($products) and count($products))
                <section class=" js-font-resize mt-50">
                    <h2 class=" js-font-resize font-24 font-weight-bold text-secondary">{{ trans('update.products') }}</h2>

                    <div class=" js-font-resize row">
                        @foreach($products as $product)
                            <div class=" js-font-resize col-md-6 col-lg-4 mt-30">
                                @include('web.default.products.includes.card',['product' => $product])
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if(!empty($teachers) and count($teachers))
                <section class=" js-font-resize mt-50">
                    <h2 class=" js-font-resize font-24 font-weight-bold text-secondary">{{ trans('panel.users') }}</h2>

                    <div class=" js-font-resize row">
                        @foreach($teachers as $teacher)
                            <div class=" js-font-resize col-6 col-md-3 col-lg-2 mt-30">
                                <div class=" js-font-resize user-search-card text-center d-flex flex-column align-items-center justify-content-center">
                                    <div class=" js-font-resize user-avatar">
                                        <img src="{{ $teacher->getAvatar() }}" class=" js-font-resize img-cover rounded-circle" alt="{{ $teacher->full_name }}">
                                    </div>
                                    <a href="{{ $teacher->getProfileUrl() }}">
                                        <h4 class=" js-font-resize font-16 font-weight-bold text-dark-blue mt-10">{{ $teacher->full_name }}</h4>
                                        <span class=" js-font-resize d-block font-14 text-gray mt-5">{{ $teacher->bio }}</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if(!empty($organizations) and count($organizations))
                <section class=" js-font-resize mt-50">
                    <h2 class=" js-font-resize font-24 font-weight-bold text-secondary">{{ trans('home.organizations') }}</h2>

                    <div class=" js-font-resize row">

                        @foreach($organizations as $organization)
                            <div class=" js-font-resize col-md-6 col-lg-3 mt-30">
                                <a href="{{ $organization->getProfileUrl() }}" class=" js-font-resize home-organizations-card d-flex flex-column align-items-center justify-content-center">
                                    <div class=" js-font-resize home-organizations-avatar">
                                        <img src="{{ $organization->getAvatar() }}" class=" js-font-resize img-cover rounded-circle" alt="{{ $organization->full_name }}">
                                    </div>
                                    <div class=" js-font-resize mt-25 d-flex flex-column align-items-center justify-content-center">
                                        <h3 class=" js-font-resize home-organizations-title">{{ $organization->full_name }}</h3>
                                        <p class=" js-font-resize home-organizations-desc mt-10">{{ $organization->bio }}</p>
                                        <span class=" js-font-resize home-organizations-badge badge mt-15">{{ $organization->getActiveWebinars(true) }} {{ trans('product.courses') }}</span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    @else

        <div class=" js-font-resize no-result status-failed my-50 d-flex align-items-center justify-content-center flex-column">
            <div class=" js-font-resize no-result-logo">
                <img src="/assets/default/img/no-results/search.png" alt="">
            </div>
            <div class=" js-font-resize container">
                <div class=" js-font-resize row h-100 align-items-center justify-content-center text-center">
                    <div class=" js-font-resize col-12 col-md-9 col-lg-7">
                        <div class=" js-font-resize d-flex align-items-center flex-column mt-30 text-center w-100">
                            <h2>{{ trans('site.no_result_search') }}</h2>
                            <p class=" js-font-resize mt-5 text-center white-space-pre-wrap">{{ trans('site.no_result_search_hint',['search' => request()->get('search')]) }}</p>

                            <div class=" js-font-resize search-input bg-white p-10 mt-20 flex-grow-1 shadow-sm rounded-pill w-100">
                                <form action="/search" method="get">
                                    <div class=" js-font-resize form-group d-flex align-items-center m-0">
                                        <input type="text" name="search" class=" js-font-resize form-control border-0" value="{{ request()->get('search','') }}" placeholder="{{ trans('home.slider_search_placeholder') }}"/>
                                        <button type="submit" class=" js-font-resize btn btn-primary rounded-pill">{{ trans('home.find') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
