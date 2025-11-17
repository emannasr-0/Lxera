@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/css/css-stars.css">
    <link rel="stylesheet" href="/assets/default/vendors/video/video-js.min.css">
@endpush


@section('content')
    <section class=" js-font-resize course-cover-container {{ empty($activeSpecialOffer) ? 'not-active-special-offer' : '' }}">
        <img src="{{ $bundle->getImageCover() }}" class=" js-font-resize img-cover course-cover-img" alt="{{ $bundle->title }}"/>

        <div class=" js-font-resize cover-content pt-40">
            <div class=" js-font-resize container position-relative">
                @if(!empty($activeSpecialOffer))
                    @include('web.default.course.special_offer')
                @endif
            </div>
        </div>
    </section>

    <section class=" js-font-resize container course-content-section {{ $bundle->type }}">
        <div class=" js-font-resize row">
            <div class=" js-font-resize col-12 col-lg-8">
                <div class=" js-font-resize course-content-body user-select-none">
                    <div class=" js-font-resize course-body-on-cover text-white">
                        <h1 class=" js-font-resize font-30 course-title">
                            {{ clean($bundle->title, 't') }}
                        </h1>
                        <span class=" js-font-resize d-block font-16 mt-10">{{ trans('public.in') }} <a href="{{ $bundle->category->getUrl() }}" target="_blank" class=" js-font-resize font-weight-500 text-decoration-underline text-white">{{ $bundle->category->title }}</a></span>

                        <div class=" js-font-resize d-flex align-items-center">
                            @include('web.default.includes.webinar.rate',['rate' => $bundle->getRate()])
                            <span class=" js-font-resize ml-10 mt-15 font-14">({{ $bundle->reviews->pluck('creator_id')->count() }} {{ trans('public.ratings') }})</span>
                        </div>

                        <div class=" js-font-resize mt-15">
                            <span class=" js-font-resize font-14">{{ trans('public.created_by') }}</span>
                            <a href="{{ $bundle->teacher->getProfileUrl() }}" target="_blank" class=" js-font-resize text-decoration-underline text-white font-14 font-weight-500">{{ $bundle->teacher->full_name }}</a>
                        </div>
                    </div>

                    <div class=" js-font-resize mt-20 pt-20  mt-md-40 pt-md-40">
                        <ul class=" js-font-resize nav nav-tabs bg-secondary rounded-sm p-15 d-flex align-items-center justify-content-between" id="tabs-tab" role="tablist">
                            <li class=" js-font-resize nav-item">
                                <a class=" js-font-resize position-relative font-14 text-secondary {{ (empty(request()->get('tab','')) or request()->get('tab','') == 'information') ? 'active' : '' }}" id="information-tab"
                                   data-toggle="tab" href="#information" role="tab" aria-controls="information"
                                   aria-selected="true">{{ trans('product.information') }}</a>
                            </li>
                            <li class=" js-font-resize nav-item">
                                <a class=" js-font-resize position-relative font-14 text-secondary {{ (request()->get('tab','') == 'content') ? 'active' : '' }}" id="content-tab" data-toggle="tab"
                                   href="#content" role="tab" aria-controls="content"
                                   aria-selected="false">{{ trans('product.content') }} ({{ $bundle->bundleWebinars->count() }})</a>
                            </li>
                            <li class=" js-font-resize nav-item">
                                <a class=" js-font-resize position-relative font-14 text-secondary {{ (request()->get('tab','') == 'reviews') ? 'active' : '' }}" id="reviews-tab" data-toggle="tab"
                                   href="#reviews" role="tab" aria-controls="reviews"
                                   aria-selected="false">{{ trans('product.reviews') }} ({{ $bundle->reviews->count() > 0 ? $bundle->reviews->pluck('creator_id')->count() : 0 }})</a>
                            </li>
                        </ul>

                        <div class=" js-font-resize tab-content" id="nav-tabContent">
                            <div class=" js-font-resize tab-pane fade {{ (empty(request()->get('tab','')) or request()->get('tab','') == 'information') ? 'show active' : '' }} " id="information" role="tabpanel"
                                 aria-labelledby="information-tab">
                                @include('web.default.bundle.tabs.information')
                            </div>
                            <div class=" js-font-resize tab-pane fade {{ (request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab">
                                @include('web.default.bundle.tabs.content')
                            </div>
                            <div class=" js-font-resize tab-pane fade {{ (request()->get('tab','') == 'reviews') ? 'show active' : '' }}" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                                @include('web.default.bundle.tabs.reviews')
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class=" js-font-resize course-content-sidebar col-12 col-lg-4 mt-25 mt-lg-0">
                <div class=" js-font-resize rounded-lg shadow-sm">
                    <div class=" js-font-resize course-img {{ $bundle->video_demo ? 'has-video' :'' }}">

                        <img src="{{ $bundle->getImage() }}" class=" js-font-resize img-cover" alt="">

                        @if($bundle->video_demo)
                            <div id="webinarDemoVideoBtn"
                                 data-video-path="{{ $bundle->video_demo_source == 'upload' ?  url($bundle->video_demo) : $bundle->video_demo }}"
                                 data-video-source="{{ $bundle->video_demo_source }}"
                                 class=" js-font-resize course-video-icon cursor-pointer d-flex align-items-center justify-content-center">
                                <i data-feather="play" width="25" height="25"></i>
                            </div>
                        @endif
                    </div>

                    <div class=" js-font-resize px-20 pb-30">
                        <form action="/cart/store" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="item_id" value="{{ $bundle->id }}">
                            <input type="hidden" name="item_name" value="bundle_id">

                            @if(!empty($bundle->tickets))
                                @foreach($bundle->tickets as $ticket)

                                    <div class=" js-font-resize form-check mt-20">
                                        <input class=" js-font-resize form-check-input" @if(!$ticket->isValid()) disabled @endif type="radio"
                                               data-discount="{{ $ticket->discount }}"
                                               data-currency
                                               value="{{ ($ticket->isValid()) ? $ticket->id : '' }}"
                                               name="ticket_id"
                                               id="courseOff{{ $ticket->id }}">
                                        <label class=" js-font-resize form-check-label d-flex flex-column cursor-pointer" for="courseOff{{ $ticket->id }}">
                                            <span class=" js-font-resize font-16 font-weight-500 text-dark-blue">{{ $ticket->title }} @if(!empty($ticket->discount))
                                                    ({{ $ticket->discount }}% {{ trans('public.off') }})
                                                @endif</span>
                                            <span class=" js-font-resize font-14 text-gray">{{ $ticket->getSubTitle() }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            @endif

                            @if($bundle->price > 0)
                                <div id="priceBox" class=" js-font-resize d-flex align-items-center justify-content-center mt-20 {{ !empty($activeSpecialOffer) ? ' flex-column ' : '' }}">
                                    <div class=" js-font-resize text-center">
                                        @php
                                            $realPrice = handleCoursePagePrice($bundle->price);
                                        @endphp
                                        <span id="realPrice" data-value="{{ $bundle->price }}"
                                              data-special-offer="{{ !empty($activeSpecialOffer) ? $activeSpecialOffer->percent : ''}}"
                                              class=" js-font-resize d-block @if(!empty($activeSpecialOffer)) font-16 text-gray text-decoration-line-through @else font-30 text-primary @endif">
                                            {{ $realPrice['price'] }}
                                        </span>

                                        @if(!empty($realPrice['tax']) and empty($activeSpecialOffer))
                                            <span class=" js-font-resize d-block font-14 text-gray">+ {{ $realPrice['tax'] }} tax</span>
                                        @endif
                                    </div>

                                    @if(!empty($activeSpecialOffer))
                                        <div class=" js-font-resize text-center">
                                            @php
                                                $priceWithDiscount = handleCoursePagePrice($bundle->getPrice());
                                            @endphp
                                            <span id="priceWithDiscount"
                                                  class=" js-font-resize d-block font-30 text-primary">
                                                {{ $priceWithDiscount['price'] }}
                                            </span>

                                            @if(!empty($priceWithDiscount['tax']))
                                                <span class=" js-font-resize d-block font-14 text-gray">+ {{ $priceWithDiscount['tax'] }} tax</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class=" js-font-resize d-flex align-items-center justify-content-center mt-20">
                                    <span class=" js-font-resize font-36 text-primary">{{ trans('public.free') }}</span>
                                </div>
                            @endif

                            @php
                                $canSale = ($bundle->canSale() and !$hasBought);
                            @endphp

                            <div class=" js-font-resize mt-20 d-flex flex-column">
                                @if($hasBought or !empty($bundle->getInstallmentOrder()))
                                    <button type="button" class=" js-font-resize btn btn-primary" disabled>{{ trans('panel.purchased') }}</button>
                                @elseif($bundle->price > 0)
                                    <button type="{{ $canSale ? 'submit' : 'button' }}" @if(!$canSale) disabled @endif class=" js-font-resize btn btn-primary">
                                        @if(!$canSale)
                                            {{ trans('update.disabled_add_to_cart') }}
                                        @else
                                            {{ trans('public.add_to_cart') }}
                                        @endif
                                    </button>
                                    

                                    @if($canSale and $bundle->subscribe)
                                        <a href="/subscribes/apply/bundle/{{ $bundle->slug }}" class=" js-font-resize btn btn-outline-primary btn-subscribe mt-20 @if(!$canSale) disabled @endif">{{ trans('public.subscribe') }}</a>
                                    @endif

                                    @if($canSale and !empty($bundle->points))
                                        <a href="{{ !(auth()->check()) ? '/login' : '#' }}" class=" js-font-resize {{ (auth()->check()) ? 'js-buy-with-point' : '' }} btn btn-outline-warning mt-20 {{ (!$canSale) ? 'disabled' : '' }}" rel="nofollow">
                                            {!! trans('update.buy_with_n_points',['points' => $bundle->points]) !!}
                                        </a>
                                    @endif

                                    @if($canSale and !empty(getFeaturesSettings('direct_bundles_payment_button_status')))
                                        <button type="button" class=" js-font-resize btn btn-outline-danger mt-20 js-bundle-direct-payment">
                                            {{ trans('update.buy_now') }}
                                        </button>
                                    @endif
                                @else
                                    <a href="{{ $canSale ? '/bundles/'. $bundle->slug .'/free' : '#' }}" class=" js-font-resize btn btn-primary @if(!$canSale) disabled @endif">{{ trans('update.enroll_on_bundle') }}</a>
                                @endif
                            </div>

                        </form>

                        @if(!empty(getOthersPersonalizationSettings('show_guarantee_text')) and getOthersPersonalizationSettings('show_guarantee_text'))
                            <div class=" js-font-resize mt-20 d-flex align-items-center justify-content-center text-gray">
                                <i data-feather="thumbs-up" width="20" height="20"></i>
                                <span class=" js-font-resize ml-5 font-14">{{ getOthersPersonalizationSettings('guarantee_text') }}</span>
                            </div>
                        @endif

                        <div class=" js-font-resize mt-40 p-10 rounded-sm border row align-items-center favorites-share-box">

                            <div class=" js-font-resize col">
                                <a href="/bundles/{{ $bundle->slug }}/favorite" id="favoriteToggle" class=" js-font-resize d-flex flex-column align-items-center text-gray">
                                    <i data-feather="heart" class=" js-font-resize {{ !empty($isFavorite) ? 'favorite-active' : '' }}" width="20" height="20"></i>
                                    <span class=" js-font-resize font-12">{{ trans('panel.favorite') }}</span>
                                </a>
                            </div>

                            <div class=" js-font-resize col">
                                <a href="#" class=" js-font-resize js-share-course d-flex flex-column align-items-center text-gray">
                                    <i data-feather="share-2" width="20" height="20"></i>
                                    <span class=" js-font-resize font-12">{{ trans('public.share') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cashback Alert --}}
                @include('web.default.includes.cashback_alert',['itemPrice' => $bundle->price])

                {{-- Gift Card --}}
                @if($bundle->canSale() and !empty(getGiftsGeneralSettings('status')) and !empty(getGiftsGeneralSettings('allow_sending_gift_for_bundles')))
                    <a href="/gift/bundle/{{ $bundle->slug }}" class=" js-font-resize d-flex align-items-center mt-30 rounded-lg border p-15">
                        <div class=" js-font-resize size-40 d-flex-center rounded-circle bg-gray200">
                            <i data-feather="gift" class=" js-font-resize text-gray" width="20" height="20"></i>
                        </div>
                        <div class=" js-font-resize ml-5">
                            <h4 class=" js-font-resize font-14 font-weight-bold text-gray">{{ trans('update.gift_this_bundle') }}</h4>
                            <p class=" js-font-resize font-12 text-gray">{{ trans('update.gift_this_bundle_hint') }}</p>
                        </div>
                    </a>
                @endif

                @if($bundle->teacher->offline)
                    <div class=" js-font-resize rounded-lg shadow-sm mt-35 d-flex">
                        <div class=" js-font-resize offline-icon offline-icon-left d-flex align-items-stretch">
                            <div class=" js-font-resize d-flex align-items-center">
                                <img src="/assets/default/img/profile/time-icon.png" alt="offline">
                            </div>
                        </div>

                        <div class=" js-font-resize p-15">
                            <h3 class=" js-font-resize font-16 text-dark-blue">{{ trans('public.instructor_is_not_available') }}</h3>
                            <p class=" js-font-resize font-14 font-weight-500 text-gray mt-15">{{ $bundle->teacher->offline_message }}</p>
                        </div>
                    </div>
                @endif

                <div class=" js-font-resize rounded-lg shadow-sm mt-35 px-25 py-20">
                    <h3 class=" js-font-resize sidebar-title font-16 text-secondary font-weight-bold">{{ trans('update.bundle_specifications') }}</h3>

                    <div class=" js-font-resize mt-30">
                        <div class=" js-font-resize mt-20 d-flex align-items-center justify-content-between text-gray">
                            <div class=" js-font-resize d-flex align-items-center">
                                <i data-feather="clock" width="20" height="20"></i>
                                <span class=" js-font-resize ml-5 font-14 font-weight-500">{{ trans('public.duration') }}:</span>
                            </div>
                            <span class=" js-font-resize font-14">{{ convertMinutesToHourAndMinute($bundle->getBundleDuration()) }} {{ trans('home.hours') }}</span>
                        </div>

                        <div class=" js-font-resize mt-20 d-flex align-items-center justify-content-between text-gray">
                            <div class=" js-font-resize d-flex align-items-center">
                                <i data-feather="users" width="20" height="20"></i>
                                <span class=" js-font-resize ml-5 font-14 font-weight-500">{{ trans('quiz.students') }}:</span>
                            </div>
                            <span class=" js-font-resize font-14">{{ $bundle->sales_count }}</span>
                        </div>

                        <div class=" js-font-resize mt-20 d-flex align-items-center justify-content-between text-gray">
                            <div class=" js-font-resize d-flex align-items-center">
                                <img src="/assets/default/img/icons/sessions.svg" width="20" alt="">
                                <span class=" js-font-resize ml-5 font-14 font-weight-500">{{ trans('product.courses') }}:</span>
                            </div>
                            <span class=" js-font-resize font-14">{{ $bundle->bundleWebinars->count() }}</span>
                        </div>

                        <div class=" js-font-resize mt-20 d-flex align-items-center justify-content-between text-gray">
                            <div class=" js-font-resize d-flex align-items-center">
                                <img src="/assets/default/img/icons/sessions.svg" width="20" alt="">
                                <span class=" js-font-resize ml-5 font-14 font-weight-500">{{ trans('public.created_at') }}:</span>
                            </div>
                            <span class=" js-font-resize font-14">{{ dateTimeFormat($bundle->created_at,'j M Y') }}</span>
                        </div>

                        @if(!empty($bundle->access_days))
                            <div class=" js-font-resize mt-20 d-flex align-items-center justify-content-between text-gray">
                                <div class=" js-font-resize d-flex align-items-center">
                                    <i data-feather="alert-circle" width="20" height="20"></i>
                                    <span class=" js-font-resize ml-5 font-14 font-weight-500">{{ trans('update.access_period') }}:</span>
                                </div>
                                <span class=" js-font-resize font-14">{{ $bundle->access_days }} {{ trans('public.days') }}</span>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- organization --}}
                @if($bundle->creator_id != $bundle->teacher_id)
                    @include('web.default.course.sidebar_instructor_profile', ['courseTeacher' => $bundle->creator])
                @endif
                {{-- teacher --}}
                @include('web.default.course.sidebar_instructor_profile', ['courseTeacher' => $bundle->teacher])
                {{-- ./ teacher --}}

                {{-- tags --}}
                @if($bundle->tags->count() > 0)
                    <div class=" js-font-resize rounded-lg tags-card shadow-sm mt-35 px-25 py-20">
                        <h3 class=" js-font-resize sidebar-title font-16 text-secondary font-weight-bold">{{ trans('public.tags') }}</h3>

                        <div class=" js-font-resize d-flex flex-wrap mt-10">
                            @foreach($bundle->tags as $tag)
                                <a href="" class=" js-font-resize tag-item bg-gray200 p-5 font-14 text-gray font-weight-500 rounded">{{ $tag->title }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
                {{-- ads --}}
                @if(!empty($advertisingBannersSidebar) and count($advertisingBannersSidebar))
                    <div class=" js-font-resize row">
                        @foreach($advertisingBannersSidebar as $sidebarBanner)
                            <div class=" js-font-resize rounded-lg sidebar-ads mt-35 col-{{ $sidebarBanner->size }}">
                                <a href="{{ $sidebarBanner->link }}">
                                    <img src="{{ $sidebarBanner->image }}" class=" js-font-resize img-cover rounded-lg" alt="{{ $sidebarBanner->title }}">
                                </a>
                            </div>
                        @endforeach
                    </div>

                @endif
            </div>
        </div>

        {{-- Ads Bannaer --}}
        @if(!empty($advertisingBanners) and count($advertisingBanners))
            <div class=" js-font-resize mt-30 mt-md-50">
                <div class=" js-font-resize row">
                    @foreach($advertisingBanners as $banner)
                        <div class=" js-font-resize col-{{ $banner->size }}">
                            <a href="{{ $banner->link }}">
                                <img src="{{ $banner->image }}" class=" js-font-resize img-cover rounded-sm" alt="{{ $banner->title }}">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        {{-- ./ Ads Bannaer --}}
    </section>

    @include('web.default.bundle.share_modal')
    @include('web.default.bundle.buy_with_point_modal')
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/parts/time-counter-down.min.js"></script>
    <script src="/assets/default/vendors/barrating/jquery.barrating.min.js"></script>
    <script src="/assets/default/vendors/video/video.min.js"></script>
    <script src="/assets/default/vendors/video/youtube.min.js"></script>
    <script src="/assets/default/vendors/video/vimeo.js"></script>

    <script>
        var webinarDemoLang = '{{ trans('webinars.webinar_demo') }}';
        var replyLang = '{{ trans('panel.reply') }}';
        var closeLang = '{{ trans('public.close') }}';
        var saveLang = '{{ trans('public.save') }}';
        var reportLang = '{{ trans('panel.report') }}';
        var reportSuccessLang = '{{ trans('panel.report_success') }}';
        var reportFailLang = '{{ trans('panel.report_fail') }}';
        var messageToReviewerLang = '{{ trans('public.message_to_reviewer') }}';
        var copyLang = '{{ trans('public.copy') }}';
        var copiedLang = '{{ trans('public.copied') }}';
        var learningToggleLangSuccess = '{{ trans('public.course_learning_change_status_success') }}';
        var learningToggleLangError = '{{ trans('public.course_learning_change_status_error') }}';
        var notLoginToastTitleLang = '{{ trans('public.not_login_toast_lang') }}';
        var notLoginToastMsgLang = '{{ trans('public.not_login_toast_msg_lang') }}';
        var notAccessToastTitleLang = '{{ trans('public.not_access_toast_lang') }}';
        var notAccessToastMsgLang = '{{ trans('public.not_access_toast_msg_lang') }}';
        var canNotTryAgainQuizToastTitleLang = '{{ trans('public.can_not_try_again_quiz_toast_lang') }}';
        var canNotTryAgainQuizToastMsgLang = '{{ trans('public.can_not_try_again_quiz_toast_msg_lang') }}';
        var canNotDownloadCertificateToastTitleLang = '{{ trans('public.can_not_download_certificate_toast_lang') }}';
        var canNotDownloadCertificateToastMsgLang = '{{ trans('public.can_not_download_certificate_toast_msg_lang') }}';
        var sessionFinishedToastTitleLang = '{{ trans('public.session_finished_toast_title_lang') }}';
        var sessionFinishedToastMsgLang = '{{ trans('public.session_finished_toast_msg_lang') }}';
        var sequenceContentErrorModalTitle = '{{ trans('update.sequence_content_error_modal_title') }}';

    </script>

    <script src="/assets/default/js/parts/comment.min.js"></script>
    <script src="/assets/default/js/parts/video_player_helpers.min.js"></script>
    <script src="/assets/default/js/parts/webinar_show.min.js"></script>
@endpush
