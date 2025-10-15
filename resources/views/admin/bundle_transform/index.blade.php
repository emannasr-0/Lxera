@extends('admin.layouts.app')


@php
    $filters = request()->getQueryString();
@endphp

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.transforms') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.transforms') }}</div>
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
                                <div class="form-group">
                                    <label class="input-label">نوع التحويل</label>
                                    <select name="transform_type" data-plugin-selectTwo class="form-control populate">
                                        <option value="">{{ trans('admin/main.all_status') }}</option>
                                        <option value="form_fee" @if (request()->get('transform_type') == 'form_fee') selected @endif>
                                            رسوم حجز مقعد
                                        </option>
                                        <option value="bundle" @if (request()->get('transform_type') == 'bundle') selected @endif>
                                            دفع كامل الرسوم
                                        </option>
                                        <option value="installment" @if (request()->get('transform_type') == 'installment') selected @endif>
                                            تقسيط
                                        </option>

                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">فرق التحويل</label>
                                    <select name="type" data-plugin-selectTwo class="form-control populate">
                                        <option value="">{{ trans('admin/main.all_status') }}</option>
                                        <option value="none" @if (request()->get('type') == 'none') selected @endif>
                                        متساوي
                                        </option>
                                        <option value="pay" @if (request()->get('type') == 'pay') selected @endif>
                                           دفع
                                        </option>
                                        <option value="refund" @if (request()->get('type') == 'refund') selected @endif>
                                            استيرداد
                                        </option>

                                    </select>
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
                            @can('admin_transforms_export')
                                <a href="{{ getAdminPanelUrl() }}/financial/transforms/export?{{ $filters }}"
                                    class="btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
                            @endcan
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <tr>
                                        <th>#</th>
                                        <th class="text-left">{{ trans('admin/main.student') }}</th>

                                        <th class="text-left">اسم البرنامج المراد التحويل منه</th>
                                        <th class="text-left">اسم البرنامج المراد التحويل إليه</th>
                                        <th class="text-center">{{ trans('admin/main.transform_diff') }}</th>
                                        <th class="text-center">نوع التحويل</th>
                                        <th class="text-center">المبلغ (ر.س)</th>
                                        <th class="text-center">{{ trans('admin/main.date') }}</th>
                                        <th class="text-cen ter">حالة الطلب</th>
                                        <th class="text-cen ter">حالة التحويل</th>
                                        <th class="text-center" width="120">{{ trans('admin/main.actions') }}</th>
                                    </tr>

                                    @foreach ($transforms as $index => $transform)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <td class="text-left">
                                                {{ !empty($transform->user) ? $transform->user->full_name : '' }}
                                                <div class="text-primary text-small font-600-bold">
                                                    {{ !empty($transform->user) ? $transform->user->email : '' }}</div>

                                                <div class="text-primary text-small font-600-bold">ID :
                                                    {{ !empty($transform->user) ? $transform->user->id : '' }}</div>
                                                <div class="text-primary text-small font-600-bold">Code :
                                                    {{ !empty($transform->user) ? $transform->user->user_code : '' }}</div>

                                            </td>

                                            <td class="text-left">
                                                {{ $transform->fromBundle->title }}
                                                <div class="text-primary text-small font-600-bold">ID :
                                                    {{ $transform->from_bundle_id }}</div>
                                            </td>
                                            <td class="text-left">
                                                {{ $transform->toBundle->title }}
                                                <div class="text-primary text-small font-600-bold">ID :
                                                    {{ $transform->to_bundle_id }}</div>
                                            </td>
                                            <td class="text-center">
                                                {{ trans('admin/main.' . $transform->type) }}
                                            </td>
                                            <td class="text-center">
                                                {{ trans('admin/main.' . $transform->transform_type) }}
                                            </td>

                                            <td class="text-center">
                                                {{ handlePrice($transform->amount ?? 0, false) }}
                                            </td>

                                            <td class="text-center">
                                                {{ dateTimeFormat(strtotime($transform->created_at), 'j F Y H:i') }}</td>

                                            <td class="text-center">
                                                {{ trans('admin/main.' . $transform->serviceRequest->status) }}
                                                @if ($transform->serviceRequest->status == 'rejected')
                                                    @include('admin.includes.message_button', [
                                                        'url' => '#',
                                                        'btnClass' =>
                                                            'd-flex align-items-center justify-content-center mt-1 text-danger',
                                                        'btnText' => '<span class="ml-2">' . ' سبب الرفض</span>',
                                                        'hideDefaultClass' => true,
                                                        'deleteConfirmMsg' => 'هذا سبب الرفض',
                                                        'message' => $transform->serviceRequest->message,
                                                        'id' => $transform->serviceRequest->id,
                                                    ])
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                {{ trans('admin/main.' . $transform->status) }}
                                                @if ($transform->status == 'rejected')
                                                    @include('admin.includes.message_button', [
                                                        'url' => '#',
                                                        'btnClass' =>
                                                            'd-flex align-items-center justify-content-center mt-1 text-danger',
                                                        'btnText' => '<span class="ml-2">' . ' سبب الرفض</span>',
                                                        'hideDefaultClass' => true,
                                                        'deleteConfirmMsg' => 'هذا سبب الرفض',
                                                        'message' => $transform->serviceRequest->message,
                                                        'id' => $transform->serviceRequest->id,
                                                    ])
                                                @endif
                                            </td>

                                            {{-- actions --}}
                                            <td width="200" class="text-center">
                                                @if ($transform->status == 'pending')
                                                    <div class="d-flex justify-content-center align-items-baseline gap-3">
                                                        @can('admin_bundle_transform_approve')
                                                            @include('admin.includes.delete_button', [
                                                                'url' =>
                                                                    getAdminPanelUrl() .
                                                                    '/financial/bundle_transforms/' .
                                                                    $transform->id .
                                                                    '/approve',
                                                                'btnClass' =>
                                                                    'btn btn-primary d-flex align-items-center btn-sm mt-1 ml-3',
                                                                'btnText' =>
                                                                    '<i class="fa fa-check"></i><span class="ml-2"> قبول' .
                                                                    // trans('admin/main.approve') .
                                                                    '</span>',
                                                                'hideDefaultClass' => true,
                                                            ])
                                                        @endcan

                                                        @can('admin_bundle_transform_reject')
                                                            @include('admin.services.confirm_reject_button', [
                                                                'url' =>
                                                                    getAdminPanelUrl() .
                                                                    '/services/requests/' .
                                                                    $transform->service_request_id .
                                                                    '/reject',
                                                                'btnClass' =>
                                                                    'btn btn-danger d-flex align-items-center btn-sm mt-1 ml-3',
                                                                'btnText' =>
                                                                    '<i class="fa fa-times"></i><span class="ml-2">' .
                                                                    trans('admin/main.reject') .
                                                                    '</span>',
                                                                'hideDefaultClass' => true,
                                                                'id' => $transform->service_request_id,
                                                            ])
                                                        @endcan

                                                        @can('admin_bundle_transform_change_amount')
                                                            @include('admin.bundle_transform.change_amount_button', [
                                                                'url' =>
                                                                    getAdminPanelUrl() .
                                                                    '/financial/bundle_transforms/' .
                                                                    $transform->id .
                                                                    '/change_amount',
                                                                'btnClass' =>
                                                                    'btn btn-warning d-flex align-items-center btn-sm mt-1',
                                                                'btnText' =>
                                                                    '<i class="fa fa-edit"></i><span class="ml-2">
                                                                        تعديل المبلغ
                                                                    </span>',
                                                                'hideDefaultClass' => true,
                                                                'id' => $transform->service_request_id,
                                                                'amount' => $transform->amount,
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
                            {{ $transforms->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
