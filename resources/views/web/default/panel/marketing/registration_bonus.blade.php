@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/chartjs/chart.min.css"/>
@endpush

@section('content')
    @php
        $registrationBonusSettings = getRegistrationBonusSettings();
        $checkReferralUserCount = (!empty($registrationBonusSettings['unlock_registration_bonus_with_referral']) and !empty($registrationBonusSettings['number_of_referred_users']));
        $purchaseAmountCount = (!empty($registrationBonusSettings['enable_referred_users_purchase']));
    @endphp

    @if(!empty($accounting))
        <div class=" js-font-resize d-flex align-items-center mb-20 p-15 success-transparent-alert">
            <div class=" js-font-resize success-transparent-alert__icon d-flex align-items-center justify-content-center">
                <i data-feather="credit-card" width="18" height="18" class=" js-font-resize "></i>
            </div>
            <div class=" js-font-resize ml-10">
                <div class=" js-font-resize font-14 font-weight-bold ">{{ trans('update.you_got_the_bonus') }}</div>
                <div class=" js-font-resize font-12 ">{{ trans('update.your_registration_bonus_was_unlocked_on_date',['date' => dateTimeFormat($accounting->created_at, 'j M Y')]) }}</div>
            </div>
        </div>
    @endif

    <section class=" js-font-resize ">
        <h2 class=" js-font-resize section-title">{{ trans('update.registration_bonus') }}</h2>

        <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35">
            <div class=" js-font-resize row">

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/36.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ handlePrice($registrationBonusSettings['registration_bonus_amount'] ?? 0) }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.registration_bonus') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/rank.png" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-36 font-weight-bold mt-5 {{ !empty($accounting) ? 'text-primary' : 'text-danger' }}">{{ !empty($accounting) ? trans('update.unlocked') : trans('update.locked') }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.bonus_status') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/computer.png" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ !empty($accounting) ? dateTimeFormat($accounting->created_at, 'j M Y') : '-' }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.bonus_date') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class=" js-font-resize row">

        @if($checkReferralUserCount or $purchaseAmountCount)
            <div class=" js-font-resize col-12 col-md-6 mt-25">
                <div class=" js-font-resize p-15 rounded-sm panel-shadow bg-white h-100">
                    <div class=" js-font-resize row">
                        <div class=" js-font-resize col-5 d-flex align-items-center justify-content-center">
                            <img src="/assets/default/img/rewards/registration_bonus.png" class=" js-font-resize img-fluid" alt="{{ trans('update.registration_bonus') }}">
                        </div>

                        <div class=" js-font-resize col-7">
                            <h4 class=" js-font-resize font-16 font-weight-bold">{{ trans('update.bonus_status') }}</h4>

                            <p class=" js-font-resize mt-10 font-14 font-weight-500 text-gray">{{ trans('update.your_bonus_is_locked_To_unlock_the_bonus_please_check_the_following_statuses') }}:</p>

                            @if(!empty($registrationBonusSettings['number_of_referred_users']))
                                <div class=" js-font-resize d-flex align-items-center position-relative mt-15 p-15 border border-gray200 rounded-lg">
                                    <div class=" js-font-resize bonus-status-pie-charts">
                                        <canvas id="bonusStatusReferredUsersChart" height="40"></canvas>
                                    </div>

                                    <div class=" js-font-resize ml-5">
                                        <span class=" js-font-resize d-block font-14 font-weight-bold text-gray">{{ trans('update.referred_users') }}</span>
                                        <span class=" js-font-resize d-block font-12 text-gray">{{ $bonusStatusReferredUsersChart['complete'] == 0 ? trans('update.you_havent_referred_any_users') : trans('update.you_referred_count_users_to_the_platform',['count' => "{$bonusStatusReferredUsersChart['referred_users']}/{$registrationBonusSettings['number_of_referred_users']}"]) }}</span>
                                    </div>

                                    @if($bonusStatusReferredUsersChart['complete'] == 100)
                                        <div class=" js-font-resize bonus-status-complete-check">
                                            <i data-feather="check" class=" js-font-resize text-white" width="12" height="12"></i>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($purchaseAmountCount)
                                <div class=" js-font-resize d-flex align-items-center position-relative mt-15 p-15 border border-gray200 rounded-lg">
                                    <div class=" js-font-resize bonus-status-pie-charts">
                                        <canvas id="bonusStatusUsersPurchasesChart" height="40"></canvas>
                                    </div>

                                    <div class=" js-font-resize ml-5">
                                        <span class=" js-font-resize d-block font-14 font-weight-bold text-gray">{{ trans('update.users_purchases') }}</span>
                                        <span class=" js-font-resize d-block font-12 text-gray">{{ $bonusStatusUsersPurchasesChart['complete'] == 0 ? trans('update.you_havent_referred_any_users_to_purchase') : trans('update.count_users_achieved_purchase_target',['count' => "{$bonusStatusUsersPurchasesChart['reached_user_purchased']}/{$bonusStatusUsersPurchasesChart['total_user_purchased']}"]) }}</span>
                                    </div>

                                    @if($bonusStatusUsersPurchasesChart['complete'] == 100)
                                        <div class=" js-font-resize bonus-status-complete-check">
                                            <i data-feather="check" class=" js-font-resize text-white" width="12" height="12"></i>
                                        </div>
                                    @endif
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        @endif

        @php
            $registrationBonusTermsSettings = getRegistrationBonusTermsSettings();
        @endphp

        @if(!empty($registrationBonusTermsSettings) and !empty($registrationBonusTermsSettings['items']))
            <div class=" js-font-resize mt-25 {{ (!empty($registrationBonusSettings['number_of_referred_users']) or !empty($registrationBonusSettings['purchase_amount_for_unlocking_bonus'])) ? 'col-12 col-md-6' : 'col-12' }}">
                <div class=" js-font-resize p-15 rounded-sm panel-shadow bg-white h-100">
                    <div class=" js-font-resize row">
                        <div class=" js-font-resize col-7">
                            <h4 class=" js-font-resize font-16 font-weight-bold mb-20">{{ trans('update.how_to_get_bonus') }}</h4>

                            @foreach($registrationBonusTermsSettings['items'] as $termItem)
                                @if(!empty($termItem['icon']) and !empty($termItem['title']) and !empty($termItem['description']))
                                    <div class=" js-font-resize how-to-get-bonus-items d-flex align-items-start">
                                        <div class=" js-font-resize icon-box d-flex align-items-center justify-content-center">
                                            <img src="{{ $termItem['icon'] }}" alt="{{ $termItem['title'] }}" width="16" height="16">
                                        </div>
                                        <div class=" js-font-resize ml-10">
                                            <span class=" js-font-resize d-block font-14 font-weight-bold text-dark">{{ $termItem['title'] }}</span>
                                            <span class=" js-font-resize d-block font-12 font-weight-500 text-gray mt-5">{{ $termItem['description'] }}</span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @if(!empty($registrationBonusTermsSettings['term_image']))
                            <div class=" js-font-resize col-5 d-flex align-items-center justify-content-center">
                                <img src="{{ $registrationBonusTermsSettings['term_image'] }}" class=" js-font-resize img-fluid" alt="{{ trans('update.how_to_get_bonus') }}">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </section>

    @if($checkReferralUserCount)
        <section class=" js-font-resize mt-25">
            <h2 class=" js-font-resize section-title">{{ trans('update.referral_history') }}</h2>

            @if(!empty($referredUsers) and count($referredUsers))
                <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                    <div class=" js-font-resize row">
                        <div class=" js-font-resize col-12 ">
                            <div class=" js-font-resize table-responsive">
                                <table class=" js-font-resize table text-center custom-table">
                                    <thead>
                                    <tr>
                                        <th>{{ trans('panel.user') }}</th>
                                        @if($purchaseAmountCount)
                                            <th class=" js-font-resize text-center">{{ trans('update.purchase_status') }}</th>
                                        @endif
                                        <th class=" js-font-resize text-right">{{ trans('panel.registration_date') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($referredUsers as $user)
                                        <tr>
                                            <td class=" js-font-resize text-left">
                                                <div class=" js-font-resize user-inline-avatar d-flex align-items-center">
                                                    <div class=" js-font-resize avatar bg-gray200">
                                                        <img src="{{ $user->getAvatar() }}" class=" js-font-resize img-cover" alt="{{ $user->full_name }}">
                                                    </div>
                                                    <div class=" js-font-resize  ml-5">
                                                        <span class=" js-font-resize d-block font-weight-500">{{ $user->full_name }}</span>
                                                    </div>
                                                </div>
                                            </td>

                                            @if($purchaseAmountCount)
                                                <td>
                                                    @if((!empty($registrationBonusSettings['purchase_amount_for_unlocking_bonus']) and $user->totalPurchase >= $registrationBonusSettings['purchase_amount_for_unlocking_bonus']) or (empty($registrationBonusSettings['purchase_amount_for_unlocking_bonus']) and $user->totalPurchase > 0))
                                                        <span class=" js-font-resize font-16 font-weight-500 text-primary">{{ trans('update.reached') }}</span>
                                                    @else
                                                        <span class=" js-font-resize font-16 font-weight-500 text-dark">{{ trans('update.not_reached') }}</span>
                                                    @endif
                                                </td>
                                            @endif

                                            <td class=" js-font-resize text-right">{{ dateTimeFormat($user->created_at, 'Y M j | H:i') }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>


                        </div>
                    </div>
                </div>
            @else
                <div class=" js-font-resize no-result my-50 d-flex align-items-center justify-content-center flex-column">
                    <div class=" js-font-resize no-result-logo">
                        <img src="/assets/default/img/no-results/no_followers.png" alt="{{ trans('update.no_referred_users') }}">
                    </div>
                    <div class=" js-font-resize d-flex align-items-center flex-column mt-30 text-center">
                        <h2>{{ trans('update.no_referred_users') }}</h2>
                        <p class=" js-font-resize mt-5 text-center">{{ trans('update.you_havent_referred_any_users_yet') }}</p>
                    </div>
                </div>
            @endif
        </section>
    @endif
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/chartjs/chart.min.js"></script>
    <script src="/assets/default/js/panel/registration_bonus.min.js"></script>

    <script>
        (function ($) {
            "use strict";

            @if(!empty($bonusStatusReferredUsersChart))
            makePieChart('bonusStatusReferredUsersChart', @json($bonusStatusReferredUsersChart['labels']), Number({{ $bonusStatusReferredUsersChart['complete'] }}));
            @endif

            @if(!empty($bonusStatusUsersPurchasesChart))
            makePieChart('bonusStatusUsersPurchasesChart', @json($bonusStatusUsersPurchasesChart['labels']), Number({{ $bonusStatusUsersPurchasesChart['complete'] }}));
            @endif
        })()
    </script>
@endpush
