@extends('admin.layouts.app')


@php
    $filters = request()->getQueryString();
@endphp

@section('content')
    <section class="section">
        <div class="section-header">
            <h1 >طلبات تأجيل البرامج</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">طلبات تأجيل البرامج</div>
            </div>
        </div>

        <div class="section-body">


            {{-- search --}}
            <section class="card">
                <div class="card-body">
                    <form method="get" class="mb-0">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">كود الطالب</label>
                                    <input name='user_code' type="text" class="form-control"
                                        value="{{ request()->get('user_code') }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">اسم الطالب</label>
                                    <input name='user_name' type="text" class="form-control"
                                        value="{{ request()->get('user_name') }}">
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">بريد الطالب</label>
                                    <input name="email" type="text" class="form-control"
                                        value="{{ request()->get('email') }}">
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group mt-1">
                                    <label class="input-label mb-4"> </label>
                                    <input type="submit" class="text-center btn btn-primary w-100"
                                        value="{{ trans('admin/main.show_results') }}">
                                </div>
                            </div>

                        </div>

                    </form>
                </div>
            </section>

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

            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            @can('admin_bundleDelays_export')
                                <a href="{{ getAdminPanelUrl() }}/financial/bundleDelays/export?{{ $filters }}"
                                    class="btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
                            @endcan
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <tr>
                                        <th>#</th>
                                        <th class="text-left">{{ trans('admin/main.student') }}</th>

                                        <th class="text-left">اسم البرنامج</th>
                                        <th class="text-center">{{ trans('admin/main.date') }}</th>
                                        <th class="text-cen ter">حالة الطلب</th>
                                        <th class="text-cen ter">حالة التأجيل</th>
                                        <th class="text-center" width="120">{{ trans('admin/main.actions') }}</th>
                                    </tr>

                                    @foreach ($bundleDelays as $index => $bundleDelay)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <td class="text-left">
                                                {{ !empty($bundleDelay->user) ? $bundleDelay->user->full_name : '' }}
                                                <div class="text-primary text-small font-600-bold">
                                                    {{ !empty($bundleDelay->user) ? $bundleDelay->user->email : '' }}</div>

                                                <div class="text-primary text-small font-600-bold">ID :
                                                    {{ !empty($bundleDelay->user) ? $bundleDelay->user->id : '' }}</div>
                                                <div class="text-primary text-small font-600-bold">Code :
                                                    {{ !empty($bundleDelay->user) ? $bundleDelay->user->user_code : '' }}</div>

                                            </td>

                                            <td class="text-left">
                                                {{ $bundleDelay->fromBundle->title }}
                                                <div class="text-primary text-small font-600-bold">ID :
                                                    {{ $bundleDelay->from_bundle_id }}</div>
                                                <div class="text-primary text-small font-600-bold">
                                                    {{ $bundleDelay->fromBundle->batch?->title }}</div>
                                            </td>

                                            <td class="text-center">
                                                {{ dateTimeFormat(strtotime($bundleDelay->created_at), 'j F Y H:i') }}</td>

                                            <td class="text-center">
                                                {{ trans('admin/main.' . $bundleDelay->serviceRequest->status) }}
                                                @if ($bundleDelay->serviceRequest->status == 'rejected')
                                                    @include('admin.includes.message_button', [
                                                        'url' => '#',
                                                        'btnClass' =>
                                                            'd-flex align-items-center justify-content-center mt-1 text-danger',
                                                        'btnText' => '<span class="ml-2">' . ' سبب الرفض</span>',
                                                        'hideDefaultClass' => true,
                                                        'deleteConfirmMsg' => 'هذا سبب الرفض',
                                                        'message' => $bundleDelay->serviceRequest->message,
                                                        'id' => $bundleDelay->serviceRequest->id,
                                                    ])
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                {{ trans('admin/main.' . $bundleDelay->status) }}
                                                @if ($bundleDelay->status == 'rejected')
                                                    @include('admin.includes.message_button', [
                                                        'url' => '#',
                                                        'btnClass' =>
                                                            'd-flex align-items-center justify-content-center mt-1 text-danger',
                                                        'btnText' => '<span class="ml-2">' . ' سبب الرفض</span>',
                                                        'hideDefaultClass' => true,
                                                        'deleteConfirmMsg' => 'هذا سبب الرفض',
                                                        'message' => $bundleDelay->serviceRequest->message,
                                                        'id' => $bundleDelay->serviceRequest->id,
                                                    ])
                                                @endif
                                            </td>

                                            {{-- actions --}}
                                            <td width="200" class="text-center">
                                                @if ($bundleDelay->status == 'pending')
                                                    <div class="d-flex justify-content-center align-items-baseline gap-3">

                                                        @php
                                                        $user = $bundleDelay->user;
                                                        @endphp


                                                        {{-- @can('admin_bundle_delay_approve') --}}
                                                            @include('admin.includes.batch_transform', [
                                                                'url' =>
                                                                    getAdminPanelUrl() .
                                                                    '/services/bundle_delay/' .
                                                                    $bundleDelay->id .
                                                                    '/approve',
                                                                'btnClass' =>
                                                                    'btn btn-primary d-flex align-items-center btn-sm mt-1 ml-3',
                                                                'btnText' =>
                                                                    '<i class="fa fa-retweet"></i><span class="ml-2"> قبول' .
                                                                    // trans('admin/main.approve') .
                                                                    '</span>',
                                                                'hideDefaultClass' => true,
                                                                'id' => $bundleDelay->id,
                                                                'bundle' => $bundleDelay->fromBundle,
                                                                'title' => 'تأجيل البرنامج'
                                                            ])
                                                        {{-- @endcan --}}

                                                        @can('admin_bundle_delay_reject')
                                                            @include('admin.services.confirm_reject_button', [
                                                                'url' =>
                                                                    getAdminPanelUrl() .
                                                                    '/services/requests/' .
                                                                    $bundleDelay->service_request_id .
                                                                    '/reject',
                                                                'btnClass' =>
                                                                    'btn btn-danger d-flex align-items-center btn-sm mt-1 ml-3',
                                                                'btnText' =>
                                                                    '<i class="fa fa-times"></i><span class="ml-2">' .
                                                                    trans('admin/main.reject') .
                                                                    '</span>',
                                                                'hideDefaultClass' => true,
                                                                'id' => $bundleDelay->service_request_id,
                                                            ])
                                                        @endcan


                                                    </div>
                                                @endif
                                            </td>

                                        </tr>
                                    @endforeach

                                </table>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            {{ $bundleDelays->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
