<style>
    .service-card .img-cover {
        width: 150px;
    }
</style>
@isset($service)
    <div
        class=" js-font-resize module-box bg-secondary-acadima rounded-sm panel-shadow py-30 d-flex  mt-0 h-100 w-100">
        <div class=" js-font-resize d-flex flex-column service-card px-20 text-center justify-content-between" style="align-items: center;">
            <img src="{{ asset('store/Acadima/reviwebg.png') }}" width="50%" alt="anas academy">

            @isset($service->title)
                <h1 class=" js-font-resize text-secondary font-weight-bold text-center pb-10 mt-10">
                    {{ $service->title }}
                </h1>
            @endisset

            @isset($service->description)
                <p class=" js-font-resize text-gray font-weight-500 font-16 mb-5">
                    {{ $service->description }}
                </p>
            @endisset

            @isset($service->price)
                <p class=" js-font-resize text-light font-weight-bold">

                    @if ($service->price > 0)
                        {{ $service->price }} 
                    {{-- @else
                        <span class=" js-font-resize text-danger">هذة الخدمه مجانيه</span> --}}
                    @endif
                </p>

            @endisset

            @isset($service->apply_link)
                <a target="_self" rel="noopener noreferrer" class=" js-font-resize btn btn-acadima-primary mt-10 px-50" style=""
                    href="{{ $service->apply_link }}">
                    {{trans('panel.submit_request')}}
                </a>
            @endisset


            {{-- @isset($service->review_link)
                <a target="_self" rel="noopener noreferrer" class=" js-font-resize mt-10 text-decoration-underline font-weight-500"
                    style="" href="{{ $service->review_link }}">
                    مراجعة طلب سابق
                </a>
            @endisset --}}
        </div>
    </div>
@endisset
