<div class=" js-font-resize row">
    <div class=" js-font-resize col-12 col-md-5">

        @if(!empty(getGeneralSettings('content_translate')))
            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('auth.language') }}</label>
                <select name="locale" class=" js-font-resize form-control {{ !empty($upcomingCourse) ? 'js-edit-content-locale' : '' }}">
                    @foreach($userLanguages as $lang => $language)
                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }} {{ (!empty($definedLanguage) and is_array($definedLanguage) and in_array(mb_strtolower($lang), $definedLanguage)) ? '('. trans('panel.content_defined') .')' : '' }}</option>
                    @endforeach
                </select>
                @error('locale')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        @else
            <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
        @endif

        <div class=" js-font-resize form-group mt-15 ">
            <label class=" js-font-resize input-label d-block">{{ trans('panel.course_type') }}</label>

            <select name="type" class=" js-font-resize custom-select @error('type')  is-invalid @enderror">
                <option value="webinar" @if((!empty($upcomingCourse) and $upcomingCourse->type == 'webinar') or old('type') == \App\Models\Webinar::$webinar) selected @endif>{{ trans('webinars.webinar') }}</option>
                <option value="course" @if((!empty($upcomingCourse) and $upcomingCourse->type == 'course') or old('type') == \App\Models\Webinar::$course) selected @endif>{{ trans('product.video_course') }}</option>
                <option value="text_lesson" @if((!empty($upcomingCourse) and $upcomingCourse->type == 'text_lesson') or old('type') == \App\Models\Webinar::$textLesson) selected @endif>{{ trans('product.text_course') }}</option>
            </select>

            @error('type')
            <div class=" js-font-resize invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div class=" js-font-resize form-group mt-15">
            <label class=" js-font-resize input-label">{{ trans('public.title') }}</label>
            <input type="text" name="title" value="{{ !empty($upcomingCourse) ? $upcomingCourse->title : old('title') }}" class=" js-font-resize form-control @error('title')  is-invalid @enderror" placeholder=""/>
            @error('title')
            <div class=" js-font-resize invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div class=" js-font-resize form-group mt-15">
            <label class=" js-font-resize input-label">{{ trans('admin/main.class_url') }}</label>
            <input type="text" name="slug" value="{{ !empty($upcomingCourse) ? $upcomingCourse->slug : old('slug') }}" class=" js-font-resize form-control @error('slug')  is-invalid @enderror" placeholder=""/>
            <div class=" js-font-resize text-muted text-small mt-1">{{ trans('admin/main.class_url_hint') }}</div>
            @error('slug')
            <div class=" js-font-resize invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

        @if(!empty($upcomingCourse) and $upcomingCourse->creator->isOrganization())
            <div class=" js-font-resize form-group mt-15 ">
                <label class=" js-font-resize input-label d-block">{{ trans('admin/main.organization') }}</label>

                <select name="organ_id" data-search-option="just_organization_role" class=" js-font-resize form-control search-user-select2" data-placeholder="{{ trans('search_organization') }}">
                    <option value="{{ $upcomingCourse->creator->id }}" selected>{{ $upcomingCourse->creator->full_name }}</option>
                </select>
            </div>
        @endif


        <div class=" js-font-resize form-group mt-15 ">
            <label class=" js-font-resize input-label d-block">{{ trans('admin/main.select_a_instructor') }}</label>

            <select name="teacher_id" data-search-option="except_user" class=" js-font-resize form-control search-user-select22"
                    data-placeholder="{{ trans('public.select_a_teacher') }}"
            >
                @if(!empty($upcomingCourse))
                    <option value="{{ $upcomingCourse->teacher->id }}" selected>{{ $upcomingCourse->teacher->full_name }}</option>
                @else
                    <option selected disabled>{{ trans('public.select_a_teacher') }}</option>
                @endif
            </select>

            @error('teacher_id')
            <div class=" js-font-resize invalid-feedback d-block">
                {{ $message }}
            </div>
            @enderror
        </div>


        <div class=" js-font-resize form-group mt-15">
            <label class=" js-font-resize input-label">{{ trans('public.seo_description') }}</label>
            <input type="text" name="seo_description" value="{{ !empty($upcomingCourse) ? $upcomingCourse->seo_description : old('seo_description') }}" class=" js-font-resize form-control @error('seo_description')  is-invalid @enderror"/>
            <div class=" js-font-resize text-muted text-small mt-1">{{ trans('admin/main.seo_description_hint') }}</div>
            @error('seo_description')
            <div class=" js-font-resize invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div class=" js-font-resize form-group mt-15">
            <label class=" js-font-resize input-label">{{ trans('public.thumbnail_image') }}</label>
            <div class=" js-font-resize input-group">
                <div class=" js-font-resize input-group-prepend">
                    <button type="button" class=" js-font-resize input-group-text admin-file-manager" data-input="thumbnail" data-preview="holder">
                        <i class=" js-font-resize fa fa-upload"></i>
                    </button>
                </div>
                <input type="text" name="thumbnail" id="thumbnail" value="{{ !empty($upcomingCourse) ? $upcomingCourse->thumbnail : old('thumbnail') }}" class=" js-font-resize form-control @error('thumbnail')  is-invalid @enderror"/>
                <div class=" js-font-resize input-group-append">
                    <button type="button" class=" js-font-resize input-group-text admin-file-view" data-input="thumbnail">
                        <i class=" js-font-resize fa fa-eye"></i>
                    </button>
                </div>
                @error('thumbnail')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>


        <div class=" js-font-resize form-group mt-15">
            <label class=" js-font-resize input-label">{{ trans('public.cover_image') }}</label>
            <div class=" js-font-resize input-group">
                <div class=" js-font-resize input-group-prepend">
                    <button type="button" class=" js-font-resize input-group-text admin-file-manager" data-input="cover_image" data-preview="holder">
                        <i class=" js-font-resize fa fa-upload"></i>
                    </button>
                </div>
                <input type="text" name="image_cover" id="cover_image" value="{{ !empty($upcomingCourse) ? $upcomingCourse->image_cover : old('image_cover') }}" class=" js-font-resize form-control @error('image_cover')  is-invalid @enderror"/>
                <div class=" js-font-resize input-group-append">
                    <button type="button" class=" js-font-resize input-group-text admin-file-view" data-input="cover_image">
                        <i class=" js-font-resize fa fa-eye"></i>
                    </button>
                </div>
                @error('image_cover')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <div class=" js-font-resize form-group mt-25">
            <label class=" js-font-resize input-label">{{ trans('public.demo_video') }} ({{ trans('public.optional') }})</label>


            <div class=" js-font-resize ">
                <label class=" js-font-resize input-label font-12">{{ trans('public.source') }}</label>
                <select name="video_demo_source"
                        class=" js-font-resize js-video-demo-source form-control"
                >
                    @foreach(\App\Models\Webinar::$videoDemoSource as $source)
                        <option value="{{ $source }}" @if(!empty($upcomingCourse) and $upcomingCourse->video_demo_source == $source) selected @endif>{{ trans('update.file_source_'.$source) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class=" js-font-resize form-group mt-0">
            <label class=" js-font-resize input-label font-12">{{ trans('update.path') }}</label>
            <div class=" js-font-resize input-group js-video-demo-path-input">
                <div class=" js-font-resize input-group-prepend">
                    <button type="button" class=" js-font-resize js-video-demo-path-upload input-group-text admin-file-manager {{ (empty($upcomingCourse) or empty($upcomingCourse->video_demo_source) or $upcomingCourse->video_demo_source == 'upload') ? '' : 'd-none' }}" data-input="demo_video" data-preview="holder">
                        <i class=" js-font-resize fa fa-upload"></i>
                    </button>

                    <button type="button" class=" js-font-resize js-video-demo-path-links rounded-left input-group-text input-group-text-rounded-left  {{ (empty($upcomingCourse) or empty($upcomingCourse->video_demo_source) or $upcomingCourse->video_demo_source == 'upload') ? 'd-none' : '' }}">
                        <i class=" js-font-resize fa fa-link"></i>
                    </button>
                </div>
                <input type="text" name="video_demo" id="demo_video" value="{{ !empty($upcomingCourse) ? $upcomingCourse->video_demo : old('video_demo') }}" class=" js-font-resize form-control @error('video_demo')  is-invalid @enderror"/>
                @error('video_demo')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

    </div>
</div>

<div class=" js-font-resize row">
    <div class=" js-font-resize col-12">
        <div class=" js-font-resize form-group mt-15">
            <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
            <textarea id="summernote" name="description" class=" js-font-resize form-control @error('description')  is-invalid @enderror" placeholder="{{ trans('forms.webinar_description_placeholder') }}">{!! !empty($upcomingCourse) ? $upcomingCourse->description : old('description')  !!}</textarea>
            @error('description')
            <div class=" js-font-resize invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
    </div>
</div>
