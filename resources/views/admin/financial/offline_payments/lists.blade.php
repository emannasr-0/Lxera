@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.offline_payments') }}</div>
            </div>
        </div>

        <div class="section-body">

            <section class="card">
                <div class="card-body">
                    <form class="mb-0">
                        <input type="hidden" name="page_type" value="{{ $pageType }}">

                        <div class="row">
                            {{-- <div class="@if ($pageType == 'requests') col-md-3 @else col-md-4 @endif">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.search') }}</label>
                                    <input type="text" class="form-control text-center" name="search"
                                        value="{{ request()->get('search') }}">
                                </div>
                            </div> --}}

                            <div class="@if ($pageType == 'requests') col-md-3 @else col-md-2 @endif">
                                <div class="form-group">
                                    <label class="input-label">طالب</label>
                                    <select name="user_ids[]" multiple="multiple" class="form-control search-user-select2"
                                        data-placeholder="البحث باسم طالب">

                                        @if (!empty($users) and $users->count() > 0)
                                            @foreach ($users as $user_filter)
                                                <option value="{{ $user_filter->id }}" selected>
                                                    {{ $user_filter->full_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="@if ($pageType == 'requests') col-md-3 @else col-md-4 @endif">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.start_date') }}</label>
                                    <div class="input-group">
                                        <input type="date" id="fsdate" class="text-center form-control" name="from"
                                            value="{{ request()->get('from') }}" placeholder="Start Date">
                                    </div>
                                </div>
                            </div>

                            <div class="@if ($pageType == 'requests') col-md-3 @else col-md-4 @endif">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.end_date') }}</label>
                                    <div class="input-group">
                                        <input type="date" id="lsdate" class="text-center form-control" name="to"
                                            value="{{ request()->get('to') }}" placeholder="End Date">
                                    </div>
                                </div>
                            </div>

                            @if ($pageType == 'requests')
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.status') }}</label>
                                        <select name="status" data-plugin-selectTwo class="form-control populate">
                                            <option value="">{{ trans('admin/main.all_status') }}</option>
                                            <option value="waiting" @if (request()->get('status') == 'waiting') selected @endif>
                                                {{ trans('admin/main.waiting') }}
                                            </option>
                                            <option value="approved" @if (request()->get('status') == 'approved') selected @endif>
                                                {{ trans('admin/main.approved') }}
                                            </option>
                                            <option value="reject" @if (request()->get('status') == 'reject') selected @endif>
                                                {{ trans('admin/main.rejected') }}
                                            </option>
                                            <option value="canceled" @if (request()->get('status') == 'canceled') selected @endif>
                                                ملغي
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            @endif

                            {{-- <div class="@if ($pageType == 'requests') col-md-3 @else col-md-2 @endif">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.role') }}</label>
                                    <select name="role_id" data-plugin-selectTwo class="form-control populate">
                                        <option value="">{{ trans('admin/main.all_roles') }}</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}"
                                                @if ($role->id == request()->get('role_id')) selected @endif>{{ $role->caption }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}





                            <div class="@if ($pageType == 'requests') col-md-3 @else col-md-2 @endif">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.bank') }}</label>
                                    <select name="account_type" data-plugin-selectTwo class="form-control populate">
                                        <option value="">{{ trans('admin/main.all_banks') }}</option>

                                        @foreach ($offlineBanks as $offlineBank)
                                            <option value="{{ $offlineBank->id }}"
                                                @if (request()->get('account_type') == $offlineBank->id) selected @endif>
                                                {{ $offlineBank->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="@if ($pageType == 'requests') col-md-3 @else col-md-2 @endif">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.filters') }}</label>
                                    <select name="sort" data-plugin-selectTwo class="form-control populate">
                                        <option value="">Filter Type</option>
                                        <option value="amount_asc" @if (request()->get('sort') == 'amount_asc') selected @endif>
                                            {{ trans('admin/main.amount_ascending') }}</option>
                                        <option value="amount_desc" @if (request()->get('sort') == 'amount_desc') selected @endif>
                                            {{ trans('admin/main.amount_descending') }}</option>
                                        <option value="pay_date_asc" @if (request()->get('sort') == 'pay_date_asc') selected @endif>
                                            {{ trans('admin/main.Transaction_time_ascending') }}</option>
                                        <option value="pay_date_desc" @if (request()->get('sort') == 'pay_date_desc') selected @endif>
                                            {{ trans('admin/main.Transaction_time_descending') }}</option>
                                    </select>
                                </div>
                            </div>


                            <div class="@if ($pageType == 'requests') col-md-3 @else col-md-2 @endif">
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

            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            @can('admin_offline_payments_export_excel')
                                <a href="{{ getAdminPanelUrl() }}/financial/offline_payments/excel?{{ http_build_query(request()->all()) }}"
                                    class="btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
                            @endcan
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <thead>
                                        <th>اسم الطالب</th>
                                        <th>{{ trans('admin/main.phone') }}</th>
                                        <th>{{ trans('panel.amount') }} ({{ $currency }})</th>
                                        <th>{{ 'غرض الدفع' }}</th>
                                        <th>{{ 'اسم البنك' }}</th>
                                        <th>{{ 'اي بان(IBAN)' }}</th>
                                        <th>{{ trans('update.attachment') }}</th>
                                        <th>{{ trans('admin/main.status') }}</th>
                                        <th class="text-center">{{ 'تاريخ الطلب ' }}</th>
                                        <th width="150px">{{ trans('admin/main.actions') }}</th>
                                    </thead>

                                    <tbody>
                                        @if ($offlinePayments->count() > 0)
                                            @foreach ($offlinePayments as $offlinePayment)
                                                <tr @if ($offlinePayment->status == 'canceled') style="opacity: 0.5" @endif>
                                                    <td class="text-left">
                                                        {{ $offlinePayment->user->full_name }}
                                                    </td>

                                                    <td class="py-2 text-center">
                                                        {{ $offlinePayment->user->mobile ?? ($offlinePayment->user->student ? $offlinePayment->user->student->phone : '') }}
                                                    </td>

                                                    {{-- <td class="py-2 text-center">{{ $offlinePayment->user->role->caption }}</td> --}}

                                                    <td class="py-2 text-center">
                                                        {{ handlePrice($offlinePayment->amount) }}
                                                    </td>


                                                    <td class="py-2 text-center align-middle w-100">
                                                        <span class="font-16 font-weight-bold text-primary">
                                                            @if ($offlinePayment->pay_for == 'form_fee')
                                                                رسوم حجز مقعد دراسي
                                                            @elseif ($offlinePayment->pay_for == 'bundle')
                                                                الدفع كامل ل
                                                                {{ $offlinePayment->order->orderItems->first()->bundle->title }}
                                                            @elseif ($offlinePayment->pay_for == 'webinar')
                                                                الدفع كامل ل

                                                                {{ $offlinePayment->order->orderItems->first()->webinar->title }}
                                                            @elseif ($offlinePayment->pay_for == 'installment')
                                                                {{ $offlinePayment->order->orderItems->first()->installmentPayment->step->installmentStep->title ?? 'القسط الأول' }}
                                                                ل
                                                                {{ $offlinePayment->order->orderItems->first()->bundle->title }}
                                                            @elseif ($offlinePayment->pay_for == 'service')
                                                                @php
                                                                    $user = $offlinePayment->order->orderItems
                                                                        ->first()
                                                                        ->service->users()
                                                                        ->where('user_id', $offlinePayment->user_id)
                                                                        ->first();
                                                                @endphp

                                                                @include(
                                                                    'admin.services.requestContentMessage',
                                                                    [
                                                                        'url' => '#',
                                                                        'btnClass' =>
                                                                            'd-flex align-items-center justify-content-center mt-1 text-primary',
                                                                        'btnText' =>
                                                                            '<span class="ml-2">' .
                                                                            ' رسوم طلب خدمة ' .
                                                                            $offlinePayment->order->orderItems->first()->service->title .
                                                                            ' </span>',
                                                                        'hideDefaultClass' => true,
                                                                        'deleteConfirmMsg' => 'test',
                                                                        'message' => $user->pivot->content ?? '',
                                                                        'id' => $user->pivot->id ?? 0,
                                                                    ]
                                                                )

                                                                {{-- {{ ' رسوم طلب خدمة '. $offlinePayment->order->orderItems->first()->service->title }} --}}
                                                            @endif
                                                        </span>
                                                    </td>


                                                    @if (!empty($offlinePayment->offlineBank->title))
                                                        <td class="py-2 text-center">
                                                            {{ $offlinePayment->offlineBank->title }}</td>
                                                    @else
                                                        <td class="py-2 text-center">-</td>
                                                    @endif

                                                    <td class="py-2 text-center">
                                                        <span>{{ $offlinePayment->iban }}</span>
                                                    </td>

                                                    <td class=" py-2 text-center align-middle">
                                                        @if (!empty($offlinePayment->attachment))
                                                            <a href="{{ $offlinePayment->getAttachmentPath() }}"
                                                                target="_blank" class="text-primary">
                                                                @if (pathinfo($offlinePayment->attachment, PATHINFO_EXTENSION) != 'pdf')
                                                                    <img src="{{ $offlinePayment->getAttachmentPath() }}"
                                                                        alt="offlinePayment_attachment" width="100px"
                                                                        style="max-height:100px">
                                                                @else
                                                                    pdf ملف <i class="fas fa-file font-20"></i>
                                                                @endif
                                                            </a>
                                                        @else
                                                            ---
                                                        @endif

                                                    </td>
                                                    <td class="py-2 text-center">
                                                        @switch($offlinePayment->status)
                                                            @case(\App\Models\OfflinePayment::$waiting)
                                                                <span class="text-warning">{{ trans('public.waiting') }}</span>
                                                            @break

                                                            @case(\App\Models\OfflinePayment::$approved)
                                                                <span
                                                                    class="text-success">{{ trans('financial.approved') }}</span>
                                                            @break

                                                            @case(\App\Models\OfflinePayment::$reject)
                                                                <span class="text-danger">{{ trans('public.rejected') }}</span>
                                                                @include('admin.includes.message_button', [
                                                                    'url' => '#',
                                                                    'btnClass' => 'd-flex align-items-center mt-1',
                                                                    'btnText' =>
                                                                        '<span class="ml-2">' .
                                                                        ' سبب الرفض</span>',
                                                                    'hideDefaultClass' => true,
                                                                    'deleteConfirmMsg' => 'هذا سبب الرفض',
                                                                    'message' => $offlinePayment->message,
                                                                    'id' => $offlinePayment->id,
                                                                ])
                                                            @break

                                                            @case('canceled')
                                                                <span class="text-primary">ملغي</span>
                                                            @break
                                                        @endswitch
                                                    </td>
                                                     <td class="font-12">
                                                    {{ Carbon\Carbon::parse($offlinePayment->created_at)->translatedFormat(handleDateAndTimeFormat('Y M j | H:i')) }}
                                                </td>

                                                    <td class="py-2 text-center">
                                                        <div class="d-flex">
                                                            @if ($offlinePayment->status == 'waiting')
                                                                @can('admin_offline_payments_approved')
                                                                    @include(
                                                                        'admin.includes.delete_button',
                                                                        [
                                                                            'url' =>
                                                                                getAdminPanelUrl() .
                                                                                '/financial/offline_payments/' .
                                                                                $offlinePayment->id .
                                                                                '/approved',
                                                                            'tooltip' => trans(
                                                                                'financial.approve'),
                                                                            'btnIcon' => 'fa-check',
                                                                        ]
                                                                    )
                                                                @endcan

                                                                @can('admin_offline_payments_reject')
                                                                    @include(
                                                                        'admin.financial.offline_payments.includes.confirm_reject_button',
                                                                        [
                                                                            'url' =>
                                                                                getAdminPanelUrl() .
                                                                                '/financial/offline_payments/' .
                                                                                $offlinePayment->id .
                                                                                '/reject',
                                                                            'tooltip' => trans('public.reject'),
                                                                            'btnIcon' => 'fa-times-circle',
                                                                            'btnClass' => 'ml-2 text-danger',
                                                                            'id' => $offlinePayment->id,
                                                                        ]
                                                                    )
                                                                @endcan
                                                            @endif
                                                        </div>
                                                    </td>

                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>

                                </table>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            {{ $offlinePayments->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-body">
            <div class="section-title ml-0 mt-0 mb-3">
                <h5>{{ trans('admin/main.hints') }}</h5>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="media-body">
                        <div class="text-primary mt-0 mb-1 font-weight-bold">
                            {{ trans('admin/main.offline_payment_hint_title_1') }}</div>
                        <div class=" text-small font-600-bold">
                            {{ trans('admin/main.offline_payment_hint_description_1') }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="media-body">
                        <div class="text-primary mt-0 mb-1 font-weight-bold">
                            {{ trans('admin/main.offline_payment_hint_title_2') }}</div>
                        <div class=" text-small font-600-bold">
                            {{ trans('admin/main.offline_payment_hint_description_2') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
