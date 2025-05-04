@extends('admin.layouts.app')

@push('libraries_top')
@endpush

@php
    $segments = explode('/', request()->path());
    $lastSegment = end($segments);
    $exportPath="excelEnroller";
    if($lastSegment=="scholarship"){
        $exportPath="excelScholarship";
    }else if($lastSegment=='users'){

        $exportPath="excelStudent";
    }
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.students') }} {{ trans('admin/main.list') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a>{{ trans('admin/main.students') }}</a></div>
                <div class="breadcrumb-item"><a href="#">{{ trans('admin/main.users_list') }}</a></div>
            </div>
        </div>
    </section>

    <div class="section-body">
        <div class="row">
            <div class="col-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ trans('admin/main.total_students') }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalStudents }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>عدد التسجيلات</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalSales }}
                        </div>
                    </div>
                </div>
            </div>
            {{-- <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ trans('admin/main.organizations_students') }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalOrganizationsStudents }}
                        </div>
                    </div>
                </div>
            </div> --}}
            {{-- <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ trans('admin/main.inactive_students') }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $inactiveStudents }}
                        </div>
                    </div>
                </div>
            </div> --}}
            {{-- <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ trans('admin/main.ban_students') }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $banStudents }}
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>

        <section class="card">
            <div class="card-body">
                <form method="get" class="mb-0">

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">كود الطالب</label>
                                <input name="user_code" type="text" class="form-control"
                                    value="{{ request()->get('user_code') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">بريد الطالب</label>
                                <input name="email" type="text" class="form-control"
                                    value="{{ request()->get('email') }}">
                            </div>
                        </div>


                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">اسم الطالب</label>
                                <input
                                    name={{ 'user_name' }}
                                    type="text" class="form-control"
                                    value="{{ request()->get('user_name') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">هاتف الطالب</label>
                                <input name="mobile" type="text" class="form-control"
                                    value="{{ request()->get('mobile') }}">
                            </div>
                        </div>

                        {{-- <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.start_date') }}</label>
                                <div class="input-group">
                                    <input type="date" id="from" class="text-center form-control" name="from"
                                        value="{{ request()->get('from') }}" placeholder="Start Date">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.end_date') }}</label>
                                <div class="input-group">
                                    <input type="date" id="to" class="text-center form-control" name="to"
                                        value="{{ request()->get('to') }}" placeholder="End Date">
                                </div>
                            </div>
                        </div>


                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.filters') }}</label>
                                <select name="sort" data-plugin-selectTwo class="form-control populate">
                                    <option value="">{{ trans('admin/main.filter_type') }}</option>
                                    <option value="purchased_classes_asc" @if (request()->get('sort') == 'purchased_classes_asc') selected @endif>
                                        {{ trans('admin/main.purchased_classes_ascending') }}</option>
                                    <option value="purchased_classes_desc"
                                        @if (request()->get('sort') == 'purchased_classes_desc') selected @endif>
                                        {{ trans('admin/main.purchased_classes_descending') }}</option>

                                    <option value="purchased_classes_amount_asc"
                                        @if (request()->get('sort') == 'purchased_classes_amount_asc') selected @endif>
                                        {{ trans('admin/main.purchased_classes_amount_ascending') }}</option>
                                    <option value="purchased_classes_amount_desc"
                                        @if (request()->get('sort') == 'purchased_classes_amount_desc') selected @endif>
                                        {{ trans('admin/main.purchased_classes_amount_descending') }}</option>


                                    <option value="purchased_appointments_asc"
                                        @if (request()->get('sort') == 'purchased_appointments_asc') selected @endif>
                                        {{ trans('admin/main.purchased_appointments_ascending') }}</option>
                                    <option value="purchased_appointments_desc"
                                        @if (request()->get('sort') == 'purchased_appointments_desc') selected @endif>
                                        {{ trans('admin/main.purchased_appointments_descending') }}</option>

                                    <option value="purchased_appointments_amount_asc"
                                        @if (request()->get('sort') == 'purchased_appointments_amount_asc') selected @endif>
                                        {{ trans('admin/main.purchased_appointments_amount_ascending') }}</option>
                                    <option value="purchased_appointments_amount_desc"
                                        @if (request()->get('sort') == 'purchased_appointments_amount_desc') selected @endif>
                                        {{ trans('admin/main.purchased_appointments_amount_descending') }}</option>

                                    <option value="register_asc" @if (request()->get('sort') == 'register_asc') selected @endif>
                                        {{ trans('admin/main.register_date_ascending') }}</option>
                                    <option value="register_desc" @if (request()->get('sort') == 'register_desc') selected @endif>
                                        {{ trans('admin/main.register_date_descending') }}</option>
                                </select>
                            </div>
                        </div> --}}


                        {{-- <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.organization') }}</label>
                                <select name="organization_id" data-plugin-selectTwo class="form-control populate">
                                    <option value="">{{ trans('admin/main.select_organization') }}</option>
                                    @foreach ($organizations as $organization)
                                        <option value="{{ $organization->id }}"
                                            @if (request()->get('organization_id') == $organization->id) selected @endif>
                                            {{ $organization->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}

                        {{-- <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.users_group') }}</label>
                                <select name="group_id" data-plugin-selectTwo class="form-control populate">
                                    <option value="">{{ trans('admin/main.select_users_group') }}</option>
                                    @foreach ($userGroups as $userGroup)
                                        <option value="{{ $userGroup->id }}"
                                            @if (request()->get('group_id') == $userGroup->id) selected @endif>{{ $userGroup->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}
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
        </section>
    </div>

    <div class="card">
        <div class="card-header">
            @can('admin_users_export_excel')
                <a href="{{ getAdminPanelUrl() }}/students/{{ $exportPath }}?{{(!empty($class->id) ? ('class_id='.$class->id ."&&") : ''). http_build_query(request()->all()) }}"
                    class="btn btn-primary">{{ trans('admin/main.export_xls') }}</a>

                @if ($lastSegment == 'scholarship')
                    @include('admin.students.includes.importStudents', [
                        'url' => getAdminPanelUrl() . '/students/importScholarshipStudent',
                        'btnClass' => 'btn btn-danger d-flex align-items-center btn-sm mt-1  mr-3',
                        'btnText' => '<span class="ml-2">اضافه طلاب المنح الدراسية</span>',
                        'hideDefaultClass' => true,
                    ])

                    <a href="{{ asset('files/import_student_template.xlsx') }}" class="btn btn-success" download>تحميل قالب
                        النموذج</a>
                    <a href="{{ getAdminPanelUrl() }}/bundles/bundleCodeExcel" class="btn btn-info mr-3">تحميل اكواد الدبلومات
                    </a>
                @endif
            @endcan
            <div class="h-10"></div>
        </div>

        <div class="card-body">
            <div class="table-responsive text-center">
                <table class="table table-striped font-14">
                    <tr>
                        <th>{{ '#' }}</th>
                        <th>كود الطالب</th>

                        <th>{{ trans('admin/main.name') }}</th>

                        <th>الهوية الوطنية</th>
                        {{-- <th>{{ trans('admin/main.classes') }}</th>
                        <th>{{ trans('admin/main.appointments') }}</th>
                        <th>{{ trans('admin/main.wallet_charge') }}</th>
                        <th>{{ trans('admin/main.income') }}</th>
                        <th>{{ trans('admin/main.user_group') }}</th> --}}
                        <th> الدبلومات المسجلة</th>
                        {{-- <th>حاله الدفع</th> --}}
                        {{-- <th>كود الطالب</th> --}}
                        <th>{{ trans('admin/main.register_date') }}</th>
                        <th>{{ trans('admin/main.status') }}</th>
                        <th width="120">{{ trans('admin/main.actions') }}</th>
                    </tr>

                    @foreach ($sales as $index => $sale)
                        <tr>
                            <td>{{ ++$index }}</td>
                            <td>{{ $sale->buyer?->user_code }}</td>

                            <td class="text-left">
                                <div class="d-flex align-items-center">
                                    <figure class="avatar mr-2">
                                        <img src="{{ $sale->buyer?->getAvatar() }}"
                                            alt="{{$sale->buyer?->student ? $sale->buyer?->student->ar_name : $sale->buyer?->full_name }}">
                                    </figure>
                                    <div class="media-body ml-1">
                                        <div class="mt-0 mb-1 font-weight-bold">
                                            {{ $sale->buyer?->student ? $sale->buyer?->student->ar_name : $sale->buyer?->full_name }}</div>

                                        @if ($sale->buyer?->mobile)
                                            <div class="text-primary text-left font-600-bold" style="font-size:12px;">
                                                {{ $sale->buyer?->mobile }}</div>
                                        @endif

                                        @if ($sale->buyer?->email)
                                            <div class="text-primary text-small font-600-bold">{{ $sale->buyer?->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="text-left">
                                @if (!empty($sale->buyer?->student?->identity_img))
                                    <a href="/store/{{ $sale->buyer?->student?->identity_img }}" target="_blank">
                                        <img src="/store/{{$sale->buyer?->student?->identity_img }}" alt="image"
                                            width="100px" style="max-height:100px">
                                    </a>
                                @else
                                <span class="text-warning">لم ترفع بعد</span>
                                @endif
                            </td>

                            {{-- @php
                                $userPurchasedBundles = $sale->buyer?->purchasedBundles($class->id ?? null)->get();
                            @endphp --}}
                            {{-- <td>

                                @foreach ($userPurchasedBundles as $purchasedBundle)
                                    {{ $purchasedBundle->bundle->title }}
                                    @if (!$loop->last)
                                        &nbsp;و&nbsp;
                                    @endif
                                @endforeach
                            </td> --}}



                            {{-- <td>
                                @foreach ($userPurchasedBundles as $purchasedBundle)
                                    {{ dateTimeFormat($purchasedBundle->created_at, 'j M Y | H:i') }}
                                    @if (!$loop->last)
                                        &nbsp;و&nbsp;
                                    @endif
                                @endforeach
                            </td> --}}

                            <td>
                                {{$sale->bundle?->title}}
                            </td>

                            <td>
                                {{ dateTimeFormat($sale->created_at, 'j M Y | H:i') }}
                            </td>

                            <td>
                                @if ($sale->buyer?->ban and !empty($sale->buyer?->ban_end_at) and $sale->buyer?->ban_end_at > time())
                                    <div class="mt-0 mb-1 font-weight-bold text-danger">{{ trans('admin/main.ban') }}
                                    </div>
                                    <div class="text-small font-600-bold">Until
                                        {{ dateTimeFormat($sale->buyer?->ban_end_at, 'Y/m/j') }}</div>
                                @else
                                    <div
                                        class="mt-0 mb-1 font-weight-bold {{ $sale->buyer?->status == 'active' ? 'text-success' : 'text-warning' }}">
                                        {{ trans('admin/main.' . $sale->buyer?->status) }}</div>
                                @endif
                            </td>

                            <td class="text-center mb-2" width="120">

                                @can('admin_users_impersonate')
                                    <a href="{{ getAdminPanelUrl() }}/users/{{ $sale->buyer?->id }}/impersonate" target="_blank"
                                        class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top"
                                        title="{{ trans('admin/main.login') }}">
                                        <i class="fa fa-user-shield"></i>
                                    </a>
                                @endcan

                                @can('admin_users_edit')
                                    <a href="{{ getAdminPanelUrl() }}/users/{{$sale->buyer?->id }}/edit"
                                        class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top"
                                        title="{{ trans('admin/main.edit') }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                @endcan

                                @can('admin_users_delete')
                                    @include('admin.includes.delete_button', [
                                        'url' => getAdminPanelUrl() . '/users/' . $sale->buyer?->id . '/delete',
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

        <div class="card-footer text-center">
            {{ $sales->appends(request()->input())->links() }}
        </div>
    </div>


    <section class="card">
        <div class="card-body">
            <div class="section-title ml-0 mt-0 mb-3">
                <h5>{{ trans('admin/main.hints') }}</h5>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="media-body">
                        <div class="text-primary mt-0 mb-1 font-weight-bold">
                            {{ trans('admin/main.students_hint_title_1') }}</div>
                        <div class=" text-small font-600-bold">{{ trans('admin/main.students_hint_description_1') }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="media-body">
                        <div class="text-primary mt-0 mb-1 font-weight-bold">
                            {{ trans('admin/main.students_hint_title_2') }}</div>
                        <div class=" text-small font-600-bold">{{ trans('admin/main.students_hint_description_2') }}
                        </div>
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="media-body">
                        <div class="text-primary mt-0 mb-1 font-weight-bold">
                            {{ trans('admin/main.students_hint_title_3') }}</div>
                        <div class="text-small font-600-bold">{{ trans('admin/main.students_hint_description_3') }}</div>
                    </div>
                </div>


            </div>
        </div>
    </section>
@endsection

@php
    $bundlesByCategory = [];
    foreach ($category as $item) {
        $bundlesByCategory[$item->id] = $item->bundles;
    }
@endphp

@push('scripts_bottom')
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script>
        var undefinedActiveSessionLang = '{{ trans('webinars.undefined_active_session') }}';
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var selectChapterLang = '{{ trans('update.select_chapter') }}';
    </script>

    <script src="/assets/default/js/panel/make_next_session.min.js"></script>
    {{-- bundle toggle and education section toggle --}}
    <script>
        function toggleHiddenInput(event) {
            var bundles = @json($bundlesByCategory);

            let selectInput = event.target;
            let myForm = selectInput.closest('form');
            let hiddenInput = myForm.bundle_id;
            let certificateSection = myForm.certificate_section;

            if (selectInput.value && hiddenInput) {
                var categoryId = selectInput.value;
                var categoryBundles = bundles[categoryId];

                if (categoryBundles) {
                    var options = categoryBundles.map(function(bundle) {
                        var isSelected = bundle.id == "{{ old('toDiploma', $student->bundle_id ?? null) }}" ?
                            'selected' : '';
                        return `<option value="${bundle.id}" ${isSelected} has_certificate="${bundle.has_certificate}">${bundle.title}</option>`;
                    }).join('');

                    hiddenInput.outerHTML =
                        '<select id="bundle_id" name="toDiploma"  class="form-control" onchange="CertificateSectionToggle(event)" required>' +
                        '<option value="" class="placeholder" disabled="" selected="selected">اختر التخصص الذي تود دراسته في اكاديما</option>' +
                        options +
                        '</select>';
                }
            }
        }
    </script>


    {{-- Certificate Section Toggle --}}
    <script>
        function CertificateSectionToggle(event) {

            let myForm = event.target.closest('form');

            let certificateSection = myForm.querySelector("#certificate_section");
            let bundleSelect = myForm.querySelector("#bundle_id");
            // Get the selected option
            var selectedOption = bundleSelect.options[bundleSelect.selectedIndex];
            if (selectedOption.getAttribute('has_certificate') == 1) {
                certificateSection.classList.remove("d-none");
            } else {
                certificateSection.classList.add("d-none");

            }
        }

        function showCertificateMessage(event) {
            let myForm = event.target.closest('form');
            let messageSection = myForm.querySelector("#certificate_message");
            let certificateOption = myForm.querySelector("input[name='certificate']:checked");
            if (certificateOption.value === "1") {
                messageSection.innerHTML = "سوف يحصل على خصم 23%"
            } else if (certificateOption.value === "0") {
                messageSection.innerHTML = "بيفوته الحصول علي خصم 23%"

            } else {
                messageSection.innerHTML = ""

            }
        }
    </script>
@endpush
