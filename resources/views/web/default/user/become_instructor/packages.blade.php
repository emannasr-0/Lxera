@extends('web.default.layouts.app')

@section('content')
    <div class=" js-font-resize container become-instructor-packages">
        <div class=" js-font-resize text-center mt-50">
            <h1 class=" js-font-resize font-36 font-weight-bold text-dark">{{ trans('update.registration_packages') }}</h1>
            <p class=" js-font-resize font-16 font-weight-normal text-gray mt-10">{{ trans('update.select_registration_package_hint') }}</p>

            @if(empty($becomeInstructor))
                <a href="/become-instructor" class=" js-font-resize btn btn-primary mt-15">{{ trans('site.become_instructor') }}</a>
            @endif
        </div>

        @if(!empty($defaultPackage))
            <div class=" js-font-resize d-flex align-items-center flex-column flex-lg-row mt-50 border rounded-lg p-15 p-lg-25">
                <div class=" js-font-resize default-package-icon">
                    <img src="/assets/default/img/become-instructor/default.png" class=" js-font-resize img-cover" alt="{{ trans('update.default_package') }}">
                </div>

                <div class=" js-font-resize ml-lg-25 w-100 mt-20 mt-lg-0">
                    <h2 class=" js-font-resize font-24 font-weight-bold text-light">{{ trans('update.default_package') }}</h2>
                    <p class=" js-font-resize font-14 font-weight-500 text-gray">{{ trans('update.default_package_hint') }}</p>

                    <div class=" js-font-resize d-flex flex-wrap align-items-center justify-content-between w-100">

                        <div class=" js-font-resize d-flex align-items-center mt-20">
                            <div class=" js-font-resize default-package-statistics-icon">
                                <img src="/assets/default/img/icons/play.svg" alt="play">
                            </div>

                            <span class=" js-font-resize font-12 text-dark-blue font-weight-bold mx-1">
                               {{ $defaultPackage->courses_count ?? trans('update.unlimited') }}
                            </span>
                            <span class=" js-font-resize font-14 font-weight-500 text-gray">{{ trans('product.courses') }}</span>
                        </div>

                        <div class=" js-font-resize d-flex align-items-center mt-20">
                            <div class=" js-font-resize default-package-statistics-icon">
                                <img src="/assets/default/img/icons/video-2.svg" alt="video-2">
                            </div>

                            <span class=" js-font-resize font-12 text-dark-blue font-weight-bold mx-1">
                               {{ $defaultPackage->courses_capacity ?? trans('update.unlimited') }}
                            </span>
                            <span class=" js-font-resize font-14 font-weight-500 text-gray">{{ trans('update.live_students') }}</span>
                        </div>

                        <div class=" js-font-resize d-flex align-items-center mt-20">
                            <div class=" js-font-resize default-package-statistics-icon">
                                <img src="/assets/default/img/icons/clock.svg" alt="clock">
                            </div>

                            <span class=" js-font-resize font-12 text-dark-blue font-weight-bold mx-1">
                               {{ $defaultPackage->meeting_count ?? trans('update.unlimited') }}
                            </span>
                            <span class=" js-font-resize font-14 font-weight-500 text-gray">{{ trans('update.meeting_hours') }}</span>
                        </div>

                        <div class=" js-font-resize d-flex align-items-center mt-20">
                            <div class=" js-font-resize default-package-statistics-icon">
                                <img src="/assets/default/img/icons/clock.svg" alt="clock">
                            </div>

                            <span class=" js-font-resize font-12 text-dark-blue font-weight-bold mx-1">
                               {{ $defaultPackage->product_count ?? trans('update.unlimited') }}
                            </span>
                            <span class=" js-font-resize font-14 font-weight-500 text-gray">{{ trans('update.products') }}</span>
                        </div>

                        @if($selectedRole == 'organizations')
                            <div class=" js-font-resize d-flex align-items-center mt-20">
                                <div class=" js-font-resize default-package-statistics-icon">
                                    <img src="/assets/default/img/icons/users.svg" alt="users">
                                </div>

                                <span class=" js-font-resize font-12 text-dark-blue font-weight-bold mx-1">
                                   {{ $defaultPackage->instructors_count ?? trans('update.unlimited') }}
                                </span>
                                <span class=" js-font-resize font-14 font-weight-500 text-gray">{{ trans('home.instructors') }}</span>
                            </div>

                            <div class=" js-font-resize d-flex align-items-center mt-20">
                                <div class=" js-font-resize default-package-statistics-icon">
                                    <img src="/assets/default/img/icons/user.svg" alt="user">
                                </div>

                                <span class=" js-font-resize font-12 text-dark-blue font-weight-bold mx-1">
                                   {{ $defaultPackage->students_count ?? trans('update.unlimited') }}
                                </span>
                                <span class=" js-font-resize font-14 font-weight-500 text-gray">{{ trans('public.students') }}</span>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('payRegistrationPackage') }}" method="post">
            {{ csrf_field() }}

            @if(!empty($becomeInstructor))
                <input type="hidden" name="become_instructor_id" value="{{ $becomeInstructor->id }}"/>
            @endif

            <div class=" js-font-resize row mt-20">
                @foreach($packages as $package)
                    @php
                        $specialOffer = $package->activeSpecialOffer();
                    @endphp

                    <div class=" js-font-resize col-12 col-sm-6 col-lg-4 mt-15 {{ !empty($becomeInstructor) ? 'charge-account-radio' : '' }}">
                        @if(!empty($becomeInstructor))
                            <input type="radio" name="id" id="package{{ $package->id }}" value="{{ $package->id }}">
                        @endif

                        <label for="package{{ $package->id }}" class=" js-font-resize subscribe-plan cursor-pointer position-relative bg-white d-flex flex-column align-items-center rounded-sm shadow pt-50 pb-20 px-20">

                            @if(!empty($activePackage) and $activePackage->package_id == $package->id)
                                <span class=" js-font-resize badge badge-primary text-dark-blue badge-popular px-15 py-5">{{ trans('update.activated') }}</span>
                            @elseif(!empty($specialOffer))
                                <span class=" js-font-resize badge badge-danger text-light badge-popular px-15 py-5">{{ trans('update.percent_off', ['percent' => $specialOffer->percent]) }}</span>
                            @endif


                            <div class=" js-font-resize plan-icon">
                                <img src="{{ $package->icon }}" class=" js-font-resize img-cover" alt="">
                            </div>

                            <h3 class=" js-font-resize mt-20 font-30 text-secondary">{{ $package->title }}</h3>
                            <p class=" js-font-resize font-weight-500 font-14 text-gray mt-10">{{ $package->description }}</p>

                            <div class=" js-font-resize d-flex align-items-start mt-30">
                                @if(!empty($package->price) and $package->price > 0)
                                    @if(!empty($specialOffer))
                                        <div class=" js-font-resize d-flex align-items-end line-height-1">
                                            <span class=" js-font-resize font-36 text-primary">{{ handlePrice($package->getPrice()) }}</span>
                                            <span class=" js-font-resize font-14 text-gray ml-5 text-decoration-line-through">{{ handlePrice($package->price) }}</span>
                                        </div>
                                    @else
                                        <span class=" js-font-resize font-36 text-primary line-height-1">{{ handlePrice($package->price) }}</span>
                                    @endif
                                @else
                                    <span class=" js-font-resize font-36 text-primary line-height-1">{{ trans('public.free') }}</span>
                                @endif
                            </div>

                            <ul class=" js-font-resize mt-20 plan-feature">
                                <li class=" js-font-resize mt-10">{{ $package->days ?? trans('update.unlimited') }} {{ trans('public.days') }}</li>
                                <li class=" js-font-resize mt-10">{{ $package->courses_count ?? trans('update.unlimited') }} {{ trans('product.courses') }}</li>
                                <li class=" js-font-resize mt-10">{{ $package->courses_capacity ?? trans('update.unlimited') }} {{ trans('update.live_students') }}</li>
                                <li class=" js-font-resize mt-10">{{ $package->meeting_count ?? trans('update.unlimited') }} {{ trans('update.meeting_hours') }}</li>
                                <li class=" js-font-resize mt-10">{{ $package->product_count ?? trans('update.unlimited') }} {{ trans('update.products') }}</li>

                                @if($selectedRole == 'organizations')
                                    <li class=" js-font-resize mt-10">{{ $package->instructors_count ?? trans('update.unlimited') }} {{ trans('home.instructors') }}</li>
                                    <li class=" js-font-resize mt-10">{{ $package->students_count ?? trans('update.unlimited') }} {{ trans('public.students') }}</li>
                                @endif
                            </ul>
                        </label>
                    </div>
                @endforeach
            </div>

            @if(!empty($becomeInstructor))
                <div class=" js-font-resize d-flex align-items-center justify-content-between mt-20 pt-10 border-top">
                    <a href="{{ url()->previous() }}" class=" js-font-resize btn btn-sm btn-border-white">{{ trans('update.back') }}</a>

                    <div class=" js-font-resize ">
                        @if(!getRegistrationPackagesGeneralSettings('force_user_to_select_a_package'))
                            <a href="/panel" class=" js-font-resize btn btn-sm btn-primary mr-5">{{ trans('update.skip') }}</a>
                        @endif

                        <a href="" class=" js-font-resize js-installment-btn d-none btn btn-primary btn-sm">
                            {{ trans('update.pay_with_installments') }}
                        </a>

                        <button type="submit" id="paymentSubmit" disabled class=" js-font-resize btn btn-sm btn-primary">{{ trans('cart.checkout') }}</button>
                    </div>
                </div>
            @endif
        </form>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/parts/become_instructor.min.js"></script>
@endpush
