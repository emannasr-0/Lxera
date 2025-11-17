@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

<div class=" js-font-resize row">
    <div class=" js-font-resize col-12 col-md-4 mt-15">

        @if(!empty(getGeneralSettings('content_translate')))
            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('auth.language') }}</label>
                <select name="locale" class=" js-font-resize custom-select {{ !empty($upcomingCourse) ? 'js-edit-content-locale' : '' }}">
                    @foreach($userLanguages as $lang => $language)
                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }} {{ (!empty($definedLanguage) and is_array($definedLanguage) and in_array(mb_strtolower($lang), $definedLanguage)) ? '('. trans('panel.content_defined') .')' : '' }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
        @endif


        <div class=" js-font-resize form-group mt-15 ">
            <label class=" js-font-resize input-label d-block">{{ trans('panel.course_type') }}</label>

            <select name="type" class=" js-font-resize custom-select @error('type')  is-invalid @enderror">
                <option value="webinar" @if(!empty($upcomingCourse) and $upcomingCourse->type == 'webinar') selected @endif>{{ trans('webinars.webinar') }}</option>
                <option value="course" @if(!empty($upcomingCourse) and $upcomingCourse->type == 'course') selected @endif>{{ trans('webinars.video_course') }}</option>
                <option value="text_lesson" @if(!empty($upcomingCourse) and $upcomingCourse->type == 'text_lesson') selected @endif>{{ trans('webinars.text_lesson') }}</option>
            </select>

            @error('type')
            <div class=" js-font-resize invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>


        @if($isOrganization)
            <div class=" js-font-resize form-group mt-15 ">
                <label class=" js-font-resize input-label d-block">{{ trans('public.select_a_teacher') }}</label>

                <select name="teacher_id" class=" js-font-resize custom-select @error('teacher_id')  is-invalid @enderror">
                    <option value="" {{ (!empty($upcomingCourse) and !empty($upcomingCourse->teacher_id)) ? '' : 'selected' }}>{{ trans('public.choose_instructor') }}</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ (!empty($upcomingCourse) && $upcomingCourse->teacher_id == $teacher->id) ? 'selected' : '' }}>{{ $teacher->full_name }}</option>
                    @endforeach
                </select>

                @error('teacher_id')
                <div class=" js-font-resize invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        @endif


        <div class=" js-font-resize form-group mt-15">
            <label class=" js-font-resize input-label">{{ trans('public.title') }}</label>
            <input type="text" name="title" value="{{ (!empty($upcomingCourse) and !empty($upcomingCourse->translate($locale))) ? $upcomingCourse->translate($locale)->title : old('title') }}" class=" js-font-resize form-control @error('title')  is-invalid @enderror" placeholder=""/>
            @error('title')
            <div class=" js-font-resize invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div class=" js-font-resize form-group mt-15">
            <label class=" js-font-resize input-label">{{ trans('public.seo_description') }}</label>
            <input type="text" name="seo_description" value="{{ (!empty($upcomingCourse) and !empty($upcomingCourse->translate($locale))) ? $upcomingCourse->translate($locale)->seo_description : old('seo_description') }}" class=" js-font-resize form-control @error('seo_description')  is-invalid @enderror " placeholder="{{ trans('forms.50_160_characters_preferred') }}"/>
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
                    <button type="button" class=" js-font-resize input-group-text panel-file-manager" data-input="thumbnail" data-preview="holder">
                        <i data-feather="arrow-up" width="18" height="18" class=" js-font-resize text-white"></i>
                    </button>
                </div>
                <input type="text" name="thumbnail" id="thumbnail" value="{{ !empty($upcomingCourse) ? $upcomingCourse->thumbnail : old('thumbnail') }}" class=" js-font-resize form-control @error('thumbnail')  is-invalid @enderror" placeholder="{{ trans('forms.course_thumbnail_size') }}"/>
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
                    <button type="button" class=" js-font-resize input-group-text panel-file-manager" data-input="cover_image" data-preview="holder">
                        <i data-feather="arrow-up" width="18" height="18" class=" js-font-resize text-white"></i>
                    </button>
                </div>
                <input type="text" name="image_cover" id="cover_image" value="{{ !empty($upcomingCourse) ? $upcomingCourse->image_cover : old('image_cover') }}" placeholder="{{ trans('forms.course_cover_size') }}" class=" js-font-resize form-control @error('image_cover')  is-invalid @enderror"/>
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
                    <button type="button" class=" js-font-resize js-video-demo-path-upload input-group-text text-white panel-file-manager {{ (empty($upcomingCourse) or empty($upcomingCourse->video_demo_source) or $upcomingCourse->video_demo_source == 'upload') ? '' : 'd-none' }}" data-input="demo_video" data-preview="holder">
                        <i data-feather="upload" width="18" height="18" class=" js-font-resize text-white"></i>
                    </button>

                    <button type="button" class=" js-font-resize js-video-demo-path-links rounded-left input-group-text input-group-text-rounded-left text-white {{ (empty($upcomingCourse) or empty($upcomingCourse->video_demo_source) or $upcomingCourse->video_demo_source == 'upload') ? 'd-none' : '' }}">
                        <i data-feather="link" width="18" height="18" class=" js-font-resize text-white"></i>
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
        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
            <textarea id="summernote" name="description" class=" js-font-resize form-control @error('description')  is-invalid @enderror" placeholder="{{ trans('forms.webinar_description_placeholder') }}">{!! (!empty($upcomingCourse) and !empty($upcomingCourse->translate($locale))) ? $upcomingCourse->translate($locale)->description : old('description')  !!}</textarea>
            @error('description')
            <div class=" js-font-resize invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
    </div>
</div>


@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>

    @push('scripts_bottom')
        <script>
            var videoDemoPathPlaceHolderBySource = {
                upload: '{{ trans('update.file_source_upload_placeholder') }}',
                youtube: '{{ trans('update.file_source_youtube_placeholder') }}',
                vimeo: '{{ trans('update.file_source_vimeo_placeholder') }}',
                external_link: '{{ trans('update.file_source_external_link_placeholder') }}',
            }
        </script>
    @endpush
@endpush
