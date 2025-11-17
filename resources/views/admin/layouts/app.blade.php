<html lang="{{ app()->getLocale() }}">
@php
    $rtlLanguages = !empty($generalSettings['rtl_languages']) ? $generalSettings['rtl_languages'] : [];

    $isRtl =
        (in_array(mb_strtoupper(app()->getLocale()), $rtlLanguages) or
        !empty($generalSettings['rtl_layout']) and $generalSettings['rtl_layout'] == 1);
@endphp

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ $pageTitle ?? '' }} </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- General CSS File -->
    <link rel="stylesheet" href="/assets/admin/vendor/bootstrap/bootstrap.min.css" />
    <link rel="stylesheet" href="/assets/vendors/fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="/assets/default/vendors/toast/jquery.toast.min.css">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    <link rel="manifest" href="{{ asset('/manifest.json') }}">

    @stack('libraries_top')

    <link rel="stylesheet" href="/assets/admin/css/style.css">
    <link rel="stylesheet" href="/assets/admin/css/custom.css">
    <link rel="stylesheet" href="/assets/admin/css/components.css">
    @if ($isRtl)
        <link rel="stylesheet" href="/assets/admin/css/rtl.css">
    @endif
    <link rel="stylesheet" href="/assets/admin/vendor/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">

    @stack('styles_top')
    @stack('scripts_top')

    <style>
        {!! !empty(getCustomCssAndJs('css')) ? getCustomCssAndJs('css') : '' !!} {!! getThemeColorsSettings(true) !!} 
        .high-contrast-overlay {
            position: fixed;
            inset: 0;
            /* top:0; right:0; bottom:0; left:0 */
            background: #ffffff;
            /* white overlay */
            mix-blend-mode: difference;
            /* this is what inverts the colors */
            pointer-events: none;
            /* don't block clicks / hovers */
            z-index: 9999;
        }
    </style>
</head>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            /* =========================
               1) FONT RESIZE (A+, A, A-)
               ========================= */
            const STEP = 1; // +2px / -2px per click
            const MIN_DELTA = -4; // how much user can shrink
            const MAX_DELTA = 4; // how much user can enlarge

            // Always start from 0 on every page load (reset on refresh)
            let delta = 0;

            function getResizableElements() {
                return document.querySelectorAll('.js-font-resize');
            }

            function initBaseSizes() {
                const elements = getResizableElements();
                elements.forEach(el => {
                    if (!el.dataset.baseFontSize) {
                        const computedSize = window.getComputedStyle(el).fontSize;
                        el.dataset.baseFontSize = parseFloat(computedSize); // store as number (px)
                    }
                });
            }

            function applyFontSizes() {
                const elements = getResizableElements();
                elements.forEach(el => {
                    const base = parseFloat(el.dataset.baseFontSize);
                    if (!isNaN(base)) {
                        el.style.fontSize = (base + delta) + 'px';
                    }
                });
            }

            // Initialize once on load
            initBaseSizes();
            applyFontSizes();

            // Buttons
            const increaseBtn = document.getElementById('font-increase');
            const decreaseBtn = document.getElementById('font-decrease');
            const resetBtn = document.getElementById('font-reset');

            if (increaseBtn) {
                increaseBtn.addEventListener('click', function() {
                    delta = Math.min(delta + STEP, MAX_DELTA);
                    applyFontSizes();
                });
            }

            if (decreaseBtn) {
                decreaseBtn.addEventListener('click', function() {
                    delta = Math.max(delta - STEP, MIN_DELTA);
                    applyFontSizes();
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    delta = 0;
                    applyFontSizes();
                });
            }

            /* ==============================
               2) HIGH CONTRAST / INVERT MODE
               ============================== */
            const contrastBtn = document.getElementById('contrast-toggle');
            const OVERLAY_ID = 'high-contrast-overlay';
            let contrastOn = false; // always off on refresh

            function addOverlay() {
                if (!document.getElementById(OVERLAY_ID)) {
                    const overlay = document.createElement('div');
                    overlay.id = OVERLAY_ID;
                    overlay.className = 'high-contrast-overlay';
                    document.body.appendChild(overlay);
                }
            }

            function removeOverlay() {
                const overlay = document.getElementById(OVERLAY_ID);
                if (overlay) {
                    overlay.remove();
                }
            }

            if (contrastBtn) {
                contrastBtn.addEventListener('click', function() {
                    contrastOn = !contrastOn;
                    if (contrastOn) {
                        addOverlay();
                    } else {
                        removeOverlay();
                    }
                });
            }
        });
    </script>
@endpush


<body class=" js-font-resize @if ($isRtl) rtl @endif">

    <div id="app">
        <div class=" js-font-resize main-wrapper">
            @include('admin.includes.navbar')

            @include('admin.includes.sidebar')


            <div class=" js-font-resize main-content js-font-resize">

                @yield('content')

            </div>
            @include('admin.additional_pages.footer')
        </div>

        @stack('models')

        <div class=" js-font-resize modal fade" id="fileViewModal" tabindex="-1" aria-labelledby="fileViewModal" aria-hidden="true">
            <div class=" js-font-resize modal-dialog">
                <div class=" js-font-resize modal-content">
                    <div class=" js-font-resize modal-header">
                        <button type="button" class=" js-font-resize close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class=" js-font-resize modal-body">
                        <img src="" class=" js-font-resize w-100" height="350px" alt="">
                    </div>

                    <div class=" js-font-resize modal-footer">
                        <button type="button" class=" js-font-resize btn btn-secondary"
                            data-dismiss="modal">{{ trans('public.close') }}</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- General JS Scripts -->
    <script src="/assets/admin/vendor/jquery/jquery-3.3.1.min.js"></script>
    <script src="/assets/admin/vendor/poper/popper.min.js"></script>
    <script src="/assets/admin/vendor/bootstrap/bootstrap.min.js"></script>
    <script src="/assets/admin/vendor/nicescroll/jquery.nicescroll.min.js"></script>
    <script src="/assets/admin/vendor/moment/moment.min.js"></script>
    <script src="/assets/admin/js/stisla.js"></script>
    <script src="/assets/default/vendors/toast/jquery.toast.min.js"></script>

    <script>
        (function() {
            "use strict";

            window.csrfToken = $('meta[name="csrf-token"]');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            window.adminPanelPrefix = '{{ getAdminPanelUrl() }}';

            @if (session()->has('toast'))
                $.toast({
                    heading: '{{ session()->get('toast')['title'] ?? '' }}',
                    text: '{!! session()->get('toast')['msg'] ?? '' !!}',
                    bgColor: '@if (session()->get('toast')['status'] == 'success') #43d477 @else #f63c3c @endif',
                    textColor: 'white',
                    hideAfter: 10000,
                    position: 'bottom-right',
                    icon: '{{ session()->get('toast')['status'] }}',
                    escapeMarkup: false // Ensure HTML tags are not escaped
                });
            @endif
        })(jQuery);
    </script>

    <script src="/assets/admin/vendor/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>

    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <!-- Template JS File -->
    <script src="/assets/admin/js/scripts.js"></script>

    @stack('styles_bottom')
    @stack('scripts_bottom')
    @stack('scripts')


    <script>
        var deleteAlertTitle = '{{ trans('public.are_you_sure') }}';
        var deleteAlertHint = '{{ trans('public.deleteAlertHint') }}';
        var deleteAlertConfirm = '{{ trans('public.deleteAlertConfirm') }}';
        var deleteAlertCancel = '{{ trans('public.cancel') }}';
        var deleteAlertSuccess = '{{ trans('public.success') }}';
        var deleteAlertFail = '{{ trans('public.fail') }}';
        var deleteAlertFailHint = '{{ trans('public.deleteAlertFailHint') }}';
        var deleteAlertSuccessHint = '{{ trans('public.deleteAlertSuccessHint') }}';
        var forbiddenRequestToastTitleLang = '{{ trans('public.forbidden_request_toast_lang') }}';
        var forbiddenRequestToastMsgLang = '{{ trans('public.forbidden_request_toast_msg_lang') }}';
    </script>

    <script src="/assets/admin/js/custom.js"></script>
    <script>
        {!! !empty(getCustomCssAndJs('js')) ? getCustomCssAndJs('js') : '' !!}
    </script>
    <script src="{{ asset('/sw.js') }}"></script>
    <script>
        if ("serviceWorker" in navigator) {
            // Register a service worker hosted at the root of the
            // site using the default scope.
            navigator.serviceWorker.register("/sw.js").then(
                (registration) => {
                    console.log("Service worker registration succeeded:", registration);
                },
                (error) => {
                    console.error(`Service worker registration failed: ${error}`);
                },
            );
        } else {
            console.error("Service workers are not supported.");
        }
    </script>
</body>

</html>
