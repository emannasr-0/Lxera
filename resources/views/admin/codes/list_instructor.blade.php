@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>أكواد االمعلمين</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class="breadcrumb-item">أكواد االمعلمين</div>
            </div>
        </div>

        <div class="section-body">
              <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-header">

                            @can('instructor_codes_create')
                                <div class="text-right">
                                    <a href="{{ getAdminPanelUrl() }}/codes/instructor_create" class="btn btn-primary ml-2">كود جديد</a>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <tr>
                                        <th class="text-left">كود المعلمين</th>
                                        <th class="text-left">اخر كود معلم </th>
                                    </tr>

                                    @foreach($codes as $code)
                                        <tr>
                                            <td class="text-center">
                                                <span>{{ $code->instructor_code }}</span>
                                            </td>
                                            <td class="text-left">
                                                @if($code->lst_tr_code)
                                                {{ $code->lst_tr_code }}
                                                @else
                                                    لا يوجد
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach

                                </table>
                            </div>
                        </div>
    </section>

@endsection

@push('scripts_bottom')

@endpush