<li data-id="{{ !empty($file) ? $file->id :'' }}" class=" js-font-resize accordion-row bg-white rounded-sm border border-gray300 mt-20 py-15 py-lg-30 px-10 px-lg-20">
    <div class=" js-font-resize d-flex align-items-center justify-content-between " role="tab" id="file_{{ !empty($file) ? $file->id :'record' }}">
        <div class=" js-font-resize d-flex align-items-center" href="#collapseFile{{ !empty($file) ? $file->id :'record' }}" aria-controls="collapseFile{{ !empty($file) ? $file->id :'record' }}" data-parent="#filesAccordion" role="button" data-toggle="collapse" aria-expanded="true">
            <span class=" js-font-resize chapter-icon chapter-content-icon mr-10">
                <i data-feather="file" class=" js-font-resize "></i>
            </span>

            <div class=" js-font-resize font-weight-bold text-dark-blue d-block">{{ !empty($file) ? $file->title : trans('public.add_new_files') }}</div>
        </div>

        <div class=" js-font-resize d-flex align-items-center">
            @if(!empty($file) and $file->status != \App\Models\ProductFile::$Active)
                <span class=" js-font-resize disabled-content-badge mr-10">{{ trans('public.disabled') }}</span>
            @endif

            <i data-feather="move" class=" js-font-resize move-icon mr-10 cursor-pointer" height="20"></i>

            @if(!empty($file))
                <a href="/panel/store/products/files/{{ $file->id }}/delete" class=" js-font-resize delete-action btn btn-sm btn-transparent text-gray">
                    <i data-feather="trash-2" class=" js-font-resize mr-10 cursor-pointer" height="20"></i>
                </a>
            @endif

            <i class=" js-font-resize collapse-chevron-icon" data-feather="chevron-down" height="20" href="#collapseFile{{ !empty($file) ? $file->id :'record' }}" aria-controls="collapseFile{{ !empty($file) ? $file->id :'record' }}" data-parent="#filesAccordion" role="button" data-toggle="collapse" aria-expanded="true"></i>
        </div>
    </div>

    <div id="collapseFile{{ !empty($file) ? $file->id :'record' }}" aria-labelledby="file_{{ !empty($file) ? $file->id :'record' }}" class=" js-font-resize  collapse @if(empty($file)) show @endif" role="tabpanel">
        <div class=" js-font-resize panel-collapse text-gray">
            <div class=" js-font-resize js-content-form file-form" data-action="/panel/store/products/files/{{ !empty($file) ? $file->id . '/update' : 'store' }}">
                <input type="hidden" name="ajax[{{ !empty($file) ? $file->id : 'new' }}][product_id]" value="{{ !empty($product) ? $product->id :'' }}">

                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 col-lg-6">

                        @if(!empty(getGeneralSettings('content_translate')))
                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('auth.language') }}</label>
                                <select name="ajax[{{ !empty($file) ? $file->id : 'new' }}][locale]"
                                        class=" js-font-resize form-control {{ !empty($file) ? 'js-product-content-locale' : '' }}"
                                        data-product-id="{{ !empty($product) ? $product->id : '' }}"
                                        data-id="{{ !empty($file) ? $file->id : '' }}"
                                        data-relation="files"
                                        data-fields="title"
                                >
                                    @foreach(getUserLanguagesLists() as $lang => $language)
                                        <option value="{{ $lang }}" {{ (!empty($file) and !empty($file->locale)) ? (mb_strtolower($file->locale) == mb_strtolower($lang) ? 'selected' : '') : ($locale == $lang ? 'selected' : '') }}>{{ $language }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="ajax[{{ !empty($file) ? $file->id : 'new' }}][locale]" value="{{ $defaultLocale }}">
                        @endif


                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('public.title') }}</label>
                            <input type="text" name="ajax[{{ !empty($file) ? $file->id : 'new' }}][title]" class=" js-font-resize js-ajax-title form-control" value="{{ !empty($file) ? $file->title : '' }}" placeholder="{{ trans('forms.maximum_255_characters') }}"/>
                            <div class=" js-font-resize invalid-feedback"></div>
                        </div>


                        <div class=" js-font-resize form-group js-file-path-input">
                            <div class=" js-font-resize local-input input-group">
                                <div class=" js-font-resize input-group-prepend">
                                    <button type="button" class=" js-font-resize input-group-text panel-file-manager text-white" data-input="file_path{{ !empty($file) ? $file->id : 'record' }}" data-preview="holder">
                                        <i data-feather="upload" width="18" height="18" class=" js-font-resize text-white"></i>
                                    </button>
                                </div>
                                <input type="text" name="ajax[{{ !empty($file) ? $file->id : 'new' }}][path]" id="file_path{{ !empty($file) ? $file->id : 'record' }}" value="{{ (!empty($file)) ? $file->path : '' }}" class=" js-font-resize js-ajax-file_path form-control" placeholder="{{ trans('webinars.file_upload_placeholder') }}"/>
                                <div class=" js-font-resize invalid-feedback"></div>
                            </div>
                        </div>

                        <div class=" js-font-resize row form-group js-file-type-volume">
                            <div class=" js-font-resize col-6">
                                <label class=" js-font-resize input-label">{{ trans('webinars.file_type') }}</label>
                                <select name="ajax[{{ !empty($file) ? $file->id : 'new' }}][file_type]" class=" js-font-resize js-ajax-file_type form-control">
                                    <option value="">{{ trans('webinars.select_file_type') }}</option>

                                    @foreach(\App\Models\File::$fileTypes as $fileType)
                                        <option value="{{ $fileType }}" @if(!empty($file) and $file->file_type == $fileType) selected @endif>{{ trans('update.file_type_'.$fileType) }}</option>
                                    @endforeach
                                </select>
                                <div class=" js-font-resize invalid-feedback"></div>
                            </div>
                            <div class=" js-font-resize col-6">
                                <label class=" js-font-resize input-label">{{ trans('webinars.file_volume') }}</label>
                                <input type="text" name="ajax[{{ !empty($file) ? $file->id : 'new' }}][volume]" value="{{ (!empty($file)) ? $file->volume : '' }}" class=" js-font-resize js-ajax-volume form-control" placeholder="{{ trans('webinars.online_file_volume') }}"/>
                                <div class=" js-font-resize invalid-feedback"></div>
                            </div>
                        </div>

                        <div class=" js-font-resize form-group">
                            <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
                            <textarea name="ajax[{{ !empty($file) ? $file->id : 'new' }}][description]" rows="4" class=" js-font-resize js-ajax-description form-control" placeholder="{{ trans('public.description') }}">{{ !empty($file) ? $file->description : '' }}</textarea>
                            <div class=" js-font-resize invalid-feedback"></div>
                        </div>

                        <div class=" js-font-resize js-online_viewer-input form-group mt-20 {{ (!empty($file) and $file->file_type == 'pdf') ? '' : 'd-none' }}">
                            <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                <label class=" js-font-resize cursor-pointer input-label" for="online_viewerSwitch{{ !empty($file) ? $file->id : '_record' }}">{{ trans('update.online_viewer') }}</label>
                                <div class=" js-font-resize custom-control custom-switch">
                                    <input type="checkbox" name="ajax[{{ !empty($file) ? $file->id : 'new' }}][online_viewer]" class=" js-font-resize custom-control-input" id="online_viewerSwitch{{ !empty($file) ? $file->id : '_record' }}" {{ (!empty($file) and $file->online_viewer) ? 'checked' : ''  }}>
                                    <label class=" js-font-resize custom-control-label" for="online_viewerSwitch{{ !empty($file) ? $file->id : '_record' }}"></label>
                                </div>
                            </div>
                        </div>

                        <div class=" js-font-resize form-group mt-20">
                            <div class=" js-font-resize d-flex align-items-center justify-content-between">
                                <label class=" js-font-resize cursor-pointer input-label" for="fileStatusSwitch{{ !empty($file) ? $file->id : '_record' }}">{{ trans('public.active') }}</label>
                                <div class=" js-font-resize custom-control custom-switch">
                                    <input type="checkbox" name="ajax[{{ !empty($file) ? $file->id : 'new' }}][status]" class=" js-font-resize custom-control-input" id="fileStatusSwitch{{ !empty($file) ? $file->id : '_record' }}" {{ (empty($file) or $file->status == \App\Models\File::$Active) ? 'checked' : ''  }}>
                                    <label class=" js-font-resize custom-control-label" for="fileStatusSwitch{{ !empty($file) ? $file->id : '_record' }}"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize mt-30 d-flex align-items-center">
                    <button type="button" class=" js-font-resize js-save-file btn btn-sm btn-primary">{{ trans('public.save') }}</button>

                    @if(empty($file))
                        <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 cancel-accordion">{{ trans('public.close') }}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</li>
