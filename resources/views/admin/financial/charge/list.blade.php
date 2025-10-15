@extends('admin.layouts.app')


@php
    $filters = request()->getQueryString();
@endphp

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>شحن حساب طالب</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">شحن محفظة طالب</div>
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
                                    <label class="input-label">{{ trans('admin/main.student_code') }}</label>
                                    <input name='user_code' type="text" class="form-control"
                                        value="{{ request()->get('user_code') }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.student_name') }}</label>
                                    <input name='user_name' type="text" class="form-control"
                                        value="{{ request()->get('user_name') }}">
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.student_email') }}</label>
                                    <input name="email" type="text" class="form-control"
                                        value="{{ request()->get('email') }}">
                                </div>
                            </div>

                            <div class="col-12 row">

                                {{-- <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.start_date') }}</label>
                                        <div class="input-group">
                                            <input type="date" id="from" class="text-center form-control"
                                                name="from" value="{{ request()->get('from') }}"
                                                placeholder="Start Date">
                                        </div>
                                    </div>
                                </div> --}}

                                {{-- <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.end_date') }}</label>
                                        <div class="input-group">
                                            <input type="date" id="to" class="text-center form-control"
                                                name="to" value="{{ request()->get('to') }}" placeholder="End Date">
                                        </div>
                                    </div>
                                </div> --}}

                                <div class="col-md-4">
                                    <div class="form-group mt-1">
                                        <label class="input-label mb-4"> </label>
                                        <input type="submit" class="text-center btn btn-primary w-100"
                                            value="{{ trans('admin/main.show_results') }}">
                                    </div>
                                </div>
                            </div>

                        </div>

                    </form>
                </div>
            </section>

            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ trans('admin/main.student_code') }}</th>
                                        <th class="text-left">بيانات الطالب</th>
                                        <th class>تاريخ التسجيل</th>
                                        <th>حالة الحساب</th>
                                        <th>رصيد الطالب (ر.س)</th>
                                        <th>قيمة الشحن (ر.س)</th>

                                        <th width="120">{{ trans('admin/main.actions') }}</th>
                                    </tr>

                                    @foreach ($users as $index => $user)
                                        <tr>
                                            <td>{{ ++$index }}</td>
                                            <td>{{ $user->user_code ?? '---' }}</td>

                                            <td class="text-left">
                                                <div class="d-flex align-items-center">
                                                    {{-- <figure class="avatar mr-2">
                                                        <img src="{{ $user->getAvatar() }}"
                                                            alt="{{ $user->student ? $user->student->ar_name : $user->full_name }}">
                                                    </figure> --}}
                                                    <div class="media-body ml-1">
                                                        <div class="mt-0 mb-1 font-weight-bold">
                                                            {{ $user->student ? $user->student->ar_name : $user->full_name }}
                                                        </div>

                                                        @if ($user->mobile || $user->student)
                                                            <div class="text-primary text-left font-600-bold"
                                                                style="font-size:12px;">
                                                                {{ $user->mobile ?? $user->student->phone }}</div>
                                                        @endif

                                                        @if ($user->email)
                                                            <div class="text-primary text-small font-600-bold">
                                                                {{ $user->email }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            <td>{{ dateTimeFormat($user->created_at, 'j F Y H:i') }}</td>

                                            <td class="{{ $user->status == 'active' ? 'text-success' : 'text-warning' }}">
                                                {{ trans('admin/main.' . $user->status) }}
                                            </td>

                                            <td>{{ handlePrice($user->getAccountCharge() ?? 0) }}</td>

                                            <form action="/admin/financial/account/{{ $user->id }}/charge"
                                                method="POST" class="chargeForm">
                                                @csrf
                                                <td class="text-center">
                                                    <input type="number" name="charge_amount" value="" required
                                                        class="form-control">
                                                </td>
                                                <td >
                                                    <div class="d-flex" style="min-width: max-content;">
                                                    <button class="btn btn-primary" type="submit" style="width: 100px">شحن
                                                        الحساب</button>

                                                    @can('admin_users_impersonate')
                                                        <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/impersonate"
                                                                target="_blank"
                                                                class="btn-sm btn-success mr-3 text-decoration-none">
                                                                <i class="fa fa-user-shield"></i> تسجيل دخول
                                                            </a>
                                                    @endcan
                                                    </div>
                                                </td>
                                            </form>


                                        </tr>
                                    @endforeach

                                </table>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            {{ $users->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        $(document).ready(function() {
            // Trigger form submission for the form with class 'charge'
            $('.chargeForm').submit(function(event) {
                event
                    .preventDefault(); // Prevent default form submission (if needed, for validation or AJAX)
                let confirmResponse = confirm('هل انت متأكد من اتمام عملية الشحن');
                if (confirmResponse) {
                    // Submit the form
                    $(this).off('submit')
                        .submit(); // This ensures the form gets submitted after our custom logic

                }
            });
        });
    </script>
@endpush
