@extends(getTemplate() . '.panel.layouts.panel_layout')

@section('content')

    @if (!empty($overdueInstallments) and count($overdueInstallments))
        <div class=" js-font-resize d-flex align-items-center mb-20 mt-20 p-15 danger-transparent-alert">
            <div class=" js-font-resize danger-transparent-alert__icon d-flex align-items-center justify-content-center">
                <i data-feather="credit-card" width="18" height="18" class=" js-font-resize "></i>
            </div>
            <div class=" js-font-resize ml-10">
                <div class=" js-font-resize font-14 font-weight-bold ">{{ trans('update.overdue_installments') }}</div>
                <div class=" js-font-resize font-12 ">
                    {{ trans('update.you_have_count_overdue_installments_please_pay_them_to_avoid_restrictions_and_negative_effects_on_your_account', ['count' => count($overdueInstallments)]) }}
                </div>
            </div>
        </div>
    @endif

    {{-- Installments Overview --}}
    <section>
        <h2 class=" js-font-resize section-title">{{ trans('update.installments_overview') }}</h2>

        <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35">
             <div class=" js-font-resize row">
                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/127.png" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $totalParts }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.total_parts') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/38.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $remainedParts }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.remained_parts') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/33.png" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ handlePrice($remainedAmount) }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.remained_amount') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/128.png" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ handlePrice($overdueAmount) }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.overdue_amount') }}</span>
                    </div>
                </div>
            </div> 
        </div>
    </section>


    <section class=" js-font-resize mt-25">
        <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class=" js-font-resize section-title">{{ trans('update.installments_list') }}</h2>
        </div>

        <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 ">
                    <div class=" js-font-resize table-responsive">
                        <table class=" js-font-resize table text-center custom-table">
                            <thead>
                                <tr>
                                    <th>{{ trans('public.title') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('panel.amount') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('update.due_date') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('update.payment_date') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.status') }}</th>
                                    <th class=" js-font-resize "></th>
                                </tr>
                            </thead>
                            <tbody>

                                @if (!empty($installment->upfront))
                                    @php
                                        $upfrontPayment = $payments->where('type', 'upfront')->first();
                                    @endphp
                                    <tr>
                                        <td class=" js-font-resize text-left">
                                            {{ trans('update.upfront') }}
                                            @if ($installment->upfront_type == 'percent')
                                                <span class=" js-font-resize ml-5">({{ $installment->upfront }}%)</span>
                                            @endif
                                        </td>

                                        <td class=" js-font-resize text-center">{{ handlePrice($installment->getUpfront($itemPrice)) }}
                                        </td>

                                        <td class=" js-font-resize text-center">-</td>

                                        <td class=" js-font-resize text-center">
                                            {{ !empty($upfrontPayment) ? dateTimeFormat($upfrontPayment->created_at, 'j M Y H:i') : '-' }}
                                        </td>

                                        <td class=" js-font-resize text-center">
                                            @if (!empty($upfrontPayment))
                                                <span class=" js-font-resize text-primary">{{ trans('public.paid') }}</span>
                                            @else
                                                <span class=" js-font-resize text-light">{{ trans('update.unpaid') }}</span>
                                            @endif
                                        </td>
                                        <td class=" js-font-resize text-right">

                                        </td>
                                    </tr>
                                @endif

                                @foreach ($installment->steps as $step)
                                    @php
                                        $stepPayment = $payments
                                            ->where('selected_installment_step_id', $step->id)
                                            ->where('status', 'paid')
                                            ->first();

                                        if ($order->selectedInstallment->deadline_type == 'days') {
                                            $dueAt = $step->deadline * 86400 + $order->bundle->start_date;
                                        } else {
                                            $dueAt = $step->deadline;
                                        }
                                        $isOverdue = ($dueAt < time() and empty($stepPayment));
                                    @endphp

                                    <tr>
                                        <td class=" js-font-resize text-left">
                                            <div class=" js-font-resize d-block font-16 font-weight-500 text-light">
                                                {{ $step->installmentStep->title }}

                                                @if ($step->amount_type == 'percent')
                                                    <span class=" js-font-resize ml-5 font-12 text-gray">({{ $step->amount }}%)</span>
                                                @endif
                                            </div>
                                            {{--
                                             <span class=" js-font-resize d-block font-12 text-gray">{{ $step->deadline }} أيام بعد بداية
                                                الدورة</span>
                                                --}}
                                        </td>

                                        <td class=" js-font-resize text-center">{{ handlePrice($step->getPrice($itemPrice)) }}</td>

                                        <td class=" js-font-resize text-center">
                                            <span
                                                class=" js-font-resize {{ $isOverdue ? 'text-danger' : '' }}">{{ dateTimeFormat($dueAt, 'j M Y') }}</span>
                                        </td>

                                        <td class=" js-font-resize text-center">
                                            {{ !empty($stepPayment) ? dateTimeFormat($stepPayment->created_at, 'j M Y H:i') : '-' }}
                                        </td>

                                        <td class=" js-font-resize text-center">
                                            @if (!empty($stepPayment))
                                                <span class=" js-font-resize text-primary">{{ trans('public.paid') }}</span>
                                            @else
                                                <span
                                                    class=" js-font-resize {{ $isOverdue ? 'text-danger' : 'text-light' }}">{{ trans('update.unpaid') }}
                                                    {{ $isOverdue ? '(' . trans('update.overdue') . ')' : '' }}</span>
                                            @endif
                                        </td>
                                        <td class=" js-font-resize text-right">
                                            @if (empty($stepPayment))
                                                <div class=" js-font-resize btn-group dropdown table-actions">
                                                    <button type="button" class=" js-font-resize btn-transparent dropdown-toggle"
                                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i data-feather="more-vertical" height="20"></i>
                                                    </button>
                                                    <div class=" js-font-resize dropdown-menu menu-lg">

                                                        <a href="/panel/financial/installments/{{ $order->id }}/steps/{{ $step->id }}/pay"
                                                            target="_blank"
                                                            class=" js-font-resize webinar-actions d-block mt-10 font-weight-normal">{{ trans('panel.pay') }}</a>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
@endpush
