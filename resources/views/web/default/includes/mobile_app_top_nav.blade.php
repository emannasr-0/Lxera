
<div class=" js-font-resize top-navbar d-flex border-bottom">
    <div class=" js-font-resize container d-flex justify-content-between flex-column flex-lg-row">
        <a class=" js-font-resize navbar-brand navbar-order mr-0 d-flex align-items-center justify-content-center" href="/">
            @if(!empty($generalSettings['logo']))
                <img src="{{ asset('store/Acadima/acadima-logo.webp') }}" class=" js-font-resize img-cover" alt="site logo">
            @endif
        </a>

        <div class=" js-font-resize top-contact-box border-bottom d-flex flex-column flex-md-row align-items-center justify-content-center">
            <div class=" js-font-resize d-flex align-items-center justify-content-center">
                @if(!empty($generalSettings['site_phone']))
                    <span class=" js-font-resize d-flex align-items-center py-10 py-lg-0 text-dark-blue font-14">
                        <i data-feather="phone" width="20" height="20" class=" js-font-resize mr-10"></i>
                        {{ $generalSettings['site_phone'] }}
                    </span>
                @endif

                @if(!empty($generalSettings['site_email']))
                    <div class=" js-font-resize border-left mx-5 mx-lg-15 h-100"></div>

                    <span class=" js-font-resize d-flex align-items-center py-10 py-lg-0 text-dark-blue font-14">
                        <i data-feather="mail" width="20" height="20" class=" js-font-resize mr-10"></i>
                        {{ $generalSettings['site_email'] }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
