<!DOCTYPE html>
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
    <title>{{ $pageTitle ?? '' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    <link rel="manifest" href="{{ asset('/manifest.json') }}">
    <!-- General CSS File -->
    <link rel="stylesheet" href="/assets/admin/vendor/bootstrap/bootstrap.min.css" />
    <link rel="stylesheet" href="/assets/vendors/fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="/assets/admin/vendor/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="/assets/admin/css/style.css">
    <link rel="stylesheet" href="/assets/admin/css/components.css">
    @if ($isRtl)
        <link rel="stylesheet" href="/assets/admin/css/rtl.css">
    @endif
    <link rel="stylesheet" href="/assets/default/vendors/toast/jquery.toast.min.css">
    <link rel="stylesheet" href="/assets/admin/css/custom.css">
    <style>
        .btn-primary {
            color: white;
            background-color: #c14b93;
            border-color: white;
            box-shadow: 0 3px 6px 0 #c14b93;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            box-shadow: 0 3px 6px 0 #c14b93;
        }

        .danger-transparent-alert {
            border-radius: 15px;
            border: 1px solid #f63c3c;
            background-color: rgba(246, 60, 60, 0.05);
        }

        .danger-transparent-alert * {
            color: #f63c3c;
        }

        .danger-transparent-alert__icon {
            width: 40px;
            min-width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(246, 60, 60, 0.3);
        }

        .auth-hero {
            background-image: url("{{ asset('assets/default/img/auth/BG.png') }}");
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
            bottom: 0;
            content: "";
            right: 0;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            overflow: hidden;


        }

        .auth_hero_2 {
            height: 100vh;
            background-position: bottom;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .content {
            overflow: auto;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-content: center;
            align-items: center;
            flex-direction: column;
            flex-wrap: wrap;
        }

        .bg-primary-acadima {
            background-color: #fff !important;
        }

        .bg-secondary-acadima {
            background-color: #fff !important;
            transition: all 0.5s ease;
        }

        .border-radius-lg {
            border-radius: 20px !important;
        }

        @media(max-width:992px) {
            .auth_hero_2 {
                height: fit-content;
                min-height: 50vh !important;
                background-position: left;
            }
        }

        @media(max-width: 992px) and (min-width: 768px) {
            .content {
                min-height: 50vh !important;
                height: fit-content;
            }
        }
    </style>
</head>

<body class="@if ($isRtl) rtl @endif">

    <div id="app">
        @php
            $getPageBackgroundSettings = getPageBackgroundSettings();
        @endphp

        <section class="section">
            <div class="d-flex flex-wrap  bg-primary-acadima col-12 content">
                <div
                    class="p-3 order-lg-1 order-2  text-light  col-sm-7 col-md-8 col-lg-4 bg-secondary-acadima border-radius-lg shadow border">

                    @yield('content')

                </div>


            </div>
        </section>
    </div>
    <!-- General JS Scripts -->
    <script src="/assets/admin/vendor/jquery/jquery-3.3.1.min.js"></script>
    <script src="/assets/admin/vendor/poper/popper.min.js"></script>
    <script src="/assets/admin/vendor/bootstrap/bootstrap.min.js"></script>
    <script src="/assets/admin/vendor/nicescroll/jquery.nicescroll.min.js"></script>
    <script src="/assets/admin/vendor/moment/moment.min.js"></script>
    <script src="/assets/admin/js/stisla.js"></script>
    <script src="/assets/admin/vendor/daterangepicker/daterangepicker.min.js"></script>
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
                    text: '{{ session()->get('toast')['msg'] ?? '' }}',
                    bgColor: '@if (session()->get('toast')['status'] == 'success') #43d477 @else #f63c3c @endif',
                    textColor: 'white',
                    hideAfter: 10000,
                    position: 'bottom-right',
                    icon: '{{ session()->get('toast')['status'] }}'
                });
            @endif
        })(jQuery);
    </script>

    <!-- Template JS File -->
    <script src="/assets/admin/js/scripts.js"></script>
    <script src="/assets/admin/js/custom.js"></script>
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
