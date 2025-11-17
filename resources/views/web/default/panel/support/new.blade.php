@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
@endpush

@section('content')
    <form method="post" action="/panel/support/store">
        {{ csrf_field() }}

        <section>
            <h2 class=" js-font-resize section-title">{{ trans('panel.create_support_message') }}</h2>

            <div class=" js-font-resize mt-25 rounded-sm shadow py-20 px-10 px-lg-25 bg-secondary-acadima">

                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">{{ trans('site.subject') }}</label>
                    <input type="text" name="title" value="{{ old('title') }}" class=" js-font-resize form-control @error('title')  is-invalid @enderror"/>
                    @error('title')
                    <div class=" js-font-resize invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label d-block">{{ trans('public.type') }}</label>

                    <select name="type" id="supportType" class=" js-font-resize form-control  @error('type')  is-invalid @enderror" data-allow-clear="false" data-search="false">
                        <option selected disabled></option>
                        <option value="course_support" @if($errors->has('webinar_id')) selected @endif>{{ trans('panel.course_support') }}</option>
                        <option value="bundle_support" @if($errors->has('bundle_id')) selected @endif>دعم البرنامج</option>
                        {{-- <option value="platform_support" @if($errors->has('department_id')) selected @endif>{{ trans('panel.platform_support') }}</option> --}}
                    </select>

                    @error('type')
                    <div class=" js-font-resize invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

            

                <div id="courseInput" class=" js-font-resize form-group @if(!$errors->has('webinar_id')) d-none @endif">
                    <label class=" js-font-resize input-label d-block">{{ trans('product.course') }}</label>
                    <select name="webinar_id" class=" js-font-resize form-control select2 @error('webinar_id')  is-invalid @enderror">
                        <option value="" selected disabled>{{ trans('panel.select_course') }}</option>
                      
                        @foreach($webinars as $webinar)
                       
                            <option value="{{ $webinar->id }}">{{ $webinar->title }} - {{ $webinar->creator->full_name }}</option>
                        @endforeach
                    </select>
                    @error('webinar_id')
                    <div class=" js-font-resize invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div id="bundleInput" class=" js-font-resize form-group @if(!$errors->has('bundle_id')) d-none @endif">
                    <label class=" js-font-resize input-label d-block">برنامج</label>
                    <select name="bundle_id" class=" js-font-resize form-control select2 @error('bundle_id')  is-invalid @enderror">
                        <option value="" selected disabled>اختر البرنامج</option>
                      
                        @foreach($bundles as $bundle)
                       
                            <option value="{{ $bundle->id }}">{{ $bundle->title }} - {{ $bundle->creator->full_name }}</option>
                        @endforeach
                    </select>
                    @error('bundle_id')
                    <div class=" js-font-resize invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class=" js-font-resize form-group ">
                    <label class=" js-font-resize input-label d-block">{{ trans('panel.department') }}</label>

                    <select name="department_id"  class=" js-font-resize form-control select2 @error('department_id')  is-invalid @enderror" data-allow-clear="false" data-search="false">
                        <option selected disabled></option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->title }}</option>
                        @endforeach
                    </select>

                    @error('department_id')
                    <div class=" js-font-resize invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label d-block">{{ trans('site.message') }}</label>
                    <textarea name="message" class=" js-font-resize form-control" rows="15">{{ old('message') }}</textarea>
                </div>

                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 col-lg-8 d-flex align-items-center">
                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('panel.attach_file') }}</label>
                            <div class=" js-font-resize input-group">
                                <div class=" js-font-resize input-group-prepend">
                                    <button type="button" class=" js-font-resize input-group-text panel-file-manager" data-input="attach" data-preview="holder">
                                        <i data-feather="arrow-up" width="18" height="18" class=" js-font-resize text-white"></i>
                                    </button>
                                </div>
                                <input type="text" name="attach" id="attach" value="{{ old('attach') }}" class=" js-font-resize form-control"/>
                            </div>
                        </div>

                        <button type="submit" class=" js-font-resize btn btn-primary btn-sm ml-40 mt-10">{{ trans('site.send_message') }}</button>
                    </div>
                </div>
            </div>
        </section>
    </form>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/js/panel/conversations.min.js"></script>
@endpush
