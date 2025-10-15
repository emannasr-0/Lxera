@extends('admin.layouts.app')

@push('libraries_top')
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>الدفعات الدراسية</h1>

            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>

                <div class="breadcrumb-item">الدفعات الدراسية</div>
            </div>
        </div>

        <div class="section-body">
            <div class="d-flex justify-content-end align-items-center mb-10">
                <button id="" type="button" data-toggle="modal" data-target="#createBatchModel"
                    class="btn btn-primary btn-sm mb-3">إنشاء دفعة جديدة</button>
            </div>
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14 ">
                                    <tr>
                                        <th>{{ trans('admin/main.id') }}</th>
                                        <th class="text-left">{{ trans('admin/main.title') }}</th>
                                        <th>عدد طلبةإنشاء حساب</th>
                                        <th>عدد طلبة حجز مقعد</th>
                                        <th>عدد طلبة تسجيل برامج</th>
                                        <th>عدد طلبة تسجيل مباشر</th>
                                        <th>عدد طلبة منح دراسية</th>

                                        <th>{{ trans('admin/main.start_date') }}</th>
                                        <th>{{ trans('admin/main.end_date') }}</th>
                                        <th>{{ trans('admin/main.created_at') }}</th>
                                        {{-- <th>{{ trans('admin/main.updated_at') }}</th> --}}
                                        <th width="120">{{ trans('admin/main.actions') }}</th>
                                    </tr>

                                    @foreach ($classes as $class)
                                        <tr class="text-center">
                                            <td>{{ $class->id }}</td>
                                            <td width="18%" class="text-left">
                                                <p class="text-primary mt-0 mb-1 font-weight-bold" href="">
                                                    {{ $class->title }}
                                                </p>

                                            <td>{{ $class->registerEnrollements()->count() }}</td>
                                            <td>{{ $class->formFeeEnrollements()->count() }}</td>
                                            <td>{{ $class->bundleEnrollements()->count() }}</td>
                                            <td>{{ $class->directRegisterEnrollements()->count() }}</td>
                                            <td>{{ $class->scholarshipEnrollements()->count() }}</td>
                                            <td class="font-12" style="min-width: 120px">{{ $class->start_date ?? '----' }}
                                            <td class="font-12"  style="min-width: 120px">{{ $class->end_date ?? '----' }}
                                            <td class="font-12"  style="min-width: 120px">{{ $class->created_at }}
                                            </td>
                                            {{-- <td class="font-12">{{ $class->updated_at }} --}}
                                            </td>

                                            <td width="150">
                                                <div class="btn-group dropdown table-actions">
                                                    <button type="button" class="btn-transparent dropdown-toggle"
                                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu text-left webinars-lists-dropdown">

                                                        @include('admin.study_classes.create', [
                                                            'url' =>
                                                                getAdminPanelUrl() .
                                                                '/classes/' .
                                                                $class->id .
                                                                '/update',
                                                            'btnClass' =>
                                                                'd-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm mt-1',
                                                            'btnText' =>
                                                                ' <i class="fa fa-edit"></i><span class="ml-2">' .
                                                                'تعديل' .
                                                                '</span>',
                                                            'class' => $class,
                                                        ])



                                                        @can('admin_webinars_delete')
                                                            @include('admin.includes.delete_button', [
                                                                'url' =>
                                                                    getAdminPanelUrl() .
                                                                    '/classes/' .
                                                                    $class->id .
                                                                    '/delete',
                                                                'btnClass' =>
                                                                    'd-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm mt-1',
                                                                'btnText' =>
                                                                    '<i class="fa fa-times"></i><span class="ml-2">' .
                                                                    trans('admin/main.delete') .
                                                                    '</span>',
                                                            ])
                                                        @endcan


                                                        <a href="{{ getAdminPanelUrl() }}/classes/{{ $class->id }}/students"
                                                            target="_self"
                                                            class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm text-primary mt-1 "
                                                            title="{{ trans('admin/main.students') }}">
                                                            <i class="fa fa-users"></i>
                                                            <span class="ml-2">{{ trans('admin/main.students') }}</span>
                                                        </a>

                                                        <a href="{{ getAdminPanelUrl() }}/classes/{{ $class->id }}/registered_users"
                                                            target="_self"
                                                            class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm text-primary mt-1 "
                                                            title="{{ trans('admin/main.students') }}">
                                                            <i class="fa fa-users"></i>
                                                            <span class="ml-2">نموذج إنشاء حساب </span>
                                                        </a>

                                                        <a href="{{ getAdminPanelUrl() }}/classes/{{ $class->id }}/users"
                                                            target="_self"
                                                            class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm text-primary mt-1 "
                                                            title="{{ trans('admin/main.students') }}">
                                                            <i class="fa fa-users"></i>
                                                            <span class="ml-2">نموذج حجز مقعد</span>
                                                        </a>

                                                        <a href="{{ getAdminPanelUrl() }}/classes/{{ $class->id }}/enrollers"
                                                            target="_self"
                                                            class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm text-primary mt-1 "
                                                            title="{{ trans('admin/main.students') }}">
                                                            <i class="fa fa-users"></i>
                                                            <span class="ml-2">تسجيل البرامج</span>
                                                        </a>

                                                        <a href="{{ getAdminPanelUrl() }}/classes/{{ $class->id }}/direct_register"
                                                            target="_self"
                                                            class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm text-primary mt-1 "
                                                            title="{{ trans('admin/main.students') }}">
                                                            <i class="fa fa-users"></i>
                                                            <span class="ml-2">تسجيل مباشر</span>
                                                        </a>
                                                        <a href="{{ getAdminPanelUrl() }}/classes/{{ $class->id }}/scholarship"
                                                            target="_self"
                                                            class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm text-primary mt-1 "
                                                            title="{{ trans('admin/main.students') }}">
                                                            <i class="fa fa-users"></i>
                                                            <span class="ml-2">تسجيل المنح الدراسية</span>
                                                        </a>


                                                        <a href="{{ getAdminPanelUrl() }}/classes/{{ $class->id }}/requirements"
                                                            target="_self"
                                                            class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm text-primary mt-1 "
                                                            title="{{ trans('admin/main.students') }}">
                                                            <i class="fa fa-users"></i>
                                                            <span class="ml-2">نموذج المتطلبات</span>
                                                        </a>




                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            {{ $classes->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection


<!-- Modal -->
<div class="modal fade" id="createBatchModel" tabindex="-1" aria-labelledby="examplelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">

                <h5 class="modal-title" id="examplelLabel">إنشاء دفعة جديدة</h5>


                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="/admin/classes" method="post" class="modal-body">
                @csrf
                <div class="">
                    <div class="form-group">
                        <label for="title">عنوان الدفعة الدراسية</label>
                        <input type="text" name="title" id="title" class="form-control">

                    </div>
                    <div class="form-group mt-15 js-start_date">
                        <div class="form-group">
                            <label class="input-label">{{ trans('public.start_date') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="dateInputGroupPrepend">
                                        <i class="fa fa-calendar-alt "></i>
                                    </span>
                                </div>

                                <input type="text" name="start_date"
                                    value="{{ old('start_date') }}"
                                    class="form-control @error('start_date')  is-invalid @enderror datetimepicker"
                                    aria-describedby="dateInputGroupPrepend" />
                                @error('start_date')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-15 js-start_date">
                        <div class="form-group">
                            <label class="input-label">{{ trans('public.end_date') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="dateInputGroupPrepend">
                                        <i class="fa fa-calendar-alt "></i>
                                    </span>
                                </div>

                                <input type="text" name="end_date"
                                    value="{{ old('end_date') }}"
                                    class="form-control @error('end_date')  is-invalid @enderror datetimepicker"
                                    aria-describedby="dateInputGroupPrepend" />
                                @error('end_date')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">الغاء</button>
                    <button type="submit" class="btn btn-danger" id="confirmAction">حفظ</button>
                </div>

            </form>
        </div>
    </div>
</div>

@push('scripts_bottom')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

        (function($) {
            "use strict";

            @if (session()->has('sweetalert'))
                Swal.fire({
                    icon: "{{ session()->get('sweetalert')['status'] ?? 'success' }}",
                    html: '<h3 class="font-20 text-center text-light py-25">{{ session()->get('sweetalert')['msg'] ?? '' }}</h3>',
                    showConfirmButton: false,
                    width: '25rem',
                });
            @endif
        })(jQuery)
    </script>
@endpush
