<style>
    .service-card .img-cover {
        width: 150px;
    }
</style>

@isset($webinar)
    <div
        class=" js-font-resize module-box dashboard-stats rounded-sm panel-shadow py-30 d-flex align-items-center justify-content-center mt-0 h-100 w-100">
        <div class=" js-font-resize d-flex flex-column service-card px-20 text-center" style="align-items: center;">
            <img src="{{ asset('assets/default/img/img.png') }}" class=" js-font-resize img-cover" alt="webinar image">

            @isset($webinar->title)
                <h1 class=" js-font-resize text-secondary font-weight-bold text-center pb-10 ">
                    {{ $webinar->title }}
                </h1>
            @endisset

      

           
                <p class=" js-font-resize text-dark font-weight-bold">
                    @if ($webinar->price > 0)
                        {{ $webinar->price }} ريال سعودي
                    @else
                        <span class=" js-font-resize text-danger">هذة الدورة مجانية</span>
                    @endif
                </p>
       
         
            <a target="_self" rel="noopener noreferrer" class=" js-font-resize btn btn-primary mt-10 px-50"
                    href="/webinars/{{$webinar->id}}/apply">
                   سجل الآن
                </a>

        </div>
    </div>
@endisset
 