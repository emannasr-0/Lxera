<section class=" js-font-resize mt-30">
    <div class=" js-font-resize d-flex justify-content-between align-items-center mb-10">
        <h2 class=" js-font-resize section-title after-line">{{ trans('site.education') }}</h2>
        <button id="userAddEducations" type="button" class=" js-font-resize btn btn-primary btn-sm">{{ trans('site.add_education') }}</button>
    </div>

    <div id="userListEducations">

        @if(!empty($educations) and !$educations->isEmpty())
            @foreach($educations as $education)
                <div class=" js-font-resize row mt-20">
                    <div class=" js-font-resize col-12">
                        <div class=" js-font-resize education-card py-15 py-lg-30 px-10 px-lg-25 rounded-sm panel-shadow bg-white d-flex align-items-center justify-content-between">
                            <div class=" js-font-resize col-8 text-secondary text-left font-weight-500 education-value">{{ $education->value }}</div>
                            <div class=" js-font-resize col-2 text-right">
                                <div class=" js-font-resize btn-group dropdown table-actions">
                                    <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i data-feather="more-vertical" height="20"></i>
                                    </button>
                                    <div class=" js-font-resize dropdown-menu">
                                        <button type="button" data-education-id="{{ $education->id }}" data-user-id="{{ (!empty($user) and empty($new_user)) ? $user->id : '' }}" class=" js-font-resize d-block btn-transparent edit-education">{{ trans('public.edit') }}</button>
                                        <a href="/panel/setting/metas/{{ $education->id }}/delete?user_id={{ (!empty($user) and empty($new_user)) ? $user->id : '' }}" class=" js-font-resize delete-action d-block mt-10 btn-transparent">{{ trans('public.delete') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else

            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'edu.png',
                'title' => trans('auth.education_no_result'),
                'hint' => trans('auth.education_no_result_hint'),
            ])
        @endif
    </div>

</section>

<div class=" js-font-resize d-none" id="newEducationModal">
    <h3 class=" js-font-resize section-title after-line">{{ trans('site.new_education') }}</h3>
    <div class=" js-font-resize mt-20 text-center">
        <img src="/assets/default/img/info.png" width="108" height="96" class=" js-font-resize rounded-circle" alt="">
        <h4 class=" js-font-resize font-16 mt-20 text-dark-blue font-weight-bold">{{ trans('site.new_education_hint') }}</h4>
        <span class=" js-font-resize d-block mt-10 text-gray font-14">{{ trans('site.new_education_exam') }}</span>
        <div class=" js-font-resize form-group mt-15 px-50">
            <input type="text" id="new_education_val" class=" js-font-resize form-control">
            <div class=" js-font-resize invalid-feedback">{{ trans('validation.required',['attribute' => 'value']) }}</div>
        </div>
    </div>

    <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
        <button type="button" id="saveEducation" class=" js-font-resize btn btn-sm btn-primary">{{ trans('public.save') }}</button>
        <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
    </div>
</div>
