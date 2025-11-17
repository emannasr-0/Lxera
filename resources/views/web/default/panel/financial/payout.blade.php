@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
    <section>
        <h2 class=" js-font-resize section-title">{{ trans('financial.account_summary') }}</h2>

        @if(!$authUser->financial_approval)
            <div class=" js-font-resize p-15 mt-20 p-lg-20 not-verified-alert font-weight-500 text-light rounded-sm panel-shadow">
                {{ trans('panel.not_verified_alert') }}
                <a href="/panel/setting/step/7" class=" js-font-resize text-decoration-underline">{{ trans('panel.this_link') }}</a>.
            </div>
        @endif

        <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/36.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $accountCharge ? handlePrice($accountCharge) : 0 }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('financial.account_charge') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/37.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ handlePrice($readyPayout ?? 0) }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('financial.ready_to_payout') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/38.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ handlePrice($totalIncome ?? 0) }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('financial.total_income') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <div class=" js-font-resize mt-45">
        <button type="button" @if(!$authUser->financial_approval) disabled @endif class=" js-font-resize request-payout btn btn-sm btn-primary">{{ trans('financial.request_payout') }}</button>
    </div>

    @if($payouts->count() > 0)
        <section class=" js-font-resize mt-35">
            <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
                <h2 class=" js-font-resize section-title">{{ trans('financial.payouts_history') }}</h2>
            </div>

            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('financial.account') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.type') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('panel.amount') }} ({{ $currency }})</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.status') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('admin/main.actions') }}</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($payouts as $payout)
                                    <tr>
                                        <td>
                                            <div class=" js-font-resize text-left">
                                            @if(!empty($payout->userSelectedBank->bank))
                                                <span class=" js-font-resize d-block font-weight-500 text-light">{{ $payout->userSelectedBank->bank->title }}</span>
                                                @else
                                                <span class=" js-font-resize d-block font-weight-500 text-light">-</span>
                                                @endif
                                                <span class=" js-font-resize d-block font-12 text-gray mt-1">{{ dateTimeFormat($payout->created_at, 'j M Y | H:i') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span>{{ trans('public.manual') }}</span>
                                        </td>
                                        <td>
                                            <span class=" js-font-resize text-primary font-weight-bold">{{ handlePrice($payout->amount, false) }}</span>
                                        </td>
                                        <td>
                                            @switch($payout->status)
                                                @case(\App\Models\Payout::$waiting)
                                                    <span class=" js-font-resize text-warning font-weight-bold">{{ trans('public.waiting') }}</span>
                                                    @break;
                                                @case(\App\Models\Payout::$reject)
                                                    <span class=" js-font-resize text-danger font-weight-bold">{{ trans('public.rejected') }}</span>
                                                    @break;
                                                @case(\App\Models\Payout::$done)
                                                    <span class=" js-font-resize ">{{ trans('public.done') }}</span>
                                                    @break;
                                            @endswitch
                                        </td>

                                        <td>
                                            {{-- For Modal --}}
                                            @php
                                                $bank = $payout->userSelectedBank->bank;
                                            @endphp

                                            <input type="hidden" class=" js-font-resize js-bank-details" data-name="{{ trans("admin/main.bank") }}" value="{{ $bank->title }}">
                                            @foreach($bank->specifications as $specification)
                                                @php
                                                    $selectedBankSpecification = $payout->userSelectedBank->specifications->where('user_selected_bank_id', $payout->userSelectedBank->id)->where('user_bank_specification_id', $specification->id)->first();
                                                @endphp

                                                @if(!empty($selectedBankSpecification))
                                                    <input type="hidden" class=" js-font-resize js-bank-details" data-name="{{ $specification->name }}" value="{{ $selectedBankSpecification->value }}">
                                                @endif
                                            @endforeach

                                            <button type="button" class=" js-font-resize js-show-details btn-transparent btn-sm" data-toggle="tooltip" data-placement="top" title="{{ trans('update.show_details') }}">
                                                <i data-feather="eye" width="18" class=" js-font-resize "></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <div class=" js-font-resize my-30">
                {{ $payouts->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        </section>
    @else
        @include(getTemplate() . '.includes.no-result',[
            'file_name' => 'payout.png',
            'title' => trans('financial.payout_no_result'),
            'hint' => nl2br(trans('financial.payout_no_result_hint')),
        ])

    @endif


    <div id="requestPayoutModal" class=" js-font-resize d-none">
        <h3 class=" js-font-resize section-title after-line font-20 text-light mb-25">{{ trans('financial.payout_confirmation') }}</h3>
        <p class=" js-font-resize text-gray mt-15">{{ trans('financial.payout_confirmation_hint') }}</p>

        <form method="post" action="/panel/financial/request-payout">
            {{ csrf_field() }}
            <div class=" js-font-resize row justify-content-center">
                <div class=" js-font-resize w-75 mt-50">
                    <div class=" js-font-resize d-flex align-items-center justify-content-between text-gray">
                        <span class=" js-font-resize font-weight-bold">{{ trans('financial.ready_to_payout') }}</span>
                        <span>{{ handlePrice($readyPayout ?? 0) }}</span>
                    </div>

                    @if(!empty($authUser->selectedBank) and !empty($authUser->selectedBank->bank))
                        <div class=" js-font-resize d-flex align-items-center justify-content-between text-gray mt-20">
                            <span class=" js-font-resize font-weight-bold">{{ trans('financial.account_type') }}</span>
                            <span>{{ $authUser->selectedBank->bank->title }}</span>
                        </div>

                        @foreach($authUser->selectedBank->bank->specifications as $specification)
                            @php
                                $selectedBankSpecification = $authUser->selectedBank->specifications->where('user_selected_bank_id', $authUser->selectedBank->id)->where('user_bank_specification_id', $specification->id)->first();
                            @endphp

                            <div class=" js-font-resize d-flex align-items-center justify-content-between text-gray mt-20">
                                <span class=" js-font-resize font-weight-bold">{{ $specification->name }}</span>
                                <span>{{ (!empty($selectedBankSpecification)) ? $selectedBankSpecification->value : '' }}</span>
                            </div>
                        @endforeach
                    @endif

                </div>
            </div>

            <div class=" js-font-resize mt-50 d-flex align-items-center justify-content-end">
                <button type="button" class=" js-font-resize js-submit-payout btn btn-sm btn-primary">{{ trans('financial.request_payout') }}</button>
                <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts_bottom')
    <script>
        var payoutDetailsLang = '{{ trans('update.payout_details') }}';
        var closeLang = '{{ trans('public.close') }}';
    </script>

    <script src="/assets/default/js/panel/financial/payout.min.js"></script>
@endpush
