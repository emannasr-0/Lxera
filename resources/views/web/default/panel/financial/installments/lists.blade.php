@extends(getTemplate() . '.panel.layouts.panel_layout')

@section('content')

    @if (!empty($overdueInstallmentsCount) and $overdueInstallmentsCount > 0)
        <div class=" js-font-resize d-flex align-items-center mb-20 mt-40 p-15 danger-transparent-alert">
            <div class=" js-font-resize danger-transparent-alert__icon d-flex align-items-center justify-content-center">
                <i data-feather="credit-card" width="18" height="18" class=" js-font-resize "></i>
            </div>
            <div class=" js-font-resize ml-10">
                <div class=" js-font-resize font-14 font-weight-bold ">{{ trans('update.overdue_installments') }}</div>
                <div class=" js-font-resize font-12 ">
                    {{ trans('update.you_have_count_overdue_installments_please_pay_them_to_avoid_restrictions_and_negative_effects_on_your_account', ['count' => $overdueInstallmentsCount]) }}
                </div>
            </div>
        </div>
    @endif

    {{-- Installments Overview --}}

    {{-- <section>
        <h2 class=" js-font-resize section-title">{{ trans('update.installments_overview') }}</h2>

        <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/129.png" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $openInstallmentsCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.open_installments') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/130.png" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $pendingVerificationCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.pending_verification') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/127.png" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $finishedInstallmentsCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.finished_installments') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/128.png" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $overdueInstallmentsCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.overdue_installments') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section> --}}


    <section class=" js-font-resize mt-25">
        <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class=" js-font-resize section-title">{{ trans('update.my_installments') }}</h2>
        </div>

        @if (!empty($orders) and count($orders))
            @foreach ($orders as $order)
                @php
                    $orderItem = $order->getItem();
                    $itemType = $order->getItemType();
                    $itemPrice = $order->getItemPrice();
                @endphp

                @if (!empty($orderItem))
                    <div class=" js-font-resize row mt-30">
                        <div class=" js-font-resize col-12">
                            <div class=" js-font-resize webinar-card webinar-list panel-installment-card d-flex p-1 shadow border">
                                <div class=" js-font-resize bg-secondary-acadima p-15">
                                    {{-- @if (in_array($itemType, ['course', 'bundle']))
                                        <img src="{{ $orderItem->getImage() }}" class=" js-font-resize img-cover" alt="">
                                    @elseif($itemType == 'product')
                                        <img src="{{ $orderItem->thumbnail }}" class=" js-font-resize img-cover" alt="">
                                    @elseif($itemType == "subscribe")
                                        <div class=" js-font-resize d-flex align-items-center justify-content-center w-100 h-100">
                                            <img src="/assets/default/img/icons/installment/subscribe_default.svg" alt="">
                                        </div>
                                    @elseif($itemType == "registrationPackage")
                                        <div class=" js-font-resize d-flex align-items-center justify-content-center w-100 h-100">
                                            <img src="/assets/default/img/icons/installment/reg_package_default.svg" alt="">
                                        </div>
                                    @endif --}}

                                    @if ($order->isCompleted())
                                        <span class=" js-font-resize badge badge-secondary text-light">{{ trans('update.completed') }}</span>
                                    @elseif($order->status == 'open')
                                        <span class=" js-font-resize badge badge-primary text-light">{{ trans('public.open') }}</span>
                                    @elseif($order->status == 'rejected')
                                        <span class=" js-font-resize badge badge-danger text-light">{{ trans('public.rejected') }}</span>
                                    @elseif($order->status == 'canceled')
                                        <span class=" js-font-resize badge badge-danger text-light">{{ trans('public.canceled') }}</span>
                                    @elseif($order->status == 'pending_verification')
                                        <span class=" js-font-resize badge badge-warning text-light">{{ trans('update.pending_verification') }}</span>
                                    @elseif($order->status == 'refunded')
                                        <span class=" js-font-resize badge badge-secondary text-light">{{ trans('update.refunded') }}</span>
                                    @endif
                                </div>

                                <div class=" js-font-resize webinar-card-body w-100 d-flex flex-column bg-secondary-acadima">
                                    <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                        <div class=" js-font-resize d-flex align-items-center text-pink">
                                            <h3 class=" js-font-resize font-16  font-weight-bold">{{ $orderItem->title }}</h3>

                                            @if ($order->has_overdue)
                                                <span
                                                    class=" js-font-resize badge badge-outlined-danger ml-10">{{ trans('update.overdue') }}</span>
                                            @endif
                                        </div>

                                        @if (!in_array($order->status, ['refunded', 'canceled']) or $order->isCompleted())
                                            <div class=" js-font-resize btn-group dropdown table-actions">
                                                {{-- <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button> --}}
                                                <div class=" js-font-resize dropdown-menu ">

                                                    {{-- @if ($order->status == 'open')
                                                        <a href="/panel/financial/installments/{{ $order->id }}/pay_upcoming_part" target="_blank" class=" js-font-resize webinar-actions d-block mt-10">{{ trans('update.pay_upcoming_part') }}</a>
                                                    @endif

                                                    @if (!in_array($order->status, ['refunded', 'canceled']))
                                                        <a href="/panel/financial/installments/{{ $order->id }}/details" target="_blank" class=" js-font-resize webinar-actions d-block mt-10">{{ trans('update.view_details') }}</a>
                                                    @endif

                                                    @if ($itemType == 'course' and ($order->isCompleted() or $order->status == 'open'))
                                                        <a href="{{ $orderItem->getLearningPageUrl() }}" target="_blank" class=" js-font-resize webinar-actions d-block mt-10">{{ trans('update.learning_page') }}</a>
                                                    @endif --}}

                                                    {{--
                                                        @if ($order->isCompleted() or $order->status == 'open')
                                                        <a href="/panel/financial/installments/{{ $order->id }}/refund" class=" js-font-resize webinar-actions d-block mt-10 delete-action">{{ trans('update.refund') }}</a>
                                                        @endif
                                                    --}}

                                                    {{-- @if ($order->status == 'pending_verification' and getInstallmentsSettings('allow_cancel_verification'))
                                                        <a href="/panel/financial/installments/{{ $order->id }}/cancel" class=" js-font-resize webinar-actions d-block mt-10 text-danger delete-action" data-title="{{ trans('public.deleteAlertHint') }}" data-confirm="{{ trans('update.yes_cancel') }}">{{ trans('public.cancel') }}</a>
                                                    @endif --}}
                                                </div>
                                            </div>
                                        @endif

                                    </div>

                                    <div class=" js-font-resize d-flex align-items-center justify-content-between flex-wrap mt-45">
                                        {{-- <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class=" js-font-resize stat-title">{{ trans('update.item_type') }}:</span>
                                            <span class=" js-font-resize stat-value">{{ trans('update.item_type_'.$itemType) }}</span>
                                        </div> --}}

                                        <div class=" js-font-resize d-flex align-items-start flex-column mt-5 mr-15">
                                            <span class=" js-font-resize stat-title">{{ trans('panel.purchase_date') }}:</span>
                                            <span
                                                class=" js-font-resize stat-value mt-20 text-dark">{{ dateTimeFormat($order->created_at, 'j M Y H:i') }}</span>
                                        </div>

                                        {{-- <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class=" js-font-resize stat-title">{{ trans('update.upfront') }}:</span>
                                            <span class=" js-font-resize stat-value">{{ !empty($order->selectedInstallment->upfront) ? handlePrice($order->selectedInstallment->getUpfront($itemPrice)) : '-' }}</span>
                                        </div> --}}

                                        <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class=" js-font-resize stat-title">{{ trans('update.total_installments') }}:</span>
                                            <span
                                                class=" js-font-resize stat-value mt-20 text-dark">{{ trans('update.total_parts_count', ['count' => $order->selectedInstallment->steps_count + 1]) }}
                                                ({{ handlePrice($order->selectedInstallment->totalPayments($itemPrice, false) + (!empty($order->selectedInstallment->upfront) ? $order->selectedInstallment->getUpfront($itemPrice) : 0)) }})
                                            </span>
                                        </div>

                                        @if ($order->status == 'open' or $order->status == 'pending_verification')
                                            <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                                <span
                                                    class=" js-font-resize stat-title">{{ trans('update.remained_installments') }}:</span>
                                                <span
                                                    class=" js-font-resize stat-value mt-20 text-dark">{{ trans('update.total_parts_count', ['count' => $order->remained_installments_count]) }}
                                                    ({{ handlePrice($order->remained_installments_amount) }})</span>
                                            </div>

                                            @if (!empty($order->upcoming_installment))
                                                <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                                    <span
                                                        class=" js-font-resize stat-title">{{ trans('update.upcoming_installment') }}:</span>
                                                    <span class=" js-font-resize stat-value mt-20 text-dark">
                                                        @if ($order->selectedInstallment->deadline_type == 'days')
                                                            {{ dateTimeFormat($order->upcoming_installment->deadline * 86400 + $order->bundle->start_date, 'j M Y') }}
                                                        @else
                                                            {{ dateTimeFormat($order->upcoming_installment->deadline, 'j M Y') }}
                                                        @endif

                                                        ({{ handlePrice($order->upcoming_installment->getPrice($itemPrice)) }})
                                                    </span>
                                                </div>
                                            @endif

                                            @if ($order->has_overdue)
                                                <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                                    <span
                                                        class=" js-font-resize stat-title">{{ trans('update.overdue_installments') }}:</span>
                                                    <span class=" js-font-resize stat-value mt-20 text-dark">{{ $order->overdue_count }}
                                                        ({{ handlePrice($order->overdue_amount) }})</span>
                                                </div>
                                            @endif
                                        @endif

                                    </div>

                                </div>

                            </div>
                            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20 shadow border">
                                <h3 class=" js-font-resize font-16 text-pink font-weight-bold mb-20">{{trans('panel.installment_schedule')}}</h3>

                                <div class=" js-font-resize row">
                                    <div class=" js-font-resize col-12 ">
                                        <div class=" js-font-resize table-responsive">
                                            <table class=" js-font-resize table text-center custom-table">
                                                <thead>
                                                    <tr>
                                                        <th class=" js-font-resize text-black">{{ trans('public.title') }}</th>
                                                        <th class=" js-font-resize text-center text-black">{{ trans('panel.amount') }}</th>
                                                        <th class=" js-font-resize text-center text-black">{{ trans('update.due_date') }}</th>
                                                        <th class=" js-font-resize text-center text-black">{{ trans('update.payment_date') }}</th>
                                                        <th class=" js-font-resize text-center text-black">{{ trans('public.status') }}</th>
                                                        <th class=" js-font-resize "></th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    @if (!empty($order->selectedInstallment->upfront))
                                                        @php
                                                            $upfrontPayment = $order->payments
                                                                ->where('type', 'upfront')
                                                                ->first();
                                                        @endphp
                                                        <tr>
                                                            <td class=" js-font-resize text-dark text-left">
                                                                <span
                                                                    class=" js-font-resize font-16 font-weight-500 text-dark text-left">
                                                                    {{ trans('update.upfront') }}</span>
                                                                

                                                                @if ($order->selectedInstallment->upfront_type == 'percent')
                                                                    <span
                                                                        class=" js-font-resize ml-5">({{ $order->selectedInstallment->upfront }}%)</span>
                                                                @endif
                                                            </td>

                                                            <td class=" js-font-resize text-center text-dark">
                                                                {{ handlePrice($order->selectedInstallment->getUpfront($itemPrice)) }}
                                                            </td>

                                                            <td class=" js-font-resize text-center text-dark">-</td>

                                                            <td class=" js-font-resize text-center text-dark">
                                                                {{ !empty($upfrontPayment) ? dateTimeFormat($upfrontPayment->created_at, 'j M Y H:i') : '-' }}
                                                            </td>

                                                            <td class=" js-font-resize text-center text-dark">
                                                                @if (!empty($upfrontPayment))
                                                                    <span
                                                                        class=" js-font-resize text-primary">{{ trans('public.paid') }}</span>
                                                                @else
                                                                    <span
                                                                        class=" js-font-resize text-danger">{{ trans('update.unpaid') }}</span>
                                                                @endif
                                                            </td>
                                                            <td class=" js-font-resize text-right text-dark">

                                                            </td>
                                                        </tr>
                                                    @endif

                                                    @foreach ($order->selectedInstallment->steps as $step)
                                                        @php
                                                            $stepPayment = $order->payments
                                                                ->where('selected_installment_step_id', $step->id)
                                                                ->where('status', 'paid')
                                                                ->first();
                                                            if ($order->selectedInstallment->deadline_type == 'days') {
                                                                $dueAt =
                                                                    $step->deadline * 86400 +
                                                                    $order->bundle->start_date;
                                                            } else {
                                                                $dueAt = $step->deadline;
                                                            }
                                                            $isOverdue = ($dueAt < time() and empty($stepPayment));
                                                        @endphp

                                                        <tr>
                                                            <td class=" js-font-resize text-left text-dark">
                                                                <div class=" js-font-resize d-block font-16 font-weight-500 ">
                                                                    {{ $step->installmentStep->title }}

                                                                    @if ($step->amount_type == 'percent')
                                                                        <span
                                                                            class=" js-font-resize ml-5 font-12 text-gray">({{ $step->amount }}%)</span>
                                                                    @endif
                                                                </div>
                                                                {{-- <span class=" js-font-resize d-block font-12 text-gray">{{ $step->deadline }} أيام بعد بداية الدورة</span> --}}
                                                            </td>

                                                            <td class=" js-font-resize text-center text-dark">
                                                                {{ handlePrice($step->getPrice($itemPrice)) }}
                                                            </td>

                                                            <td class=" js-font-resize text-center text-dark">
                                                                <span
                                                                    class=" js-font-resize {{ $isOverdue ? 'text-danger' : '' }}">{{ dateTimeFormat($dueAt, 'j M Y') }}</span>
                                                            </td>

                                                            <td class=" js-font-resize text-center text-dark">
                                                                {{ !empty($stepPayment) ? dateTimeFormat($stepPayment->created_at, 'j M Y H:i') : '-' }}
                                                            </td>

                                                            <td class=" js-font-resize text-center text-dark">
                                                                @if (!empty($stepPayment))
                                                                    <span
                                                                        class=" js-font-resize text-primary">{{ trans('public.paid') }}</span>
                                                                @else
                                                                    <span
                                                                        class=" js-font-resize {{ $isOverdue ? 'text-danger' : 'text-dark' }}">{{ trans('update.unpaid') }}
                                                                        {{ $isOverdue ? '(' . trans('update.overdue') . ')' : '' }}</span>
                                                                @endif
                                                            </td>
                                                            <td class=" js-font-resize text-right text-dark">
                                                                @if (empty($stepPayment))
                                                                    @if (!in_array($order->status, ['refunded', 'canceled']) or $order->isCompleted())
                                                                        <div class=" js-font-resize btn-group dropdown table-actions">
                                                                            <a href="/panel/financial/installments/{{ $order->id }}/steps/{{ $step->id }}/pay"
                                                                                target="_blank"
                                                                                class=" js-font-resize btn btn-primary">{{ trans('panel.pay') }}</a>

                                                                        </div>
                                                                    @endif
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
                        </div>
                    </div>
                @endif
            @endforeach

            <div class=" js-font-resize my-30">
                {{ $orders->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        @else
            @include('web.default.includes.no-result', [
                'file_name' => 'webinar.png',
                'title' => trans('update.you_not_have_any_installment'),
                'hint' => trans('update.you_not_have_any_installment_hint'),
            ])
        @endif
    </section>
@endsection

@push('scripts_bottom')
@endpush
