@php
    if (empty($authUser) and auth()->check()) {
        $authUser = auth()->user();
    }

    $navBtnUrl = null;
    $navBtnText = null;

    if (request()->is('forums*')) {
        $navBtnUrl = '/forums/create-topic';
        $navBtnText = trans('update.create_new_topic');
    } else {
        $navbarButton = getNavbarButton(!empty($authUser) ? $authUser->role_id : null, empty($authUser));

        if (!empty($navbarButton)) {
            $navBtnUrl = $navbarButton->url;
            $navBtnText = $navbarButton->title;
        }
    }
@endphp
@php
    $userLanguages = !empty($generalSettings['site_language'])
        ? [$generalSettings['site_language'] => getLanguages($generalSettings['site_language'])]
        : [];

    if (!empty($generalSettings['user_languages']) and is_array($generalSettings['user_languages'])) {
        $userLanguages = getLanguages($generalSettings['user_languages']);
    }

    $localLanguage = [];

    foreach ($userLanguages as $key => $userLanguage) {
        $localLanguage[localeToCountryCode($key)] = $userLanguage;
    }

@endphp
<div id="navbarVacuum"></div>
<nav id="navbar" class=" js-font-resize navbar navbar-expand-lg bg-secondary-acadima d-lg-flex d-none ">
    <div class=" js-font-resize {{ (!empty($isPanel) and $isPanel) ? 'container-fluid' : 'container' }} flex-nowrap">
        <div class=" js-font-resize d-flex align-items-center justify-content-between">

            <a class=" js-font-resize navbar-brand navbar-order  align-items-center justify-content-start mr-0 {{ (empty($navBtnUrl) and empty($navBtnText)) ? 'mr-auto' : '' }}"
                href="">
                @if (!empty($generalSettings['logo']))
                    <img src="{{ asset('store/Acadima/logo2.webp') }}" class=" js-font-resize logo-img-cover" width="70%" alt="site logo">
                @endif
            </a>


            <span class=" js-font-resize d-none navbar-order"></span>

            {{-- <div class=" js-font-resize mx-lg-30 d-none d-lg-flex flex-grow-1 navbar-toggle-content " id="navbarContent">
                <div class=" js-font-resize navbar-toggle-header text-right d-lg-none">
                    <button class=" js-font-resize btn-transparent" id="navbarClose">
                        <i data-feather="x" width="32" height="32"></i>
                    </button>
                </div>

                 <ul class=" js-font-resize navbar-nav mr-auto d-flex align-items-center">
                    @if (!empty($categories) and count($categories))
                        <li class=" js-font-resize mr-lg-25">
                            <div class=" js-font-resize menu-category">
                                <ul>
                                    <li class=" js-font-resize cursor-pointer user-select-none d-flex xs-categories-toggle">
                                        <i data-feather="grid" width="20" height="20" class=" js-font-resize mr-10 d-none d-lg-block"></i>
                                        {{ trans('categories.categories') }}

                                        <ul class=" js-font-resize cat-dropdown-menu">
                                            @foreach ($categories as $category)
                                                <li>
                                                    <a href="{{ $category->getUrl() }}">
                                                        <div class=" js-font-resize d-flex align-items-center">
                                                            <img src="{{ $category->icon }}" class=" js-font-resize cat-dropdown-menu-icon mr-10" alt="{{ $category->title }} icon">
                                                            {{ $category->title }}
                                                        </div>

                                                        @if (!empty($category->subCategories) and count($category->subCategories))
                                                            <i data-feather="chevron-right" width="20" height="20" class=" js-font-resize d-none d-lg-inline-block mr-10"></i>
                                                            <i data-feather="chevron-down" width="20" height="20" class=" js-font-resize d-inline-block d-lg-none"></i>
                                                        @endif
                                                    </a>

                                                    @if (!empty($category->subCategories) and count($category->subCategories))
                                                        <ul class=" js-font-resize sub-menu" data-simplebar @if (!empty($isRtl) and $isRtl) data-simplebar-direction="rtl" @endif>
                                                            @foreach ($category->subCategories as $subCategory)
                                                                <li>
                                                                    <a href="{{ $subCategory->getUrl() }}">
                                                                        @if (!empty($subCategory->icon))
                                                                            <img src="{{ $subCategory->icon }}" class=" js-font-resize cat-dropdown-menu-icon mr-10" alt="{{ $subCategory->title }} icon">
                                                                        @endif

                                                                        {{ $subCategory->title }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    @if (!empty($navbarPages) and count($navbarPages))
                        @foreach ($navbarPages as $navbarPage)
                            <li class=" js-font-resize nav-item">
                                <a class=" js-font-resize nav-link" href="{{ $navbarPage['link'] }}">{{ $navbarPage['title'] }}</a>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div> --}}




        </div>

        {{-- "xs-w-100" --}}
        <div class=" js-font-resize d-flex align-items-center justify-content-between w-50 ">
            <div class=" js-font-resize d-flex">

                {{-- @include(getTemplate().'.includes.shopping-cart-dropdwon') --}}
                {{--  @include(getTemplate().'.includes.notification-dropdown') --}}
            </div>

            {{-- User Menu --}}
            <div class=" js-font-resize d-flex flex-nowrap align-items-center justify-content-between ">
                {{-- currency --}}
                <div class=" js-font-resize d-flex align-items-center justify-content-between justify-content-md-center">

                    {{-- Currency --}}
                    @include('web.default.includes.top_nav.currency')

                    


@if (!empty($localLanguage) and count($localLanguage) > 1 and (session::get('impersonated') == null) )
                    <form action="/locale" method="post" class=" js-font-resize mr-15 mx-md-20">
                            {{ csrf_field() }}

                            <input type="hidden" name="locale">

                            @if (!empty($previousUrl))
                                <input type="hidden" name="previous_url" value="{{ $previousUrl }}">
                            @endif

                            <div class=" js-font-resize language-select ">
                                <div id="localItems"
                                    data-selected-country="{{ localeToCountryCode(mb_strtoupper(app()->getLocale())) }}"
                                    data-countries='{{ json_encode($localLanguage) }}'></div>
                            </div>
                        </form>
                    @else
                        <div class=" js-font-resize mr-15 mx-md-20"></div>
                    @endif


                    {{-- <form action="/search" method="get" class=" js-font-resize form-inline my-2 my-lg-0 navbar-search position-relative">
                    <input class=" js-font-resize form-control mr-5 rounded" type="text" name="search" placeholder="{{ trans('navbar.search_anything') }}" aria-label="Search">

                        <button type="submit" class=" js-font-resize btn-transparent d-flex align-items-center justify-content-center search-icon">
                            <i data-feather="search" width="20" height="20" class=" js-font-resize mr-10"></i>
                        </button>
                    </form> --}}
                </div>

                {{-- notification --}}
                <div class=" js-font-resize nav-icons-or-start-live mr-25 ">

                    @if (!empty($navBtnUrl))
                        <a href="{{ $navBtnUrl }}"
                            class=" js-font-resize d-none d-lg-flex btn btn-sm btn-primary nav-start-a-live-btn">
                            {{ $navBtnText }}
                        </a>

                        <a href="{{ $navBtnUrl }}"
                            class=" js-font-resize d-flex d-lg-none text-primary nav-start-a-live-btn font-14">
                            {{ $navBtnText }}
                        </a>
                    @endif

                    <div class=" js-font-resize d-none nav-notify-cart-dropdown top-navbar     ">
                        {{-- @include(getTemplate().'.includes.shopping-cart-dropdwon') --}}

                        <div class=" js-font-resize border-left mx-15"></div>

                        @include(getTemplate() . '.includes.notification-dropdown')
                    </div>

                </div>
                @include('web.default.includes.top_nav.user_menu')
            </div>
        </div>
    </div>
</nav>

@push('scripts_bottom')
    <script src="/assets/default/js/parts/navbar.min.js"></script>
    <link href="/assets/default/vendors/flagstrap/css/flags.css" rel="stylesheet">
    <script src="/assets/default/vendors/flagstrap/js/jquery.flagstrap.min.js"></script>
    <script src="/assets/default/js/parts/top_nav_flags.min.js"></script>
@endpush
