<!-- Modal -->
<div class=" js-font-resize d-none" id="webinarAssignmentModal">
    <h3 class=" js-font-resize section-title after-line font-20 text-dark-blue mb-25">{{ trans('update.add_new_assignments') }}</h3>
    <form action="{{ getAdminPanelUrl() }}/assignments/store" method="post">
        <input type="hidden" name="webinar_id" value="{{  !empty($webinar) ? $webinar->id :''  }}">

        @if(!empty(getGeneralSettings('content_translate')))
            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('auth.language') }}</label>
                <select name="locale" class=" js-font-resize form-control ">
                    @foreach($userLanguages as $lang => $language)
                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
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


        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.title') }}</label>
            <input type="text" name="title" class=" js-font-resize form-control" placeholder=""/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('public.chapter') }}</label>
            <select class=" js-font-resize custom-select" name="chapter_id">
                @if(!empty($chapters))
                    @foreach($chapters as $chapter)
                        <option value="{{ $chapter->id }}">{{ $chapter->title }}</option>
                    @endforeach
                @endif
            </select>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('admin/main.description') }}</label>
            <div class=" js-font-resize content-summernote js-ajax-description">
                <textarea name="description" rows="5" class=" js-font-resize form-control"></textarea>
            </div>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('quiz.grade') }}</label>
            <input type="text" name="grade" class=" js-font-resize form-control" placeholder=""/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('update.pass_grade') }}</label>
            <input type="text" name="pass_grade" class=" js-font-resize form-control" placeholder=""/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('update.deadline') }}</label>
            <input type="text" name="deadline" class=" js-font-resize form-control" placeholder=""/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize form-group">
            <label class=" js-font-resize input-label">{{ trans('update.attempts') }}</label>
            <input type="text" name="attempts" class=" js-font-resize form-control" placeholder=""/>
            <div class=" js-font-resize invalid-feedback"></div>
        </div>

        <div class=" js-font-resize js-assignment-attachments-items form-group mt-15">
            <div class=" js-font-resize d-flex align-items-center justify-content-between">
                <label class=" js-font-resize input-label mb-0">{{ trans('public.attachments') }}</label>

                <button type="button" class=" js-font-resize btn btn-primary btn-sm assignment-attachments-add-btn">
                    <i class=" js-font-resize fa fa-plus"></i>
                </button>
            </div>

            <div class=" js-font-resize assignment-attachments-main-row js-ajax-attachments position-relative">
                <div class=" js-font-resize mt-2 p-2 border rounded">
                    <div class=" js-font-resize mb-2">
                        <label class=" js-font-resize input-label">{{ trans('public.title') }}</label>
                        <input type="text" name="attachments[assignmentTemp][title]" class=" js-font-resize form-control" placeholder="{{ trans('forms.maximum_255_characters') }}"/>
                    </div>

                    <div class=" js-font-resize input-group product-images-input-group">
                        <div class=" js-font-resize input-group-prepend">
                            <button type="button" class=" js-font-resize input-group-text admin-file-manager" data-input="attachments_assignmentTemp" data-preview="holder">
                                <i class=" js-font-resize fa fa-upload text-light"></i>
                            </button>
                        </div>
                        <input type="text" name="attachments[assignmentTemp][attach]" id="attachments_assignmentTemp" value="" class=" js-font-resize form-control" placeholder="{{ trans('update.assignment_attachments_placeholder') }}"/>
                    </div>
                </div>

                <button type="button" class=" js-font-resize btn btn-danger btn-sm assignment-attachments-remove-btn d-none">
                    <i class=" js-font-resize fa fa-times"></i>
                </button>
            </div>

            <div class=" js-font-resize invalid-feedback"></div>

            <div class=" js-font-resize js-assignment-attachments-lists"></div>
        </div>

        <div class=" js-font-resize js-textLesson-status form-group mt-3">
            <div class=" js-font-resize d-flex align-items-center justify-content-between">
                <label class=" js-font-resize cursor-pointer input-label" for="textLessonStatusSwitch_record">{{ trans('admin/main.active') }}</label>
                <div class=" js-font-resize custom-control custom-switch">
                    <input type="checkbox" name="status" checked class=" js-font-resize custom-control-input" id="textLessonStatusSwitch_record">
                    <label class=" js-font-resize custom-control-label" for="textLessonStatusSwitch_record"></label>
                </div>
            </div>
        </div>

        @if(getFeaturesSettings('sequence_content_status'))
            <div class=" js-font-resize form-group mb-1">
                <div class=" js-font-resize d-flex align-items-center justify-content-between">
                    <label class=" js-font-resize cursor-pointer input-label" for="SequenceContentSwitch_record">{{ trans('update.sequence_content') }}</label>
                    <div class=" js-font-resize custom-control custom-switch">
                        <input type="checkbox" name="sequence_content" class=" js-font-resize js-sequence-content-switch custom-control-input" id="SequenceContentSwitch_record">
                        <label class=" js-font-resize custom-control-label" for="SequenceContentSwitch_record"></label>
                    </div>
                </div>
            </div>

            <div class=" js-font-resize js-sequence-content-inputs pl-2 d-none">
                <div class=" js-font-resize form-group mb-1">
                    <div class=" js-font-resize d-flex align-items-center justify-content-between">
                        <label class=" js-font-resize cursor-pointer input-label" for="checkPreviousPartsSwitch_record">{{ trans('update.check_previous_parts') }}</label>
                        <div class=" js-font-resize custom-control custom-switch">
                            <input type="checkbox" checked name="check_previous_parts" class=" js-font-resize custom-control-input" id="checkPreviousPartsSwitch_record">
                            <label class=" js-font-resize custom-control-label" for="checkPreviousPartsSwitch_record"></label>
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label">{{ trans('update.access_after_day') }}</label>
                    <input type="number" name="access_after_day" value="" class=" js-font-resize js-ajax-access_after_day form-control" placeholder="{{ trans('update.access_after_day_placeholder') }}"/>
                    <div class=" js-font-resize invalid-feedback"></div>
                </div>
            </div>
        @endif

        <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
            <button type="button" id="saveAssignment" class=" js-font-resize btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class=" js-font-resize btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </form>
</div>
