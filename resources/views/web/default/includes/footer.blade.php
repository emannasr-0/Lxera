@php
    $socials = getSocials();
    if (!empty($socials) and count($socials)) {
        $socials = collect($socials)->sortBy('order')->toArray();
    }

    $footerColumns = getFooterColumns();
@endphp

<footer class=" js-font-resize footer position-relative user-select-none" style="background-color: #333; z-index: 100;" >
    {{--
    <div class=" js-font-resize container">
        <div class=" js-font-resize row">
            <div class=" js-font-resize col-12">
                <div class=" js-font-resize  footer-subscribe d-block d-md-flex align-items-center justify-content-between">
                    <div class=" js-font-resize flex-grow-1">
                        <strong>{{ trans('footer.join_us_today') }}</strong>
                        <span class=" js-font-resize d-block mt-5 text-white">{{ trans('footer.subscribe_content') }}</span>
                    </div>
                    <div class=" js-font-resize subscribe-input bg-white p-10 flex-grow-1 mt-30 mt-md-0">
                        <form action="/newsletters" method="post">
                            {{ csrf_field() }}

                            <div class=" js-font-resize form-group d-flex align-items-center m-0">
                                <div class=" js-font-resize w-100">
                                    <input type="text" name="newsletter_email" class=" js-font-resize form-control border-0 @error('newsletter_email') is-invalid @enderror" placeholder="{{ trans('footer.enter_email_here') }}"/>
                                    @error('newsletter_email')
                                    <div class=" js-font-resize invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class=" js-font-resize btn btn-primary rounded-pill">{{ trans('footer.join') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $columns = ['first_column','second_column','third_column','forth_column'];
    @endphp

    <div class=" js-font-resize container">
        <div class=" js-font-resize row">

            @foreach ($columns as $column)
                <div class=" js-font-resize col-6 col-md-3">
                    @if (!empty($footerColumns[$column]))
                        @if (!empty($footerColumns[$column]['title']))
                            <span class=" js-font-resize header d-block text-white font-weight-bold">{{ $footerColumns[$column]['title'] }}</span>
                        @endif

                        @if (!empty($footerColumns[$column]['value']))
                            <div class=" js-font-resize mt-20">
                                {!! $footerColumns[$column]['value'] !!}
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach

        </div>

        <div class=" js-font-resize mt-40 border-blue py-25 d-flex align-items-center justify-content-between">
            <div class=" js-font-resize footer-logo">
                <a href="https://anasacademy.uk/">
                    @if (!empty($generalSettings['footer_logo']))
                        <img src="{{ $generalSettings['footer_logo'] }}" class=" js-font-resize img-cover" alt="footer logo">
                    @endif
                </a>
            </div>
            <div class=" js-font-resize footer-social">
                @if (!empty($socials) and count($socials))
                    @foreach ($socials as $social)
                        <a href="{{ $social['link'] }}">
                            <img src="{{ $social['image'] }}" alt="{{ $social['title'] }}" class=" js-font-resize mr-15">
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
--}}
    {{-- @if (getOthersPersonalizationSettings('platform_phone_and_email_position') == 'footer') --}}
    <div class=" js-font-resize footer-copyright-card">
        <div class=" js-font-resize container d-flex align-items-center justify-content-center py-15">
            <div class=" js-font-resize font-14 text-white ltr"><a class=" js-font-resize text-white" href="https://lxera.com/">All rights reserved 2025 ©
                    Lxera.</a> </div>

            {{-- <div class=" js-font-resize d-flex align-items-center justify-content-center">
                    @if (!empty($generalSettings['site_phone']))
                        <div class=" js-font-resize d-flex align-items-center text-white font-14">
                            <i data-feather="phone" width="20" height="20" class=" js-font-resize mr-10"></i>
                            {{ $generalSettings['site_phone'] }}
                        </div>
                    @endif

                    @if (!empty($generalSettings['site_email']))
                        <div class=" js-font-resize border-left mx-5 mx-lg-15 h-100"></div>

                        <div class=" js-font-resize d-flex align-items-center text-white font-14">
                            <i data-feather="mail" width="20" height="20" class=" js-font-resize mr-10"></i>
                            {{ $generalSettings['site_email'] }}
                        </div>
                    @endif
                </div> --}}
        </div>
    </div>
    {{-- @endif --}}

</footer>
