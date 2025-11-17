@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')

@endpush

@section('content')
    <section class=" js-font-resize ">
        <h2 class=" js-font-resize section-title">{{ trans('panel.affiliate_statistics') }}</h2>

        <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35">
            <div class=" js-font-resize row">

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/48.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $referredUsersCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('panel.referred_users') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/38.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ handlePrice($registrationBonus) }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('panel.registration_bonus') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/36.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ handlePrice($affiliateBonus) }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('panel.affiliate_bonus') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class=" js-font-resize mt-25">
        <h2 class=" js-font-resize section-title">{{ trans('panel.affiliate_summary') }}</h2>

        @if(!empty($referralSettings))
            <div class=" js-font-resize mt-15 font-14 text-gray">
                @if(!empty($referralSettings['affiliate_user_amount']))<p>- {{ trans('panel.user_registration_reward') }}: {{ handlePrice($referralSettings['affiliate_user_amount']) }}</p>@endif
                @if(!empty($referralSettings['referred_user_amount']))<p>- {{ trans('panel.referred_user_registration_reward') }}: {{ handlePrice($referralSettings['referred_user_amount']) }}</p>@endif
                @if(!empty($referralSettings['affiliate_user_commission']))<p>- {{ trans('panel.referred_user_purchase_commission') }}: {{ $referralSettings['affiliate_user_commission'] }}%</p>@endif
                <p>- {{ trans('panel.your_affiliate_code') }}: {{ $affiliateCode->code }}</p>
                @if(!empty($referralSettings['referral_description']))<p>- {{ $referralSettings['referral_description'] }}</p>@endif
            </div>
        @endif

        <div class=" js-font-resize row mt-15">
            <div class=" js-font-resize col-12 col-lg-5">
                <h3 class=" js-font-resize font-16 font-weight-500">{{ trans('panel.affiliate_url') }}</h3>

                <div class=" js-font-resize form-group mt-5">
                    <div class=" js-font-resize input-group">
                        <div class=" js-font-resize input-group-prepend">
                            <button type="button" class=" js-font-resize input-group-text js-copy" data-input="affiliate_url" data-toggle="tooltip" data-placement="top" title="{{ trans('public.copy') }}" data-copy-text="{{ trans('public.copy') }}" data-done-text="{{ trans('public.done') }}">
                                <i data-feather="copy" width="18" height="18" class=" js-font-resize text-white"></i>
                            </button>
                        </div>
                        <input type="text" name="affiliate_url" readonly value="{{ $affiliateCode->getAffiliateUrl() }}" class=" js-font-resize form-control"/>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <section class=" js-font-resize mt-25">
        <h2 class=" js-font-resize section-title">{{ trans('panel.earnings') }}</h2>

        <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 ">
                    <div class=" js-font-resize table-responsive">
                        <table class=" js-font-resize table text-center custom-table">
                            <thead>
                            <tr>
                                <th>{{ trans('panel.user') }}</th>
                                <th class=" js-font-resize text-center">{{ trans('panel.registration_bonus') }}</th>
                                <th class=" js-font-resize text-center">{{ trans('panel.affiliate_bonus') }}</th>
                                <th class=" js-font-resize text-center">{{ trans('panel.registration_date') }}</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($affiliates as $affiliate)
                                <tr>
                                    <td class=" js-font-resize text-left">
                                        <div class=" js-font-resize user-inline-avatar d-flex align-items-center">
                                            <div class=" js-font-resize avatar bg-gray200">
                                                <img src="{{ $affiliate->referredUser->getAvatar() }}" class=" js-font-resize img-cover" alt="{{ $affiliate->referredUser->full_name }}">
                                            </div>
                                            <div class=" js-font-resize  ml-5">
                                                <span class=" js-font-resize d-block font-weight-500">{{ $affiliate->referredUser->full_name }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td>{{ handlePrice($affiliate->getAffiliateRegistrationAmountsOfEachReferral()) }}</td>

                                    <td>{{ handlePrice($affiliate->getTotalAffiliateCommissionOfEachReferral()) }}</td>

                                    <td>{{ dateTimeFormat($affiliate->created_at, 'Y M j | H:i') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class=" js-font-resize my-30">
                        {{ $affiliates->appends(request()->input())->links('vendor.pagination.panel') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
