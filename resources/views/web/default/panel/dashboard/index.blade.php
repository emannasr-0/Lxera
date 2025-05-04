@extends(getTemplate() . '.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/chartjs/chart.min.css" />
    <link rel="stylesheet" href="/assets/default/vendors/apexcharts/apexcharts.css" />
@endpush
<style>
    .dashboard-banner-container {
        margin-top: 0px !important;
    }

    .module-box {
        flex-wrap: wrap;
        align-content: center;
        justify-content: center;
        align-items: center;
        gap: 20px;
    }

    .container_cal {
        max-width: 100%;
        margin: 0 auto;
        padding: 25px;
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        font-family: sans-serif !important;
    }
    @media(max-width: 600px) {
        .container_cal {
            margin: 0;
        }
    }

    #content-table .cls-3 {
        fill: var(--secondary);
    }

    .module-box {
        min-height: 250px
    }

    .micon {
        width: 50px !important;
        margin-top: -35px !important;
        margin-bottom: 0 !important;
    }
</style>
@section('content')
    <section class="dashboard">

        <div class="mt-10 d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h1 class="section-title text-pink p-20">{{ trans('panel.dashboard') }}</h1>

            @if (!$authUser->isUser())
                <div
                    class="d-flex align-items-center flex-row-reverse flex-md-row justify-content-start justify-content-md-center mt-20 mt-md-0">
                    <label class="mb-0 mr-10 cursor-pointer text-gray font-14 font-weight-500"
                        for="iNotAvailable">{{ trans('panel.i_not_available') }}</label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="disabled" @if ($authUser->offline) checked @endif
                            class="custom-control-input" id="iNotAvailable">
                        <label class="custom-control-label" for="iNotAvailable"></label>
                    </div>
                </div>
            @endif
        </div>

        <div class="row p-20 pt-0 pt-lg-20  justify-content-center justify-content-lg-start">
            <div class="col-12  mt-0 @if ($authUser->isUser()) col-lg-5 col-custom3 @endif mt-lg-35 px-lg-15  p-0">
                {{-- @if (!$authUser->financial_approval and !$authUser->isUser())
                    <div
                        class="p-15 mt-20 p-lg-20 not-verified-alert font-weight-500 text-light rounded-sm panel-shadow">
                        {{ trans('panel.not_verified_alert') }}
                        <a href="/panel/setting/step/7"
                            class="text-decoration-underline">{{ trans('panel.this_link') }}</a>.
                    </div>
                @endif --}}

                <div class="bg-secondary-acadima dashboard-banner-container position-relative p-40 rounded-sm shadow border">
                    <h2 class="font-30 text-primary line-height-1">
                        <span class="d-block">{{ trans('panel.hi') }} {{ $authUser->full_name }}</span>
                    </h2>
                    @if (!$authUser->isUser())
                        <span
                            class="font-16 text-secondary font-weight-bold">{{ trans('panel.have_event', ['count' => !empty($unReadNotifications) ? count($unReadNotifications) : 0]) }}
                        </span>

                        <ul class="mt-15 unread-notification-lists text-black">
                            @if (!empty($unReadNotifications) and !$unReadNotifications->isEmpty())
                                @foreach ($unReadNotifications->take(5) as $unReadNotification)
                                    <li class="font-14 mt-1 text-dark">- {{ $unReadNotification->title }}</li>
                                @endforeach

                                @if (count($unReadNotifications) > 5)
                                    <li>&nbsp;&nbsp;...</li>
                                @endif
                            @endif
                        </ul>

                        <a href="/panel/notifications"
                            class="mt-15 font-weight-500 text-pink d-inline-block">{{ trans('panel.view_all_events') }}
                        </a>

                        <div class="dashboard-banner">
                            <img src="{{ getPageBackgroundSettings('dashboard') }}" alt="" class="img-cover2">
                        </div>
                    @endif

                    @if ($authUser->isUser())
                        <ul class="mt-15 unread-notification-lists">
                            <h4 class="text-dark">{{ trans('panel.academic_info') }}</h4>
                            <li class="mt-1 text-gray font-16 font-weight-bold text-left">
                                {{ trans('panel.personal_card_stucode') }} :
                                {{ $authUser->user_code }}</li>
                            <li class="mt-1 text-gray font-16 font-weight-bold text-left">
                                {{ trans('panel.personal_card_email') }} :
                                {{ $authUser->user_code }}@anasacademy.uk</li>
                            <li class="mt-1 text-gray font-16 font-weight-bold text-left">
                                {{ trans('panel.personal_card_password') }}:
                                SD$$2025</li>
                            <li class="mt-1 text-gray font-16 font-weight-bold text-left">
                                {{ trans('panel.study_program') }} :

                                @if ($bundleSales->isNotEmpty())
                                    @foreach ($bundleSales as $bundleSale)
                                        {{ !empty($bundleSale->bundle) ? $bundleSale->bundle->title : '' }}
                                        {{ !empty($bundleSale->webinar) ? ' ' . $bundleSale->webinar->title : '' }}
                                        @if (!$loop->last)
                                            و
                                        @endif
                                    @endforeach
                                @else
                                    {{ trans('panel.not_registered_yet') }}
                                @endif
                            </li>
                        </ul>
                    @endif
                </div>
            </div>

            @if ($authUser->isUser())
                <div class="col-12 col-lg-7 row col-custom3 g-3 px-10 justify-content-center">


                    {{-- Microsoft Team --}}
                    <div class="col-12 col-lg-6 mt-35 px-0 px-lg-15">
                        <div
                            class="module-box rounded-sm panel-shadow p-40 p-md-15 d-flex align-items-center mt-0 bg-secondary-acadima height-94 shadow border">

                            <div class="d-flex flex-column pt-35" style="align-items: center;">
                                <span class="micon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="48px"
                                        height="48px" baseProfile="basic">
                                        <radialGradient id="yRNsYj0k48m_5059Aqtv_a" cx="-1207.054" cy="544.406" r=".939"
                                            gradientTransform="matrix(-11.7002 31.247 54.5012 20.4075 -43776.117 26617.47)"
                                            gradientUnits="userSpaceOnUse">
                                            <stop offset=".064" stop-color="#bf8af6" />
                                            <stop offset=".533" stop-color="#3079d6" />
                                            <stop offset="1" stop-color="#11408c" />
                                        </radialGradient>
                                        <path fill="url(#yRNsYj0k48m_5059Aqtv_a)"
                                            d="M20.084,3.026L19.86,3.162c-0.357,0.216-0.694,0.458-1.008,0.722l0.648-0.456H25L26,11l-5,5	l-5,3.475v4.007c0,2.799,1.463,5.394,3.857,6.844l5.264,3.186L14,40h-2.145l-3.998-2.42C5.463,36.131,4,33.535,4,30.736V17.261	c0-2.8,1.464-5.396,3.86-6.845l12-7.259C19.934,3.112,20.009,3.068,20.084,3.026z" />
                                        <radialGradient id="yRNsYj0k48m_5059Aqtv_b" cx="-1152.461" cy="523.628" r="1"
                                            gradientTransform="matrix(30.7198 -4.5183 -2.9847 -20.2925 36976.637 5454.876)"
                                            gradientUnits="userSpaceOnUse">
                                            <stop offset=".211" stop-color="#bf8af6" />
                                            <stop offset="1" stop-color="#591c96" />
                                        </radialGradient>
                                        <path fill="url(#yRNsYj0k48m_5059Aqtv_b)"
                                            d="M32,19v4.48c0,2.799-1.463,5.394-3.857,6.844l-12,7.264c-2.455,1.486-5.509,1.54-8.007,0.161	l11.722,7.095c2.547,1.542,5.739,1.542,8.285,0l12-7.264C42.537,36.131,44,33.535,44,30.736V27.5L43,26L32,19z" />
                                        <radialGradient id="yRNsYj0k48m_5059Aqtv_c" cx="-1236.079" cy="516.112" r="1.19"
                                            gradientTransform="matrix(-24.1583 -6.1256 -10.3118 40.6682 -24498.48 -28534.523)"
                                            gradientUnits="userSpaceOnUse">
                                            <stop offset=".059" stop-color="#50e6ff" />
                                            <stop offset=".68" stop-color="#3079d6" />
                                            <stop offset="1" stop-color="#11408c" />
                                        </radialGradient>
                                        <path fill="url(#yRNsYj0k48m_5059Aqtv_c)"
                                            d="M40.14,10.415l-12-7.259c-2.467-1.492-5.538-1.538-8.043-0.139L19.86,3.162	C17.464,4.611,16,7.208,16,10.007v9.484l3.86-2.335c2.546-1.54,5.735-1.54,8.281,0l12,7.259c2.321,1.404,3.767,3.884,3.855,6.583	C43.999,30.911,44,30.824,44,30.736V17.26C44,14.461,42.536,11.864,40.14,10.415z" />
                                    </svg>
                                </span>
                                <span class="font-16 text-dark font-weight-500 text-center pb-10">
                                    {{ trans('panel.microsoft') }}
                                </span>
                                <a target="_blank" rel="noopener noreferrer" class="btn btn-primary mt-10" style=""
                                    href="https://go.microsoft.com/fwlink/?linkid=2187217&amp;clcid=0x409&amp;culture=en-us&amp;country=us/">
                                    {{ trans('panel.download_here') }}
                                </a>

                                <a target="_blank" rel="noopener noreferrer" class="btn btn-primary mt-10" style=""
                                    href="https://portal.office.com/">
                                    {{ trans('panel.login_here') }}

                                </a>
                            </div>

                        </div>
                    </div>

                    @if ($authUser->isUser())
                        {{-- content table files --}}
                        <div class="col-12 col-lg-6 mt-35">
                        <div class="row">
                            <div class="col-md-12 bg-secondary-acadima rounded-sm p-20 shadow border">
                                <div class="m-b-30">
                                    <div class="card-header">
                                        <div class="row align-items-center">
                                            <div class="col-8">
                                                <h5 class="card-title mb-0 text-pink">{{trans('panel.lecture_schedule')}}</h5>
                                            </div>
                                            <div class="col-4">
                                                <ul class="list-inline-group text-right mb-1 pl-0">
                                                    <li class="list-inline-item mr-0 font-12"><i
                                                            class="feather icon-more-vertical- font-20 text-light"></i>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="product-file-type">
                                            <ul class="list-unstyled">



                                                @foreach ($bundleSales as $bundleSale)
                                                    @if (!empty($bundleSale->bundle) && !empty($bundleSale->bundle->content_table))
                                                        <li class="media mb-3">
                                                            <span
                                                                class="ml-3 align-self-center img-icon danger-rgba text-danger">.pdf</span>
                                                            <div class="media-body">
                                                                <a href="{{ $bundleSale->bundle->content_table }}"
                                                                    target="_blank">
                                                                    <h5 class="font-16 mb-1 text-light">
                                                                        {{ $bundleSale->bundle->title }}
                                                                        <i
                                                                            class="feather icon-download-cloud float-right"></i>
                                                                    </h5>
                                                                </a>
                                                            </div>
                                                        </li>
                                                    @endif
                                                @endforeach

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        </div>
                    @endif


                    {{-- account charge --}}
                    {{-- <div class="col-12 col-lg-4 mt-35">
                        <div class="bg-white account-balance rounded-sm p-25">
                            <div class="text-center">
                                <svg width="63" viewBox="0 0 63 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M62.4835 35.1357C62.4188 34.5078 62.2896 33.8734 62.0208 33.2815C61.6058 32.3658 61.2043 31.4436 60.7893 30.5247C60.2416 29.3049 59.7143 28.0786 59.1258 26.8785C58.5985 25.8026 58.1562 24.6973 57.6323 23.6181C56.9724 22.261 56.4961 20.8188 55.8225 19.4682C55.2544 18.3237 54.8121 17.1333 54.2236 15.9953C53.6215 14.8377 53.1452 13.6081 52.5975 12.4145C52.1757 11.4923 51.8015 10.5472 51.3286 9.65116C50.8965 8.83689 50.4543 8.01607 50.1039 7.17564C49.7229 6.26326 49.182 5.45879 48.5662 4.73609C47.4708 3.45745 46.2019 2.36848 44.5962 1.6098C43.2933 0.991732 41.9325 0.59604 40.5513 0.344236C39.0953 0.0793519 37.5849 0.0760817 36.0982 0.027029C34.5606 -0.0220237 33.0195 0.0106781 31.4818 0.0106781C28.6446 0.0106781 25.804 -0.0252938 22.9702 0.154566C21.9837 0.216699 20.9971 0.170917 20.0072 0.170917C19.2622 0.170917 18.5103 0.180727 17.7619 0.180727C16.8842 0.180727 16.0031 0.197078 15.1254 0.206889C14.1627 0.21997 13.2 0.112054 12.2372 0.295184C11.1826 0.501205 10.4648 1.22718 10.4206 2.25402C10.39 2.95711 10.5397 3.62095 10.8118 4.25537L10.8492 4.23902L16.8366 17.7187V17.7285C16.9148 17.8757 16.9931 18.0195 17.0713 18.1667C17.5136 18.9777 17.8402 19.8443 18.2484 20.6717C18.4015 20.9791 18.6872 21.3453 18.626 21.6135L21.5653 27.8693L21.5108 27.8922C21.5857 28.0426 21.6605 28.1963 21.7388 28.3468C22.5212 29.8641 23.1744 31.4273 24.2834 32.7844C25.5829 34.3704 27.1002 35.6491 29.0154 36.4764C29.9918 36.9016 31.0124 37.3038 32.0738 37.3725C33.9142 37.4902 35.7648 37.4477 37.6121 37.4608C38.8232 37.4706 40.0376 37.4542 41.2487 37.4575C42.4224 37.4575 43.5926 37.4804 44.7697 37.4771C45.6338 37.4771 46.4945 37.451 47.362 37.4477C47.4878 37.4477 47.6001 37.464 47.6885 37.5H50.5427C50.5904 37.4837 50.655 37.4738 50.7332 37.4738C52.3492 37.4738 53.9617 37.4509 55.5742 37.4444C57.1799 37.4346 58.7856 37.4575 60.3913 37.415C61.6194 37.3823 62.6093 36.3064 62.4903 35.1357H62.4835Z"
                                        fill="#F70387" fill-opacity="0.5" />
                                    <g clip-path="url(#clip0_10_1564)">
                                        <path
                                            d="M23.9077 8.34321C27.3259 8.34321 30.7441 8.31652 34.1623 8.35163C37.2891 8.38394 40.2215 9.08763 42.7449 10.981C45.5409 13.0794 46.9984 15.9082 47.4857 19.2567C47.6554 20.4253 47.5858 21.5996 47.593 22.771C47.5945 23.0828 47.683 23.2893 47.9687 23.4592C49.1622 24.1713 49.8424 25.2346 49.9134 26.5689C50.0091 28.3851 50.0628 30.211 49.8757 32.0257C49.751 33.2238 49.1129 34.1705 48.0615 34.818C47.7091 35.0343 47.58 35.2745 47.5945 35.6706C47.667 37.6735 47.593 39.668 46.9056 41.5866C45.2059 46.3271 41.708 48.9831 36.6553 49.8006C35.6561 49.9621 34.6438 50 33.6301 50C26.9561 49.9972 20.2821 50.0057 13.6081 49.9944C10.9802 49.9902 8.46261 49.5056 6.17702 48.1881C2.71095 46.188 0.834332 43.1626 0.209278 39.3576C0.0308981 38.2845 -0.0010072 37.1988 0.000443043 36.1116C0.00769425 31.2069 -0.00970865 26.3021 0.0091445 21.3973C0.0192962 18.8887 0.532682 16.4869 1.87561 14.3056C3.96831 10.9094 7.14144 9.11572 11.1151 8.53704C12.2796 8.36708 13.4529 8.33618 14.629 8.3404C17.7224 8.35023 20.8143 8.34321 23.9077 8.34321ZM23.8439 46.4872V46.49C27.0997 46.49 30.3569 46.49 33.6127 46.49C34.39 46.49 35.1659 46.4928 35.9389 46.3847C38.6653 46.0054 40.9263 44.8762 42.4664 42.5923C43.8572 40.5318 44.0922 38.2143 44.0472 35.8363C44.0414 35.5343 43.8384 35.5133 43.6034 35.5133C42.7536 35.5133 41.9023 35.5343 41.0524 35.5077C37.6023 35.4009 34.6235 32.7786 34.4727 29.5101C34.3248 26.3428 36.6481 23.4339 39.8618 22.9269C41.0684 22.7359 42.2794 22.8539 43.4889 22.8356C43.9036 22.8286 44.0631 22.726 44.0602 22.299C44.0574 21.5265 44.0312 20.7568 43.9442 19.9913C43.6426 17.3479 42.5621 15.109 40.2635 13.5331C38.5986 12.3912 36.7032 11.8771 34.6989 11.8673C27.8799 11.8308 21.0594 11.8518 14.2389 11.8504C13.4253 11.8504 12.6146 11.8504 11.804 11.9558C8.90493 12.3322 6.54103 13.5443 4.99653 16.029C4.05822 17.5375 3.64345 19.2034 3.63475 20.9436C3.6101 26.0942 3.6246 31.2462 3.62315 36.3967C3.62315 37.1496 3.6217 37.901 3.73047 38.6511C4.13363 41.4195 5.40259 43.6626 7.91731 45.1458C9.45457 46.0518 11.1557 46.4605 12.9337 46.4774C16.5695 46.5125 20.2052 46.4872 23.8424 46.4872H23.8439ZM43.2771 31.985C43.2771 31.985 43.2771 31.9864 43.2771 31.9878C44.0719 31.9878 44.8666 31.985 45.6613 31.9878C46.1022 31.9892 46.3371 31.7954 46.3371 31.3571C46.3371 29.9048 46.3357 28.4539 46.3371 27.0016C46.3371 26.5718 46.1297 26.3512 45.6802 26.3526C44.0008 26.3569 42.32 26.3231 40.642 26.3737C39.3107 26.4144 37.9431 27.7502 38.0765 29.3135C38.2085 30.8557 39.5021 31.9471 41.109 31.9822C41.8312 31.9976 42.5534 31.985 43.2757 31.985H43.2771Z"
                                            fill="#5E0A83" />
                                        <path
                                            d="M20.3106 24.1601C17.9975 24.1601 15.6844 24.1657 13.3712 24.1559C12.4866 24.1516 11.7847 23.5533 11.628 22.7148C11.4816 21.938 11.9732 21.1262 12.7607 20.8256C12.9855 20.7399 13.2161 20.7245 13.4524 20.7245C18.0425 20.7245 22.6325 20.7217 27.2225 20.7259C28.2928 20.7259 29.0933 21.4633 29.1035 22.4296C29.1151 23.4086 28.2971 24.1573 27.1949 24.1601C24.9007 24.1657 22.6049 24.1615 20.3106 24.1601Z"
                                            fill="#5E0A83" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_10_1564">
                                            <rect width="50" height="41.6667" fill="white" transform="translate(0 8.33334)" />
                                        </clipPath>
                                    </defs>
                                </svg>

                                <h3 class="font-16 font-weight-500 text-gray mt-25">{{ trans('panel.account_balance') }}</h3>
                                <span
                                    class="mt-5 d-block font-30 text-secondary">{{ handlePrice($authUser->getAccountingBalance()) }}</span>
                            </div>

                            @php
                                $getFinancialSettings = getFinancialSettings();
                                $drawable = $authUser->getPayout();
                                $can_drawable =
                                    $drawable >
                                    ((!empty($getFinancialSettings) and !empty($getFinancialSettings['minimum_payout']))
                                        ? (int) $getFinancialSettings['minimum_payout']
                                        : 0);
                            @endphp

                            <div
                                class="mt-20 pt-10 border-top border-gray300 d-flex align-items-center @if ($can_drawable) justify-content-between @else justify-content-center @endif">
                                @if ($can_drawable)
                                    <span class="font-16 font-weight-500 text-gray">{{ trans('panel.with_drawable') }}:</span>
                                    <span class="font-16 font-weight-bold text-secondary">{{ handlePrice($drawable) }}</span>
                                @else
                                    <a href="/panel/financial/account"
                                        class="font-16 font-weight-bold text-light">{{ trans('financial.charge_account') }}</a>
                                @endif
                            </div>
                        </div>
                    </div> --}}
                    {{-- SCT Team --}}
                    <!-- <div class="col-6 col-lg-6 mt-35 ">
                        <div
                            class="module-box rounded-sm panel-shadow py-30 d-flex align-items-center mt-0 bg-secondary-acadima height-94">

                            <div class="d-flex flex-column" style="align-items: center;">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M0.053834 14.9777C0.053834 13.9942 0.0514971 13.0106 0.053834 12.0271C0.0569499 10.9609 0.758801 10.1405 1.80574 9.99455C2.00437 9.96723 2.03397 9.87122 2.04098 9.70496C2.12979 7.42181 2.86825 5.37595 4.33583 3.6228C6.07526 1.5465 8.29533 0.328038 10.996 0.0571822C14.0776 -0.251141 16.7565 0.689439 18.9851 2.82897C20.6147 4.39322 21.5869 6.32355 21.9047 8.57314C21.9592 8.95874 22.0106 9.34434 22.0114 9.73384C22.0114 9.89932 22.0722 9.96879 22.2459 9.99377C23.2819 10.1405 23.9947 10.9578 23.997 11.9983C24.0009 13.9848 24.0009 15.9705 23.997 17.9571C23.9947 19.1045 23.1183 19.9928 21.9709 20.0115C21.4077 20.0209 20.8437 20.0201 20.2805 20.01C20.078 20.0061 19.9425 20.0677 19.8108 20.2301C18.122 22.3072 15.9526 23.5428 13.3041 23.9159C12.2805 24.0603 11.2476 23.9706 10.2186 23.9901C10.055 23.9932 9.98958 23.9104 9.9888 23.759C9.98803 23.2329 9.99036 22.7076 9.98725 22.1815C9.98569 21.952 10.1212 21.9138 10.3113 21.9145C10.9719 21.9177 11.6332 21.9294 12.2938 21.9091C14.4827 21.842 16.3086 20.963 17.8229 19.3972C17.946 19.27 17.9592 19.1287 17.9592 18.9703C17.9577 17.3834 17.9592 15.7957 17.9577 14.2088C17.9569 13.3978 18.1727 12.6547 18.6268 11.9811C18.975 11.4651 19.2967 10.9297 19.6691 10.4317C19.9105 10.1093 19.969 9.77365 19.9362 9.39429C19.6208 5.70144 16.9364 2.82506 13.491 2.19983C9.16855 1.41536 5.16932 4.12939 4.25637 8.43654C4.19016 8.74955 4.15744 9.07036 4.11927 9.38805C4.07254 9.77677 4.14887 10.121 4.39737 10.4512C4.77127 10.9476 5.09376 11.4831 5.43963 12.0006C5.87896 12.6586 6.09396 13.3845 6.09474 14.1745C6.09552 15.7223 6.0963 17.271 6.09474 18.8188C6.09474 19.5963 5.68266 20.0107 4.90837 20.0123C3.99464 20.0147 3.08169 20.0162 2.16796 20.0123C0.9255 20.0084 0.0569499 19.1373 0.054613 17.8985C0.0530551 16.9252 0.054613 15.951 0.054613 14.9777H0.053834ZM2.12278 14.9621C2.12278 15.8379 2.13057 16.7129 2.1181 17.5879C2.11421 17.8462 2.18042 17.9579 2.46007 17.943C2.86669 17.9212 3.27565 17.922 3.68227 17.943C3.95569 17.9571 4.02969 17.8572 4.02658 17.5926C4.01411 16.435 4.02034 15.2774 4.0219 14.1198C4.0219 13.7694 3.92453 13.4493 3.73057 13.159C3.56932 12.9162 3.38237 12.6859 3.25228 12.4276C3.07857 12.0833 2.83008 11.9834 2.45617 12.0022C2.1773 12.0162 2.11499 12.1099 2.1181 12.3651C2.12979 13.2308 2.12278 14.0964 2.12278 14.9621ZM21.9273 14.9995C21.9273 14.1144 21.9281 13.2292 21.9257 12.344C21.9257 12.241 21.9584 12.1216 21.858 12.0443C21.7029 11.9249 21.0673 12.0404 20.9544 12.2067C20.7261 12.5454 20.4979 12.8834 20.2766 13.2269C20.1216 13.4681 20.0321 13.7374 20.0313 14.0246C20.0266 15.2407 20.0313 16.4561 20.025 17.6722C20.0243 17.8782 20.1029 17.9446 20.3008 17.9407C20.737 17.9313 21.1748 17.9274 21.611 17.9423C21.8502 17.9501 21.9343 17.8689 21.9312 17.6261C21.9203 16.7511 21.9265 15.8753 21.9273 15.0003V14.9995Z"
                                        fill="black" class=" fill-black "></path>
                                    <path
                                        d="M12.0289 3.49627C13.6959 3.48456 15.1394 4.06452 16.3849 5.16121C16.663 5.40553 16.6584 5.41177 16.3951 5.67638C16.1894 5.88323 15.969 6.07681 15.7805 6.29849C15.6106 6.49832 15.4891 6.47334 15.3076 6.30942C14.559 5.63189 13.6811 5.22834 12.677 5.10111C11.2523 4.9208 9.98726 5.29 8.87333 6.1939C8.44178 6.54437 8.56876 6.58028 8.15123 6.1697C7.96428 5.98549 7.78823 5.78879 7.59193 5.6155C7.42289 5.46563 7.4447 5.36026 7.60206 5.21663C8.57732 4.32913 9.7115 3.77649 11.0108 3.56027C11.3481 3.50407 11.687 3.50173 12.0274 3.49627H12.0289Z"
                                        fill="black" class=" fill-black "></path>
                                </svg>

                                <span class="font-16 text-light font-weight-500 text-center pb-10">
                                    {{trans('panel.support_communication_team')}}
                                </span>
                                <a target="_blank" rel="noopener noreferrer" class="btn btn-primary mt-10"
                                    style="" href="https://support.anasacademy.uk/">
                                    {{trans('panel.apply_here')}}
                                </a>
                                <a target="_blank" rel="noopener noreferrer" class="btn btn-primary mt-10"
                                    style="" href="https://support.anasacademy.uk/search">
                                    {{trans('panel.follow_up_previous_request_here')}}
                                </a>
                            </div>

                        </div>

                    </div> -->

                </div>
            @endif
        </div>
    </section>

    <section class="dashboard">
        <div class="row p-10 g-30">
            @if ($authUser->isUser())
                {{-- Calender --}}
                <div class="col-12 col-lg-5 col-custom mt-5 rounded-sm">
                    @include('web.default.panel.includes.calender')
                </div>
                <div class="col-12 col-lg-7 col-custom2 row g-3 px-10">
                    {{-- download files --}}
                    <div class="col-12 col-lg-6 col-custom3 mt-35 mt-lg-0 rounded-sm px-20">
                        @include('web.default.panel.includes.downloadfiles')
                    </div>

                     {{-- content table files --}}
                    <!-- <div class="col-12 col-lg-6 mt-5  p-3">
                        <div class="row">
                            <div class="col-md-12 bg-secondary-acadima rounded-sm p-0">
                                <div class="m-b-30">
                                    <div class="card-header">
                                        <div class="row align-items-center">
                                            <div class="col-8">
                                                <h5 class="card-title mb-0 text-light">{{trans('panel.lecture_schedule')}}</h5>
                                            </div>
                                            <div class="col-4">
                                                <ul class="list-inline-group text-right mb-1 pl-0">
                                                    <li class="list-inline-item mr-0 font-12"><i
                                                            class="feather icon-more-vertical- font-20 text-light"></i>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="product-file-type">
                                            <ul class="list-unstyled">



                                                @foreach ($bundleSales as $bundleSale)
                                                    @if (!empty($bundleSale->bundle) && !empty($bundleSale->bundle->content_table))
                                                        <li class="media mb-3">
                                                            <span
                                                                class="ml-3 align-self-center img-icon danger-rgba text-danger">.pdf</span>
                                                            <div class="media-body">
                                                                <a href="{{ $bundleSale->bundle->content_table }}"
                                                                    target="_blank">
                                                                    <h5 class="font-16 mb-1 text-light">
                                                                        {{ $bundleSale->bundle->title }}
                                                                        <i
                                                                            class="feather icon-download-cloud float-right"></i>
                                                                    </h5>
                                                                </a>
                                                            </div>
                                                        </li>
                                                    @endif
                                                @endforeach

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div> -->
                </div>
            @endif
        </div>
    </section>

    @if (!$authUser->isUser())
        <section class="dashboard">
            {{-- <div class="row">
            <div class="col-12 col-lg-3 mt-35">
                <div class="bg-white account-balance rounded-sm panel-shadow py-15 py-md-30 px-10 px-md-20">
                    <div class="text-center">
                        <img src="/assets/default/img/activity/36.svg" class="account-balance-icon" alt="">

                        <h3 class="font-16 font-weight-500 text-gray mt-25">{{ trans('panel.account_balance') }}</h3>
                        <span class="mt-5 d-block font-30 text-secondary">{{ handlePrice($authUser->getAccountingBalance()) }}</span>
                    </div>

                    @php
                        $getFinancialSettings = getFinancialSettings();
                        $drawable = $authUser->getPayout();
                        $can_drawable = ($drawable > ((!empty($getFinancialSettings) and !empty($getFinancialSettings['minimum_payout'])) ? (int)$getFinancialSettings['minimum_payout'] : 0))
                    @endphp

                    <div class="mt-20 pt-30 border-top border-gray300 d-flex align-items-center @if ($can_drawable) justify-content-between @else justify-content-center @endif">
                        @if ($can_drawable)
                            <span class="font-16 font-weight-500 text-gray">{{ trans('panel.with_drawable') }}:</span>
                            <span class="font-16 font-weight-bold text-secondary">{{ handlePrice($drawable) }}</span>
                        @else
                            <a href="/panel/financial/account" class="font-16 font-weight-bold text-light">{{ trans('financial.charge_account') }}</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-3 mt-35">
                <a href="@if ($authUser->isUser()) /panel/webinars/purchases @else /panel/meetings/requests @endif" class="dashboard-stats rounded-sm panel-shadow p-10 p-md-20 d-flex align-items-center">
                    <div class="stat-icon requests">
                        <img src="/assets/default/img/icons/request.svg" alt="">
                    </div>
                    <div class="d-flex flex-column ml-15">
                        <span class="font-30 text-secondary">{{ !empty($pendingAppointments) ? $pendingAppointments : (!empty($webinarsCount) ? $webinarsCount : 0) }}</span>
                        <span class="font-16 text-gray font-weight-500">{{ $authUser->isUser() ? trans('panel.purchased_courses') : trans('panel.pending_appointments') }}</span>
                    </div>
                </a>

                <a href="@if ($authUser->isUser()) /panel/meetings/reservation @else /panel/financial/sales @endif" class="dashboard-stats rounded-sm panel-shadow p-10 p-md-20 d-flex align-items-center mt-15 mt-md-30">
                    <div class="stat-icon monthly-sales">
                        <img src="@if ($authUser->isUser()) /assets/default/img/icons/meeting.svg @else /assets/default/img/icons/monay.svg @endif" alt="">
                    </div>
                    <div class="d-flex flex-column ml-15">
                        <span class="font-30 text-secondary">{{ !empty($monthlySalesCount) ? handlePrice($monthlySalesCount) : (!empty($reserveMeetingsCount) ? $reserveMeetingsCount : 0) }}</span>
                        <span class="font-16 text-gray font-weight-500">{{ $authUser->isUser() ? trans('panel.meetings') : trans('panel.monthly_sales') }}</span>
                    </div>
                </a>
            </div>

            <div class="col-12 col-lg-3 mt-35">
                <a href="/panel/support" class="dashboard-stats rounded-sm panel-shadow p-10 p-md-20 d-flex align-items-center">
                    <div class="stat-icon support-messages">
                        <img src="/assets/default/img/icons/support.svg" alt="">
                    </div>
                    <div class="d-flex flex-column ml-15">
                        <span class="font-30 text-secondary">{{ !empty($supportsCount) ? $supportsCount : 0 }}</span>
                        <span class="font-16 text-gray font-weight-500">{{ trans('panel.support_messages') }}</span>
                    </div>
                </a>

                <a href="@if ($authUser->isUser()) /panel/webinars/my-comments @else /panel/webinars/comments @endif" class="dashboard-stats rounded-sm panel-shadow p-10 p-md-20 d-flex align-items-center mt-15 mt-md-30">
                    <div class="stat-icon comments">
                        <img src="/assets/default/img/icons/comment.svg" alt="">
                    </div>
                    <div class="d-flex flex-column ml-15">
                        <span class="font-30 text-secondary">{{ !empty($commentsCount) ? $commentsCount : 0 }}</span>
                        <span class="font-16 text-gray font-weight-500">{{ trans('panel.comments') }}</span>
                    </div>
                </a>
            </div>

            <div class="col-12 col-lg-3 mt-35">
                <div class="bg-white account-balance rounded-sm panel-shadow py-15 py-md-15 px-10 px-md-20">
                    <div data-percent="{{ !empty($nextBadge) ? $nextBadge['percent'] : 0 }}" data-label="{{ (!empty($nextBadge) and !empty($nextBadge['earned'])) ? $nextBadge['earned']->title : '' }}" id="nextBadgeChart" class="text-center">
                    </div>
                    <div class="mt-10 pt-10 border-top border-gray300 d-flex align-items-center justify-content-between">
                        <span class="font-16 font-weight-500 text-gray">{{ trans('panel.next_badge') }}:</span>
                        <span class="font-16 font-weight-bold text-secondary">{{ (!empty($nextBadge) and !empty($nextBadge['badge'])) ? $nextBadge['badge']->title : trans('public.not_defined') }}</span>
                    </div>
                </div>
            </div>
        </div> --}}

            <div class="row p-20 mt-md-20">
                <div class="col-12 col-lg-6 px-lg-15  p-0">
                    <div class="bg-secondary-acadima noticeboard rounded-sm border shadow py-10 py-md-20 px-15 px-md-30">
                        <h3 class="font-16 text-pink font-weight-bold">{{ trans('panel.noticeboard') }}</h3>

                        @foreach ($authUser->getUnreadNoticeboards() as $getUnreadNoticeboard)
                            <div class="noticeboard-item py-15 text-light">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="js-noticeboard-title font-weight-500 text-secondary">
                                            {!! truncate($getUnreadNoticeboard->title, 150) !!}</h4>
                                        <div class="font-12 text-gray mt-5">
                                            <span class="mr-5">{{ trans('public.created_by') }}
                                                {{ $getUnreadNoticeboard->sender }}</span>
                                            |
                                            <span
                                                class="js-noticeboard-time ml-5">{{ dateTimeFormat($getUnreadNoticeboard->created_at, 'j M Y | H:i') }}</span>
                                        </div>
                                    </div>

                                    <div>
                                        <button type="button" data-id="{{ $getUnreadNoticeboard->id }}"
                                            class="js-noticeboard-info btn btn-sm btn-border-white">{{ trans('panel.more_info') }}</button>
                                        <input type="hidden" class="js-noticeboard-message"
                                            value="{{ $getUnreadNoticeboard->message }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>

                {{--    <div class="col-12 col-lg-6 mt-35">
                <div class="bg-white monthly-sales-card rounded-sm panel-shadow py-10 py-md-20 px-15 px-md-30">
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="font-16 text-light font-weight-bold">{{ ($authUser->isUser()) ? trans('panel.learning_statistics') : trans('panel.monthly_sales') }}</h3>

                        <span class="font-16 font-weight-500 text-gray">{{ dateTimeFormat(time(),'M Y') }}</span>
                    </div>

                    <div class="monthly-sales-chart">
                        <canvas id="myChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        --}}
        </section>
    @endif
    <div class="d-none" id="iNotAvailableModal">
        <div class="offline-modal">
            <h3 class="section-title after-line">{{ trans('panel.offline_title') }}</h3>
            <p class="mt-20 font-16 text-gray">{{ trans('panel.offline_hint') }}</p>

            <div class="form-group mt-15">
                <label>{{ trans('panel.offline_message') }}</label>
                <textarea name="message" rows="4" class="form-control ">{{ $authUser->offline_message }}</textarea>
                <div class="invalid-feedback"></div>
            </div>

            <div class="mt-30 d-flex align-items-center justify-content-end">
                <button type="button"
                    class="js-save-offline-toggle btn btn-primary btn-sm">{{ trans('public.save') }}</button>
                <button type="button" class="btn btn-danger ml-10 close-swl btn-sm">{{ trans('public.close') }}</button>
            </div>
        </div>
    </div>

    <div class="d-none" id="noticeboardMessageModal">
        <div class="text-center">
            <h3 class="modal-title font-20 font-weight-500 text-light"></h3>
            <span class="modal-time d-block font-12 text-gray mt-25"></span>
            <p class="modal-message font-weight-500 text-gray mt-4"></p>
        </div>
    </div>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/apexcharts/apexcharts.min.js"></script>
    <script src="/assets/default/vendors/chartjs/chart.min.js"></script>

    <script>
        var offlineSuccess = '{{ trans('panel.offline_success') }}';
        var $chartDataMonths = @json($monthlyChart['months']);
        var $chartData = @json($monthlyChart['data']);
    </script>

    <script src="/assets/default/js/panel/dashboard.min.js"></script>
@endpush

@if (!empty($giftModal))
    @push('scripts_bottom2')
        <script>
            (function() {
                "use strict";

                handleLimitedAccountModal('{!! $giftModal !!}', 40)
            })(jQuery)
        </script>
    @endpush
@endif
