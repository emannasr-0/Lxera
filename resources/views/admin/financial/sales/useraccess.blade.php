@extends('admin.layouts.app')


@php
    $filters = request()->getQueryString();
@endphp

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.sales') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.sales') }}</div>
            </div>
        </div>

        <div class="section-body">

          


            {{-- <section class="card">
                <div class="card-body">
                    <form method="get" class="mb-0">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.search') }}</label>
                                    <input type="text" class="form-control" name="item_title"
                                        value="{{ request()->get('item_title') }}">
                                </div>
                            </div>


                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.start_date') }}</label>
                                    <div class="input-group">
                                        <input type="date" id="fsdate" class="text-center form-control" name="from"
                                            value="{{ request()->get('from') }}" placeholder="Start Date">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.end_date') }}</label>
                                    <div class="input-group">
                                        <input type="date" id="lsdate" class="text-center form-control" name="to"
                                            value="{{ request()->get('to') }}" placeholder="End Date">
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.status') }}</label>
                                    <select name="status" data-plugin-selectTwo class="form-control populate">
                                        <option value="">{{ trans('admin/main.all_status') }}</option>
                                        <option value="success" @if (request()->get('status') == 'success') selected @endif>
                                            {{ trans('admin/main.success') }}</option>
                                        <option value="refund" @if (request()->get('status') == 'refund') selected @endif>
                                            {{ trans('admin/main.refund') }}</option>
                                        <option value="blocked" @if (request()->get('status') == 'blocked') selected @endif>
                                            {{ trans('update.access_blocked') }}</option>
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.class') }}</label>
                                    <select name="webinar_ids[]" multiple="multiple"
                                        class="form-control search-webinar-select2" data-placeholder="Search classes">

                                        @if (!empty($webinars) and $webinars->count() > 0)
                                            @foreach ($webinars as $webinar)
                                                <option value="{{ $webinar->id }}" selected>{{ $webinar->title }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.instructor') }}</label>
                                    <select name="teacher_ids[]" multiple="multiple" data-search-option="just_teacher_role"
                                        class="form-control search-user-select2" data-placeholder="Search teachers">

                                        @if (!empty($teachers) and $teachers->count() > 0)
                                            @foreach ($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" selected>{{ $teacher->full_name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.student') }}</label>
                                    <select name="student_ids[]" multiple="multiple"
                                        data-search-option="just_student_role" class="form-control search-user-select2"
                                        data-placeholder="Search students">

                                        @if (!empty($students) and $students->count() > 0)
                                            @foreach ($students as $student)
                                                <option value="{{ $student->id }}" selected>{{ $student->full_name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-3">
                                <div class="form-group mt-1">
                                    <label class="input-label mb-4"> </label>
                                    <input type="submit" class="text-center btn btn-primary w-100"
                                        value="{{ trans('admin/main.show_results') }}">
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </section> --}}

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
                                    <label class="input-label">رقم الدفعه</label>
                                    <select name="class_id" class="form-control">
                                        <option value="">كل الدفعات</option>
                                        @foreach ($studyClasses as $class)
                                            <option value="{{ $class->id }}"
                                                @if (request()->get('class_id') == $class->id) selected @endif>
                                                {{ $class->title }}
                                            </option>
                                        @endforeach
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

            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            @can('admin_sales_export')
                                <a href="{{ getAdminPanelUrl() }}/financial/sales/export?{{ $filters }}"
                                    class="btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
                            @endcan
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <tr>
                                        <th>#</th>
                                        <th class="text-left">{{ trans('admin/main.student') }}</th>
                                        <th class="text-center">{{ trans('admin/main.item') }}</th>
                                        <th class="text-center">رقم الدفعة</th>

                                        <th width="120">user access</th>
                                    </tr>

                                    @foreach ($sales as $index => $sale)
                                        <tr>

                                            <td>{{ ++$index }}</td>
                                            <td class="text-left">
                                                {{ !empty($sale->buyer) ? $sale->buyer->full_name : '' }}
                                                <div class="text-primary text-small font-600-bold">ID :
                                                    {{ !empty($sale->buyer) ? $sale->buyer->id : '' }}</div>
                                                <div class="text-primary text-small font-600-bold">Code :
                                                    {{ !empty($sale->buyer) ? $sale->buyer->user_code : '' }}</div>


                                            </td>
                                            <td>
                                                <div>{{ $sale->item_title }}</div>
                                            </td>

                                            <td>{{ $sale?->class?->title ?? '--' }}</td>

                                            <td>
                                                <div
                                                    class="form-group mt-3 d-flex align-items-center justify-content-between">
                                                    <label class=""
                                                        for="user_access_{{ $sale->id }}">Access</label>
                                                    <div class="custom-control custom-switch">
                                                        <!-- Check if the user has access -->
                                                        <input type="checkbox" name="user_access"
                                                            class="custom-control-input"
                                                            id="user_access_{{ $sale->id }}"
                                                            {{ $sale->access_to_purchased_item == 1 ? 'checked' : '' }}
                                                            data-sale-id="{{ $sale->id }}"
                                                            data-status="{{ $sale->access_to_purchased_item }}">
                                                        <label class="custom-control-label"
                                                            for="user_access_{{ $sale->id }}"></label>
                                                    </div>
                                                </div>
                                            </td>

                                        </tr>
                                    @endforeach

                                </table>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            {{ $sales->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        $(document).ready(function() {
            $('input[name="user_access"]').on('change', function() {
                let saleId = $(this).data('sale-id');
                let newStatus = $(this).prop('checked') ? 1 : 0;

                // Send an AJAX request to update the access status
                $.ajax({
                    url: '/admin/financial/sales/' + saleId + '/toggle-access', // Corrected URL
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        access_to_purchased_item: newStatus,
                    },
                    success: function(response) {
                        // Optionally show a message or handle success here
                        alert('Access updated successfully.');
                    },
                    error: function(error) {
                        // Optionally handle error here
                        alert('There was an error updating the access.');
                    }
                });
            });
        });
    </script>
@endsection
