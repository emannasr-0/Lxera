@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>الشهادات المعتمدة</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class="breadcrumb-item">الشهادات المغتمدة</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            @can('admin_certificate_export_excel')
                                <div class="text-right">
                                    <a href="{{ getAdminPanelUrl() }}/certificates/excel?{{ http_build_query(request()->all()) }}" class="btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
                                </div>
                            @endcan
                        </div>

                        <div class="card-body">

                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <tr>
                                        <th>#</th>
                                        <th class="text-left">الشهادة</th>
                                        <th class="text-left">{{ trans('quiz.student') }}</th>
                                        <th class="text-left">دبلومة</th>
                                        <th class="text-left">السعر</th>
                                        <th class="text-center">{{ trans('public.date_time') }}</th>
                                        <th>{{ trans('admin/main.action') }}</th>
                                    </tr>
                                    @if(!empty($purchased_certificates))
                                    @foreach($purchased_certificates as $certificate)
                                        <tr>
                                            <td class="text-center">{{ $certificate->id }}</td>
                                            <td class="text-left">
                                                <span>{{ $certificate->certificate_template->title }}</span>
                                            </td>
                                            <td class="text-left">
                                                {{ \App\Models\Api\User::find($certificate->buyer_id)->full_name }}
                                            <td class="text-left">{{ \App\Models\Bundle::find($certificate->certificate_bundle_id)->title }}</td>
                                            <td class="text-left">
                                                <span>{{ $certificate->certificate_template->price }}{{$currency}}</span>
                                            </td>
                                            <td class="text-center">{{ dateTimeFormat($certificate->created_at, 'j M Y') }}</td>
                                            <td>
                                                <a href="{{ getAdminPanelUrl() }}/certificates/{{ $certificate->id }}/download" target="_blank" class="btn-transparent text-primary" data-toggle="tooltip" title="{{ trans('quiz.download_certificate') }}">
                                                    <i class="fa fa-download" aria-hidden="true"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @endif
                                </table>
                            </div>
                        </div>

                        
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

