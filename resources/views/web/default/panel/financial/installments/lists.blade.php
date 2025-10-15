@extends(getTemplate() . '.panel.layouts.panel_layout')

@section('content')

    @if (!empty($overdueInstallmentsCount) and $overdueInstallmentsCount > 0)
        <div class="d-flex align-items-center mb-20 mt-40 p-15 danger-transparent-alert">
            <div class="danger-transparent-alert__icon d-flex align-items-center justify-content-center">
                <i data-feather="credit-card" width="18" height="18" class=""></i>
            </div>
            <div class="ml-10">
                <div class="font-14 font-weight-bold ">{{ trans('update.overdue_installments') }}</div>
                <div class="font-12 ">
                    {{ trans('update.you_have_count_overdue_installments_please_pay_them_to_avoid_restrictions_and_negative_effects_on_your_account', ['count' => $overdueInstallmentsCount]) }}
                </div>
            </div>
        </div>
    @endif

    {{-- Installments Overview --}}

    {{-- <section>
        <h2 class="section-title">{{ trans('update.installments_overview') }}</h2>

        <div class="activities-container mt-25 p-20 p-lg-35">
            <div class="row">
                <div class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/129.png" width="64" height="64" alt="">
                        <strong class="font-30 text-light font-weight-bold mt-5">{{ $openInstallmentsCount }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('update.open_installments') }}</span>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/130.png" width="64" height="64" alt="">
                        <strong class="font-30 text-light font-weight-bold mt-5">{{ $pendingVerificationCount }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('update.pending_verification') }}</span>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/127.png" width="64" height="64" alt="">
                        <strong class="font-30 text-light font-weight-bold mt-5">{{ $finishedInstallmentsCount }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('update.finished_installments') }}</span>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/128.png" width="64" height="64" alt="">
                        <strong class="font-30 text-light font-weight-bold mt-5">{{ $overdueInstallmentsCount }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('update.overdue_installments') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section> --}}


    <section class="mt-25">
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">{{ trans('update.my_installments') }}</h2>
        </div>

        @if (!empty($orders) and count($orders))
            @foreach ($orders as $order)
                @php
                    $orderItem = $order->getItem();
                    $itemType = $order->getItemType();
                    $itemPrice = $order->getItemPrice();
                @endphp

                @if (!empty($orderItem))
                    <div class="row mt-30">
                        <div class="col-12">
                            <div class="webinar-card webinar-list panel-installment-card d-flex p-1 shadow border">
                                <div class="bg-secondary-acadima p-15">
                                    {{-- @if (in_array($itemType, ['course', 'bundle']))
                                        <img src="{{ $orderItem->getImage() }}" class="img-cover" alt="">
                                    @elseif($itemType == 'product')
                                        <img src="{{ $orderItem->thumbnail }}" class="img-cover" alt="">
                                    @elseif($itemType == "subscribe")
                                        <div class="d-flex align-items-center justify-content-center w-100 h-100">
                                            <img src="/assets/default/img/icons/installment/subscribe_default.svg" alt="">
                                        </div>
                                    @elseif($itemType == "registrationPackage")
                                        <div class="d-flex align-items-center justify-content-center w-100 h-100">
                                            <img src="/assets/default/img/icons/installment/reg_package_default.svg" alt="">
                                        </div>
                                    @endif --}}

                                    @if ($order->isCompleted())
                                        <span class="badge badge-secondary text-light">{{ trans('update.completed') }}</span>
                                    @elseif($order->status == 'open')
                                        <span class="badge badge-primary text-light">{{ trans('public.open') }}</span>
                                    @elseif($order->status == 'rejected')
                                        <span class="badge badge-danger text-light">{{ trans('public.rejected') }}</span>
                                    @elseif($order->status == 'canceled')
                                        <span class="badge badge-danger text-light">{{ trans('public.canceled') }}</span>
                                    @elseif($order->status == 'pending_verification')
                                        <span class="badge badge-warning text-light">{{ trans('update.pending_verification') }}</span>
                                    @elseif($order->status == 'refunded')
                                        <span class="badge badge-secondary text-light">{{ trans('update.refunded') }}</span>
                                    @endif
                                </div>

                                <div class="webinar-card-body w-100 d-flex flex-column bg-secondary-acadima">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center text-pink">
                                            <h3 class="font-16  font-weight-bold">{{ $orderItem->title }}</h3>

                                            @if ($order->has_overdue)
                                                <span
                                                    class="badge badge-outlined-danger ml-10">{{ trans('update.overdue') }}</span>
                                            @endif
                                        </div>

                                        @if (!in_array($order->status, ['refunded', 'canceled']) or $order->isCompleted())
                                            <div class="btn-group dropdown table-actions">
                                                {{-- <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button> --}}
                                                <div class="dropdown-menu ">

                                                    {{-- @if ($order->status == 'open')
                                                        <a href="/panel/financial/installments/{{ $order->id }}/pay_upcoming_part" target="_blank" class="webinar-actions d-block mt-10">{{ trans('update.pay_upcoming_part') }}</a>
                                                    @endif

                                                    @if (!in_array($order->status, ['refunded', 'canceled']))
                                                        <a href="/panel/financial/installments/{{ $order->id }}/details" target="_blank" class="webinar-actions d-block mt-10">{{ trans('update.view_details') }}</a>
                                                    @endif

                                                    @if ($itemType == 'course' and ($order->isCompleted() or $order->status == 'open'))
                                                        <a href="{{ $orderItem->getLearningPageUrl() }}" target="_blank" class="webinar-actions d-block mt-10">{{ trans('update.learning_page') }}</a>
                                                    @endif --}}

                                                    {{--
                                                        @if ($order->isCompleted() or $order->status == 'open')
                                                        <a href="/panel/financial/installments/{{ $order->id }}/refund" class="webinar-actions d-block mt-10 delete-action">{{ trans('update.refund') }}</a>
                                                        @endif
                                                    --}}

                                                    {{-- @if ($order->status == 'pending_verification' and getInstallmentsSettings('allow_cancel_verification'))
                                                        <a href="/panel/financial/installments/{{ $order->id }}/cancel" class="webinar-actions d-block mt-10 text-danger delete-action" data-title="{{ trans('public.deleteAlertHint') }}" data-confirm="{{ trans('update.yes_cancel') }}">{{ trans('public.cancel') }}</a>
                                                    @endif --}}
                                                </div>
                                            </div>
                                        @endif

                                    </div>

                                    <div class="d-flex align-items-center justify-content-between flex-wrap mt-45">
                                        {{-- <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class="stat-title">{{ trans('update.item_type') }}:</span>
                                            <span class="stat-value">{{ trans('update.item_type_'.$itemType) }}</span>
                                        </div> --}}

                                        <div class="d-flex align-items-start flex-column mt-5 mr-15">
                                            <span class="stat-title">{{ trans('panel.purchase_date') }}:</span>
                                            <span
                                                class="stat-value mt-20 text-dark">{{ dateTimeFormat($order->created_at, 'j M Y H:i') }}</span>
                                        </div>

                                        {{-- <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class="stat-title">{{ trans('update.upfront') }}:</span>
                                            <span class="stat-value">{{ !empty($order->selectedInstallment->upfront) ? handlePrice($order->selectedInstallment->getUpfront($itemPrice)) : '-' }}</span>
                                        </div> --}}

                                        <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class="stat-title">{{ trans('update.total_installments') }}:</span>
                                            <span
                                                class="stat-value mt-20 text-dark">{{ trans('update.total_parts_count', ['count' => $order->selectedInstallment->steps_count + 1]) }}
                                                ({{ handlePrice($order->selectedInstallment->totalPayments($itemPrice, false) + (!empty($order->selectedInstallment->upfront) ? $order->selectedInstallment->getUpfront($itemPrice) : 0)) }})
                                            </span>
                                        </div>

                                        @if ($order->status == 'open' or $order->status == 'pending_verification')
                                            <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                <span
                                                    class="stat-title">{{ trans('update.remained_installments') }}:</span>
                                                <span
                                                    class="stat-value mt-20 text-dark">{{ trans('update.total_parts_count', ['count' => $order->remained_installments_count]) }}
                                                    ({{ handlePrice($order->remained_installments_amount) }})</span>
                                            </div>

                                            @if (!empty($order->upcoming_installment))
                                                <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                    <span
                                                        class="stat-title">{{ trans('update.upcoming_installment') }}:</span>
                                                    <span class="stat-value mt-20 text-dark">
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
                                                <div class="d-flex align-items-start flex-column mt-20 mr-15">
                                                    <span
                                                        class="stat-title">{{ trans('update.overdue_installments') }}:</span>
                                                    <span class="stat-value mt-20 text-dark">{{ $order->overdue_count }}
                                                        ({{ handlePrice($order->overdue_amount) }})</span>
                                                </div>
                                            @endif
                                        @endif

                                    </div>

                                </div>

                            </div>
                            <div class="panel-section-card py-20 px-25 mt-20 shadow border">
                                <h3 class="font-16 text-pink font-weight-bold mb-20">{{trans('panel.installment_schedule')}}</h3>

                                <div class="row">
                                    <div class="col-12 ">
                                        <div class="table-responsive">
                                            <table class="table text-center custom-table">
                                                <thead>
                                                    <tr>
                                                        <th class="text-black">{{ trans('public.title') }}</th>
                                                        <th class="text-center text-black">{{ trans('panel.amount') }}</th>
                                                        <th class="text-center text-black">{{ trans('update.due_date') }}</th>
                                                        <th class="text-center text-black">{{ trans('update.payment_date') }}</th>
                                                        <th class="text-center text-black">{{ trans('public.status') }}</th>
                                                        <th class=""></th>
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
                                                            <td class="text-dark text-left">
                                                                <span
                                                                    class="font-16 font-weight-500 text-dark text-left">
                                                                    {{ trans('update.upfront') }}</span>
                                                                

                                                                @if ($order->selectedInstallment->upfront_type == 'percent')
                                                                    <span
                                                                        class="ml-5">({{ $order->selectedInstallment->upfront }}%)</span>
                                                                @endif
                                                            </td>

                                                            <td class="text-center text-dark">
                                                                {{ handlePrice($order->selectedInstallment->getUpfront($itemPrice)) }}
                                                            </td>

                                                            <td class="text-center text-dark">-</td>

                                                            <td class="text-center text-dark">
                                                                {{ !empty($upfrontPayment) ? dateTimeFormat($upfrontPayment->created_at, 'j M Y H:i') : '-' }}
                                                            </td>

                                                            <td class="text-center text-dark">
                                                                @if (!empty($upfrontPayment))
                                                                    <span
                                                                        class="text-primary">{{ trans('public.paid') }}</span>
                                                                @else
                                                                    <span
                                                                        class="text-danger">{{ trans('update.unpaid') }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-right text-dark">

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
                                                            <td class="text-left text-dark">
                                                                <div class="d-block font-16 font-weight-500 ">
                                                                    {{ $step->installmentStep->title }}

                                                                    @if ($step->amount_type == 'percent')
                                                                        <span
                                                                            class="ml-5 font-12 text-gray">({{ $step->amount }}%)</span>
                                                                    @endif
                                                                </div>
                                                                {{-- <span class="d-block font-12 text-gray">{{ $step->deadline }} أيام بعد بداية الدورة</span> --}}
                                                            </td>

                                                            <td class="text-center text-dark">
                                                                {{ handlePrice($step->getPrice($itemPrice)) }}
                                                            </td>

                                                            <td class="text-center text-dark">
                                                                <span
                                                                    class="{{ $isOverdue ? 'text-danger' : '' }}">{{ dateTimeFormat($dueAt, 'j M Y') }}</span>
                                                            </td>

                                                            <td class="text-center text-dark">
                                                                {{ !empty($stepPayment) ? dateTimeFormat($stepPayment->created_at, 'j M Y H:i') : '-' }}
                                                            </td>

                                                            <td class="text-center text-dark">
                                                                @if (!empty($stepPayment))
                                                                    <span
                                                                        class="text-primary">{{ trans('public.paid') }}</span>
                                                                @else
                                                                    <span
                                                                        class="{{ $isOverdue ? 'text-danger' : 'text-dark' }}">{{ trans('update.unpaid') }}
                                                                        {{ $isOverdue ? '(' . trans('update.overdue') . ')' : '' }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-right text-dark">
                                                                @if (empty($stepPayment))
                                                                    @if (!in_array($order->status, ['refunded', 'canceled']) or $order->isCompleted())
                                                                        <div class="btn-group dropdown table-actions">
                                                                            <a href="/panel/financial/installments/{{ $order->id }}/steps/{{ $step->id }}/pay"
                                                                                target="_blank"
                                                                                class="btn btn-primary">{{ trans('panel.pay') }}</a>

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

            <div class="my-30">
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
