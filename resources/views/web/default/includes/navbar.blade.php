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
<nav id="navbar" class="navbar navbar-expand-lg bg-secondary-acadima d-lg-flex d-none">
    <div class="{{ (!empty($isPanel) and $isPanel) ? 'container-fluid' : 'container' }} flex-nowrap">
        <div class="d-flex align-items-center justify-content-between">

            <a class="navbar-brand navbar-order  align-items-center justify-content-start mr-0 {{ (empty($navBtnUrl) and empty($navBtnText)) ? 'mr-auto' : '' }}"
                href="">
                @if (!empty($generalSettings['logo']))
                    <img src="{{ asset('store/Acadima/acadima-logo.webp') }}" class="logo-img-cover" width="70%" alt="site logo">
                @endif
            </a>


            <span class="d-none navbar-order"></span>

            {{-- <div class="mx-lg-30 d-none d-lg-flex flex-grow-1 navbar-toggle-content " id="navbarContent">
                <div class="navbar-toggle-header text-right d-lg-none">
                    <button class="btn-transparent" id="navbarClose">
                        <i data-feather="x" width="32" height="32"></i>
                    </button>
                </div>

                 <ul class="navbar-nav mr-auto d-flex align-items-center">
                    @if (!empty($categories) and count($categories))
                        <li class="mr-lg-25">
                            <div class="menu-category">
                                <ul>
                                    <li class="cursor-pointer user-select-none d-flex xs-categories-toggle">
                                        <i data-feather="grid" width="20" height="20" class="mr-10 d-none d-lg-block"></i>
                                        {{ trans('categories.categories') }}

                                        <ul class="cat-dropdown-menu">
                                            @foreach ($categories as $category)
                                                <li>
                                                    <a href="{{ $category->getUrl() }}">
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ $category->icon }}" class="cat-dropdown-menu-icon mr-10" alt="{{ $category->title }} icon">
                                                            {{ $category->title }}
                                                        </div>

                                                        @if (!empty($category->subCategories) and count($category->subCategories))
                                                            <i data-feather="chevron-right" width="20" height="20" class="d-none d-lg-inline-block mr-10"></i>
                                                            <i data-feather="chevron-down" width="20" height="20" class="d-inline-block d-lg-none"></i>
                                                        @endif
                                                    </a>

                                                    @if (!empty($category->subCategories) and count($category->subCategories))
                                                        <ul class="sub-menu" data-simplebar @if (!empty($isRtl) and $isRtl) data-simplebar-direction="rtl" @endif>
                                                            @foreach ($category->subCategories as $subCategory)
                                                                <li>
                                                                    <a href="{{ $subCategory->getUrl() }}">
                                                                        @if (!empty($subCategory->icon))
                                                                            <img src="{{ $subCategory->icon }}" class="cat-dropdown-menu-icon mr-10" alt="{{ $subCategory->title }} icon">
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
                            <li class="nav-item">
                                <a class="nav-link" href="{{ $navbarPage['link'] }}">{{ $navbarPage['title'] }}</a>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div> --}}




        </div>

        {{-- "xs-w-100" --}}
        <div class="d-flex align-items-center justify-content-between w-50 ">
            <div class="d-flex">

                {{-- @include(getTemplate().'.includes.shopping-cart-dropdwon') --}}
                {{--  @include(getTemplate().'.includes.notification-dropdown') --}}
            </div>

            {{-- User Menu --}}
            <div class="d-flex flex-nowrap align-items-center justify-content-between">
                {{-- currency --}}
                <div class="d-flex align-items-center justify-content-between justify-content-md-center">

                    {{-- Currency --}}
                    @include('web.default.includes.top_nav.currency')

                    
                    @if (!empty($localLanguage) and count($localLanguage) > 1 and (session::get('impersonated') == null) )
                        <form action="/locale" method="post" class="mr-15 mx-md-20">
                            {{ csrf_field() }}

                            <input type="hidden" name="locale">

                            @if (!empty($previousUrl))
                                <input type="hidden" name="previous_url" value="{{ $previousUrl }}">
                            @endif

                            <div class="language-select">
                                <div id="localItems"
                                    data-selected-country="{{ localeToCountryCode(mb_strtoupper(app()->getLocale())) }}"
                                    data-countries='{{ json_encode($localLanguage) }}'></div>
                            </div>
                        </form>
                    @else
                        <div class="mr-15 mx-md-20"></div>
                    @endif


                    {{-- <form action="/search" method="get" class="form-inline my-2 my-lg-0 navbar-search position-relative">
                    <input class="form-control mr-5 rounded" type="text" name="search" placeholder="{{ trans('navbar.search_anything') }}" aria-label="Search">

                        <button type="submit" class="btn-transparent d-flex align-items-center justify-content-center search-icon">
                            <i data-feather="search" width="20" height="20" class="mr-10"></i>
                        </button>
                    </form> --}}
                </div>

                {{-- notification --}}
                <div class="nav-icons-or-start-live mr-25">

                    @if (!empty($navBtnUrl))
                        <a href="{{ $navBtnUrl }}"
                            class="d-none d-lg-flex btn btn-sm btn-primary nav-start-a-live-btn">
                            {{ $navBtnText }}
                        </a>

                        <a href="{{ $navBtnUrl }}"
                            class="d-flex d-lg-none text-primary nav-start-a-live-btn font-14">
                            {{ $navBtnText }}
                        </a>
                    @endif

                    <div class="d-none nav-notify-cart-dropdown top-navbar ">
                        {{-- @include(getTemplate().'.includes.shopping-cart-dropdwon') --}}

                        <div class="border-left mx-15"></div>

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
