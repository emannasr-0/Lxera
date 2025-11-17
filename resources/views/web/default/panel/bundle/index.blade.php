@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')
    <section>
        <h2 class=" js-font-resize section-title mt-20 mt-lg-0">{{ trans('panel.my_activity') }}</h2>

        <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35 shadow border col-12 col-lg-6">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-6  mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/webinars.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-black font-weight-bold mt-5">{{ !empty($bundles) ? $bundlesCount : 0}}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.bundles') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6  mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/hours.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-black font-weight-bold mt-5">{{ $bundlesHours }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('home.hours') }}</span>
                    </div>
                </div>

                {{-- <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/sales.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-black font-weight-bold mt-5">{{ handlePrice($bundleSalesAmount) }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.bundle_sales') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/download-sales.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-black font-weight-bold mt-5">{{ $bundleSalesCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('update.bundle_sales_count') }}</span>
                    </div>
                </div> --}}
            </div>
        </div>
    </section>

    <section class=" js-font-resize mt-25">
        <div class=" js-font-resize d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class=" js-font-resize section-title">{{ trans('update.my_bundles') }}</h2>
        </div>

        @if(!empty($bundles) and !$bundles->isEmpty())
            @foreach($bundles as $bundle)

                <div class=" js-font-resize row mt-30">
                    <div class=" js-font-resize col-12">
                        <div class=" js-font-resize webinar-card webinar-list d-flex ">
                            <div class=" js-font-resize bg-secondary-acadima px-15 py-15 py-md-0">
                                <img src="{{ $bundle->getImage() }}" class=" js-font-resize " alt="">

                                @switch($bundle->status)
                                    @case(\App\Models\Bundle::$active)
                                    <span class=" js-font-resize badge badge-primary text-light">{{  trans('panel.active') }}</span>
                                    @break
                                    @case(\App\Models\Bundle::$isDraft)
                                    <span class=" js-font-resize badge badge-danger text-light">{{ trans('public.draft') }}</span>
                                    @break
                                    @case(\App\Models\Bundle::$pending)
                                    <span class=" js-font-resize badge badge-warning text-light">{{ trans('public.waiting') }}</span>
                                    @break
                                    @case(\App\Models\Bundle::$inactive)
                                    <span class=" js-font-resize badge badge-danger text-light">{{ trans('public.rejected') }}</span>
                                    @break
                                @endswitch
                            </div>

                            <div class=" js-font-resize webinar-card-body w-100 d-flex flex-column bg-secondary-acadima">
                                <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                    <a  target="_blank">
                                        <h3 class=" js-font-resize font-16 text-pink font-weight-bold">{{ $bundle->title }}</h3>
                                    </a>

                                    @if($authUser->id == $bundle->creator_id or $authUser->id == $bundle->teacher_id or $bundle->isPartnerTeacher($authUser->id))
                                        <div class=" js-font-resize btn-group dropdown table-actions">
                                            <button type="button" class=" js-font-resize btn-transparent dropdown-toggle text-black" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i data-feather="more-vertical" height="20"></i>
                                            </button>
                                            <div class=" js-font-resize dropdown-menu ">

                                                {{--   <a href="/panel/bundles/{{ $bundle->id }}/edit" class=" js-font-resize webinar-actions d-block mt-10">{{ trans('public.edit') }}</a> --}}

                                                <a href="/panel/bundles/{{ $bundle->id }}/courses" class=" js-font-resize webinar-actions d-block mt-10">{{ trans('product.courses') }}</a>

                                                {{--
                                                @if($authUser->id == $bundle->teacher_id or $authUser->id == $bundle->creator_id)
                                                    <a href="/panel/bundles/{{ $bundle->id }}/export-students-list" class=" js-font-resize webinar-actions d-block mt-10">{{ trans('public.export_list') }}</a>
                                                @endif --}}
                                                {{--
                                                @if($bundle->creator_id == $authUser->id)
                                                    <a href="/panel/bundles/{{ $bundle->id }}/delete" class=" js-font-resize webinar-actions d-block mt-10 text-danger delete-action">{{ trans('public.delete') }}</a>
                                                @endif
                                                --}}
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @include(getTemplate() . '.includes.webinar.rate',['rate' => $bundle->getRate()])

                                <div class=" js-font-resize webinar-price-box mt-15">
                                    @if($bundle->price > 0)
                                        @if($bundle->bestTicket() < $bundle->price)
                                            <span class=" js-font-resize real">{{ handlePrice($bundle->bestTicket(), true, true, false, null, true) }}</span>
                                            <span class=" js-font-resize off ml-10">{{ handlePrice($bundle->price, true, true, false, null, true) }}</span>
                                        @else
                                            <span class=" js-font-resize real">{{ handlePrice($bundle->price, true, true, false, null, true) }}</span>
                                        @endif
                                    @else
                                        <span class=" js-font-resize real">{{ trans('public.free') }}</span>
                                    @endif
                                </div>

                                <div class=" js-font-resize d-flex align-items-center justify-content-between flex-wrap mt-auto">
                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('public.item_id') }}:</span>
                                        <span class=" js-font-resize stat-value text-black">{{ $bundle->id }}</span>
                                    </div>

                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('public.category') }}:</span>
                                        <span class=" js-font-resize stat-value text-black">{{ !empty($bundle->category_id) ? $bundle->category->title : '' }}</span>
                                    </div>


                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('public.duration') }}:</span>
                                        <span class=" js-font-resize stat-value text-black">{{ $bundle->getBundleDuration() }} Hrs</span>
                                    </div>

                                    <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('product.courses') }}:</span>
                                        <span class=" js-font-resize stat-value text-black">{{ $bundle->bundleWebinars->count() }}</span>
                                    </div>

                                    {{-- <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                        <span class=" js-font-resize stat-title">{{ trans('panel.sales') }}:</span>
                                        <span class=" js-font-resize stat-value text-black">{{ count($bundle->sales) }} ({{ (!empty($bundle->sales) and count($bundle->sales)) ? handlePrice($bundle->sales->sum('amount')) : 0 }})</span>
                                    </div> --}}

                                    @if($authUser->id == $bundle->teacher_id and $authUser->id != $bundle->creator_id and $bundle->creator->isOrganization())
                                        <div class=" js-font-resize d-flex align-items-start flex-column mt-20 mr-15">
                                            <span class=" js-font-resize stat-title">{{ trans('webinars.organization_name') }}:</span>
                                            <span class=" js-font-resize stat-value text-black">{{ $bundle->creator->full_name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class=" js-font-resize my-30">
                {{ $bundles->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>

        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'webinar.png',
                'title' => trans('update.you_not_have_any_bundle'),
                'hint' =>  trans('update.no_result_bundle_hint') ,
                'btn' => ['url' => '/panel/bundles/new','text' => trans('update.create_a_bundle') ]
            ])
        @endif

    </section>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

@endpush
