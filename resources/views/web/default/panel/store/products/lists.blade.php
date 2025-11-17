@extends('web.default.panel.layouts.panel_layout')

@push('styles_top')

@endpush

@section('content')
    <section>
        <h2 class=" js-font-resize section-title">{{ trans('update.products_statistics') }}</h2>

        <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/webinars.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $physicalProducts }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.physical_products') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/hours.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $virtualProducts }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.virtual_products') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/sales.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ !empty($physicalSales) ? handlePrice($physicalSales) : 0 }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.physical_sales') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/download-sales.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ !empty($virtualSales) ? handlePrice($virtualSales) : 0 }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.virtual_sales') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class=" js-font-resize mt-25">
        <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class=" js-font-resize section-title">{{ trans('update.my_products') }}</h2>
        </div>

        @if(!empty($products) and !$products->isEmpty())
            @foreach($products as $product)

                @php
                    $hasDiscount = $product->getActiveDiscount();
                @endphp

                <div class=" js-font-resize row mt-30">
                    <div class=" js-font-resize col-12">
                        <div class=" js-font-resize webinar-card webinar-list panel-product-card d-flex">
                            <div class=" js-font-resize image-box">
                                <img src="{{ $product->thumbnail }}" class=" js-font-resize img-cover" alt="">

                                @if($product->ordering and !empty($product->inventory) and $product->getAvailability() < 1)
                                    <span class=" js-font-resize badge badge-danger">{{ trans('update.out_of_stock') }}</span>
                                @elseif(!$product->ordering and $product->getActiveDiscount())
                                    <span class=" js-font-resize badge badge-info">{{ trans('update.ordering_off') }}</span>
                                @elseif($hasDiscount)
                                <span class=" js-font-resize badge badge-danger">{{ trans('public.offer',['off' => $hasDiscount->percent]) }}</span>
                                @else
                                    @switch($product->status)
                                        @case(\App\Models\Product::$active)
                                        <span class=" js-font-resize badge badge-primary text-dark-blue">{{ trans('public.active') }}</span>
                                        @break
                                        @case(\App\Models\Product::$draft)
                                        <span class=" js-font-resize badge badge-danger text-light">{{ trans('public.draft') }}</span>
                                        @break
                                        @case(\App\Models\Product::$pending)
                                        <span class=" js-font-resize badge badge-warning text-dark-blue">{{ trans('public.waiting') }}</span>
                                        @break
                                        @case(\App\Models\Product::$inactive)
                                        <span class=" js-font-resize badge badge-danger text-light">{{ trans('public.rejected') }}</span>
                                        @break
                                    @endswitch
                                @endif
                            </div>

                            <div class=" js-font-resize webinar-card-body w-100 d-flex flex-column">
                                <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                    <a href="{{ $product->getUrl() }}" target="_blank">
                                        <h3 class=" js-font-resize font-16 text-light font-weight-bold">{{ $product->title }}</h3>
                                    </a>

                                    @if($authUser->id == $product->creator_id)
                                        <div class=" js-font-resize btn-group dropdown table-actions">
                                            <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i data-feather="more-vertical" height="20"></i>
                                            </button>
                                            <div class=" js-font-resize dropdown-menu ">
                                                <a href="/panel/store/products/{{ $product->id }}/edit" class=" js-font-resize webinar-actions d-block mt-10">{{ trans('public.edit') }}</a>

                                                @if($product->creator_id == $authUser->id)
                                                    <a href="/panel/store/products/{{ $product->id }}/delete" class=" js-font-resize webinar-actions d-block mt-10 text-danger delete-action">{{ trans('public.delete') }}</a>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @include('web.default.includes.webinar.rate',['rate' => $product->getRate()])

                                <div class=" js-font-resize webinar-price-box mt-15">
                                    @if($product->price > 0)
                                        @if($product->getPriceWithActiveDiscountPrice() < $product->price)
                                            <span class=" js-font-resize real">{{ handlePrice($product->getPriceWithActiveDiscountPrice(), true, true, false, null, true, 'store') }}</span>
                                            <span class=" js-font-resize off ml-10">{{ handlePrice($product->price, true, true, false, null, true, 'store') }}</span>
                                        @else
                                            <span class=" js-font-resize real">{{ handlePrice($product->price, true, true, false, null, true, 'store') }}</span>
                                        @endif
                                    @else
                                        <span class=" js-font-resize real">{{ trans('public.free') }}</span>
                                    @endif
                                </div>

                                <div class=" js-font-resize d-flex align-items-center justify-content-between flex-wrap mt-auto">
                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('public.item_id') }}:</span>
                                        <span class=" js-font-resize stat-value">{{ $product->id }}</span>
                                    </div>

                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('public.category') }}:</span>
                                        <span class=" js-font-resize stat-value">{{ !empty($product->category_id) ? $product->category->title : '' }}</span>
                                    </div>

                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('public.type') }}:</span>
                                        <span class=" js-font-resize stat-value">{{ trans('update.product_type_'.$product->type) }}</span>
                                    </div>

                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('update.availability') }}:</span>
                                        @if($product->unlimited_inventory)
                                            {{ trans('update.unlimited') }}
                                        @else
                                            <span class=" js-font-resize stat-value">{{ $product->getAvailability() }}</span>
                                        @endif
                                    </div>

                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('panel.sales') }}:</span>
                                        @if(!empty($product->sales()) and count($product->sales()))
                                            <span class=" js-font-resize stat-value">{{ $product->salesCount() }} ({{ handlePrice($product->sales()->sum('total_amount')) }})</span>
                                        @else
                                            <span class=" js-font-resize stat-value">0</span>
                                        @endif
                                    </div>

                                    @if($product->isPhysical())
                                        <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class=" js-font-resize stat-title">{{ trans('update.shipping_cost') }}:</span>
                                            <span class=" js-font-resize stat-value">{{ !empty($product->delivery_fee) ? handlePrice($product->delivery_fee) : 0 }}</span>
                                        </div>

                                        <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class=" js-font-resize stat-title">{{ trans('update.waiting_orders') }}:</span>
                                            <span class=" js-font-resize stat-value">{{ $product->productOrders->whereIn('status',[\App\Models\ProductOrder::$waitingDelivery,\App\Models\ProductOrder::$shipped])->count() }}</span>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class=" js-font-resize my-30">
                {{ $products->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>

        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'webinar.png',
                'title' => trans('panel.you_not_have_any_webinar'),
                'hint' =>  trans('panel.no_result_hint') ,
                'btn' => ['url' => '/panel/webinars/new','text' => trans('panel.create_a_webinar') ]
            ])
        @endif
    </section>
@endsection
