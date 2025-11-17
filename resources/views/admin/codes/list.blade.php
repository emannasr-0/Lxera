@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header js-font-resize">
            <h1 class=" js-font-resize js-font-resize">أكواد الطلاب</h1>
            <div class=" js-font-resize section-header-breadcrumb js-font-resize">
                <div class=" js-font-resize breadcrumb-item active js-font-resize"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item js-font-resize">أكواد الطلاب</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">
              <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-12">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-header">

                            @can('admin_codes_create')
                                <div class=" js-font-resize text-right js-font-resize">
                                    <a href="{{ getAdminPanelUrl() }}/codes/create" class=" js-font-resize btn btn-primary ml-2 js-font-resize">كود جديد</a>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class=" js-font-resize card-body">
                            <div class=" js-font-resize table-responsive">
                                <table class=" js-font-resize table table-striped font-14 js-font-resize">
                                    <tr>
                                        <th class=" js-font-resize text-left">{{ trans('code.code_title') }}</th>
                                        <th class=" js-font-resize text-left">{{ trans('code.last_code') }}</th>
                                    </tr>

                                    @foreach($codes as $code)
                                        <tr>
                                            <td class=" js-font-resize text-center">
                                                <span>{{ $code->student_code }}</span>
                                            </td>
                                            <td class=" js-font-resize text-left">
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