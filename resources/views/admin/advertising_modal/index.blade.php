@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{ trans('update.advertising_modal') }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class=" js-font-resize breadcrumb-item">{{ trans('update.advertising_modal') }}</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">

            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-body">

                            <form action="{{ getAdminPanelUrl() }}/advertising_modal" method="post">
                                {{ csrf_field() }}

                                <div class=" js-font-resize row">

                                    <div class=" js-font-resize col-12 col-md-6">

                                        <div class=" js-font-resize form-group">
                                            <label class=" js-font-resize input-label">{{ trans('admin/main.image') }}</label>
                                            <div class=" js-font-resize input-group">
                                                <div class=" js-font-resize input-group-prepend">
                                                    <button type="button" class=" js-font-resize input-group-text admin-file-manager" data-input="image" data-preview="holder">
                                                        <i class=" js-font-resize fa fa-chevron-up"></i>
                                                    </button>
                                                </div>
                                                <input type="text" name="value[image]" id="image" value="{{ (!empty($value) and !empty($value['image'])) ? $value['image'] : old('image') }}" class=" js-font-resize form-control"/>
                                            </div>
                                        </div>

                                        <div class=" js-font-resize form-group">
                                            <label>{{ trans('admin/main.title') }}</label>
                                            <input type="text" name="value[title]" value="{{ (!empty($value) and !empty($value['title'])) ? $value['title'] : old('title') }}" class=" js-font-resize form-control "/>
                                        </div>

                                        <div class=" js-font-resize form-group">
                                            <label>{{ trans('public.description') }}</label>
                                            <textarea type="text" name="value[description]" rows="5" class=" js-font-resize form-control ">{{ (!empty($value) and !empty($value['description'])) ? $value['description'] : old('description') }}</textarea>
                                        </div>

                                        <div class=" js-font-resize form-group">
                                            <label>{{ trans('update.button') }} 1</label>
                                            <div class=" js-font-resize row">
                                                <div class=" js-font-resize col-6">
                                                    <label>{{ trans('admin/main.title') }}</label>
                                                    <input type="text" name="value[button1][title]" value="{{ (!empty($value) and !empty($value['button1'])) ? $value['button1']['title'] : '' }}" class=" js-font-resize form-control "/>
                                                </div>
                                                <div class=" js-font-resize col-6">
                                                    <label>{{ trans('admin/main.link') }}</label>
                                                    <input type="text" name="value[button1][link]" value="{{ (!empty($value) and !empty($value['button1'])) ? $value['button1']['link'] : '' }}" class=" js-font-resize form-control "/>
                                                </div>
                                            </div>
                                        </div>

                                        <div class=" js-font-resize form-group">
                                            <label>{{ trans('update.button') }} 2</label>
                                            <div class=" js-font-resize row">
                                                <div class=" js-font-resize col-6">
                                                    <label>{{ trans('admin/main.title') }}</label>
                                                    <input type="text" name="value[button2][title]" value="{{ (!empty($value) and !empty($value['button2'])) ? $value['button2']['title'] : '' }}" class=" js-font-resize form-control "/>
                                                </div>
                                                <div class=" js-font-resize col-6">
                                                    <label>{{ trans('admin/main.link') }}</label>
                                                    <input type="text" name="value[button2][link]" value="{{ (!empty($value) and !empty($value['button2'])) ? $value['button2']['link'] : '' }}" class=" js-font-resize form-control "/>
                                                </div>
                                            </div>
                                        </div>

                                        <div class=" js-font-resize form-group custom-switches-stacked">
                                            <label class=" js-font-resize custom-switch pl-0 d-flex align-items-center">
                                                <input type="hidden" name="value[status]" value="0">
                                                <input type="checkbox" name="value[status]" id="advertiseModalStatusSwitch" value="1" {{ (!empty($value) and !empty($value['status']) and $value['status']) ? 'checked="checked"' : '' }} class=" js-font-resize custom-switch-input"/>
                                                <span class=" js-font-resize custom-switch-indicator"></span>
                                                <label class=" js-font-resize custom-switch-description mb-0 cursor-pointer" for="advertiseModalStatusSwitch">{{ trans('admin/main.active') }}</label>
                                            </label>
                                            <div class=" js-font-resize text-muted text-small mt-1">{{ trans('update.advertising_modal_status_hint') }}</div>
                                        </div>

                                    </div>
                                </div>

                                <div class=" js-font-resize ">
                                    <button type="submit" class=" js-font-resize btn btn-primary">{{ trans('admin/main.save_change') }}</button>
                                    <button type="button" class=" js-font-resize js-preview-modal btn btn-warning ml-2">{{ trans('update.preview') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/js/admin/advertising_modal.min.js"></script>
@endpush
