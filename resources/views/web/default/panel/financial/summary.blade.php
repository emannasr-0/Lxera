@extends(getTemplate() .'.panel.layouts.panel_layout')


@section('content')
    @if($accountings->count() > 0)
        <section>
            <h2 class=" js-font-resize section-title">{{ trans('financial.financial_documents') }}</h2>

            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('public.title') }}</th>
                                    <th>{{ trans('public.description') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('panel.amount') }} ({{ $currency }})</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.creator') }}</th>
                                    <th class=" js-font-resize text-center">{{ trans('public.date') }}</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($accountings as $accounting)
                                    <tr>
                                        <td class=" js-font-resize text-left">
                                            <div class=" js-font-resize d-flex flex-column">
                                                <div class=" js-font-resize font-14 font-weight-500">
                                                    @if($accounting->is_cashback)
                                                        {{ trans('update.cashback') }}
                                                    @elseif(!empty($accounting->webinar_id) and !empty($accounting->webinar))
                                                        {{ $accounting->webinar->title }}
                                                    @elseif(!empty($accounting->bundle_id) and !empty($accounting->bundle))
                                                        {{ $accounting->bundle->title }}
                                                    @elseif(!empty($accounting->product_id) and !empty($accounting->product))
                                                        {{ $accounting->product->title }}
                                                    @elseif(!empty($accounting->meeting_time_id))
                                                        {{ trans('meeting.reservation_appointment') }}
                                                    @elseif(!empty($accounting->subscribe_id) and !empty($accounting->subscribe))
                                                        {{ $accounting->subscribe->title }}
                                                    @elseif(!empty($accounting->promotion_id) and !empty($accounting->promotion))
                                                        {{ $accounting->promotion->title }}
                                                    @elseif(!empty($accounting->registration_package_id) and !empty($accounting->registrationPackage))
                                                        {{ $accounting->registrationPackage->title }}
                                                    @elseif(!empty($accounting->installment_payment_id))
                                                        {{ trans('update.installment') }}
                                                    @elseif($accounting->store_type == \App\Models\Accounting::$storeManual)
                                                        {{ trans('financial.manual_document') }}
                                                    @elseif($accounting->type == \App\Models\Accounting::$addiction and $accounting->type_account == \App\Models\Accounting::$asset)
                                                        {{ trans('financial.charge_account') }}
                                                    @elseif($accounting->type == \App\Models\Accounting::$deduction and $accounting->type_account == \App\Models\Accounting::$income)
                                                        {{ trans('financial.payout') }}
                                                    @elseif($accounting->is_registration_bonus)
                                                        {{ trans('update.registration_bonus') }}
                                                    @else
                                                        ---
                                                    @endif
                                                </div>

                                                @if(!empty($accounting->gift_id) and !empty($accounting->gift))
                                                    <div class=" js-font-resize text-gray font-12">{!! trans('update.a_gift_for_name_on_date',['name' => $accounting->gift->name, 'date' => dateTimeFormat($accounting->gift->date, 'j M Y H:i')]) !!}</div>
                                                @endif

                                                <div class=" js-font-resize font-12 text-gray">
                                                    @if(!empty($accounting->webinar_id) and !empty($accounting->webinar))
                                                        #{{ $accounting->webinar->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->webinar->title : '' }}
                                                    @elseif(!empty($accounting->bundle_id) and !empty($accounting->bundle))
                                                        #{{ $accounting->bundle->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->bundle->title : '' }}
                                                    @elseif(!empty($accounting->product_id) and !empty($accounting->product))
                                                        #{{ $accounting->product->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->product->title : '' }}
                                                    @elseif(!empty($accounting->meeting_time_id) and !empty($accounting->meetingTime))
                                                        {{ $accounting->meetingTime->meeting->creator->full_name }}
                                                    @elseif(!empty($accounting->subscribe_id) and !empty($accounting->subscribe))
                                                        {{ $accounting->subscribe->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->subscribe->title : '' }}
                                                    @elseif(!empty($accounting->promotion_id) and !empty($accounting->promotion))
                                                        {{ $accounting->promotion->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->promotion->title : '' }}
                                                    @elseif(!empty($accounting->registration_package_id) and !empty($accounting->registrationPackage))
                                                        {{ $accounting->registrationPackage->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->registrationPackage->title : '' }}
                                                    @elseif(!empty($accounting->installment_payment_id))
                                                        @php
                                                            $installmentItemTitle = "--";
                                                            $installmentOrderPayment = $accounting->installmentOrderPayment;

                                                            if (!empty($installmentOrderPayment)) {
                                                                $installmentOrder = $installmentOrderPayment->installmentOrder;
                                                                if (!empty($installmentOrder)) {
                                                                    $installmentItem = $installmentOrder->getItem();
                                                                    if (!empty($installmentItem)) {
                                                                        $installmentItemTitle = $installmentItem->title;
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        {{ $installmentItemTitle }}
                                                    @else
                                                        ---
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class=" js-font-resize text-left align-middle">
                                            <span class=" js-font-resize font-weight-500 text-gray">{{ $accounting->description }}</span>
                                        </td>
                                        <td class=" js-font-resize text-center align-middle">
                                            @switch($accounting->type)
                                                @case(\App\Models\Accounting::$addiction)
                                                    <span class=" js-font-resize font-16 font-weight-bold text-primary">+{{ handlePrice($accounting->amount, false) }}</span>
                                                    @break;
                                                @case(\App\Models\Accounting::$deduction)
                                                    <span class=" js-font-resize font-16 font-weight-bold text-danger">-{{ handlePrice($accounting->amount, false) }}</span>
                                                    @break;
                                            @endswitch
                                        </td>
                                        <td class=" js-font-resize text-center align-middle">{{ trans('public.'.$accounting->store_type) }}</td>
                                        <td class=" js-font-resize text-center align-middle">
                                            <span>{{ dateTimeFormat($accounting->created_at, 'j M Y') }}</span>
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
    @else

        @include(getTemplate() . '.includes.no-result',[
            'file_name' => 'financial.png',
            'title' => trans('financial.financial_summary_no_result'),
            'hint' => nl2br(trans('financial.financial_summary_no_result_hint')),
        ])
    @endif
    <div class=" js-font-resize my-30">
        {{ $accountings->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection
