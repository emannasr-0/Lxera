@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
@endpush

@section('content')
    <section class=" js-font-resize ">
        <h2 class=" js-font-resize section-title">{{ trans('panel.select_promotion_plan') }}</h2>

        <div class=" js-font-resize row mt-20">

            @foreach($promotions as $promotion)
                <div class=" js-font-resize col-12 col-sm-6 col-lg-3 mt-15">
                    <div class=" js-font-resize subscribe-plan position-relative bg-white d-flex flex-column align-items-center rounded-sm shadow pt-50 pb-20 px-20">
                        @if($promotion->is_popular)
                            <span class=" js-font-resize badge badge-primary text-dark-blue badge-popular px-15 py-5">{{ trans('panel.popular') }}</span>
                        @endif

                        <div class=" js-font-resize plan-icon">
                            <img src="{{ $promotion->icon }}" class=" js-font-resize img-cover" alt="">
                        </div>

                        <h3 class=" js-font-resize mt-20 font-30 text-secondary subscribe-plan-title">{{ $promotion->title }}</h3>
                        <p class=" js-font-resize font-weight-500 text-gray mt-10">{{ trans('panel.promotion_days',['day' => $promotion->days]) }}</p>

                        <div class=" js-font-resize d-flex align-items-start text-primary mt-30">
                            <span class=" js-font-resize font-36 line-height-1 subscribe-plan-price">{{ (!empty($promotion->price) and $promotion->price > 0) ? handlePrice($promotion->price) : trans('public.free') }}</span>
                        </div>

                        <p class=" js-font-resize text-dark-blue font-14 mt-25">{{ nl2br($promotion->description) }}</p>

                        <button type="button" data-promotion-id="{{ $promotion->id }}"
                                class=" js-font-resize js-pay-promotion btn btn-primary btn-block mt-50">{{ trans('update.purchase') }}</button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    @if($promotionSales->count() > 0)
        <section class=" js-font-resize mt-35">
            <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
                <h2 class=" js-font-resize section-title">{{ trans('panel.promotions_history') }}</h2>

                <div
                    class=" js-font-resize d-flex align-items-center flex-row-reverse flex-md-row justify-content-start justify-content-md-center mt-20 mt-md-0">
                    <label class=" js-font-resize mb-0 mr-10 text-gray font-14 font-weight-500"
                           for="activePromotionSwitch">{{ trans('panel.show_only_active_promotions') }}</label>
                    <div class=" js-font-resize custom-control custom-switch">
                        <input type="checkbox" name="active_promotions" class=" js-font-resize custom-control-input"
                               id="activePromotionSwitch">
                        <label class=" js-font-resize custom-control-label" for="activePromotionSwitch"></label>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table custom-table text-center ">
                                <thead>
                                <tr>
                                    <th class=" js-font-resize text-left text-gray">{{ trans('panel.webinar') }}</th>
                                    <th class=" js-font-resize text-center text-gray">{{ trans('panel.plan') }}</th>
                                    <th class=" js-font-resize text-center text-gray">{{ trans('public.price') }}</th>
                                    <th class=" js-font-resize text-center text-gray">{{ trans('public.date') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($promotionSales as $promotionSale)
                                    <tr>
                                        <td class=" js-font-resize text-left text-dark-blue font-weight-500 align-middle">{{ $promotionSale->webinar->title }}</td>
                                        <td class=" js-font-resize align-middle">
                                            <span class=" js-font-resize text-dark-blue font-weight-500">{{ $promotionSale->promotion->title }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            <span class=" js-font-resize text-dark-blue font-weight-500">{{ (!empty($promotionSale->promotion->price) and $promotionSale->promotion->price > 0) ? handlePrice($promotionSale->promotion->price) : trans('public.free') }}</span>
                                        </td>
                                        <td class=" js-font-resize text-dark-blue font-weight-500 align-middle">{{ dateTimeFormat($promotionSale->created_at, 'j M Y | H:i') }}</td>
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
            'file_name' => 'promotion.png',
            'title' => trans('panel.promotion_no_result'),
            'hint' =>  nl2br(trans('panel.promotion_no_result_hint')) ,
        ])

    @endif

    <div class=" js-font-resize my-30">
        {{ $promotionSales->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>

    <div id="promotionModal" class=" js-font-resize d-none">
        <form action="/panel/marketing/pay-promotion" method="post">
            {{ csrf_field() }}
            <input type="hidden" name="promotion_id" value="">


            <h3 class=" js-font-resize section-title after-line">{{ trans('panel.promote_the_webinar') }}</h3>
            <div class=" js-font-resize mt-25 d-flex flex-column align-items-center">
                <img src="/assets/default/img/check.png" alt="" width="120" height="117">
                <p class=" js-font-resize mt-10">{{ trans('panel.select_webinar_for_promotion') }}</p>
                <div class=" js-font-resize w-75">

                    <div class=" js-font-resize mt-15 d-flex justify-content-between">
                        <span class=" js-font-resize text-gray font-weight-bold">{{ trans('panel.plan') }}:</span>
                        <span class=" js-font-resize text-gray modal-title"></span>
                    </div>

                    <div class=" js-font-resize mt-10 d-flex justify-content-between">
                        <span class=" js-font-resize text-gray font-weight-bold">{{ trans('public.price') }}:</span>
                        <span class=" js-font-resize text-gray"><span class=" js-font-resize modal-price"></span></span>
                    </div>

                    <div class=" js-font-resize form-group mt-15">
                        <select name="webinar_id" class=" js-font-resize form-control custom-select">
                            <option selected disabled>{{ trans('panel.select_course') }}</option>

                            @foreach($webinars as $webinar)
                                <option value="{{ $webinar->id }}">{{ $webinar->title }}</option>
                            @endforeach
                        </select>
                        <div class=" js-font-resize invalid-feedback">
                            {{ trans('panel.select_course') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
                <button type="button"
                        class=" js-font-resize btn btn-sm btn-primary js-submit-promotion">{{ trans('panel.pay') }}</button>
                <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>

    <script src="/assets/default/js/panel/marketing/promotions.min.js"></script>
@endpush
