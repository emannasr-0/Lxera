@extends('web.default.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')
    <div class=" js-font-resize container mt-50">
        <div class=" js-font-resize text-center">
            <h1 class=" js-font-resize font-36 font-weight-bold text-dark">{{ $pageTitle }}</h1>
            <p class=" js-font-resize font-16 text-gray mt-10">{{ $titleHint }}</p>
        </div>

        <div class=" js-font-resize mt-50 rounded-lg border border-gray300">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-6 px-30 px-lg-80 py-30 py-lg-50 border-right">
                    <h3 class=" js-font-resize font-24 font-weight-bold mb-25">{{ trans('update.recipient_information') }}</h3>

                    <form action="/gift/{{ $itemType }}/{{ $item->slug }}" method="post">
                        {{ csrf_field() }}

                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('auth.name') }}:</label>
                            <input name="name" type="text" class=" js-font-resize form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                            @error('name')
                            <div class=" js-font-resize invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('auth.email') }}:</label>
                            <input name="email" type="email" class=" js-font-resize form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                            @error('email')
                            <div class=" js-font-resize invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('update.gift_date') }}:</label>
                            <input name="date" type="text" class=" js-font-resize form-control datetimepicker @error('date') is-invalid @enderror" autocomplete="off">
                            @error('date')
                            <div class=" js-font-resize invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('update.message_to_recipient_(optional)') }}:</label>
                            <textarea name="description" rows="5" class=" js-font-resize form-control"></textarea>
                        </div>

                        <button type="submit" class=" js-font-resize btn btn-primary btn-block mt-20">{{ trans('update.proceed_to_checkout') }}</button>
                    </form>
                </div>

                <div class=" js-font-resize col-12 col-md-6 d-flex-center px-30 px-lg-80 py-30 py-lg-50">
                    <div class=" js-font-resize gift-item-card d-flex">

                        @if($itemType == 'course')
                            @include('web.default.gift.course_card',['webinar' => $item])
                        @elseif($itemType == 'bundle')
                            @include('web.default.gift.bundle_card',['bundle' => $item])
                        @elseif($itemType == 'product')
                            @include('web.default.gift.product_card',['product' => $item])
                        @endif

                        <div class=" js-font-resize gift-item-card-icon">
                            <img src="/assets/default/img/gift/gift.svg" alt="gift">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/js/parts/gifts.min.js"></script>

@endpush
