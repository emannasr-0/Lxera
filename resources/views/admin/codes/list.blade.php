@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>أكواد الطلاب</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class="breadcrumb-item">أكواد الطلاب</div>
            </div>
        </div>

        <div class="section-body">
              <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-header">

                            @can('admin_codes_create')
                                <div class="text-right">
                                    <a href="{{ getAdminPanelUrl() }}/codes/create" class="btn btn-primary ml-2">كود جديد</a>
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
                                        <th class="text-left">{{ trans('code.code_title') }}</th>
                                        <th class="text-left">{{ trans('code.last_code') }}</th>
                                    </tr>

                                    @foreach($codes as $code)
                                        <tr>
                                            <td class="text-center">
                                                <span>{{ $code->student_code }}</span>
                                            </td>
                                            <td class="text-left">
                                                @if($code->lst_sd_code)
                                                {{ $code->lst_sd_code }}
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