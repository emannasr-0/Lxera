@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/leaflet/leaflet.css">
@endpush


@section('content')
    <section class=" js-font-resize site-top-banner search-top-banner opacity-04 position-relative">
        <img src="{{ $contactSettings['background'] }}" class=" js-font-resize img-cover" alt="{{ $pageTitle ?? '' }}"/>

        <div class=" js-font-resize container h-100">
            <div class=" js-font-resize row contact-us-head h-100 justify-content-center text-center">
                <div class=" js-font-resize col-12 col-md-9 col-lg-7">
                    <div class=" js-font-resize top-search-categories-form">
                        <h1 class=" js-font-resize text-white font-30 mb-15">{{ trans('site.contact_us') }}</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class=" js-font-resize container">
        <section class=" js-font-resize ">
            @if(!empty($contactSettings['latitude']) and !empty($contactSettings['longitude']))
                <div class=" js-font-resize contact-map" id="contactMap"
                     data-latitude="{{ $contactSettings['latitude'] }}"
                     data-longitude="{{ $contactSettings['longitude'] }}"
                     data-zoom="{{ $contactSettings['map_zoom'] ?? 12 }}"
                ></div>
            @endif


            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-4">
                    <div class=" js-font-resize contact-items mt-30 rounded-lg py-20 py-md-40 px-15 px-md-30 text-center">
                        <div class=" js-font-resize contact-icon-box box-info p-20 d-flex align-items-center justify-content-center mx-auto">
                            <i data-feather="map-pin" width="50" height="50" class=" js-font-resize text-white"></i>
                        </div>

                        <h3 class=" js-font-resize mt-30 font-16 font-weight-bold text-dark-blue">{{ trans('site.our_address') }}</h3>
                        @if(!empty($contactSettings['address']))
                            <p class=" js-font-resize font-weight-500 font-14 text-gray mt-10">{!! nl2br($contactSettings['address']) !!}</p>
                        @else
                            <p class=" js-font-resize font-weight-500 text-gray font-14 mt-10">{{ trans('site.not_defined') }}</p>
                        @endif
                    </div>
                </div>

                <div class=" js-font-resize col-12 col-md-4">
                    <div class=" js-font-resize contact-items mt-30 rounded-lg py-20 py-md-40 px-15 px-md-30 text-center">
                        <div class=" js-font-resize contact-icon-box box-green p-20 d-flex align-items-center justify-content-center mx-auto">
                            <i data-feather="phone" width="50" height="50" class=" js-font-resize text-white"></i>
                        </div>

                        <h3 class=" js-font-resize mt-30 font-16 font-weight-bold text-dark-blue">{{ trans('site.phone_number') }}</h3>
                        @if(!empty($contactSettings['phones']))
                            <p class=" js-font-resize font-weight-500 text-gray font-14 mt-10">{!! nl2br(str_replace(',','<br/>',$contactSettings['phones'])) !!}</p>
                        @else
                            <p class=" js-font-resize font-weight-500 text-gray font-14 mt-10">{{ trans('site.not_defined') }}</p>
                        @endif
                    </div>
                </div>

                <div class=" js-font-resize col-12 col-md-4">
                    <div class=" js-font-resize contact-items mt-30 rounded-lg py-20 py-md-40 px-15 px-md-30 text-center">
                        <div class=" js-font-resize contact-icon-box box-red p-20 d-flex align-items-center justify-content-center mx-auto">
                            <i data-feather="mail" width="50" height="50" class=" js-font-resize text-white"></i>
                        </div>

                        <h3 class=" js-font-resize mt-30 font-16 font-weight-bold text-dark-blue">{{ trans('public.email') }}</h3>
                        @if(!empty($contactSettings['emails']))
                            <p class=" js-font-resize font-weight-500 text-gray font-14 mt-10">{!! nl2br(str_replace(',','<br/>',$contactSettings['emails'])) !!}</p>
                        @else
                            <p class=" js-font-resize font-weight-500 text-gray font-14 mt-10">{{ trans('site.not_defined') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class=" js-font-resize mt-30 mt-md-50">
            <h2 class=" js-font-resize font-16 font-weight-bold text-secondary">{{ trans('site.send_your_message_directly') }}</h2>

            @if(!empty(session()->has('msg')))
                <div class=" js-font-resize alert alert-success my-25 d-flex align-items-center">
                    <i data-feather="check-square" width="50" height="50" class=" js-font-resize mr-2"></i>
                    {{ session()->get('msg') }}
                </div>
            @endif

            <form action="/contact/store" method="post" class=" js-font-resize mt-20">
                {{ csrf_field() }}

                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 col-md-6">
                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label font-weight-500">{{ trans('site.your_name') }}</label>
                            <input type="text" name="name" value="{{ old('name') }}" class=" js-font-resize form-control @error('name')  is-invalid @enderror"/>
                            @error('name')
                            <div class=" js-font-resize invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                    <div class=" js-font-resize col-12 col-md-6">
                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label font-weight-500">{{ trans('public.email') }}</label>
                            <input type="text" name="email" value="{{ old('email') }}" class=" js-font-resize form-control @error('email')  is-invalid @enderror"/>
                            @error('email')
                            <div class=" js-font-resize invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 col-md-6">
                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label font-weight-500">{{ trans('site.phone_number') }}</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class=" js-font-resize form-control @error('phone')  is-invalid @enderror"/>
                            @error('phone')
                            <div class=" js-font-resize invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                    <div class=" js-font-resize col-12 col-md-6">
                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label font-weight-500">{{ trans('site.subject') }}</label>
                            <input type="text" name="subject" value="{{ old('subject') }}" class=" js-font-resize form-control @error('subject')  is-invalid @enderror"/>
                            @error('subject')
                            <div class=" js-font-resize invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12">
                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label font-weight-500">{{ trans('site.message') }}</label>
                            <textarea name="message" id="" rows="10" class=" js-font-resize form-control @error('message')  is-invalid @enderror">{{ old('message') }}</textarea>
                            @error('message')
                            <div class=" js-font-resize invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 col-md-6">
                        @include('web.default.includes.captcha_input')
                    </div>
                </div>

                <button type="submit" class=" js-font-resize btn btn-primary mt-20">{{ trans('site.send_message') }}</button>
            </form>
        </section>

    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/leaflet/leaflet.min.js"></script>
    <script>
        var leafletApiPath = '{{ getLeafletApiPath() }}';
    </script>
    <script src="/assets/default/js/parts/contact.min.js"></script>
@endpush
