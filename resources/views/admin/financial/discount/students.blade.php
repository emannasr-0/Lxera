@extends('admin.layouts.app')


@php
    $filters = request()->getQueryString();
@endphp

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>قائمة بمستخدمي الكوبون ({{ $discount->code }})</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">مستخدمي الكوبون</div>
            </div>
        </div>

        <div class="section-body">

            {{-- search --}}
            <section class="card">
                <div class="card-body">
                    <form method="get" class="mb-0">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">كود الطالب</label>
                                    <input name='user_code' type="text" class="form-control"
                                        value="{{ request()->get('user_code') }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">اسم الطالب</label>
                                    <input name='user_name' type="text" class="form-control"
                                        value="{{ request()->get('user_name') }}">
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">بريد الطالب</label>
                                    <input name="email" type="text" class="form-control"
                                        value="{{ request()->get('email') }}">
                                </div>
                            </div>

                            {{-- <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">نوع المبيعة</label>
                                    <select name="type" data-plugin-selectTwo class="form-control populate">
                                        <option value="">{{ trans('admin/main.all_status') }}</option>
                                        <option value="form_fee" @if (request()->get('type') == 'form_fee') selected @endif>
                                            رسوم حجز مقعد
                                        </option>
                                        <option value="bundle" @if (request()->get('type') == 'bundle') selected @endif>
                                            دفع كامل الرسوم
                                        </option>
                                        <option value="upfront" @if (request()->get('type') == 'upfront') selected @endif>
                                            قسط التسجيل
                                        </option>

                                        <option value="installment_payment"
                                            @if (request()->get('type') == 'installment_payment') selected @endif>
                                            اقساط
                                        </option>
                                        <option value="webinar" @if (request()->get('type') == 'webinar') selected @endif>
                                            دورة
                                        </option>
                                        <option value="service" @if (request()->get('type') == 'service') selected @endif>
                                            خدمات الكترونية
                                        </option>
                                        <option value="scholarship" @if (request()->get('type') == 'scholarship') selected @endif>
                                            منح دراسية
                                        </option>
                                    </select>
                                </div>
                            </div> --}}

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">عنوان البرنامج</label>
                                    <input name="bundle_title" class="form-control"  value="{{ request()->get('bundle_title') }}">
                                </div>
                            </div>

                            {{-- <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.status') }}</label>
                                    <select name="status" data-plugin-selectTwo class="form-control populate">
                                        <option value="">{{ trans('admin/main.all_status') }}</option>
                                        <option value="success" @if (request()->get('status') == 'success') selected @endif>
                                            {{ trans('admin/main.success') }}</option>
                                        <option value="refund" @if (request()->get('status') == 'refund') selected @endif>
                                            {{ trans('admin/main.refund') }}</option>

                                        <option value="blocked" @if (request()->get('status') == 'blocked') selected @endif>
                                            {{ trans('update.access_blocked') }}</option>

                                    </select>
                                </div>
                            </div> --}}

                            {{-- <div class="col-12 row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.start_date') }}</label>
                                        <div class="input-group">
                                            <input type="date" id="from" class="text-center form-control"
                                                name="from" value="{{ request()->get('from') }}"
                                                placeholder="Start Date">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.end_date') }}</label>
                                        <div class="input-group">
                                            <input type="date" id="to" class="text-center form-control"
                                                name="to" value="{{ request()->get('to') }}"
                                                placeholder="End Date">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mt-1">
                                        <label class="input-label mb-4"> </label>
                                        <input type="submit" class="text-center btn btn-primary w-100"
                                            value="{{ trans('admin/main.show_results') }}">
                                    </div>
                                </div>
                            </div> --}}

                             <div class="col-md-4">
                                    <div class="form-group mt-1">
                                        <label class="input-label mb-4"> </label>
                                        <input type="submit" class="text-center btn btn-primary w-100"
                                            value="{{ trans('admin/main.show_results') }}">
                                    </div>
                                </div>
                        </div>

                    </form>
                </div>
            </section>

            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        {{-- <div class="card-header">
                            @can('admin_sales_export')
                                <a href="{{ getAdminPanelUrl() }}/financial/sales/export?{{ $filters }}"
                                    class="btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
                            @endcan
                        </div> --}}

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <tr>
                                        <th>#</th>
                                        <th class="text-left">{{ trans('admin/main.student') }}</th>
                                        {{-- <th class="text-left">{{ trans('admin/main.instructor') }}</th>  --}}
                                        <th>{{ trans('admin/main.paid_amount') }}</th>
                                        <th>{{ trans('admin/main.discount') }}</th>
                                        <th class="text-left">{{ trans('admin/main.item') }}</th>
                                        <th>{{ trans('admin/main.sale_type') }}</th>
                                        <th>{{ trans('admin/main.date') }}</th>
                                        <th>{{ trans('admin/main.status') }}</th>
                                        <th width="120">{{ trans('admin/main.actions') }}</th>
                                    </tr>

                                    @foreach ($sales as $index => $sale)
                                        <tr>
                                            <td>{{ ++$index }}</td>

                                            <td class="text-left">
                                                {{ !empty($sale->buyer) ? $sale->buyer->full_name : '' }}
                                                <div class="text-primary text-small font-600-bold">
                                                    {{ !empty($sale->buyer) ? $sale->buyer->email : '' }}</div>

                                                <div class="text-primary text-small font-600-bold">ID :
                                                    {{ !empty($sale->buyer) ? $sale->buyer->id : '' }}</div>
                                                <div class="text-primary text-small font-600-bold">Code :
                                                    {{ !empty($sale->buyer) ? $sale->buyer->user_code : '' }}</div>
                                            </td>


                                            <td>
                                                @if ($sale->payment_method == \App\Models\Sale::$subscribe)
                                                    <span class="">{{ trans('admin/main.subscribe') }}</span>
                                                @else
                                                    @if (!empty($sale->total_amount))
                                                        <span
                                                            class="">{{ handlePrice($sale->total_amount ?? 0) }}</span>
                                                    @else
                                                        <span class="">{{ trans('public.free') }}</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <span class="">{{ handlePrice($sale->discount ?? 0)  }} {{ $sale->discount>0 ? '('.$sale->order->orderItems[0]->getDiscount->percent . '%)' ?? ''  : '' }} </span>
                                            </td>

                                            <td class="text-left">
                                                <div class="media-body">
                                                    <div>{{ $sale->item_title }}</div>
                                                    @if (!empty($sale->transform_bundle_id) && $sale->type != 'transform_bundle')
                                                        <div class="text-center font-weight-500 text-success">محول</div>
                                                    @endif
                                                    <div class="text-primary text-small font-600-bold">ID :
                                                        {{ $sale->item_id }}</div>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="font-weight-bold">
                                                    @if ($sale->type == \App\Models\Sale::$registrationPackage)
                                                        {{ trans('update.registration_package') }}
                                                    @elseif($sale->type == \App\Models\Sale::$product)
                                                        {{ trans('update.product') }}
                                                    @elseif($sale->type == \App\Models\Sale::$bundle)
                                                        @if ($sale->payment_method == 'scholarship')
                                                            منحة دراسية
                                                        @else
                                                            دفع كامل الرسوم
                                                        @endif
                                                    @elseif($sale->type == \App\Models\Sale::$gift)
                                                        {{ trans('update.gift') }}
                                                    @elseif($sale->type == \App\Models\Sale::$installmentPayment)
                                                        {{ $sale->order->orderItems->first()->installmentPayment->step->installmentStep->title ?? 'قسط التسجيل' }}
                                                    @elseif($sale->type == 'form_fee')
                                                        رسوم حجز مقعد
                                                    @elseif($sale->type == 'certificate')
                                                        شراء شهادة
                                                    @elseif($sale->type == 'webinar')
                                                        دورة
                                                    @elseif($sale->type == 'service')
                                                        خدمة الكترونية
                                                    @else
                                                        {{ trans('update.'.$sale->type) }}
                                                    @endif
                                                </span>
                                            </td>

                                            <td>{{ dateTimeFormat($sale->created_at, 'j F Y H:i') }}</td>

                                            <td>
                                                @if (!empty($sale->refund_at))
                                                    <span class="text-warning">{{ trans('admin/main.refund') }}</span>
                                                    @include('admin.includes.message_button', [
                                                                    'url' => '#',
                                                                    'btnClass' => 'd-flex align-items-center mt-1',
                                                                    'btnText' =>
                                                                        '<span class="ml-2">' .
                                                                        ' سبب الإستيرداد</span>',
                                                                    'hideDefaultClass' => true,
                                                                    'deleteConfirmMsg' => 'سبب  طلب الإستيرداد',
                                                                    'message' => $sale->message,
                                                                    'id' => $sale->id,
                                                                ])
                                                @elseif(!$sale->access_to_purchased_item)
                                                    <span class="text-danger">{{ trans('update.access_blocked') }}</span>
                                                @else
                                                    <span class="text-success">{{ trans('admin/main.success') }}</span>
                                                @endif
                                            </td>

                                            <td>
                                                @can('admin_sales_invoice')
                                                    @if (!empty($sale->webinar_id))
                                                        <a href="{{ getAdminPanelUrl() }}/financial/sales/{{ $sale->id }}/invoice"
                                                            target="_blank" title="{{ trans('admin/main.invoice') }}"><i
                                                                class="fa fa-print" aria-hidden="true"></i></a>
                                                    @endif
                                                @endcan

                                                @can('admin_sales_refund')
                                                    @if (empty($sale->refund_at) and $sale->payment_method != \App\Models\Sale::$subscribe)
                                                        @include('admin.includes.send_message', [
                                                            'url' =>
                                                                getAdminPanelUrl() .
                                                                '/financial/sales/' .
                                                                $sale->id .
                                                                '/refund',
                                                            'tooltip' => trans('admin/main.refund'),
                                                            'btnIcon' => 'fa-times-circle',
                                                            'id' => $sale->id,
                                                            'title' =>"تأكيد طلب الإسترداد",
                                                            'subTitle' => "اذكر سبب الاستيرداد"
                                                        ])
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach

                                </table>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            {{ $sales->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
