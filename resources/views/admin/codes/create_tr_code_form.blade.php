    <form action="{{ getAdminPanelUrl() }}/codes/instructor_store" method="POST">
    @csrf
    <section>
        <div class="row">
            <div class="col-12 col-md-4">


                <div class="d-flex align-items-center justify-content-between">
                    <div class="">
                        <h2 class="section-title">{{ !empty($code) ? (trans('public.edit').' ('. $code->instructor_code .')') : trans('code.new_code') }}</h2>

                        @if(!empty($creator))
                            <p>{{ trans('admin/main.instructor') }}: {{ $creator->full_name }}</p>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label class="input-label">{{ trans('code.code_title') }}</label>
                    <input type="text" name="instructor_code" value=""  class="js-ajax-student_code form-control " placeholder=""/>
                     <p class="font-12 text-gray mt-1">يجب أن يبدأ ب TR</p>
                    <div class="invalid-feedback"></div>
                </div>


            </div>
        </div>
    </section>


    <!--<input type="hidden" name="ajax[{{ !empty($quiz) ? $quiz->id : 'new' }}][is_webinar_page]" value="@if(!empty($inWebinarPage) and $inWebinarPage) 1 @else 0 @endif">-->

    <div class="mt-20 mb-20">
        <button type="submit" class="btn btn-sm btn-primary">{{ !empty($code) ? trans('public.save_change') : trans('public.create') }}</button>

    </div>
    </form>


