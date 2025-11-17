@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')

@endpush

@section('content')

    <section>
        <div class=" js-font-resize d-flex align-items-center justify-content-between">
            <h2 class=" js-font-resize section-title">{{ trans('panel.favorite_live_classes') }}</h2>
        </div>

        @if(!empty($favorites) and !$favorites->isEmpty())

            @foreach($favorites as $favorite)
                <div class=" js-font-resize row mt-30">
                    <div class=" js-font-resize col-12">
                        <div class=" js-font-resize webinar-card webinar-list d-flex">
                            <div class=" js-font-resize image-box">
                                <img src="{{ $favorite->webinar->getImage() }}" class=" js-font-resize img-cover" alt="">

                                @if($favorite->webinar->type == 'webinar')
                                    <div class=" js-font-resize progress">
                                        <span class=" js-font-resize progress-bar" style="width: {{ $favorite->webinar->getProgress() }}%"></span>
                                    </div>
                                @endif
                            </div>

                            <div class=" js-font-resize webinar-card-body w-100 d-flex flex-column">
                                <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                    <a href="{{ $favorite->webinar->getUrl() }}" target="_blank">
                                        <h3 class=" js-font-resize font-16 text-dark-blue font-weight-bold">{{ $favorite->webinar->title }}</h3>
                                    </a>

                                    <div class=" js-font-resize btn-group dropdown table-actions">
                                        <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i data-feather="more-vertical" height="20"></i>
                                        </button>
                                        <div class=" js-font-resize dropdown-menu">
                                            <a href="/panel/webinars/favorites/{{ $favorite->id }}/delete" class=" js-font-resize webinar-actions d-block delete-action">{{ trans('public.remove') }}</a>
                                        </div>
                                    </div>
                                </div>

                                @include(getTemplate() . '.includes.webinar.rate',['rate' => $favorite->webinar->getRate()])

                                <div class=" js-font-resize webinar-price-box mt-15">
                                    @if($favorite->webinar->bestTicket() < $favorite->webinar->price)
                                        <span class=" js-font-resize real">{{ handlePrice($favorite->webinar->bestTicket(), true, true, false, null, true) }}</span>
                                        <span class=" js-font-resize off ml-10">{{ handlePrice($favorite->webinar->price, true, true, false, null, true) }}</span>
                                    @else
                                        <span class=" js-font-resize real">{{ handlePrice($favorite->webinar->price, true, true, false, null, true) }}</span>
                                    @endif
                                </div>

                                <div class=" js-font-resize d-flex align-items-center justify-content-between flex-wrap mt-auto">
                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('public.item_id') }}:</span>
                                        <span class=" js-font-resize stat-value">{{ $favorite->webinar->id }}</span>
                                    </div>

                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('public.category') }}:</span>
                                        <span class=" js-font-resize stat-value">{{ !empty($favorite->webinar->category_id) ? $favorite->webinar->category->title : '' }}</span>
                                    </div>

                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('public.duration') }}:</span>
                                        <span class=" js-font-resize stat-value">{{ convertMinutesToHourAndMinute($favorite->webinar->duration) }} {{ trans('home.hours') }}</span>
                                    </div>

                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        @if($favorite->webinar->isWebinar())
                                            <span class=" js-font-resize stat-title">{{ trans('public.start_date') }}:</span>
                                        @else
                                            <span class=" js-font-resize stat-title">{{ trans('public.created_at') }}:</span>
                                        @endif
                                        <span class=" js-font-resize stat-value">{{ dateTimeFormat(!empty($favorite->webinar->start_date) ? $favorite->webinar->start_date : $favorite->webinar->created_at,'j M Y') }}</span>
                                    </div>

                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('public.instructor') }}:</span>
                                        <span class=" js-font-resize stat-value">{{ $favorite->webinar->teacher->full_name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'student.png',
                'title' => trans('panel.no_result_favorites'),
                'hint' =>  trans('panel.no_result_favorites_hint') ,
            ])
        @endif

    </section>

    <div class=" js-font-resize my-30">
        {{ $favorites->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection
