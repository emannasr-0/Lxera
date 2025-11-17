@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
    @if($activeSubscribe)
        <section>
            <h2 class=" js-font-resize section-title">{{ trans('financial.my_active_plan') }}</h2>

            <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                        <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/webinars.svg" width="64" height="64" alt="">
                            <strong class=" js-font-resize font-30 font-weight-bold mt-5">{{ $activeSubscribe->title }}</strong>
                            <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('financial.active_plan') }}</span>
                        </div>
                    </div>

                    <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                        <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/53.svg" width="64" height="64" alt="">
                            <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">
                                @if($activeSubscribe->infinite_use)
                                    {{ trans('update.unlimited') }}
                                @else
                                    {{ $activeSubscribe->usable_count - $activeSubscribe->used_count }}
                                @endif
                            </strong>
                            <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('financial.remained_downloads') }}</span>
                        </div>
                    </div>

                    <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                        <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                            <img src="/assets/default/img/activity/54.svg" width="64" height="64" alt="">
                            <strong class=" js-font-resize font-30 text-light text-light font-weight-bold mt-5">{{ $activeSubscribe->days - $dayOfUse }}</strong>
                            <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('financial.days_remained') }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    @else
        @include(getTemplate() . '.includes.no-result',[
           'file_name' => 'subcribe.png',
           'title' => trans('financial.subcribe_no_result'),
           'hint' => nl2br(trans('financial.subcribe_no_result_hint')),
       ])
    @endif

    <section class=" js-font-resize mt-30">
        <h2 class=" js-font-resize section-title">{{ trans('financial.select_a_subscribe_plan') }}</h2>

        <div class=" js-font-resize row mt-15">

            @foreach($subscribes as $subscribe)
                @php
                    $subscribeSpecialOffer = $subscribe->activeSpecialOffer();
                @endphp

                <div class=" js-font-resize col-12 col-sm-6 col-lg-3 mt-15">
                    <div class=" js-font-resize subscribe-plan position-relative bg-white d-flex flex-column align-items-center rounded-sm shadow pt-50 pb-20 px-20">
                        @if($subscribe->is_popular)
                            <span class=" js-font-resize badge badge-primary text-dark-blue badge-popular px-15 py-5">{{ trans('panel.popular') }}</span>
                        @elseif(!empty($subscribeSpecialOffer))
                            <span class=" js-font-resize badge badge-danger text-light badge-popular px-15 py-5">{{ trans('update.percent_off', ['percent' => $subscribeSpecialOffer->percent]) }}</span>
                        @endif

                        <div class=" js-font-resize plan-icon">
                            <img src="{{ $subscribe->icon }}" class=" js-font-resize img-cover" alt="">
                        </div>

                        <h3 class=" js-font-resize mt-20 font-30 text-secondary">{{ $subscribe->title }}</h3>
                        <p class=" js-font-resize font-weight-500 font-14 text-gray mt-10">{{ $subscribe->description }}</p>

                        <div class=" js-font-resize d-flex align-items-start mt-30">
                            @if(!empty($subscribe->price) and $subscribe->price > 0)
                                @if(!empty($subscribeSpecialOffer))
                                    <div class=" js-font-resize d-flex align-items-end line-height-1">
                                        <span class=" js-font-resize font-36 text-primary">{{ handlePrice($subscribe->getPrice()) }}</span>
                                        <span class=" js-font-resize font-14 text-gray ml-5 text-decoration-line-through">{{ handlePrice($subscribe->price) }}</span>
                                    </div>
                                @else
                                    <span class=" js-font-resize font-36 text-primary line-height-1">{{ handlePrice($subscribe->price) }}</span>
                                @endif
                            @else
                                <span class=" js-font-resize font-36 text-primary line-height-1">{{ trans('public.free') }}</span>
                            @endif
                        </div>

                        <ul class=" js-font-resize mt-20 plan-feature">
                            <li class=" js-font-resize mt-10">{{ $subscribe->days }} {{ trans('financial.days_of_subscription') }}</li>
                            <li class=" js-font-resize mt-10">
                                @if($subscribe->infinite_use)
                                    {{ trans('update.unlimited') }}
                                @else
                                    {{ $subscribe->usable_count }}
                                @endif
                                <span class=" js-font-resize ml-5">{{ trans('update.subscribes') }}</span>
                            </li>
                        </ul>
                        <form action="/panel/financial/pay-subscribes" method="post" class=" js-font-resize btn-block">
                            {{ csrf_field() }}
                            <input name="amount" value="{{ $subscribe->price }}" type="hidden">
                            <input name="id" value="{{ $subscribe->id }}" type="hidden">

                            <div class=" js-font-resize d-flex align-items-center mt-50 w-100">
                                <button type="submit" class=" js-font-resize btn btn-primary {{ !empty($subscribe->has_installment) ? '' : 'btn-block' }}">{{ trans('update.purchase') }}</button>

                                @if(!empty($subscribe->has_installment))
                                    <a href="/panel/financial/subscribes/{{ $subscribe->id }}/installments" class=" js-font-resize btn btn-outline-primary flex-grow-1 ml-10">{{ trans('update.installments') }}</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/panel/financial/subscribes.min.js"></script>
@endpush
