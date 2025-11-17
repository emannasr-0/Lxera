<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ $pageTitle ?? '' }} </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- General CSS File -->
    <link rel="stylesheet" href="/assets/admin/vendor/bootstrap/bootstrap.min.css"/>
    <link rel="stylesheet" href="/assets/vendors/fontawesome/css/all.min.css"/>


    <link rel="stylesheet" href="/assets/admin/css/style.css">
    <link rel="stylesheet" href="/assets/admin/css/custom.css">
    <link rel="stylesheet" href="/assets/admin/css/components.css">

    <style>
        {!! !empty(getCustomCssAndJs('css')) ? getCustomCssAndJs('css') : '' !!}
    </style>
</head>
<body>

<div id="app">
    <section class=" js-font-resize section">
        <div class=" js-font-resize container mt-5">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-10 offset-md-1 col-lg-10 offset-lg-1">

                    <div class=" js-font-resize card card-primary">
                        <div class=" js-font-resize row m-0">
                            <div class=" js-font-resize col-12 col-md-12">
                                <div class=" js-font-resize card-body">

                                    <div class=" js-font-resize section-body">
                                        <div class=" js-font-resize invoice">
                                            <div class=" js-font-resize invoice-print">
                                                <div class=" js-font-resize row">
                                                    <div class=" js-font-resize col-lg-12">
                                                        <div class=" js-font-resize invoice-title">
                                                            <h2>{{ $generalSettings['site_name'] }}</h2>
                                                            <div class=" js-font-resize invoice-number">{{ trans('public.item_id') }}: #{{ $order->product_id }}</div>
                                                        </div>
                                                        <hr>
                                                        <div class=" js-font-resize row">
                                                            <div class=" js-font-resize col-md-6">
                                                                <address>
                                                                    <strong>{{ trans('admin/main.buyer') }}:</strong>
                                                                    <br>
                                                                    {{ $buyer->full_name }}
                                                                </address>

                                                                <address class=" js-font-resize mt-2">
                                                                    <strong>{{ trans('update.buyer_address') }}:</strong><br>
                                                                    {{ $buyer->getAddress(true) }}
                                                                </address>
                                                            </div>
                                                            <div class=" js-font-resize col-md-6 text-md-right">
                                                                <address>
                                                                    <strong>{{ trans('home.platform_address') }}:</strong><br>
                                                                    {!! nl2br(getContactPageSettings('address')) !!}
                                                                </address>
                                                            </div>
                                                        </div>
                                                        <div class=" js-font-resize row">
                                                            <div class=" js-font-resize col-md-6">
                                                                <address>
                                                                    <strong>{{ trans('admin/main.seller') }}:</strong><br>
                                                                    {{ $seller->full_name }}
                                                                </address>
                                                            </div>

                                                            <div class=" js-font-resize col-md-6 text-md-right">
                                                                <address>
                                                                    <strong>{{ trans('panel.purchase_date') }}:</strong><br>
                                                                    {{ dateTimeFormat($sale->created_at,'Y M j | H:i') }}<br><br>
                                                                </address>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class=" js-font-resize row mt-4">
                                                    <div class=" js-font-resize col-md-12">
                                                        <div class=" js-font-resize section-title">{{ trans('home.order_summary') }}</div>
                                                        <div class=" js-font-resize table-responsive">
                                                            <table class=" js-font-resize table table-striped table-hover table-md">
                                                                <tr>
                                                                    <th class=" js-font-resize text-center">{{ trans('admin/main.item') }}</th>
                                                                    <th class=" js-font-resize text-center">{{ trans('update.quantity') }}</th>
                                                                    <th class=" js-font-resize text-center">{{ trans('public.price') }}</th>
                                                                    <th class=" js-font-resize text-center">{{ trans('panel.discount') }}</th>
                                                                    <th class=" js-font-resize text-center">{{ trans('update.delivery_fee') }}</th>
                                                                    <th class=" js-font-resize text-right">{{ trans('cart.total') }}</th>
                                                                </tr>

                                                                <tr>
                                                                    <td class=" js-font-resize text-center">
                                                                        <span>{{ !empty($product) ? $product->title : trans('update.delete_item') }}</span>
                                                                        @if(!empty($order->specifications))
                                                                            (
                                                                            <div class=" js-font-resize d-inline-block">
                                                                                @foreach(json_decode($order->specifications,true) as $specificationKey => $specificationValue)
                                                                                    <span>{{ str_replace('_',' ',$specificationValue) }}{{ (!$loop->last) ? ', ' : '' }}</span>
                                                                                @endforeach
                                                                            </div>)
                                                                        @endif
                                                                    </td>
                                                                    <td class=" js-font-resize text-center">{{ $order->quantity }} {{ trans('cart.item') }}</td>

                                                                    <td class=" js-font-resize text-center">
                                                                        @if(!empty($sale->amount))
                                                                            {{ handlePrice($sale->amount) }}
                                                                        @else
                                                                            {{ trans('public.free') }}
                                                                        @endif
                                                                    </td>
                                                                    <td class=" js-font-resize text-center">
                                                                        @if(!empty($sale->discount))
                                                                            {{ handlePrice($sale->discount) }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </td>
                                                                    <td class=" js-font-resize text-center">
                                                                        @if(!empty($sale->product_delivery_fee))
                                                                            {{ handlePrice($sale->product_delivery_fee) }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </td>
                                                                    <td class=" js-font-resize text-right">
                                                                        @if(!empty($sale->total_amount))
                                                                            {{ handlePrice($sale->total_amount) }}
                                                                        @else
                                                                            0
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class=" js-font-resize row mt-4">

                                                            <div class=" js-font-resize col-lg-6 text-left">
                                                                <div class=" js-font-resize invoice-detail-item">
                                                                    <div class=" js-font-resize invoice-detail-name">{{ trans('admin/main.item') }}</div>
                                                                    <div class=" js-font-resize invoice-detail-value">{{ !empty($product) ? $product->title : trans('update.delete_item') }} {{ !empty($order->gift_id) ? "(".trans('update.gift').")" : '' }}</div>
                                                                </div>

                                                                <div class=" js-font-resize invoice-detail-item">
                                                                    <div class=" js-font-resize invoice-detail-name">{{ trans('update.quantity') }}</div>
                                                                    <div class=" js-font-resize invoice-detail-value">{{ $order->quantity }} {{ trans('cart.item') }}</div>
                                                                </div>

                                                                @if(!empty($order->specifications))
                                                                    <div class=" js-font-resize invoice-detail-item">
                                                                        <div class=" js-font-resize invoice-detail-name">{{ trans('update.specifications') }}</div>

                                                                        @foreach(json_decode($order->specifications,true) as $specificationKey => $specificationValue)
                                                                            <div class=" js-font-resize invoice-detail-value">
                                                                                <span class=" js-font-resize ">{{ $specificationKey }}</span>
                                                                                <span class=" js-font-resize ml-3">{{ str_replace('_',' ',$specificationValue) }}</span>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @endif

                                                                @if(!empty($order->message_to_seller))
                                                                    <div class=" js-font-resize invoice-detail-item">
                                                                        <div class=" js-font-resize invoice-detail-name">{{ trans('update.message_to_seller') }}</div>
                                                                        <div class=" js-font-resize invoice-detail-value">{!! $order->message_to_seller !!}</div>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class=" js-font-resize col-lg-6 text-right">
                                                                <div class=" js-font-resize invoice-detail-item">
                                                                    <div class=" js-font-resize invoice-detail-name">{{ trans('cart.sub_total') }}</div>
                                                                    <div class=" js-font-resize invoice-detail-value">{{ handlePrice($sale->amount) }}</div>
                                                                </div>
                                                                <div class=" js-font-resize invoice-detail-item">
                                                                    <div class=" js-font-resize invoice-detail-name">{{ trans('cart.tax') }} @if(!empty($product))
                                                                            ({{ $product->getTax() }}%)
                                                                        @endif</div>
                                                                    <div class=" js-font-resize invoice-detail-value">
                                                                        @if(!empty($sale->tax))
                                                                            {{ handlePrice($sale->tax) }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class=" js-font-resize invoice-detail-item">
                                                                    <div class=" js-font-resize invoice-detail-name">{{ trans('public.discount') }}</div>
                                                                    <div class=" js-font-resize invoice-detail-value">
                                                                        @if(!empty($sale->discount))
                                                                            {{ handlePrice($sale->discount) }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                <div class=" js-font-resize invoice-detail-item">
                                                                    <div class=" js-font-resize invoice-detail-name">{{ trans('update.delivery_fee') }}</div>
                                                                    <div class=" js-font-resize invoice-detail-value">
                                                                        @if(!empty($sale->product_delivery_fee))
                                                                            {{ handlePrice($sale->product_delivery_fee) }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <hr class=" js-font-resize mt-2 mb-2">
                                                                <div class=" js-font-resize invoice-detail-item">
                                                                    <div class=" js-font-resize invoice-detail-name">{{ trans('cart.total') }}</div>
                                                                    <div class=" js-font-resize invoice-detail-value invoice-detail-value-lg">
                                                                        @if(!empty($sale->total_amount))
                                                                            {{ handlePrice($sale->total_amount) }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class=" js-font-resize text-md-right">

                                                <button type="button" onclick="window.print()" class=" js-font-resize btn btn-warning btn-icon icon-left"><i class=" js-font-resize fas fa-print"></i> Print</button>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>
</body>
