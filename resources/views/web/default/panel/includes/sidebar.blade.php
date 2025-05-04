@php
    $getPanelSidebarSettings = getPanelSidebarSettings();
@endphp
<style>

</style>
<div class="xs-panel-nav d-flex d-lg-none justify-content-between p-15">
    <div class="user-info d-flex align-items-center justify-content-between">
        <div class="user-avatar">
            {{-- <img src="{{ $authUser->getAvatar(100) }}" class="img-cover" alt="{{ $authUser->full_name }}"> --}}
            <img src="{{ asset('store/Acadima/acad-logo.webp') }}" class="img-cover" alt="{{ $authUser->full_name }}">

        </div>

        <div class="user-name ml-15">
            <h3 class="font-16 font-weight-bold text-black">{{ $authUser->full_name }}</h3>
        </div>
    </div>

    <button
        class="sidebar-toggler btn-transparent d-flex flex-column-reverse justify-content-center align-items-center p-5 rounded-sm sidebarNavToggle gap-5"
        type="button">
        <!-- <span class="text-black">{{ trans('navbar.menu') }}</span> -->
        <i data-feather="menu" width="30" height="30" class="text-black"></i>
    </button>
</div>
<div class="panel-sidebar pt-50 pb-25 px-25 " id="panelSidebar" style="overflow: auto">
    <button class="btn-transparent panel-sidebar-close sidebarNavToggle text-black p-3">
        <i data-feather="x" width="24" height="24"></i>
    </button>

    <div class="user-info d-flex align-items-center flex-row flex-lg-column justify-content-lg-center gap-3">
        <a href="/panel" class="p-3">
            <img src="{{ asset('store/Acadima/acad-logo.webp') }}"  class="" alt="{{ $authUser->full_name }}" style="color:#000;">
        </a>

        <div class="d-flex flex-column align-items-center justify-content-center">
            <a href="/panel" class="user-name mt-15">
                <h3 class="font-16 font-weight-bold text-center text-pink">{{ $authUser->full_name }}</h3>
                <h3 class="font-16 font-weight-bold text-center text-pink">{{ $authUser->user_code }}</h3>
            </a>

            @if (!empty($authUser->getUserGroup()))
                <span class="create-new-user mt-10">{{ $authUser->getUserGroup()->name }}</span>
            @endif
        </div>
    </div>

    @if ($authUser->isOrganization() or $authUser->isTeacher())
        <div class="d-flex sidebar-user-stats pb-10 pb-lg-20 mt-15 mt-lg-10 justify-content-center">
            <div class="sidebar-user-stat-item d-flex flex-column">
                <strong class="text-center text-black">{{ $authUser->webinars()->count() }}</strong>
                <span class="font-12">{{ trans('panel.classes') }}</span>
            </div>

            <!-- <div class="border-left mx-30"></div> -->
           {{-- <div class="sidebar-user-stat-item d-flex flex-column">
                <strong class="text-center text-black">{{ $authUser->followers()->count() }}</strong>
                <span class="font-12">{{ trans('panel.followers') }}</span>
            </div>  --}}
            {{-- @else
            <div class="sidebar-user-stat-item d-flex flex-column">
                <strong class="text-center">{{ $authUser->following()->count() }}</strong>
                <span class="font-12">{{ trans('panel.following') }}</span>
            </div> --}}
        </div>
    @endif

    <ul id="panel-sidebar-scroll"
        class="sidebar-menu pt-10 @if (!empty($authUser->userGroup)) has-user-group @endif @if (empty($getPanelSidebarSettings) or empty($getPanelSidebarSettings['background'])) without-bottom-image @endif"
        @if (!empty($isRtl) and $isRtl) data-simplebar-direction="rtl" @endif>

        @can('show_panel')
            <li class="sidenav-item {{ request()->is('panel') ? 'sidenav-item-active' : '' }}">
                <a href="/panel" class="d-flex align-items-center">
                    <span class="sidenav-item-icon mr-10">
                        @include('web.default.panel.includes.sidebar_icons.dashboard', [
                            'class' => 'cls-3',
                        ])
                    </span>
                    <span class="font-14 text-black font-weight-500">{{ trans('panel.main_menu') }}</span>
                </a>
            </li>
        @endcan
        {{-- requirements link --}}
        @if (auth()->user()->Student)
            <li
                class="sidenav-item {{ (request()->is('panel/requirements') or request()->is('panel/requirements/*') or request()->is('panel/newEnrollment') or request()->is('panel/courses/applied')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#requirementsCollapse" role="button"
                    aria-expanded="false" aria-controls="requirementsCollapse">
                    <span class="sidenav-item-icon mr-10">
                        @include('web.default.panel.includes.sidebar_icons.requirements')
                    </span>
                    <span class="font-14 text-black font-weight-500">{{ trans('panel.acpt_rgst') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/requirements') or request()->is('panel/requirements/*') or request()->is('panel/newEnrollment') or request()->is('panel/courses/applied')) ? 'show' : '' }}"
                    id="requirementsCollapse">
                    <ul class="sidenav-item-collapse">
                        @if (count(auth()->user()->getAllPurchasedWebinarsIds()) > 0 || count(auth()->user()->webinarOfflinePayments) > 0)
                            <li class="mt-5 {{ request()->is('panel/courses/applied') ? 'active' : '' }}">
                                <a href="/panel/courses/applied">{{ trans('panel.courses_pay') }}</a>
                            </li>
                        @endif
                        @if (count(auth()->user()->student->bundleStudent) > 0)
                            <li class="mt-5 {{ request()->is('panel/requirements/applied') ? 'active' : '' }}">
                                <a href="/panel/requirements/applied">{{ trans('panel.req_apply') }}</a>
                            </li>

                            @if (count(auth()->user()->student->bundleStudent()->whereHas('bundle.category.categoryRequirements')->get()) > 0)
                                <li class="mt-5 {{ request()->is('panel/requirements') ? 'active' : '' }}">
                                    <a href="/panel/requirements">{{ trans('panel.requirements') }}</a>
                                </li>
                            @endif
                        @endif
                        <li class="mt-5 {{ request()->is('panel/newEnrollment') ? 'active' : '' }}">
                            <a href="/panel/newEnrollment">
                                {{ trans('panel.new_rgstr') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif


        @if ($authUser->isOrganization())
            <li
                class="sidenav-item {{ (request()->is('panel/instructors') or request()->is('panel/manage/instructors*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#instructorsCollapse" role="button"
                    aria-expanded="false" aria-controls="instructorsCollapse">
                    <span class="sidenav-item-icon mr-10">
                        @include('web.default.panel.includes.sidebar_icons.teachers')
                    </span>
                    <span class="font-14 text-black font-weight-500">{{ trans('public.instructors') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/instructors') or request()->is('panel/manage/instructors*')) ? 'show' : '' }}"
                    id="instructorsCollapse">
                    <ul class="sidenav-item-collapse">
                        <li class="mt-5 {{ request()->is('panel/instructors/new') ? 'active' : '' }}">
                            <a href="/panel/manage/instructors/new">{{ trans('public.new') }}</a>
                        </li>
                        <li class="mt-5 {{ request()->is('panel/manage/instructors') ? 'active' : '' }}">
                            <a href="/panel/manage/instructors">{{ trans('public.list') }}</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li
                class="sidenav-item {{ (request()->is('panel/students') or request()->is('panel/manage/students*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#studentsCollapse" role="button"
                    aria-expanded="false" aria-controls="studentsCollapse">
                    <span class="sidenav-item-icon mr-10">
                        @include('web.default.panel.includes.sidebar_icons.students')
                    </span>
                    <span class="font-14 text-black font-weight-500">{{ trans('quiz.students') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/students') or request()->is('panel/manage/students*')) ? 'show' : '' }}"
                    id="studentsCollapse">
                    <ul class="sidenav-item-collapse">
                        <li class="mt-5 {{ request()->is('panel/manage/students/new') ? 'active' : '' }}">
                            <a href="/panel/manage/students/new">{{ trans('public.new') }}</a>
                        </li>
                        <li class="mt-5 {{ request()->is('panel/manage/students') ? 'active' : '' }}">
                            <a href="/panel/manage/students">{{ trans('public.list') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

        @can('student_showClasses')
            <li
                class="sidenav-item {{ (request()->is('panel/webinars') or request()->is('panel/webinars/*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#webinarCollapse" role="button"
                    aria-expanded="false" aria-controls="webinarCollapse">
                    <span class="sidenav-item-icon mr-10">
                        @include('web.default.panel.includes.sidebar_icons.webinars')
                    </span>
                    @if ($authUser->isUser())
                        <span class="font-14 text-black font-weight-500">{{ trans('panel.moqarrat') }}</span>
                    @else
                        <span class="font-14 text-black font-weight-500">{{ trans('panel.edu_courses') }}  </span>
                    @endif
                </a>

                <div class="collapse {{ (request()->is('panel/bundles/*') or request()->is('panel/webinars/*')) ? 'show' : '' }}"
                    id="webinarCollapse">
                    <ul class="sidenav-item-collapse">
                        @if ($authUser->isOrganization() || $authUser->isTeacher())
                            {{-- <li class="mt-5 {{ request()->is('panel/webinars/new') ? 'active' : '' }}">
                                <a href="/panel/webinars/new">{{ trans('public.new') }}</a>
                            </li> --}}

                            <li class="mt-5 {{ request()->is('panel/webinars') ? 'active' : '' }}">
                                <a href="/panel/webinars">{{ trans('panel.my_classes') }}</a>
                            </li>

                            {{-- <li class="mt-5 {{ request()->is('panel/webinars/invitations') ? 'active' : '' }}">
                                <a href="/panel/webinars/invitations">{{ trans('panel.invited_classes') }}</a>
                            </li> --}}
                        @endif

                        @if (!empty($authUser->organ_id))
                            <li class="mt-5 {{ request()->is('panel/webinars/organization_classes') ? 'active' : '' }}">
                                <a
                                    href="/panel/webinars/organization_classes">{{ trans('panel.organization_classes') }}</a>
                            </li>
                        @endif
                        @if ($authUser->isUser())
                            @if (count(auth()->user()->purchasedBundles) > 0)
                                <li class="mt-5 {{ request()->is('panel/bundles/purchases') ? 'active' : '' }}">
                                    <a href="/panel/bundles/purchases">{{trans('panel.academic_program_schedule')}}</a>
                                </li>
                            @endif

                            @if (count(auth()->user()->getAllPurchasedWebinarsIds()) > 0)
                                <li class="mt-5 {{ request()->is('panel/webinars/purchases') ? 'active' : '' }}">
                                    <a href="/panel/webinars/purchases">{{ trans('panel.courses_tt') }}</a>
                                </li>
                            @endif
                        @endif


                        {{-- @if ($authUser->isOrganization() || $authUser->isTeacher())
                            <li class="mt-5 {{ request()->is('panel/webinars/comments') ? 'active' : '' }}">
                                <a href="/panel/webinars/comments">{{ trans('panel.my_class_comments') }}</a>
                            </li>


                            <li class="mt-5 {{ request()->is('panel/webinars/my-comments') ? 'active' : '' }}">
                                <a href="/panel/webinars/my-comments">{{ trans('panel.my_comments') }}</a>
                            </li>

                            <li class="mt-5 {{ request()->is('panel/webinars/favorites') ? 'active' : '' }}">
                                <a href="/panel/webinars/favorites">{{ trans('panel.favorites') }}</a>
                            </li>
                        @endif --}}
                    </ul>
                </div>
            </li>
        @endcan

        @if (!empty(getFeaturesSettings('upcoming_courses_status')))
            @can('student_showUpcomingCourses')
                <li
                    class="sidenav-item {{ (request()->is('panel/upcoming_courses') or request()->is('panel/upcoming_courses/*')) ? 'sidenav-item-active' : '' }}">
                    <a class="d-flex align-items-center" data-toggle="collapse" href="#upcomingCoursesCollapse"
                        role="button" aria-expanded="false" aria-controls="upcomingCoursesCollapse">
                        <span class="sidenav-item-icon mr-10">
                            <i data-feather="film" class="img-cover"></i>
                        </span>
                        <span class="font-14 text-black font-weight-500">{{ trans('update.upcoming_courses') }}</span>
                    </a>

                    <div class="collapse {{ (request()->is('panel/upcoming_courses') or request()->is('panel/upcoming_courses/*')) ? 'show' : '' }}"
                        id="upcomingCoursesCollapse">
                        <ul class="sidenav-item-collapse">
                            @if ($authUser->isOrganization() || $authUser->isTeacher())
                                <li class="mt-5 {{ request()->is('panel/upcoming_courses/new') ? 'active' : '' }}">
                                    <a href="/panel/upcoming_courses/new">{{ trans('public.new') }}</a>
                                </li>

                                <li class="mt-5 {{ request()->is('panel/upcoming_courses') ? 'active' : '' }}">
                                    <a href="/panel/upcoming_courses">{{ trans('update.my_upcoming_courses') }}</a>
                                </li>
                            @endif

                            <li class="mt-5 {{ request()->is('panel/upcoming_courses/followings') ? 'active' : '' }}">
                                <a href="/panel/upcoming_courses/followings">{{ trans('update.following_courses') }}</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endcan
        @endif

        @if ($authUser->isOrganization() or $authUser->isTeacher())
            <li
                class="sidenav-item {{ (request()->is('panel/bundles') or request()->is('panel/bundles/*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#bundlesCollapse" role="button"
                    aria-expanded="false" aria-controls="bundlesCollapse">
                    <span class="sidenav-item-icon assign-fill mr-10">
                        @include('web.default.panel.includes.sidebar_icons.bundles')
                    </span>
                    <span class="font-14 text-black font-weight-500">{{ trans('update.bundles') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/bundles') or request()->is('panel/bundles/*')) ? 'show' : '' }}"
                    id="bundlesCollapse">
                    <ul class="sidenav-item-collapse">
                        {{-- <li class="mt-5 {{ request()->is('panel/bundles/new') ? 'active' : '' }}">
                            <a href="/panel/bundles/new">{{ trans('public.new') }}</a>
                        </li> --}}

                        <li class="mt-5 {{ request()->is('panel/bundles*') ? 'active' : '' }}">
                            <a href="/panel/bundles">{{ trans('update.my_bundles') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

        @if (getFeaturesSettings('webinar_assignment_status'))
            @can('student_showTasks')
                <li
                    class="sidenav-item {{ (request()->is('panel/assignments') or request()->is('panel/assignments/*')) ? 'sidenav-item-active' : '' }}">
                    <a class="d-flex align-items-center" data-toggle="collapse" href="#assignmentCollapse"
                        role="button" aria-expanded="false" aria-controls="assignmentCollapse">
                        <span class="sidenav-item-icon mr-10">
                            @include('web.default.panel.includes.sidebar_icons.assignments')
                        </span>
                        <span class="font-14 text-black font-weight-500">{{ trans('update.assignments') }}</span>
                    </a>

                    <div class="collapse {{ (request()->is('panel/assignments') or request()->is('panel/assignments/*')) ? 'show' : '' }}"
                        id="assignmentCollapse">
                        <ul class="sidenav-item-collapse">
                            @if (auth()->user()->isUser())
                                <li class="mt-5 {{ request()->is('panel/assignments/my-assignments') ? 'active' : '' }}">
                                    <a href="/panel/assignments/my-assignments">{{ trans('update.my_assignments') }}</a>
                                </li>
                            @endif

                            @if ($authUser->isOrganization() || $authUser->isTeacher())
                                <li
                                    class="mt-5 {{ request()->is('panel/assignments/my-courses-assignments') ? 'active' : '' }}">
                                    <a
                                        href="/panel/assignments/my-courses-assignments">{{ trans('update.students_assignments') }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endcan
        @endif

        @can('student_showSession')
            <li
                class="sidenav-item {{ (request()->is('panel/meetings') or request()->is('panel/meetings/*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#meetingCollapse" role="button"
                    aria-expanded="false" aria-controls="meetingCollapse">
                    <span class="sidenav-item-icon mr-10">
                        @include('web.default.panel.includes.sidebar_icons.requests')
                    </span>
                    <span class="font-14 text-black font-weight-500">{{ trans('panel.meetings') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/meetings') or request()->is('panel/meetings/*')) ? 'show' : '' }}"
                    id="meetingCollapse">
                    <ul class="sidenav-item-collapse">

                        <li class="mt-5 {{ request()->is('panel/meetings/reservation') ? 'active' : '' }}">
                            <a href="/panel/meetings/reservation">{{ trans('public.my_reservation') }}</a>
                        </li>

                        @if ($authUser->isOrganization() || $authUser->isTeacher())
                            <li class="mt-5 {{ request()->is('panel/meetings/requests') ? 'active' : '' }}">
                                <a href="/panel/meetings/requests">{{ trans('panel.requests') }}</a>
                            </li>

                            <li class="mt-5 {{ request()->is('panel/meetings/settings') ? 'active' : '' }}">
                                <a href="/panel/meetings/settings">{{ trans('panel.settings') }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>
        @endcan
        @can('student_showTest')
            <li
                class="sidenav-item {{ (request()->is('panel/quizzes') or request()->is('panel/quizzes/*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#quizzesCollapse" role="button"
                    aria-expanded="false" aria-controls="quizzesCollapse">
                    <span class="sidenav-item-icon mr-10">
                        @include('web.default.panel.includes.sidebar_icons.quizzes')
                    </span>
                    <span class="font-14 text-black font-weight-500">{{ trans('panel.quizzes') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/quizzes') or request()->is('panel/quizzes/*')) ? 'show' : '' }}"
                    id="quizzesCollapse">
                    <ul class="sidenav-item-collapse">
                        @if ($authUser->isOrganization() || $authUser->isTeacher())
                            <li class="mt-5 {{ request()->is('panel/quizzes/new') ? 'active' : '' }}">
                                <a href="/panel/quizzes/new">{{ trans('quiz.new_quiz') }}</a>
                            </li>
                            <li class="mt-5 {{ request()->is('panel/quizzes') ? 'active' : '' }}">
                                <a href="/panel/quizzes">{{ trans('public.list') }}</a>
                            </li>
                            <li class="mt-5 {{ request()->is('panel/quizzes/results') ? 'active' : '' }}">
                                <a href="/panel/quizzes/results">{{ trans('public.results') }}</a>
                            </li>
                        @else
                            <li class="mt-5 {{ request()->is('panel/quizzes/my-results') ? 'active' : '' }}">
                                <a href="/panel/quizzes/my-results">{{ trans('public.my_results') }}</a>
                            </li>

                            <li class="mt-5 {{ request()->is('panel/quizzes/opens') ? 'active' : '' }}">
                                <a href="/panel/quizzes/opens">{{ trans('public.not_participated') }}</a>
                            </li>
                        @endif

                    </ul>
                </div>
            </li>
        @endcan
        @can('student_showCertificate')


            <li
                class="sidenav-item {{ (request()->is('panel/certificates') or request()->is('panel/certificates/*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#certificatesCollapse" role="button"
                    aria-expanded="false" aria-controls="certificatesCollapse">
                    <span class="sidenav-item-icon mr-10">
                        @include('web.default.panel.includes.sidebar_icons.certificate')
                    </span>
                    <span class="font-14 text-black font-weight-500">{{ trans('panel.certificates') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/certificates') or request()->is('panel/certificates/*')) ? 'show' : '' }}"
                    id="certificatesCollapse">
                    <ul class="sidenav-item-collapse">
                        @if ($authUser->isOrganization() || $authUser->isTeacher())
                            <li class="mt-5 {{ request()->is('panel/certificates') ? 'active' : '' }}">
                                <a href="/panel/certificates">{{ trans('public.list') }}</a>
                            </li>
                        @endif

                        <li class="mt-5 {{ request()->is('panel/certificates/achievements') ? 'active' : '' }}">
                            <a href="/panel/certificates/achievements">{{ trans('quiz.achievements') }}</a>
                        </li>

                        <li class="mt-5">
                            <a href="/certificate">{{ trans('site.certificate_validation') }}</a>
                        </li>

                        {{-- <li class="mt-5 {{ request()->is('panel/certificates/webinars') ? 'active' : '' }}">
                            <a href="/panel/certificates/webinars">{{ trans('update.course_certificates') }}</a>
                        </li> --}}

                        {{-- <li class="mt-5 {{ request()->is('panel/certificates/certificates_template') ? 'active' : '' }}">
                            <a href="/panel/certificates/certificates_template">الشهادات المعتمدة</a>
                        </li> --}}

                    </ul>
                </div>
            </li>


        @endcan

        @if ($authUser->checkCanAccessToStore())
            @can('student_showStore')
                <li
                    class="sidenav-item {{ (request()->is('panel/store') or request()->is('panel/store/*')) ? 'sidenav-item-active' : '' }}">
                    <a class="d-flex align-items-center" data-toggle="collapse" href="#storeCollapse" role="button"
                        aria-expanded="false" aria-controls="storeCollapse">
                        <span class="sidenav-item-icon assign-fill mr-10">
                            @include('web.default.panel.includes.sidebar_icons.store')
                        </span>
                        <span class="font-14 text-black font-weight-500">{{ trans('update.store') }}</span>
                    </a>

                    <div class="collapse {{ (request()->is('panel/store') or request()->is('panel/store/*')) ? 'show' : '' }}"
                        id="storeCollapse">
                        <ul class="sidenav-item-collapse">
                            @if ($authUser->isOrganization() || $authUser->isTeacher())
                                <li class="mt-5 {{ request()->is('panel/store/products/new') ? 'active' : '' }}">
                                    <a href="/panel/store/products/new">{{ trans('update.new_product') }}</a>
                                </li>

                                <li class="mt-5 {{ request()->is('panel/store/products') ? 'active' : '' }}">
                                    <a href="/panel/store/products">{{ trans('update.products') }}</a>
                                </li>

                                @php
                                    $sellerProductOrderWaitingDeliveryCount = $authUser->getWaitingDeliveryProductOrdersCount();
                                @endphp

                                <li class="mt-5 {{ request()->is('panel/store/sales') ? 'active' : '' }}">
                                    <a href="/panel/store/sales">{{ trans('panel.sales') }}</a>

                                    @if ($sellerProductOrderWaitingDeliveryCount > 0)
                                        <span
                                            class="d-inline-flex align-items-center justify-content-center font-weight-500 ml-15 panel-sidebar-store-sales-circle-badge">{{ $sellerProductOrderWaitingDeliveryCount }}</span>
                                    @endif
                                </li>
                            @endif

                            <li class="mt-5 {{ request()->is('panel/store/purchases') ? 'active' : '' }}">
                                <a href="/panel/store/purchases">{{ trans('panel.my_purchases') }}</a>
                            </li>

                            @if ($authUser->isOrganization() || $authUser->isTeacher())
                                <li class="mt-5 {{ request()->is('panel/store/products/comments') ? 'active' : '' }}">
                                    <a href="/panel/store/products/comments">{{ trans('update.product_comments') }}</a>
                                </li>
                            @endif

                            <li class="mt-5 {{ request()->is('panel/store/products/my-comments') ? 'active' : '' }}">
                                <a href="/panel/store/products/my-comments">{{ trans('panel.my_comments') }}</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endcan
        @endif

        @if (\App\Models\InstallmentOrder::where('user_id', $authUser->id)->where('status', '!=', 'paying')->exists() || \App\Models\OfflinePayment::where('user_id', $authUser->id)->exists())
            <li
                class="sidenav-item {{ (request()->is('panel/financial') or request()->is('panel/financial/*') or request()->is('panel/rewards')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#financialCollapse" role="button"
                    aria-expanded="false" aria-controls="financialCollapse">
                    <span class="sidenav-item-icon mr-10">
                        @include('web.default.panel.includes.sidebar_icons.financial')
                    </span>
                    <span class="font-14 text-black font-weight-500">{{ trans('panel.financial') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/financial') or request()->is('panel/financial/*') or request()->is('panel/rewards')) ? 'show' : '' }}"
                    id="financialCollapse">
                    <ul class="sidenav-item-collapse">

                        @if ($authUser->isOrganization() || $authUser->isTeacher())
                            <li class="mt-5 {{ request()->is('panel/financial/sales') ? 'active' : '' }}">
                                <a href="/panel/financial/sales">{{ trans('financial.sales_report') }}</a>
                            </li>
                        @endif

                        {{-- <li class="mt-5 {{ request()->is('panel/financial/summary') ? 'active' : '' }}">
                                <a href="/panel/financial/summary">{{ trans('financial.financial_summary') }}</a>
                            </li> --}}

                        {{-- <li class="mt-5 {{ request()->is('panel/financial/payout') ? 'active' : '' }}">
                            <a href="/panel/financial/payout">استرداد</a>
                        </li> --}}

                        {{-- <li class="mt-5 {{ request()->is('panel/financial/account') ? 'active' : '' }}">
                            <a href="/panel/financial/account">{{ trans('financial.charge_account') }}</a>
                        </li> --}}

                        {{-- <li class="mt-5 {{ request()->is('panel/financial/subscribes') ? 'active' : '' }}">
                            <a href="/panel/financial/subscribes">{{ trans('financial.subscribes') }}</a>
                        </li> --}}

                        @if ($authUser->isOrganization() || $authUser->isTeacher() and getRegistrationPackagesGeneralSettings('status'))
                            <li
                                class="mt-5 {{ request()->is('panel/financial/registration-packages') ? 'active' : '' }}">
                                <a
                                    href="{{ route('panelRegistrationPackagesLists') }}">{{ trans('update.registration_packages') }}</a>
                            </li>
                        @endif

                        @if (getInstallmentsSettings('status'))
                            @if (\App\Models\InstallmentOrder::query()->where('user_id', auth()->user()->id)->where('status', '!=', 'paying')->exists())
                                <li
                                    class="mt-5 {{ request()->is('panel/financial/installments*') ? 'active' : '' }}">
                                    <a href="/panel/financial/installments">
                                        {{trans('panel.program_installments')}}
                                    </a>
                                </li>
                            @endif
                        @endif
                        @php
                            $rewardSetting = getRewardsSettings();
                        @endphp


                        @if (\App\Models\OfflinePayment::where('user_id', $authUser->id)->exists())
                            <li class="mt-5 {{ request()->is('panel/financial/offline-payments') ? 'active' : '' }}">
                                <a href="/panel/financial/offline-payments">{{trans('panel.offline_payment_record')}}</a>
                            </li>
                        @endif

                        @if (!empty($rewardSetting) and $rewardSetting['status'] == '1')
                            @can('show_reward')
                                <li class="mt-5 {{ request()->is('panel/rewards') ? 'active' : '' }}">
                                    <a href="/panel/rewards">{{ trans('update.rewards') }}
                                    </a>
                                </li>
                            @endcan
                        @endif
                    </ul>
                </div>
            </li>
        @endif


        @can('show_support')
            <li
                class="sidenav-item {{ (request()->is('panel/support') or request()->is('panel/support/*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#supportCollapse" role="button"
                    aria-expanded="false" aria-controls="supportCollapse">
                    <span class="sidenav-item-icon assign-fill mr-10">
                        @include('web.default.panel.includes.sidebar_icons.support')
                    </span>
                    <span class="font-14 text-black font-weight-500">{{trans('panel.support_and_communication_team')}}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/support') or request()->is('panel/support/*')) ? 'show' : '' }}"
                    id="supportCollapse">
                    <ul class="sidenav-item-collapse">
                        @if(auth()->check() && auth()->user()->role_name === 'user')
                        <li class="mt-5 {{ request()->is('panel/support/new') ? 'active' : '' }}">
                            <a href="/panel/support/new">{{ trans('public.new') }}</a>
                        </li>
                    @endif
                        {{-- <li class="mt-5 {{ request()->is('panel/support') ? 'active' : '' }}">
                            <a href="/panel/support">{{ trans('panel.classes_support') }}</a>
                        </li> --}}
                        <li class="mt-5 {{ request()->is('panel/support/tickets') ? 'active' : '' }}">
                            <a href="/panel/support/tickets">{{ trans('panel.support_tickets') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endcan

        @if (
            !$authUser->isUser() or
                !empty($referralSettings) and $referralSettings['status'] and $authUser->affiliate or
                !empty(getRegistrationBonusSettings('status')) and $authUser->enable_registration_bonus)
            @can('student_showMarkting')
                <li
                    class="sidenav-item {{ (request()->is('panel/marketing') or request()->is('panel/marketing/*')) ? 'sidenav-item-active' : '' }}">
                    <a class="d-flex align-items-center" data-toggle="collapse" href="#marketingCollapse" role="button"
                        aria-expanded="false" aria-controls="marketingCollapse">
                        <span class="sidenav-item-icon mr-10">
                            @include('web.default.panel.includes.sidebar_icons.marketing')
                        </span>
                        <span class="font-14 text-black font-weight-500">{{ trans('panel.marketing') }}</span>
                    </a>

                    <div class="collapse {{ (request()->is('panel/marketing') or request()->is('panel/marketing/*')) ? 'show' : '' }}"
                        id="marketingCollapse">
                        <ul class="sidenav-item-collapse">
                            @if (!$authUser->isUser())
                                <li class="mt-5 {{ request()->is('panel/marketing/special_offers') ? 'active' : '' }}">
                                    <a href="/panel/marketing/special_offers">{{ trans('panel.discounts') }}</a>
                                </li>
                                <li class="mt-5 {{ request()->is('panel/marketing/promotions') ? 'active' : '' }}">
                                    <a href="/panel/marketing/promotions">{{ trans('panel.promotions') }}</a>
                                </li>
                            @endif

                            @if (!empty($referralSettings) and $referralSettings['status'] and $authUser->affiliate)
                                <li class="mt-5 {{ request()->is('panel/marketing/affiliates') ? 'active' : '' }}">
                                    <a href="/panel/marketing/affiliates">{{ trans('panel.affiliates') }}</a>
                                </li>
                            @endif

                            @if (!empty(getRegistrationBonusSettings('status')) and $authUser->enable_registration_bonus)
                                <li
                                    class="mt-5 {{ request()->is('panel/marketing/registration_bonus') ? 'active' : '' }}">
                                    <a
                                        href="/panel/marketing/registration_bonus">{{ trans('update.registration_bonus') }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endcan
        @endif

        @if (getFeaturesSettings('forums_status'))
            @can('student_showForums')
                <li
                    class="sidenav-item {{ (request()->is('panel/forums') or request()->is('panel/forums/*')) ? 'sidenav-item-active' : '' }}">
                    <a class="d-flex align-items-center" data-toggle="collapse" href="#forumsCollapse" role="button"
                        aria-expanded="false" aria-controls="forumsCollapse">
                        <span class="sidenav-item-icon assign-fill mr-10">
                            @include('web.default.panel.includes.sidebar_icons.forums')
                        </span>
                        <span class="font-14 text-black font-weight-500">{{ trans('update.forums') }}</span>
                    </a>

                    <div class="collapse {{ (request()->is('panel/forums') or request()->is('panel/forums/*')) ? 'show' : '' }}"
                        id="forumsCollapse">
                        <ul class="sidenav-item-collapse">
                            <li class="mt-5 {{ request()->is('/forums/create-topic') ? 'active' : '' }}">
                                <a href="/forums/create-topic">{{ trans('update.new_topic') }}</a>
                            </li>
                            <li class="mt-5 {{ request()->is('panel/forums/topics') ? 'active' : '' }}">
                                <a href="/panel/forums/topics">{{ trans('update.my_topics') }}</a>
                            </li>

                            <li class="mt-5 {{ request()->is('panel/forums/posts') ? 'active' : '' }}">
                                <a href="/panel/forums/posts">{{ trans('update.my_posts') }}</a>
                            </li>

                            <li class="mt-5 {{ request()->is('panel/forums/bookmarks') ? 'active' : '' }}">
                                <a href="/panel/forums/bookmarks">{{ trans('update.bookmarks') }}</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endcan
        @endif


        {{-- @if ($authUser->isTeacher())
            <li
                class="sidenav-item {{ (request()->is('panel/blog') or request()->is('panel/blog/*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#blogCollapse" role="button"
                    aria-expanded="false" aria-controls="blogCollapse">
                    <span class="sidenav-item-icon assign-fill mr-10">
                        @include('web.default.panel.includes.sidebar_icons.blog')
                    </span>
                    <span class="font-14 text-black font-weight-500">{{ trans('update.articles') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/blog') or request()->is('panel/blog/*')) ? 'show' : '' }}"
                    id="blogCollapse">
                    <ul class="sidenav-item-collapse">
                        <li class="mt-5 {{ request()->is('panel/blog/posts/new') ? 'active' : '' }}">
                            <a href="/panel/blog/posts/new">{{ trans('update.new_article') }}</a>
                        </li>

                        <li class="mt-5 {{ request()->is('panel/blog/posts') ? 'active' : '' }}">
                            <a href="/panel/blog/posts">{{ trans('update.my_articles') }}</a>
                        </li>

                        <li class="mt-5 {{ request()->is('panel/blog/comments') ? 'active' : '' }}">
                            <a href="/panel/blog/comments">{{ trans('panel.comments') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif --}}

        {{-- @if ($authUser->isOrganization() || $authUser->isTeacher())
            <li
                class="sidenav-item {{ (request()->is('panel/noticeboard*') or request()->is('panel/course-noticeboard*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#noticeboardCollapse"
                    role="button" aria-expanded="false" aria-controls="noticeboardCollapse">
                    <span class="sidenav-item-icon mr-10">
                        @include('web.default.panel.includes.sidebar_icons.noticeboard')
                    </span>

                    <span class="font-14 text-black font-weight-500">{{ trans('panel.noticeboard') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/noticeboard*') or request()->is('panel/course-noticeboard*')) ? 'show' : '' }}"
                    id="noticeboardCollapse">
                    <ul class="sidenav-item-collapse">
                        <li class="mt-5 {{ request()->is('panel/noticeboard') ? 'active' : '' }}">
                            <a href="/panel/noticeboard">{{ trans('public.history') }}</a>
                        </li>

                        <li class="mt-5 {{ request()->is('panel/noticeboard/new') ? 'active' : '' }}">
                            <a href="/panel/noticeboard/new">{{ trans('public.new') }}</a>
                        </li>

                        <li class="mt-5 {{ request()->is('panel/course-noticeboard') ? 'active' : '' }}">
                            <a href="/panel/course-noticeboard">{{ trans('update.course_notices') }}</a>
                        </li>

                        <li class="mt-5 {{ request()->is('panel/course-noticeboard/new') ? 'active' : '' }}">
                            <a href="/panel/course-noticeboard/new">{{ trans('update.new_course_notices') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif --}}


        @can('show_notifications')
            <li class="sidenav-item {{ request()->is('panel/notifications') ? 'sidenav-item-active' : '' }}">
                <a href="/panel/notifications" class="d-flex align-items-center">
                    <span class="sidenav-notification-icon sidenav-item-icon mr-10">
                        @include('web.default.panel.includes.sidebar_icons.notifications')
                    </span>
                    <span class="font-14 text-black font-weight-500">{{ trans('panel.notifications') }}</span>
                </a>
            </li>
        @endcan

        {{-- services --}}

        @can('users_services')
            <li class="sidenav-item {{ request()->is('panel/service*') ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#servicesCollapse" role="button"
                    aria-expanded="false" aria-controls="servicesCollapse">
                    <span class="sidenav-setting-icon sidenav-item-icon mr-10">
                        @include('web.default.panel.includes.sidebar_icons.service', ['class' => 'cls-1'])
                    </span>
                    <span class="font-14 text-black font-weight-500">{{  trans('panel.srvcs') }}</span>
                </a>

                <div class="collapse {{ request()->is('panel/services*') ? 'show' : '' }}" id="servicesCollapse">
                    <ul class="sidenav-item-collapse">
                        @can('users_services_list')
                            <li class="{{ request()->is('panel/services', false) ? 'active' : '' }}">
                                <a class="nav-link" href="/panel/services">
                                    <!-- {{ trans('panel.simple_menu') }} -->
                                      Digital Services
                                </a>
                            </li>

                            @if (auth()->user()->services->count() > 0)
                                <li class="{{ request()->is('panel/services/requests', false) ? 'active' : '' }}">
                                    <a class="nav-link" href="/panel/services/requests">{{trans('panel.list_of_requests')}}</a>
                                </li>
                            @endif
                        @endcan
                    </ul>
                </div>
            </li>
        @endcan

        {{-- Setting --}}
        <li class="sidenav-item {{ request()->is('panel/setting') ? 'sidenav-item-active' : '' }}">
            <a href="/panel/setting" class="d-flex align-items-center">
                <span class="sidenav-setting-icon sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.setting')
                </span>
                <span class="font-14 text-black font-weight-500">{{ trans('panel.settings') }}</span>
            </a>
        </li>


        {{-- @if ($authUser->isTeacher() or $authUser->isOrganization())
            <li class="sidenav-item ">
                <a href="{{ $authUser->getProfileUrl() }}" class="d-flex align-items-center">
                    <span class="sidenav-item-icon assign-strock mr-10">
                        <i data-feather="user" stroke="#1f3b64" stroke-width="1.5" width="24" height="24"
                            class="mr-10 webinar-icon"></i>
                    </span>
                    <span class="font-14 text-black font-weight-500">{{ trans('public.my_profile') }}</span>
                </a>
            </li>
        @endif --}}

        <li class="sidenav-item">
            <a href="/logout" class="d-flex align-items-center">
                <span class="sidenav-logout-icon mr-10">
                @include("web.default.panel.includes.sidebar_icons.logout")
                </span>
                
                <span class="font-14 text-danger font-weight-500 ">{{ trans('panel.log_out') }}</span>
            </a>
        </li>
    </ul>
    {{--
    @if (!empty($getPanelSidebarSettings) and !empty($getPanelSidebarSettings['background']))
        <div class="sidebar-create-class d-none d-md-block">
            <a href="{{ !empty($getPanelSidebarSettings['link']) ? $getPanelSidebarSettings['link'] : '' }}"
                class="sidebar-create-class-btn d-block text-right p-5">
                <img src="{{ !empty($getPanelSidebarSettings['background']) ? $getPanelSidebarSettings['background'] : '' }}"
                    alt="">
            </a>
        </div>
    @endif
    --}}
</div>
