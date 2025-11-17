@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ trans('admin/main.sales') }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{ trans('admin/main.sales') }}</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class=" js-font-resize card card-statistic-1">
                        <div class=" js-font-resize card-icon bg-success">
                            <i class=" js-font-resize fas fa-check-circle"></i>
                        </div>
                        <div class=" js-font-resize card-wrap">
                            <div class=" js-font-resize card-header">
                                <h4>{{trans('update.total_success_orders')}}</h4>
                            </div>
                            <div class=" js-font-resize card-body">
                                {{ $successOrders['count'] }}
                            </div>
                            <div class=" js-font-resize text-primary font-weight-bold">
                                {{ handlePrice($successOrders['amount']) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class=" js-font-resize card card-statistic-1">
                        <div class=" js-font-resize card-icon bg-danger">
                            <i class=" js-font-resize fas fa-times-circle"></i></div>
                        <div class=" js-font-resize card-wrap">
                            <div class=" js-font-resize card-header">
                                <h4>{{trans('update.total_canceled_orders')}}</h4>
                            </div>
                            <div class=" js-font-resize card-body">
                                {{ $canceledOrders['count'] }}
                            </div>
                            <div class=" js-font-resize text-success font-weight-bold">
                                {{ handlePrice($canceledOrders['amount']) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class=" js-font-resize card card-statistic-1">
                        <div class=" js-font-resize card-icon bg-warning">
                            <i class=" js-font-resize fas fa-hourglass-half"></i></div>
                        <div class=" js-font-resize card-wrap">
                            <div class=" js-font-resize card-header">
                                <h4>{{trans('update.total_waiting_orders')}}</h4>
                            </div>
                            <div class=" js-font-resize card-body">
                                {{ $waitingOrders['count'] }}
                            </div>
                            <div class=" js-font-resize text-danger font-weight-bold">
                                {{ handlePrice($waitingOrders['amount']) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class=" js-font-resize card card-statistic-1">
                        <div class=" js-font-resize card-icon bg-primary">
                            <i class=" js-font-resize fas fa-shopping-basket"></i></div>
                        <div class=" js-font-resize card-wrap">
                            <div class=" js-font-resize card-header">
                                <h4>{{trans('update.total_orders')}}</h4>
                            </div>
                            <div class=" js-font-resize card-body">
                                {{ $totalOrders['count'] }}
                            </div>
                            <div class=" js-font-resize text-danger font-weight-bold">
                                {{ handlePrice($totalOrders['amount']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <section class=" js-font-resize card">
                <div class=" js-font-resize card-body">
                    <form method="get" class=" js-font-resize mb-0">
                        <div class=" js-font-resize row">
                            <div class=" js-font-resize col-md-3">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{ trans('admin/main.search') }}</label>
                                    <input type="text" class=" js-font-resize form-control" name="item_title" value="{{ request()->get('item_title') }}">
                                </div>
                            </div>


                            <div class=" js-font-resize col-md-3">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{ trans('admin/main.start_date') }}</label>
                                    <div class=" js-font-resize input-group">
                                        <input type="date" id="fsdate" class=" js-font-resize text-center form-control" name="from" value="{{ request()->get('from') }}" placeholder="Start Date">
                                    </div>
                                </div>
                            </div>
                            <div class=" js-font-resize col-md-3">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{ trans('admin/main.end_date') }}</label>
                                    <div class=" js-font-resize input-group">
                                        <input type="date" id="lsdate" class=" js-font-resize text-center form-control" name="to" value="{{ request()->get('to') }}" placeholder="End Date">
                                    </div>
                                </div>
                            </div>


                            <div class=" js-font-resize col-md-3">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{ trans('admin/main.status') }}</label>
                                    <select name="status" data-plugin-selectTwo class=" js-font-resize form-control populate">
                                        <option value="">{{ trans('admin/main.all_status') }}</option>
                                        @foreach(\App\Models\ProductOrder::$status as $str)
                                            @if($str != \App\Models\ProductOrder::$pending)
                                                <option value="{{ $str }}" @if(request()->get('status') == $str) selected @endif>{{ trans('update.product_order_status_'.$str) }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class=" js-font-resize col-md-4">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{ trans('admin/main.seller') }}</label>
                                    <select name="seller_ids[]" multiple="multiple" data-search-option="just_organization_and_teacher_role" class=" js-font-resize form-control search-user-select2"
                                            data-placeholder="{{ trans('update.search_seller') }}">

                                        @if(!empty($sellers) and $sellers->count() > 0)
                                            @foreach($sellers as $seller)
                                                <option value="{{ $seller->id }}" selected>{{ $seller->full_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>


                            <div class=" js-font-resize col-md-4">
                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{ trans('update.customer') }}</label>
                                    <select name="customer_ids[]" multiple="multiple" data-search-option="just_student_role" class=" js-font-resize form-control search-user-select2"
                                            data-placeholder="{{ trans('public.search_user') }}">

                                        @if(!empty($customers) and $customers->count() > 0)
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" selected>{{ $customer->full_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>


                            <div class=" js-font-resize col-md-3">
                                <div class=" js-font-resize form-group mt-1">
                                    <label class=" js-font-resize input-label mb-4"> </label>
                                    <input type="submit" class=" js-font-resize text-center btn btn-primary w-100" value="{{ trans('admin/main.show_results') }}">
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </section>

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-12">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-header">
                            @can('admin_store_products_orders_export')
                                <a href="{{ getAdminPanelUrl() }}/store/orders/export?{{ !empty($inHouseOrders) ? 'in-house-orders=true&' : '' }}{{ http_build_query(request()->all()) }}" class=" js-font-resize btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
                            @endcan
                        </div>

                        <div class=" js-font-resize card-body">
                            <div class=" js-font-resize table-responsive">
                                <table class=" js-font-resize table table-striped font-14">
                                    <tr>
                                        <th>#</th>
                                        <th class=" js-font-resize text-left">{{ trans('update.customer') }}</th>
                                        <th class=" js-font-resize text-left">{{ trans('admin/main.seller') }}</th>
                                        <th>{{ trans('admin/main.type') }}</th>
                                        <th>{{ trans('update.quantity') }}</th>
                                        <th>{{ trans('admin/main.paid_amount') }}</th>
                                        <th>{{ trans('admin/main.discount') }}</th>
                                        <th>{{ trans('admin/main.tax') }}</th>
                                        <th>{{ trans('admin/main.date') }}</th>
                                        <th>{{ trans('admin/main.status') }}</th>
                                        <th width="120">{{ trans('admin/main.actions') }}</th>
                                    </tr>

                                    @foreach($orders as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>

                                            <td class=" js-font-resize text-left">
                                                @if(!empty($order->buyer))
                                                    {{ $order->buyer->full_name  }}
                                                    <div class=" js-font-resize text-primary text-small font-600-bold">ID : {{  $order->buyer->id }}</div>
                                                @elseif(!empty($order->gift) and !empty($order->gift))
                                                    {{ $order->gift->user->full_name }}
                                                    <div class=" js-font-resize text-primary text-small font-600-bold">ID : {{  $order->gift->user_id }}</div>
                                                    <span class=" js-font-resize d-block mt-1 text-muted font-12">{!! trans('update.a_gift_for_name_on_date',['name' => $order->gift->name, 'date' => (!empty($order->gift->date) ? dateTimeFormat($order->gift->date, 'j M Y H:i') : trans('update.instantly'))]) !!}</span>
                                                @endif
                                            </td>

                                            <td class=" js-font-resize text-left">
                                                {{ !empty($order->seller) ? $order->seller->full_name : '' }}
                                                <div class=" js-font-resize text-primary text-small font-600-bold">ID : {{  !empty($order->seller) ? $order->seller->id : '' }}</div>
                                            </td>

                                            <td>
                                                @if(!empty($order->product))
                                                    <span>{{ trans('update.product_type_'.$order->product->type) }}</span>
                                                @endif
                                            </td>

                                            <td>
                                                <span>{{ $order->quantity }}</span>
                                            </td>

                                            <td>
                                                @if(!empty($order->sale))
                                                    <span class=" js-font-resize ">{{ handlePrice($order->sale->total_amount) }}</span>
                                                @endif
                                            </td>

                                            <td>
                                                @if(!empty($order->sale))
                                                    <span class=" js-font-resize ">{{ handlePrice($order->sale->discount) }}</span>
                                                @endif
                                            </td>

                                            <td>
                                                @if(!empty($order->sale))
                                                    <span class=" js-font-resize ">{{ handlePrice($order->sale->tax) }}</span>
                                                @endif
                                            </td>

                                            <td>{{ dateTimeFormat($order->created_at, 'j F Y H:i') }}</td>

                                            <td>
                                                @if($order->status == \App\Models\ProductOrder::$waitingDelivery)
                                                    <span class=" js-font-resize text-warning">{{ trans('update.product_order_status_waiting_delivery') }}</span>
                                                @elseif($order->status == \App\Models\ProductOrder::$success)
                                                    <span class=" js-font-resize text-dark-blue">{{ trans('update.product_order_status_success') }}</span>
                                                @elseif($order->status == \App\Models\ProductOrder::$shipped)
                                                    <span class=" js-font-resize text-warning">{{ trans('update.product_order_status_shipped') }}</span>
                                                @elseif($order->status == \App\Models\ProductOrder::$canceled)
                                                    <span class=" js-font-resize text-danger">{{ trans('update.product_order_status_canceled') }}</span>
                                                @endif
                                            </td>

                                            <td>
                                                @can('admin_store_products_orders_invoice')
                                                    @if(!empty($order->product))
                                                        <a href="{{ getAdminPanelUrl() }}/store/orders/{{ $order->id }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><i class=" js-font-resize fa fa-print" aria-hidden="true"></i></a>
                                                    @endif
                                                @endcan

                                                @can('admin_store_products_orders_refund')
                                                    @include('admin.includes.delete_button',[
                                                            'url' => getAdminPanelUrl().'/store/orders/'. $order->id .'/refund',
                                                            'tooltip' => trans('admin/main.refund'),
                                                            'btnIcon' => 'fa-times-circle'
                                                        ])
                                                @endcan

                                                @if($order->status == \App\Models\ProductOrder::$waitingDelivery)
                                                    @can('admin_store_products_orders_tracking_code')
                                                        <button type="button"
                                                                data-sale-id="{{ $order->sale_id }}"
                                                                data-product-order-id="{{ $order->id }}"
                                                                data-toggle="tooltip" title="{{ trans('update.enter_tracking_code') }}"
                                                                class=" js-font-resize js-enter-tracking-code btn-transparent text-primary">
                                                            <i class=" js-font-resize fa fa-map"></i>
                                                        </button>
                                                    @endcan
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach

                                </table>
                            </div>
                        </div>

                        <div class=" js-font-resize card-footer text-center">
                            {{ $orders->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('scripts_bottom')
    <script>
        var enterTrackingCodeModalTitleLang = '{{ trans('update.enter_tracking_code') }}';
        var trackingCodeLang = '{{ trans('update.tracking_code') }}';
        var addressLang = '{{ trans('update.address') }}';
        var saveLang = '{{ trans('public.save') }}';
        var closeLang = '{{ trans('public.close') }}';
        var trackingCodeSaveSuccessLang = '{{ trans('update.tracking_code_success_save') }}';
    </script>

    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/js/admin/store/orders.min.js"></script>
@endpush
