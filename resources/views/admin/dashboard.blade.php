@extends('admin.layouts.app')

@push('libraries_top')
    <link rel="stylesheet" href="/assets/admin/vendor/owl.carousel/owl.carousel.min.css">
    <link rel="stylesheet" href="/assets/admin/vendor/owl.carousel/owl.theme.min.css">
@endpush

@section('content')


    <section class=" js-font-resize section js-font-resize">
        <div class=" js-font-resize row">
            <div class=" js-font-resize col-12 mb-4">
                <div class=" js-font-resize hero text-white hero-bg-image hero-bg js-font-resize"
                    data-background="{{ !empty(getPageBackgroundSettings('admin_dashboard')) ? getPageBackgroundSettings('admin_dashboard') : '' }}">
                    <div class=" js-font-resize hero-inner js-font-resize">
                        <h2 class=" js-font-resize js-font-resize">{{ trans('admin/main.welcome') }}, {{ $authUser->full_name }}!</h2>

                        <div class=" js-font-resize d-flex flex-column flex-lg-row align-items-center justify-content-between">
                            @can('admin_general_dashboard_quick_access_links')
                                <div>
                                    <p class=" js-font-resize lead js-font-resize">{{ trans('admin/main.welcome_card_text') }}</p>

                                    <div class=" js-font-resize mt-2 mb-2 d-flex flex-column flex-md-row">
                                        <a href="{{ getAdminPanelUrl() }}/comments/webinars"
                                            class=" js-font-resize js-font-resize mt-2 mt-md-0 btn btn-outline-white btn-lg btn-icon icon-left ml-0 ml-md-2"><i
                                                class=" js-font-resize far fa-comment"></i>{{ trans('admin/main.comments') }} </a>
                                        <a href="{{ getAdminPanelUrl() }}/supports"
                                            class=" js-font-resize js-font-resize mt-2 mt-md-0 btn btn-outline-white btn-lg btn-icon icon-left ml-0 ml-md-2"><i
                                                class=" js-font-resize far fa-envelope"></i>{{ trans('admin/main.tickets') }}</a>
                                        <a href="{{ getAdminPanelUrl() }}/reports/webinars"
                                            class=" js-font-resize js-font-resize mt-2 mt-md-0 btn btn-outline-white btn-lg btn-icon icon-left ml-0 ml-md-2"><i
                                                class=" js-font-resize fas fa-info"></i>{{ trans('admin/main.reports') }}</a>
                                    </div>
                                </div>
                            @endcan

                            @can('admin_clear_cache')
                                <div class=" js-font-resize w-xs-to-lg-100 js-font-resize">
                                    <p class=" js-font-resize lead d-none d-lg-bloc js-font-resizek">&nbsp;</p>

                                    @include('admin.includes.delete_button', [
                                        'url' => getAdminPanelUrl() . '/clear-cache',
                                        'btnClass' => 'btn btn-outline-white btn-lg btn-icon icon-left mt-2 w-100',
                                        'btnText' => trans('admin/main.clear_all_cache'),
                                        'hideDefaultClass' => true,
                                    ])
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class=" js-font-resize row">
            <div class=" js-font-resize col-lg-4 col-md-4 col-sm-12">
                @can('admin_general_dashboard_daily_sales_statistics')
                    @if (!empty($dailySalesTypeStatistics))
                        <div class=" js-font-resize card card-statistic-2 js-font-resize">
                            <div class=" js-font-resize card-stats">
                                <div class=" js-font-resize card-stats-title js-font-resize">{{ trans('admin/main.daily_sales_type_statistics') }}</div>

                                <div class=" js-font-resize card-stats-items">
                                    <div class=" js-font-resize card-stats-item">
                                        <div class=" js-font-resize card-stats-item-count js-font-resize">{{ $dailySalesTypeStatistics['webinarsSales'] }}
                                        </div>
                                        <div class=" js-font-resize card-stats-item-label js-font-resize">{{ trans('admin/main.live_class') }}</div>
                                    </div>

                                    <div class=" js-font-resize card-stats-item"> 
                                        <div class=" js-font-resize card-stats-item-count js-font-resize">{{ $dailySalesTypeStatistics['courseSales'] }}</div>
                                        <div class=" js-font-resize card-stats-item-label js-font-resize">{{ trans('admin/main.course') }}</div>
                                    </div>

                                    <div class=" js-font-resize card-stats-item">
                                        <div class=" js-font-resize card-stats-item-count js-font-resize">{{ $dailySalesTypeStatistics['appointmentSales'] }}
                                        </div>
                                        <div class=" js-font-resize card-stats-item-label js-font-resize">{{ trans('admin/main.appointment') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class=" js-font-resize card-icon shadow-primary bg-primary">
                                <i class=" js-font-resize fas fa-archive"></i>
                            </div>
                            <div class=" js-font-resize card-wrap">
                                <div class=" js-font-resize card-header js-font-resize">
                                    <h4>{{ trans('admin/main.today_sales') }}</h4>
                                </div>
                                <div class=" js-font-resize card-body js-font-resize">
                                    {{ $dailySalesTypeStatistics['allSales'] }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endcan
            </div>


            <div class=" js-font-resize col-lg-4 col-md-4 col-sm-12">
                @can('admin_general_dashboard_income_statistics')
                    @if (!empty($getIncomeStatistics))
                        <div class=" js-font-resize card card-statistic-2 js-font-resize">
                            <div class=" js-font-resize card-stats">
                                <div class=" js-font-resize card-stats-title js-font-resize">{{ trans('admin/main.income_statistics') }}</div>

                                <div class=" js-font-resize card-stats-items">
                                    <div class=" js-font-resize card-stats-item">
                                        <div class=" js-font-resize card-stats-item-count js-font-resize">
                                            {{ handlePrice($getIncomeStatistics['todaySales']) }}</div>
                                        <div class=" js-font-resize card-stats-item-label js-font-resize">{{ trans('admin/main.today') }}</div>
                                    </div>

                                    <div class=" js-font-resize card-stats-item">
                                        <div class=" js-font-resize card-stats-item-count js-font-resize">
                                            {{ handlePrice($getIncomeStatistics['monthSales']) }}</div>
                                        <div class=" js-font-resize card-stats-item-label js-font-resize">{{ trans('admin/main.this_month') }}</div>
                                    </div>

                                    <div class=" js-font-resize card-stats-item">
                                        <div class=" js-font-resize card-stats-item-count js-font-resize">{{ handlePrice($getIncomeStatistics['yearSales']) }}
                                        </div>
                                        <div class=" js-font-resize card-stats-item-label js-font-resize">{{ trans('admin/main.this_year') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class=" js-font-resize card-icon shadow-primary bg-primary">
                                <i class=" js-font-resize fas fa-dollar-sign"></i>
                            </div>
                            <div class=" js-font-resize card-wrap">
                                <div class=" js-font-resize card-header js-font-resize">
                                    <h4>{{ trans('admin/main.total_incomes') }}</h4>
                                </div>
                                <div class=" js-font-resize card-body js-font-resize">
                                    {{ handlePrice($getIncomeStatistics['totalSales']) }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endcan
            </div>

            <div class=" js-font-resize col-lg-4 col-md-4 col-sm-12">
                @can('admin_general_dashboard_total_sales_statistics')
                    @if (!empty($getTotalSalesStatistics))
                        <div class=" js-font-resize card card-statistic-2 js-font-resize">
                            <div class=" js-font-resize card-stats">
                                <div class=" js-font-resize card-stats-title js-font-resize">{{ trans('admin/main.salescount') }}</div>

                                <div class=" js-font-resize card-stats-items">
                                    <div class=" js-font-resize card-stats-item">
                                        <div class=" js-font-resize card-stats-item-count js-font-resize">{{ $getTotalSalesStatistics['todaySales'] }}</div>
                                        <div class=" js-font-resize card-stats-item-label js-font-resize">{{ trans('admin/main.today') }}</div>
                                    </div>
                                    <div class=" js-font-resize card-stats-item">
                                        <div class=" js-font-resize card-stats-item-count js-font-resize">{{ $getTotalSalesStatistics['monthSales'] }}</div>
                                        <div class=" js-font-resize card-stats-item-label js-font-resize">{{ trans('admin/main.this_month') }}</div>
                                    </div>
                                    <div class=" js-font-resize card-stats-item">
                                        <div class=" js-font-resize card-stats-item-count js-font-resize">{{ $getTotalSalesStatistics['yearSales'] }}</div>
                                        <div class=" js-font-resize card-stats-item-label js-font-resize">{{ trans('admin/main.this_year') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class=" js-font-resize card-icon shadow-primary bg-primary">
                                <i class=" js-font-resize fas fa-shopping-cart"></i>
                            </div>

                            <div class=" js-font-resize card-wrap">
                                <div class=" js-font-resize card-header js-font-resize">
                                    <h4>{{ trans('admin/main.total_sales') }}</h4>
                                </div>
                                <div class=" js-font-resize card-body js-font-resize">
                                    {{ $getTotalSalesStatistics['totalSales'] }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endcan
            </div>
        </div>

        <div class=" js-font-resize row">

            @can('admin_general_dashboard_new_sales')
                <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                    <a href="{{ getAdminPanelUrl() }}/financial/sales" class=" js-font-resize card card-statistic-1">
                        <div class=" js-font-resize card-icon bg-primary">
                            <i class=" js-font-resize fas fa-shopping-cart"></i>
                        </div>
                        <div class=" js-font-resize card-wrap">
                            <div class=" js-font-resize card-header js-font-resize">
                                <h4>{{ trans('admin/main.new_sale') }}</h4>
                            </div>
                            <div class=" js-font-resize card-body js-font-resize">
                                {{ $getNewSalesCount }}
                            </div>
                        </div>
                    </a>
                </div>
            @endcan

            @can('admin_general_dashboard_new_comments')
                <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                    <a href="{{ getAdminPanelUrl() }}/comments/webinars" class=" js-font-resize card card-statistic-1 js-font-resize">
                        <div class=" js-font-resize card-icon bg-danger">
                            <i class=" js-font-resize fas fa-comment"></i>
                        </div>
                        <div class=" js-font-resize card-wrap">
                            <div class=" js-font-resize card-header js-font-resize">
                                <h4>{{ trans('admin/main.new_comment') }}</h4>
                            </div>
                            <div class=" js-font-resize card-body js-font-resize">
                                {{ $getNewCommentsCount }}
                            </div>
                        </div>
                    </a>
                </div>
            @endcan

            @can('admin_general_dashboard_new_tickets')
                <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                    <a href="{{ getAdminPanelUrl() }}/supports" class=" js-font-resize card card-statistic-1 js-font-resize">
                        <div class=" js-font-resize card-icon bg-warning">
                            <i class=" js-font-resize far fa-envelope"></i>
                        </div>
                        <div class=" js-font-resize card-wrap">
                            <div class=" js-font-resize card-header js-font-resize">
                                <h4>{{ trans('admin/main.new_ticket') }}</h4>
                            </div>
                            <div class=" js-font-resize card-body js-font-resize">
                                {{ $getNewTicketsCount }}
                            </div>
                        </div>
                    </a>
                </div>
            @endcan

            @can('admin_general_dashboard_new_reviews')
                <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                    <a class=" js-font-resize card card-statistic-1 js-font-resize">
                        <div class=" js-font-resize card-icon bg-success">
                            <i class=" js-font-resize fas fa-eye"></i>
                        </div>
                        <div class=" js-font-resize card-wrap">
                            <div class=" js-font-resize card-header js-font-resize">
                                <h4>{{ trans('admin/main.pending_review_classes') }}</h4>
                            </div>
                            <div class=" js-font-resize card-body js-font-resize">
                                {{ $getPendingReviewCount }}
                            </div>
                        </div>
                    </a>
                </div>
            @endcan

            @can('admin_marketing_dashboard_about_us')
                <div class=" js-font-resize col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class=" js-font-resize card card-statistic-1 js-font-resize">
                        <div class=" js-font-resize card-icon bg-success">
                            <i class=" js-font-resize fas fa-archive"></i>
                        </div>
                        <div class=" js-font-resize card-wrap">
                            <div class=" js-font-resize card-header js-font-resize">
                                <h4>احصائيات بيانات الطلاب (عرفونا منين)</h4>
                            </div>
                            <div class=" js-font-resize card-body js-font-resize">
                                <a href="/admin/abous_us_export" class=" js-font-resize btn btn-primary btn-sm mt-10">اضغط هنا لتنزيل الملف</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

        </div>


        <div class=" js-font-resize row">
            @can('admin_general_dashboard_sales_statistics_chart')
                <div class=" js-font-resize col-lg-8 col-md-12 col-12 col-sm-12">
                    <div class=" js-font-resize card js-font-resize">
                        <div class=" js-font-resize card-header js-font-resize">
                            <h4>{{ trans('admin/main.sales_statistics') }}</h4>
                            <div class=" js-font-resize card-header-action js-font-resize">
                                <div class=" js-font-resize btn-group">
                                    <button type="button"
                                        class=" js-font-resize js-sale-chart-month btn js-font-resize">{{ trans('admin/main.month') }}</button>
                                    <button type="button"
                                        class=" js-font-resize js-sale-chart-year btn btn-primary js-font-resize">{{ trans('admin/main.year') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class=" js-font-resize card-body">
                            <div class=" js-font-resize row">
                                <div class=" js-font-resize col-12">
                                    <div class=" js-font-resize position-relative">
                                        <canvas id="saleStatisticsChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class=" js-font-resize row">
                                <div class=" js-font-resize col-12">
                                    @if (!empty($getMonthAndYearSalesChartStatistics))
                                        <div class=" js-font-resize statistic-details mt-4 position-relative js-font-resize">
                                            <div class=" js-font-resize statistic-details-item">
                                                <span class=" js-font-resize text-muted">
                                                    @if ($getMonthAndYearSalesChartStatistics['todaySales']['grow_percent']['status'] == 'up')
                                                        <span class=" js-font-resize text-primary"><i class=" js-font-resize fas fa-caret-up"></i></span>
                                                    @else
                                                        <span class=" js-font-resize text-danger"><i class=" js-font-resize fas fa-caret-down"></i></span>
                                                    @endif

                                                    {{ $getMonthAndYearSalesChartStatistics['todaySales']['grow_percent']['percent'] }}
                                                </span>

                                                <div class=" js-font-resize detail-value">
                                                    {{ handlePrice($getMonthAndYearSalesChartStatistics['todaySales']['amount']) }}
                                                </div>
                                                <div class=" js-font-resize detail-name js-font-resize">{{ trans('admin/main.today_sales') }}</div>
                                            </div>
                                            <div class=" js-font-resize statistic-details-item">
                                                <span class=" js-font-resize text-muted">
                                                    @if ($getMonthAndYearSalesChartStatistics['weekSales']['grow_percent']['status'] == 'up')
                                                        <span class=" js-font-resize text-primary"><i class=" js-font-resize fas fa-caret-up"></i></span>
                                                    @else
                                                        <span class=" js-font-resize text-danger"><i class=" js-font-resize fas fa-caret-down"></i></span>
                                                    @endif

                                                    {{ $getMonthAndYearSalesChartStatistics['weekSales']['grow_percent']['percent'] }}
                                                </span>

                                                <div class=" js-font-resize detail-value js-font-resize">
                                                    {{ handlePrice($getMonthAndYearSalesChartStatistics['weekSales']['amount']) }}
                                                </div>
                                                <div class=" js-font-resize detail-name js-font-resize">{{ trans('admin/main.week_sales') }}</div>
                                            </div>
                                            <div class=" js-font-resize statistic-details-item js-font-resize">
                                                <span class=" js-font-resize text-muted js-font-resize">
                                                    @if ($getMonthAndYearSalesChartStatistics['monthSales']['grow_percent']['status'] == 'up')
                                                        <span class=" js-font-resize text-primary"><i class=" js-font-resize fas fa-caret-up"></i></span>
                                                    @else
                                                        <span class=" js-font-resize text-danger"><i class=" js-font-resize fas fa-caret-down"></i></span>
                                                    @endif

                                                    {{ $getMonthAndYearSalesChartStatistics['monthSales']['grow_percent']['percent'] }}
                                                </span>

                                                <div class=" js-font-resize detail-value js-font-resize">
                                                    {{ handlePrice($getMonthAndYearSalesChartStatistics['monthSales']['amount']) }}
                                                </div>
                                                <div class=" js-font-resize detail-name js-font-resize">{{ trans('admin/main.month_sales') }}</div>
                                            </div>
                                            <div class=" js-font-resize statistic-details-item js-font-resize">
                                                <span class=" js-font-resize text-muted js-font-resize">
                                                    @if ($getMonthAndYearSalesChartStatistics['yearSales']['grow_percent']['status'] == 'up')
                                                        <span class=" js-font-resize text-primary"><i class=" js-font-resize fas fa-caret-up"></i></span>
                                                    @else
                                                        <span class=" js-font-resize text-danger"><i class=" js-font-resize fas fa-caret-down"></i></span>
                                                    @endif

                                                    {{ $getMonthAndYearSalesChartStatistics['yearSales']['grow_percent']['percent'] }}
                                                </span>

                                                <div class=" js-font-resize detail-value js-font-resize">
                                                    {{ handlePrice($getMonthAndYearSalesChartStatistics['yearSales']['amount']) }}
                                                </div>
                                                <div class=" js-font-resize detail-name js-font-resize">{{ trans('admin/main.year_sales') }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            @can('admin_general_dashboard_recent_comments')
                <div class=" js-font-resize col-lg-4 col-md-12 col-12 col-sm-12 @if (count($recentComments) < 6) pb-30 @endif">
                    <div class=" js-font-resize card @if (count($recentComments) < 6) h-100 @endif">
                        <div class=" js-font-resize card-header js-font-resize">
                            <h4>{{ trans('admin/main.recent_comments') }}</h4>
                        </div>

                        <div class=" js-font-resize card-body d-flex flex-column justify-content-between">
                            <ul class=" js-font-resize list-unstyled list-unstyled-border js-font-resize">
                                @foreach ($recentComments as $recentComment)
                                    <li class=" js-font-resize media">
                                        <img class=" js-font-resize mr-3 rounded-circle" width="50" height="50"
                                            src="{{ $recentComment->user->getAvatar() }}" alt="avatar">
                                        <div class=" js-font-resize media-body js-font-resize">
                                            <div class=" js-font-resize float-right text-primary font-12 js-font-resize">
                                                {{ dateTimeFormat($recentComment->created_at, 'j M Y | H:i') }}</div>
                                            <div class=" js-font-resize media-title js-font-resize">{{ $recentComment->user->full_name }}</div>
                                            <span
                                                class=" js-font-resize text-small text-muted js-font-resize">{{ truncate($recentComment->comment, 150) }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            <div class=" js-font-resize text-center pt-1 pb-1">
                                <a href="{{ getAdminPanelUrl() }}/comments/webinars"
                                    class=" js-font-resize btn btn-primary btn-lg btn-round ">
                                    {{ trans('admin/main.view_all') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
        </div>


        <div class=" js-font-resize row">

            @can('admin_general_dashboard_recent_tickets')
                @if (!empty($recentTickets))
                    <div class=" js-font-resize col-md-4">
                        <div class=" js-font-resize card card-hero">
                            <div class=" js-font-resize card-header">
                                <div class=" js-font-resize card-icon">
                                    <i class=" js-font-resize fas fa-envelope"></i>
                                </div>
                                <h5>{{ trans('admin/main.recent_tickets') }}</h5>
                                <div class=" js-font-resize card-description">{{ $recentTickets['pendingReply'] }}
                                    {{ trans('admin/main.pending_reply') }}</div>
                            </div>

                            <div class=" js-font-resize card-body p-0">
                                <div class=" js-font-resize tickets-list">

                                    @foreach ($recentTickets['tickets'] as $ticket)
                                        <a href="{{ getAdminPanelUrl() }}/supports/{{ $ticket->id }}/conversation"
                                            class=" js-font-resize ticket-item">
                                            <div class=" js-font-resize ticket-title">
                                                <h4>{{ $ticket->title }}</h4>
                                            </div>
                                            <div class=" js-font-resize ticket-info">
                                                <div>{{ $ticket->user->full_name }}</div>
                                                <div class=" js-font-resize bullet"></div>
                                                @if ($ticket->status == 'replied' or $ticket->status == 'open')
                                                    <span
                                                        class=" js-font-resize text-warning  text-small font-600-bold">{{ trans('admin/main.pending_reply') }}</span>
                                                @elseif($ticket->status == 'close')
                                                    <span
                                                        class=" js-font-resize text-danger  text-small font-600-bold">{{ trans('admin/main.close') }}</span>
                                                @else
                                                    <span
                                                        class=" js-font-resize text-primary  text-small font-600-bold">{{ trans('admin/main.replied') }}</span>
                                                @endif
                                            </div>
                                        </a>
                                    @endforeach

                                    <a href="{{ getAdminPanelUrl() }}/supports" class=" js-font-resize ticket-item ticket-more">
                                        {{ trans('admin/main.view_all') }} <i class=" js-font-resize fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endcan

            @can('admin_general_dashboard_recent_webinars')
                @if (!empty($recentWebinars))
                    <div class=" js-font-resize col-md-4">
                        <div class=" js-font-resize card card-hero">
                            <div class=" js-font-resize card-header">
                                <div class=" js-font-resize card-icon">
                                    <i class=" js-font-resize fas fa-users"></i>
                                </div>
                                <h5>{{ trans('admin/main.recent_live_classes') }}</h5>
                                <div class=" js-font-resize card-description">{{ $recentWebinars['pendingReviews'] }}
                                    {{ trans('admin/main.pending_review') }}</div>
                            </div>
                            <div class=" js-font-resize card-body p-0">
                                <div class=" js-font-resize tickets-list">
                                    @foreach ($recentWebinars['webinars'] as $webinar)
                                        <a href="{{ getAdminPanelUrl() }}/webinars/{{ $webinar->id }}/edit"
                                            class=" js-font-resize ticket-item">
                                            <div class=" js-font-resize ticket-title">
                                                <h4>{{ $webinar->title }}</h4>
                                            </div>

                                            <div class=" js-font-resize ticket-info">
                                                <div>{{ $webinar->teacher->full_name }}</div>
                                                <div class=" js-font-resize bullet"></div>
                                                @switch($webinar->status)
                                                    @case(\App\Models\Webinar::$active)
                                                        <span class=" js-font-resize text-success">{{ trans('admin/main.publish') }}</span>
                                                        @if ($webinar->isProgressing())
                                                            <div class=" js-font-resize text-warning text-small font-600-bold">
                                                                ({{ trans('webinars.in_progress') }})</div>
                                                        @elseif($webinar->start_date > time())
                                                            <div class=" js-font-resize text-danger text-small font-600-bold">
                                                                ({{ trans('admin/main.not_conducted') }})</div>
                                                        @else
                                                            <span
                                                                class=" js-font-resize text-success text-small font-600-bold">({{ trans('public.finished') }})</span>
                                                        @endif
                                                    @break

                                                    @case(\App\Models\Webinar::$isDraft)
                                                        <span class=" js-font-resize text-dark">{{ trans('admin/main.is_draft') }}</span>
                                                    @break

                                                    @case(\App\Models\Webinar::$pending)
                                                        <span class=" js-font-resize text-warning">{{ trans('admin/main.waiting') }}</span>
                                                    @break

                                                    @case(\App\Models\Webinar::$inactive)
                                                        <span class=" js-font-resize text-danger">{{ trans('public.rejected') }}</span>
                                                    @break
                                                @endswitch
                                            </div>
                                        </a>
                                    @endforeach

                                    <a href="{{ getAdminPanelUrl() }}/webinars?type=webinar" class=" js-font-resize ticket-item ticket-more">
                                        {{ trans('admin/main.view_all') }} <i class=" js-font-resize fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endcan

            @can('admin_general_dashboard_recent_courses')
                @if (!empty($recentCourses))
                    <div class=" js-font-resize col-md-4">
                        <div class=" js-font-resize card card-hero">
                            <div class=" js-font-resize card-header">
                                <div class=" js-font-resize card-icon">
                                    <i class=" js-font-resize fas fa-play-circle"></i>
                                </div>
                                <h5>{{ trans('admin/main.recent_courses') }}</h5>
                                <div class=" js-font-resize card-description">{{ $recentCourses['pendingReviews'] }}
                                    {{ trans('admin/main.pending_review') }}</div>
                            </div>
                            <div class=" js-font-resize card-body p-0">
                                <div class=" js-font-resize tickets-list">


                                    @foreach ($recentCourses['courses'] as $course)
                                        <a href="{{ getAdminPanelUrl() }}/webinars/{{ $course->id }}/edit"
                                            class=" js-font-resize ticket-item">
                                            <div class=" js-font-resize ticket-title">
                                                <h4>{{ $course->title }}</h4>
                                            </div>

                                            <div class=" js-font-resize ticket-info">
                                                <div>{{ $course->teacher->full_name }}</div>
                                                <div class=" js-font-resize bullet"></div>
                                                @switch($course->status)
                                                    @case(\App\Models\Webinar::$active)
                                                        <span class=" js-font-resize text-success">{{ trans('admin/main.publish') }}</span>
                                                        @if ($course->isProgressing())
                                                            <div class=" js-font-resize text-warning text-small font-600-bold">
                                                                ({{ trans('webinars.in_progress') }})</div>
                                                        @elseif($course->start_date > time())
                                                            <div class=" js-font-resize text-danger text-small font-600-bold">
                                                                ({{ trans('admin/main.not_conducted') }})</div>
                                                        @else
                                                            <span
                                                                class=" js-font-resize text-success text-small font-600-bold">({{ trans('public.finished') }})</span>
                                                        @endif
                                                    @break

                                                    @case(\App\Models\Webinar::$isDraft)
                                                        <span class=" js-font-resize text-dark">{{ trans('admin/main.is_draft') }}</span>
                                                    @break

                                                    @case(\App\Models\Webinar::$pending)
                                                        <span class=" js-font-resize text-warning">{{ trans('admin/main.waiting') }}</span>
                                                    @break

                                                    @case(\App\Models\Webinar::$inactive)
                                                        <span class=" js-font-resize text-danger">{{ trans('public.rejected') }}</span>
                                                    @break
                                                @endswitch
                                            </div>
                                        </a>
                                    @endforeach


                                    <a href="{{ getAdminPanelUrl() }}/webinars?type=course" class=" js-font-resize ticket-item ticket-more">
                                        {{ trans('admin/main.view_all') }} <i class=" js-font-resize fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endcan
        </div>

        @can('admin_general_dashboard_users_statistics_chart')
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-lg-12 col-md-12 col-12 col-sm-12">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-header">
                            <h4>{{ trans('admin/main.new_registration_statistics') }}</h4>
                            <div class=" js-font-resize card-header-action">
                                <div class=" js-font-resize btn-group">
                                    {{-- <a href="#" class=" js-font-resize btn">Views
                                    </a> --}}
                                </div>
                            </div>
                        </div>
                        <div class=" js-font-resize card-body">
                            <div class=" js-font-resize row">
                                <div class=" js-font-resize col-12">
                                    <div class=" js-font-resize position-relative">
                                        <canvas id="usersStatisticsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/chartjs/chart.min.js"></script>
    <script src="/assets/admin/vendor/owl.carousel/owl.carousel.min.js"></script>

    <script src="/assets/admin/js/dashboard.min.js"></script>

    <script>
        (function($) {
            "use strict";

            @if (!empty($getMonthAndYearSalesChart))
                makeStatisticsChart('saleStatisticsChart', saleStatisticsChart, 'Sale', @json($getMonthAndYearSalesChart['labels']),
                    @json($getMonthAndYearSalesChart['data']));
            @endif

            @if (!empty($usersStatisticsChart))
                makeStatisticsChart('usersStatisticsChart', usersStatisticsChart, 'Users', @json($usersStatisticsChart['labels']),
                    @json($usersStatisticsChart['data']));
            @endif

        })(jQuery)
    </script>
@endpush
