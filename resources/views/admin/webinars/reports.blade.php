@extends('admin.layouts.app')

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 col-md-12">
                    <div class=" js-font-resize card">

                        <div class=" js-font-resize card-body">
                            <div class=" js-font-resize table-responsive">
                                <table class=" js-font-resize table table-striped font-14">
                                    <tr>
                                        <th>{{ trans('admin/main.user') }}</th>
                                        <th class=" js-font-resize text-left">{{ trans('admin/main.class') }}</th>
                                        <th class=" js-font-resize text-center">{{ trans('product.reason') }}</th>
                                        <th class=" js-font-resize text-center">{{ trans('public.date') }}</th>
                                        <th>{{ trans('admin/main.actions') }}</th>
                                    </tr>
                                    @foreach($reports as $report)
                                        <tr>
                                            @if (!empty($report->user->id))

                                            <td>{{ $report->user->id .' - '.$report->user->full_name }}</td>

                                            @else

                                            <td class=" js-font-resize text-danger">Deleted User</td>


                                            @endif

                                            <td class=" js-font-resize text-left" width="30%">
                                                <a href="{{ $report->webinar->getUrl() }}" target="_blank">
                                                    {{ $report->webinar->title }}
                                                </a>
                                            </td>

                                            <td class=" js-font-resize text-center">
                                                <button type="button" class=" js-font-resize js-show-description btn btn-outline-primary">{{ trans('admin/main.show') }}</button>
                                                <input type="hidden" class=" js-font-resize report-reason" value="{{ nl2br($report->reason) }}">
                                                <input type="hidden" class=" js-font-resize report-description" value="{{ nl2br($report->message) }}">
                                            </td>

                                            <td class=" js-font-resize text-center">{{ dateTimeFormat($report->created_at, 'j M Y | H:i') }}</td>

                                            <td width="150px" class=" js-font-resize text-center">
                                                @can('admin_webinar_reports_delete')
                                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/reports/webinars/'.$report->id.'/delete','btnClass' => 'btn-sm'])
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <div class=" js-font-resize card-footer text-center">
                            {{ $reports->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class=" js-font-resize modal fade" id="reportMessage" tabindex="-1" aria-labelledby="reportMessageLabel" aria-hidden="true">
        <div class=" js-font-resize modal-dialog modal-dialog-centered">
            <div class=" js-font-resize modal-content">
                <div class=" js-font-resize modal-header">
                    <h5 class=" js-font-resize modal-title" id="reportMessageLabel">{{ trans('panel.report') }}</h5>
                    <button type="button" class=" js-font-resize close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class=" js-font-resize modal-body">
                    <div class=" js-font-resize ">
                        <h5 class=" js-font-resize font-weight-bold js-reason">{{ trans('product.reason') }}: <span class=" js-font-resize font-weight-light"></span></h5>

                        <div class=" js-font-resize mt-2 js-description">
                            <h5 class=" js-font-resize font-weight-bold js-reason">{{ trans('site.message') }} :</h5>
                            <p class=" js-font-resize mt-2">

                            </p>
                        </div>
                    </div>
                </div>
                <div class=" js-font-resize modal-footer">
                    <button type="button" class=" js-font-resize btn btn-secondary" data-dismiss="modal">{{ trans('admin/main.close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/admin/webinar_reports.min.js"></script>
@endpush
