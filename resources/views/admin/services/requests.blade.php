@extends('admin.layouts.app')


@section('content')
    <section class="section">
        <div class="section-header">
            <h1>قائمة بالخدمات الإالكترونية</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}/services">الخدمات الإلكترونية</a>
                </div>
                <div class="breadcrumb-item">
                    قائمة بالطلبات
                </div>
            </div>
        </div>
        @php
            $users = $service->users()->paginate(10);
        @endphp

        @if (Session::has('success'))
            <div class="container d-flex justify-content-center mt-80">
                <p class="alert alert-success w-75 text-center"> {{ Session::get('success') }} </p>
            </div>
        @endif

        @if (Session::has('error'))
            <div class="container d-flex justify-content-center mt-80">
                <p class="alert alert-success w-75 text-center"> {{ Session::get('error') }} </p>
            </div>
        @endif



        <div class="section-body">

            <div class="d-flex justify-content-between align-items-center mt-30">
                <h2 class="section-title after-line"> خدمة
                    {{ $service->title }}</h2>
            </div>

            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14 ">
                                    <tr>
                                        <th class="text-center">{{ 'Index' }}</th>
                                        <th class="text-center">{{ 'كود الطالب' }}</th>
                                        <th class="text-center">{{ 'اسم الطالب' }}</th>
                                        <th class="text-center">{{ 'حالة الطلب' }}</th>
                                        <th class="text-center">{{ 'محتوي الطلب' }}</th>
                                        <th class="text-center">{{ 'تاريخ الطلب ' }}</th>
                                        <th class="text-center">{{ 'الادمن' }}</th>

                                        <th width="120">{{ 'الأجراءات' }}</th>
                                    </tr>
                                    @foreach ($users as $user)
                                        <tr class="text-center">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $user->user_code }}</td>
                                            <td class="text-left">
                                                <div class="d-flex align-items-center">
                                                    <figure class="avatar mr-2">
                                                        <img src="{{ $user->getAvatar() }}"
                                                            alt="{{ $user->student ? $user->student->ar_name : null }}">
                                                    </figure>
                                                    <div class="media-body ml-1">
                                                        <div class="mt-0 mb-1 font-weight-bold">
                                                            {{ $user->student ? $user->student->ar_name : null }}</div>

                                                        @if ($user->mobile)
                                                            <div class="text-primary text-left font-600-bold"
                                                                style="font-size:12px;">
                                                                {{ $user->mobile }}</div>
                                                        @endif

                                                        @if ($user->email)
                                                            <div class="text-primary text-small font-600-bold">
                                                                {{ $user->email }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span>{{ trans('admin/main.' . $user->pivot->status) }} </span>
                                                @if ($user->pivot->status == 'rejected')
                                                    @include('admin.includes.message_button', [
                                                        'url' => '#',
                                                        'btnClass' =>
                                                            'd-flex align-items-center justify-content-center mt-1 text-danger',
                                                        'btnText' => '<span class="ml-2">' . ' سبب الرفض</span>',
                                                        'hideDefaultClass' => true,
                                                        'deleteConfirmMsg' => 'هذا سبب الرفض',
                                                        'message' => $user->pivot->message,
                                                        'id' => $user->pivot->id . '_message',
                                                    ])
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @include('admin.services.requestContentMessage', [
                                                    'url' => '#',
                                                    'btnClass' =>
                                                        'd-flex align-items-center justify-content-center mt-1 text-primary',
                                                    'btnText' => '<span class="ml-2">' . ' محتوي الطلب</span>',
                                                    'hideDefaultClass' => true,
                                                    'deleteConfirmMsg' => 'test',
                                                    'message' => $user->pivot->content,
                                                    'id' => $user->pivot->id . '_content',
                                                ])
                                            </td>
                                            <td class="font-12">
                                                {{ Carbon\Carbon::parse($user->pivot->created_at)->translatedFormat(handleDateAndTimeFormat('Y M j | H:i')) }}
                                            </td>
                                            <td class="text-center">{{ $user->pivot->admin->full_name ?? '' }}</td>


                                            {{-- actions --}}
                                            <td width="200" class="">

                                                <div class="d-flex justify-content-center align-items-baseline gap-3"
                                                    style="min-width: max-content">
                                                    @if ($user->pivot->status == 'pending')
                                                        @can('admin_services_requests_approve')
                                                            @if ($user->pivot->bundleDelay)
                                                                @include('admin.includes.batch_transform', [
                                                                    'url' =>
                                                                        getAdminPanelUrl() .
                                                                        '/services/requests/' .
                                                                        $user->pivot->id .
                                                                        '/approve',
                                                                    'btnClass' =>
                                                                        'btn btn-primary d-flex align-items-center btn-sm mt-1 ml-3',
                                                                    'btnText' =>
                                                                        '<i class="fa fa-retweet"></i><span class="ml-2"> قبول' .
                                                                        // trans('admin/main.approve') .
                                                                        '</span>',
                                                                    'hideDefaultClass' => true,
                                                                    'id' => $user->pivot->bundleDelay->id,
                                                                    'bundle' =>
                                                                        $user->pivot->bundleDelay->fromBundle,
                                                                    'title' => 'تأجيل البرنامج',
                                                                ])
                                                            @else
                                                                @include('admin.includes.delete_button', [
                                                                    'url' =>
                                                                        getAdminPanelUrl() .
                                                                        '/services/requests/' .
                                                                        $user->pivot->id .
                                                                        '/approve',
                                                                    'btnClass' =>
                                                                        'btn btn-primary d-flex align-items-center btn-sm mt-1 ml-3',
                                                                    'btnText' =>
                                                                        '<i class="fa fa-check"></i><span class="ml-2"> قبول' .
                                                                        '</span>',
                                                                    'hideDefaultClass' => true,
                                                                ])
                                                            @endif
                                                        @endcan

                                                        @can('admin_services_requests_reject')
                                                            @include(
                                                                'admin.services.confirm_reject_button',
                                                                [
                                                                    'url' =>
                                                                        getAdminPanelUrl() .
                                                                        '/services/requests/' .
                                                                        $user->pivot->id .
                                                                        '/reject',
                                                                    'btnClass' =>
                                                                        'btn btn-danger d-flex align-items-center btn-sm mt-1',
                                                                    'btnText' =>
                                                                        '<i class="fa fa-times"></i><span class="ml-2">' .
                                                                        trans('admin/main.reject') .
                                                                        '</span>',
                                                                    'hideDefaultClass' => true,
                                                                    'id' => $user->pivot->id,
                                                                ]
                                                            )
                                                        @endcan
                                                        
                                                        @can('admin_users_impersonate')
                                                            <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/impersonate"
                                                                target="_blank"
                                                                class="btn-sm btn-success mr-3 text-decoration-none">
                                                                <i class="fa fa-user-shield"></i> تسجيل دخول
                                                            </a>
                                                        @endcan
                                                    @endif

                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                </table>

                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            {{ $users->links() }}
        </div>
    </section>
@endsection




@push('libraries_top')
    <link rel="stylesheet" href="/assets/admin/vendor/owl.carousel/owl.carousel.min.css">
    <link rel="stylesheet" href="/assets/admin/vendor/owl.carousel/owl.theme.min.css">
@endpush
