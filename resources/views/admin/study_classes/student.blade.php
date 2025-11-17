@extends('admin.layouts.app')

@push('libraries_top')
@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ trans('admin/main.list') }} {{ trans('admin/main.students') }} {{ $class->title }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="/admin/classes" style="color: #6c757d">الدفعات الدراسية</a></div>
                <div class=" js-font-resize breadcrumb-item active"><a>{{ $class->title }}</a></div>
                <div class=" js-font-resize breadcrumb-item"><a href="#">{{ trans('admin/main.students') }}</a></div>
            </div>
        </div>
    </section>

    <div class=" js-font-resize section-body">
        <div class=" js-font-resize row">
            <div class=" js-font-resize col-6">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-primary">
                        <i class=" js-font-resize fas fa-users"></i>
                    </div>
                    <div class=" js-font-resize card-wrap">
                        <div class=" js-font-resize card-header">
                            <h4>{{ trans('admin/main.total_students') }}</h4>
                        </div>
                        <div class=" js-font-resize card-body">
                            {{ $class->enrollments()->count() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize col-6">
                <div class=" js-font-resize card card-statistic-1">
                    <div class=" js-font-resize card-icon bg-primary">
                        <i class=" js-font-resize fas fa-users"></i>
                    </div>
                    <div class=" js-font-resize card-wrap">
                        <div class=" js-font-resize card-header">
                            <h4>عدد التسجيلات</h4>
                        </div>
                        <div class=" js-font-resize card-body">
                            {{ $totalSales }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class=" js-font-resize card">
            <div class=" js-font-resize card-body">
                <form method="get" class=" js-font-resize mb-0">

                    <div class=" js-font-resize row">
                        <div class=" js-font-resize col-md-3">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">كود الطالب</label>
                                <input name="user_code" type="text" class=" js-font-resize form-control"
                                    value="{{ request()->get('user_code') }}">
                            </div>
                        </div>

                        <div class=" js-font-resize col-md-3">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">بريد الطالب</label>
                                <input name="email" type="text" class=" js-font-resize form-control"
                                    value="{{ request()->get('email') }}">
                            </div>
                        </div>


                        <div class=" js-font-resize col-md-3">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">اسم الطالب</label>
                                <input
                                    name={{ 'user_name' }}
                                    type="text" class=" js-font-resize form-control"
                                    value="{{ request()->get('user_name') }}">
                            </div>
                        </div>

                        <div class=" js-font-resize col-md-3">
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">هاتف الطالب</label>
                                <input name="mobile" type="text" class=" js-font-resize form-control"
                                    value="{{ request()->get('mobile') }}">
                            </div>
                        </div>

                        <div class=" js-font-resize col-md-3">
                            <div class=" js-font-resize form-group mt-1">
                                <label class=" js-font-resize input-label mb-4"> </label>
                                <input type="submit" class=" js-font-resize text-center btn btn-primary w-100"
                                    value="{{ trans('admin/main.show_results') }}">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>


    <div class=" js-font-resize card">
        <div class=" js-font-resize card-header">
            @can('admin_users_export_excel')
                <a href="{{ getAdminPanelUrl() }}/classes/{{ $class->id }}/excelStudent? {{ http_build_query(request()->all()) }}"
                    class=" js-font-resize btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
            @endcan

            @can('admin_users_export_excel')
                @include('admin.students.includes.importStudents', [
                    'url' => getAdminPanelUrl() . "/classes/$class->id/importStudents",
                    'btnClass' => 'btn btn-danger d-flex align-items-center btn-sm mt-1  mr-3',
                    'btnText' => '<span class=" js-font-resize ml-2">رفع الطلاب من الاكسيل</span>',
                    'hideDefaultClass' => true,
                ])

                <a href="{{ asset('files/import_student_template.xlsx') }}" class=" js-font-resize btn btn-success" download>تحميل قالب
                    النموذج</a>
                <a href="{{ getAdminPanelUrl() }}/bundles/bundleCodeExcel" class=" js-font-resize btn btn-info mr-3">تحميل اكواد الدبلومات
                </a>
            @endcan
            <div class=" js-font-resize h-10"></div>
        </div>

        <div class=" js-font-resize card-body">
            <div class=" js-font-resize table-responsive text-center">
                <table class=" js-font-resize table table-striped font-14">
                    <tr>
                        <th>{{ '#' }}</th>
                        <th>كود الطالب</th>

                        <th>{{ trans('admin/main.name') }}</th>

                        <th>الهوية الوطنية</th>

                        <th> الدبلومات المسجلة</th>

                        <th>{{ trans('admin/main.register_date') }}</th>
                        <th>{{ trans('admin/main.status') }}</th>
                        <th width="120">{{ trans('admin/main.actions') }}</th>
                    </tr>

                    @foreach ($enrollments as $enrollment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $enrollment->buyer?->user_code }}</td>

                            <td class=" js-font-resize text-left">
                                <div class=" js-font-resize d-flex align-items-center">
                                    <figure class=" js-font-resize avatar mr-2">
                                        <img src="{{ $enrollment->buyer?->getAvatar() }}"
                                            alt="{{ $enrollment->buyer?->student ? $enrollment->buyer?->student->ar_name : $enrollment->buyer?->full_name }}">
                                    </figure>
                                    <div class=" js-font-resize media-body ml-1">
                                        <div class=" js-font-resize mt-0 mb-1 font-weight-bold">
                                            {{ $enrollment->buyer?->student ? $enrollment->buyer?->student->ar_name : $enrollment->buyer?->full_name }}
                                        </div>

                                        @if ($enrollment->mobile)
                                            <div class=" js-font-resize text-primary text-left font-600-bold" style="font-size:12px;">
                                                {{ $enrollment->buyer?->mobile }}</div>
                                        @endif

                                        @if ($enrollment->email)
                                            <div class=" js-font-resize text-primary text-small font-600-bold">
                                                {{ $enrollment->buyer?->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class=" js-font-resize text-left">
                                @if (!empty($enrollment->student->identity_img))
                                    <a href="/store/{{ $enrollment->buyer?->student->identity_img }}" target="_blank">
                                        <img src="/store/{{ $enrollment->buyer?->student->identity_img }}" alt="image"
                                            width="100px" style="max-height:100px">
                                    </a>
                                @else
                                    <span class=" js-font-resize text-warning">لم ترفع بعد</span>
                                @endif
                            </td>
                            {{--
                                <td>
                                    @foreach ($enrollment->bundleSales($class->id)->get() as $record)
                                        {{ $record->bundle->title }}
                                        @if (!$loop->last)
                                            و
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    @foreach ($enrollment->bundleSales($class->id)->get() as $record)
                                        {{ dateTimeFormat($record->created_at, 'j M Y | H:i') }}
                                        @if (!$loop->last)
                                            و
                                        @endif
                                    @endforeach
                                </td>
                            --}}

                            <td>{{ $enrollment->bundle?->title }}</td>
                            <td> {{ dateTimeFormat($enrollment->created_at, 'j M Y | H:i') }}</td>
                            <td>
                                @if ($enrollment->ban and !empty($enrollment->ban_end_at) and $enrollment->buyer?->ban_end_at > time())
                                    <div class=" js-font-resize mt-0 mb-1 font-weight-bold text-danger">{{ trans('admin/main.ban') }}
                                    </div>
                                    <div class=" js-font-resize text-small font-600-bold">Until
                                        {{ dateTimeFormat($enrollment->ban_end_at, 'Y/m/j') }}
                                    </div>
                                @else
                                    <div
                                        class=" js-font-resize mt-0 mb-1 font-weight-bold {{ $enrollment->buyer?->status == 'active' ? 'text-success' : 'text-warning' }}">
                                        {{ trans('admin/main.' . $enrollment->buyer?->status) }}</div>
                                @endif
                            </td>

                            <td class=" js-font-resize text-center mb-2" width="120">

                                @can('admin_users_impersonate')
                                    <a href="{{ getAdminPanelUrl() }}/users/{{ $enrollment->buyer?->id }}/impersonate" target="_blank"
                                        class=" js-font-resize btn-transparent  text-primary" data-toggle="tooltip" data-placement="top"
                                        title="{{ trans('admin/main.login') }}">
                                        <i class=" js-font-resize fa fa-user-shield"></i>
                                    </a>
                                @endcan

                                @can('admin_users_edit')
                                    <a href="{{ getAdminPanelUrl() }}/users/{{ $enrollment->buyer?->id }}/edit"
                                        class=" js-font-resize btn-transparent  text-primary" data-toggle="tooltip" data-placement="top"
                                        title="{{ trans('admin/main.edit') }}">
                                        <i class=" js-font-resize fa fa-edit"></i>
                                    </a>
                                @endcan

                                @can('admin_users_delete')
                                    @include('admin.includes.delete_button', [
                                        'url' => getAdminPanelUrl() . '/users/' . $enrollment->buyer?->id . '/delete',
                                        'btnClass' => '',
                                        'deleteConfirmMsg' => trans('update.user_delete_confirm_msg'),
                                    ])
                                @endcan
                            </td>

                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <div class=" js-font-resize card-footer text-center">
            {{ $enrollments->appends(request()->input())->links() }}
        </div>
    </div>


    <section class=" js-font-resize card">
        <div class=" js-font-resize card-body">
            <div class=" js-font-resize section-title ml-0 mt-0 mb-3">
                <h5>{{ trans('admin/main.hints') }}</h5>
            </div>
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-md-4">
                    <div class=" js-font-resize media-body">
                        <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">
                            {{ trans('admin/main.students_hint_title_1') }}</div>
                        <div class=" js-font-resize  text-small font-600-bold">{{ trans('admin/main.students_hint_description_1') }}
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize col-md-4">
                    <div class=" js-font-resize media-body">
                        <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">
                            {{ trans('admin/main.students_hint_title_2') }}</div>
                        <div class=" js-font-resize  text-small font-600-bold">{{ trans('admin/main.students_hint_description_2') }}
                        </div>
                    </div>
                </div>


                <div class=" js-font-resize col-md-4">
                    <div class=" js-font-resize media-body">
                        <div class=" js-font-resize text-primary mt-0 mb-1 font-weight-bold">
                            {{ trans('admin/main.students_hint_title_3') }}</div>
                        <div class=" js-font-resize text-small font-600-bold">{{ trans('admin/main.students_hint_description_3') }}</div>
                    </div>
                </div>


            </div>
        </div>
    </section>
@endsection


