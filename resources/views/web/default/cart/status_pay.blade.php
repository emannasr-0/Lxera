@extends(getTemplate().'.layouts.app')


@section('content')


    @if(!empty($order) && $order->status === \App\Models\Order::$paid)
        <div class=" js-font-resize no-result default-no-result my-50 d-flex align-items-center justify-content-center flex-column">
            <div class=" js-font-resize no-result-logo">
                <img src="/assets/default/img/no-results/search.png" alt="">
            </div>
            <div class=" js-font-resize d-flex align-items-center flex-column mt-30 text-center">
                <h2>{{ trans('cart.success_pay_title') }}</h2>
                <p class=" js-font-resize mt-5 text-center">{!! trans('cart.success_pay_msg') !!}</p>
                @if(auth()->user()->isUser())
                <a href="/panel" class=" js-font-resize btn btn-sm btn-primary mt-20">{{ trans('public.my_panel') }}</a>
                @else
                <a href="/panel/requirements/applied" class=" js-font-resize btn btn-sm btn-primary mt-20">{{ trans('public.my_panel') }}</a>
                @endif
            </div>
        </div>
    @endif

    @if(!empty($order) && $order->status === \App\Models\Order::$fail)
        <div class=" js-font-resize no-result status-failed my-50 d-flex align-items-center justify-content-center flex-column">
            <div class=" js-font-resize no-result-logo">
                <img src="/assets/default/img/no-results/failed_pay.png" alt="">
            </div>
            <div class=" js-font-resize d-flex align-items-center flex-column mt-30 text-center">
                <h2>{{ trans('cart.failed_pay_title') }}</h2>
                <p class=" js-font-resize mt-5 text-center">{!! nl2br(trans('cart.failed_pay_msg')) !!}</p>
                @if(auth()->user()->isUser())
                <a href="/panel" class=" js-font-resize btn btn-sm btn-primary mt-20">{{ trans('public.my_panel') }}</a>
                @else
                <a href="/panel/requirements/applied" class=" js-font-resize btn btn-sm btn-primary mt-20">{{ trans('public.my_panel') }}</a>
                @endif
            </div>
        </div>
    @endif


@endsection
