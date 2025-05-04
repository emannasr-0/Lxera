@extends('admin.layouts.app')

@push('libraries_top')
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            {{-- <h1>{{ trans('admin/main.type_'.$classesType.'s') }} {{trans('admin/main.list')}}</h1> --}}
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.classes') }}</div>

                {{-- <div class="breadcrumb-item">{{ trans('admin/main.type_'.$classesType.'s') }}</div> --}}
            </div>
        </div>

        <div class="section-body">

            <div class="row">



            </div>

            <section class="card">
                <div class="card-body">
                    <form method="get" class="mb-0">
                        {{-- <input type="hidden" name="type" value="{{ request()->get('type') }}"> --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.search') }}</label>
                                    <input name="title" type="text" class="form-control"
                                        value="{{ request()->get('title') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.start_date') }}</label>
                                    <div class="input-group">
                                        <input type="date" id="from" class="text-center form-control" name="from"
                                            value="{{ request()->get('from') }}" placeholder="Start Date">
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.end_date') }}</label>
                                    <div class="input-group">
                                        <input type="date" id="to" class="text-center form-control" name="to"
                                            value="{{ request()->get('to') }}" placeholder="End Date">
                                    </div>
                                </div>
                            </div> --}}


                            {{-- <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.filters') }}</label>
                                    <select name="sort" data-plugin-selectTwo class="form-control populate">
                                        <option value="">{{ trans('admin/main.filter_type') }}</option>
                                        <option value="has_discount" @if (request()->get('sort') == 'has_discount') selected @endif>
                                            {{ trans('admin/main.discounted_classes') }}</option>
                                        <option value="sales_asc" @if (request()->get('sort') == 'sales_asc') selected @endif>
                                            {{ trans('admin/main.sales_ascending') }}</option>
                                        <option value="sales_desc" @if (request()->get('sort') == 'sales_desc') selected @endif>
                                            {{ trans('admin/main.sales_descending') }}</option>
                                        <option value="price_asc" @if (request()->get('sort') == 'price_asc') selected @endif>
                                            {{ trans('admin/main.Price_ascending') }}</option>
                                        <option value="price_desc" @if (request()->get('sort') == 'price_desc') selected @endif>
                                            {{ trans('admin/main.Price_descending') }}</option>
                                        <option value="income_asc" @if (request()->get('sort') == 'income_asc') selected @endif>
                                            {{ trans('admin/main.Income_ascending') }}</option>
                                        <option value="income_desc" @if (request()->get('sort') == 'income_desc') selected @endif>
                                            {{ trans('admin/main.Income_descending') }}</option>
                                        <option value="created_at_asc" @if (request()->get('sort') == 'created_at_asc') selected @endif>
                                            {{ trans('admin/main.create_date_ascending') }}</option>
                                        <option value="created_at_desc" @if (request()->get('sort') == 'created_at_desc') selected @endif>
                                            {{ trans('admin/main.create_date_descending') }}</option>
                                        <option value="updated_at_asc" @if (request()->get('sort') == 'updated_at_asc') selected @endif>
                                            {{ trans('admin/main.update_date_ascending') }}</option>
                                        <option value="updated_at_desc" @if (request()->get('sort') == 'updated_at_desc') selected @endif>
                                            {{ trans('admin/main.update_date_descending') }}</option>
                                        <option value="public_courses" @if (request()->get('sort') == 'public_courses') selected @endif>
                                            {{ trans('update.public_courses') }}</option>
                                        <option value="courses_private" @if (request()->get('sort') == 'courses_private') selected @endif>
                                            {{ trans('update.courses_private') }}</option>
                                    </select>
                                </div>
                            </div> --}}




                            {{-- <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{trans('admin/main.category')}}</label>
                                    <select name="category_id" data-plugin-selectTwo class="form-control populate">
                                        <option value="">{{trans('admin/main.all_categories')}}</option>

                                        @foreach ($categories as $category)
                                            @if (!empty($category->subCategories) and count($category->subCategories))
                                                <optgroup label="{{  $category->title }}">
                                                    @foreach ($category->subCategories as $subCategory)
                                                        <option value="{{ $subCategory->id }}" @if (request()->get('category_id') == $subCategory->id) selected="selected" @endif>{{ $subCategory->title }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @else
                                                <option value="{{ $category->id }}" @if (request()->get('category_id') == $category->id) selected="selected" @endif>{{ $category->title }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}


                            {{-- <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{trans('admin/main.status')}}</label>
                                    <select name="status" data-plugin-selectTwo class="form-control populate">
                                        <option value="">{{trans('admin/main.all_status')}}</option>
                                        <option value="pending" @if (request()->get('status') == 'pending') selected @endif>{{trans('admin/main.pending_review')}}</option>
                                        @if ($classesType == 'webinar')
                                            <option value="active_not_conducted" @if (request()->get('status') == 'active_not_conducted') selected @endif>{{trans('admin/main.publish_not_conducted')}}</option>
                                            <option value="active_in_progress" @if (request()->get('status') == 'active_in_progress') selected @endif>{{trans('admin/main.publish_inprogress')}}</option>
                                            <option value="active_finished" @if (request()->get('status') == 'active_finished') selected @endif>{{trans('admin/main.publish_finished')}}</option>
                                        @else
                                            <option value="active" @if (request()->get('status') == 'active') selected @endif>{{trans('admin/main.published')}}</option>
                                        @endif
                                        <option value="inactive" @if (request()->get('status') == 'inactive') selected @endif>{{trans('admin/main.rejected')}}</option>
                                        <option value="is_draft" @if (request()->get('status') == 'is_draft') selected @endif>{{trans('admin/main.draft')}}</option>
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

            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            @can('admin_webinars_export_excel')
                                <div class="text-right">
                                    <a href="{{ getAdminPanelUrl() }}/webinars/excel?{{ http_build_query(request()->all()) }}"
                                        class="btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
                                </div>
                            @endcan

                            @include('admin.students.includes.importStudents', [
                                'url' => getAdminPanelUrl() . '/students/importCourseStudent',
                                'btnClass' => 'btn btn-danger d-flex align-items-center btn-sm mt-1  mr-3',
                                'btnText' => '<span class="ml-2">رفع الطلاب من الاكسيل</span>',
                                'hideDefaultClass' => true,
                            ])
                            <a href="{{ asset('files/import_student_template.xlsx') }}" class="btn btn-success"
                                download>تحميل قالب
                                النموذج</a>
                            <a href="{{ getAdminPanelUrl() }}/webinars/courseCodeExcel" class="btn btn-info mr-3">تحميل
                                اكواد الدورات
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14 ">
                                    <tr>
                                        <th>{{ trans('admin/main.id') }}</th>
                                        <th class="text-left">{{ trans('admin/main.title') }}</th>
                                        {{-- <th class="text-left">{{trans('admin/main.instructor')}}</th>
                                        <th>{{trans('admin/main.price')}}</th>
                                        <th>{{trans('admin/main.sales')}}</th>
                                        <th>{{trans('admin/main.income')}}</th> --}}
                                        <th>{{ trans('admin/main.students_count') }}</th>
                                        <th>الجروب</th>
                                        <th>تاريخ البدأ</th>
                                        {{-- @if ($classesType == 'webinar')
                                            <th>{{trans('admin/main.start_date')}}</th>
                                        @else
                                            <th>{{trans('admin/main.updated_at')}}</th>
                                        @endif --}}

                                        <th width="120">{{ trans('admin/main.actions') }}</th>
                                    </tr>

                                    @foreach ($webinars as $webinar)
                                        <tr class="text-center">
                                            <td>{{ $webinar->id }}</td>
                                            <td width="18%" class="text-left">
                                                <a class="text-primary mt-0 mb-1 font-weight-bold"
                                                    href="{{ $webinar->getUrl() }}">{{ $webinar->title }}</a>
                                                @if (!empty($webinar->category->title))
                                                    <div class="text-small">{{ $webinar->category->title }}</div>
                                                @else
                                                    <div class="text-small text-warning">
                                                        {{ trans('admin/main.no_category') }}</div>
                                                @endif
                                            </td>



                                            <td class="font-12">
                                                <a href="{{ getAdminPanelUrl() }}/webinars/{{ $webinar->id }}/students"
                                                    target="_blank" class="">{{ $webinar->sales->count() }}</a>
                                            </td>


                                            <td class="font-12">{{ $webinar->groups_count }}</td>





                                            <td class="font-12">{{ dateTimeFormat($webinar->start_date, 'Y M j | H:i') }}
                                            </td>






                                            <td width="200" class="">
                                                <a class="btn-transparent  text-primary  @if (!empty($sidebarBeeps['courses']) and $sidebarBeeps['courses']) beep beep-sidebar @endif"
                                                    href="{{ getAdminPanelUrl() }}/courses/{{ $webinar->id }}"
                                                    style="height: auto">
                                                    <i class="fas fa-eye"></i>

                                                </a>

                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            {{ $webinars->appends(request()->input())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
@endpush
