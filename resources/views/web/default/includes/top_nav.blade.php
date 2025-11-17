{{-- @php
    $userLanguages = !empty($generalSettings['site_language']) ? [$generalSettings['site_language'] => getLanguages($generalSettings['site_language'])] : [];

    if (!empty($generalSettings['user_languages']) and is_array($generalSettings['user_languages'])) {
        $userLanguages = getLanguages($generalSettings['user_languages']);
    }

    $localLanguage = [];

    foreach($userLanguages as $key => $userLanguage) {
        $localLanguage[localeToCountryCode($key)] = $userLanguage;
    }

@endphp

<div class=" js-font-resize top-navbar d-flex border-bottom">
    <div class=" js-font-resize container d-flex justify-content-between flex-column flex-lg-row">
        <div class=" js-font-resize top-contact-box border-bottom d-flex flex-column flex-md-row align-items-center justify-content-center">

            @if(getOthersPersonalizationSettings('platform_phone_and_email_position') == 'header')
                <div class=" js-font-resize d-flex align-items-center justify-content-center mr-15 mr-md-30">
                    @if(!empty($generalSettings['site_phone']))
                        <div class=" js-font-resize d-flex align-items-center py-10 py-lg-0 text-dark-blue font-14">
                            <i data-feather="phone" width="20" height="20" class=" js-font-resize mr-10"></i>
                            {{ $generalSettings['site_phone'] }}
                        </div>
                    @endif

                    @if(!empty($generalSettings['site_email']))
                        <div class=" js-font-resize border-left mx-5 mx-lg-15 h-100"></div>

                        <div class=" js-font-resize d-flex align-items-center py-10 py-lg-0 text-dark-blue font-14">
                            <i data-feather="mail" width="20" height="20" class=" js-font-resize mr-10"></i>
                            {{ $generalSettings['site_email'] }}
                        </div>
                    @endif
                </div>
            @endif

            <div class=" js-font-resize d-flex align-items-center justify-content-between justify-content-md-center">

                @include('web.default.includes.top_nav.currency')


                @if(!empty($localLanguage) and count($localLanguage) > 1)
                    <form action="/locale" method="post" class=" js-font-resize mr-15 mx-md-20">
                        {{ csrf_field() }}

                        <input type="hidden" name="locale">

                        @if(!empty($previousUrl))
                            <input type="hidden" name="previous_url" value="{{ $previousUrl }}">
                        @endif

                        <div class=" js-font-resize language-select">
                            <div id="localItems"
                                 data-selected-country="{{ localeToCountryCode(mb_strtoupper(app()->getLocale())) }}"
                                 data-countries='{{ json_encode($localLanguage) }}'
                            ></div>
                        </div>
                    </form>
                @else
                    <div class=" js-font-resize mr-15 mx-md-20"></div>
                @endif


                 <form action="/search" method="get" class=" js-font-resize form-inline my-2 my-lg-0 navbar-search position-relative">
                    <input class=" js-font-resize form-control mr-5 rounded" type="text" name="search" placeholder="{{ trans('navbar.search_anything') }}" aria-label="Search">

                    <button type="submit" class=" js-font-resize btn-transparent d-flex align-items-center justify-content-center search-icon">
                        <i data-feather="search" width="20" height="20" class=" js-font-resize mr-10"></i>
                    </button>
                </form> 
            </div>
        </div>

        <div class=" js-font-resize xs-w-100 d-flex align-items-center justify-content-between ">
            <div class=" js-font-resize d-flex">

         @include(getTemplate().'.includes.shopping-cart-dropdwon') 

                <div class=" js-font-resize border-left mx-5 mx-lg-15"></div>

                @include(getTemplate().'.includes.notification-dropdown')
            </div>

            @include('web.default.includes.top_nav.user_menu')
        </div>
    </div>
</div>


@push('scripts_bottom')
    <link href="/assets/default/vendors/flagstrap/css/flags.css" rel="stylesheet">
    <script src="/assets/default/vendors/flagstrap/js/jquery.flagstrap.min.js"></script>
    <script src="/assets/default/js/parts/top_nav_flags.min.js"></script>
@endpush
--}}
