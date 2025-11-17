<section class=" js-font-resize mt-30">
    <div class=" js-font-resize d-flex justify-content-between align-items-center mb-10">
        <h2 class=" js-font-resize section-title after-line">{{trans('public.known_people')}}</h2>
        <button id="userAddReferences" type="button" class=" js-font-resize btn btn-primary btn-sm">{{trans('public.add_known_person')}}</button>
    </div>

    <div id="userListReferences">

        @if (!empty($references) and !$references->isEmpty())
            @foreach ($references as $reference)
                <div class=" js-font-resize row mt-20">
                    <div class=" js-font-resize col-12">
                        <div
                            class=" js-font-resize reference-card py-15 py-lg-30 px-10 px-lg-25 rounded-sm panel-shadow bg-secondary-acadima d-flex align-items-center justify-content-between">
                            <div class=" js-font-resize col-10 text-secondary font-weight-500 text-left reference-value"
                                reference-value="{{ $reference }}">

                                <div>
                                    <p>{{trans('public.name')}} {{ $reference->name }}</p>
                                </div>

                                <div>
                                    <p>{{trans('public.email')}} {{ $reference->email }}</p>
                                </div>

                                <div>
                                    <p>{{trans('public.job_title')}} {{ $reference->job_title }}</p>
                                </div>
                                <div>
                                    <p>{{trans('public.employer')}} {{ $reference->workplace }}</p>
                                </div>

                                <div>
                                    <p>{{trans('public.relationship_nature')}} {{ $reference->relationship }}</p>
                                </div>

                            </div>
                            <div class=" js-font-resize col-2 text-right">
                                <div class=" js-font-resize btn-group dropdown table-actions">
                                    <button type="button" class=" js-font-resize btn-transparent dropdown-toggle"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i data-feather="more-vertical" height="20" class=" js-font-resize text-dark"></i>
                                    </button>
                                    <div class=" js-font-resize dropdown-menu font-weight-normal">
                                        <button type="button" data-reference-id="{{ $reference->id }}"
                                            data-user-id="{{ (!empty($user) and empty($new_user)) ? $user->id : '' }}"
                                            class=" js-font-resize d-block btn-transparent edit-reference">{{ trans('public.edit') }}</button>
                                        <a href="/panel/setting/references/{{ $reference->id }}/delete?user_id={{ (!empty($user) and empty($new_user)) ? $user->id : '' }}"
                                            class=" js-font-resize delete-action d-block mt-10 btn-transparent">{{ trans('public.delete') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            @include(getTemplate() . '.includes.no-result', [
                'file_name' => 'exp.png',
                'title' => trans('public.no_known_people_added'),
                'hint' =>trans('public.mention_two_known_people'),
            ])
        @endif
    </div>

</section>

<div class=" js-font-resize d-none" id="newReferenceModal">
    <h3 class=" js-font-resize section-title after-line">{{trans('panel.add_id')}}</h3>
    <div class=" js-font-resize mt-20">
        {{-- <div  class=" js-font-resize text-center">
            <img src="/assets/default/img/info.png" width="108" height="96" class=" js-font-resize rounded-circle" alt="">
            <h4 class=" js-font-resize font-16 mt-20 text-black font-weight-bold">{{ trans('site.new_reference_hint') }}</h4>
            <span class=" js-font-resize d-block mt-10 text-gray font-14">{{ trans('site.new_reference_exam') }}</span>
        </div> --}}

        <div class=" js-font-resize form-group mt-15 px-50 text-dark">
            <label class=" js-font-resize form-label text-left">
                {{trans('public.name')}}
                <span class=" js-font-resize text-danger">*</span></label>

            <input type="text" id="reference_name" name ="name" required class=" js-font-resize form-control"
                placeholder="{{trans('public.enter_name')}}">
            <div class=" js-font-resize invalid-feedback">{{ trans('validation.required', ['attribute' => 'اسم المعرف']) }}</div>
        </div>
        <div class=" js-font-resize form-group mt-15 px-50 text-dark">
            <label class=" js-font-resize form-label text-left">
                {{trans('public.email')}}
                <span class=" js-font-resize text-danger">*</span></label>

            <input type="text" id="reference_email" name="email" required class=" js-font-resize form-control"
                placeholder="{{trans('public.enter_email')}} ">
            <div class=" js-font-resize invalid-feedback">{{ trans('validation.required', ['attribute' => 'البريد الالكتروني']) }}
            </div>
        </div>

        <div class=" js-font-resize form-group mt-15 px-50 text-dark">
            <label class=" js-font-resize form-label text-left">
                {{trans('public.job_title')}}
                <span class=" js-font-resize text-danger">*</span></label>

            <input type="text" id="reference_job_title" name="job_title" required class=" js-font-resize form-control"
                placeholder="{{trans('public.job_title')}}">
            <div class=" js-font-resize invalid-feedback">{{ trans('validation.required', ['attribute' => 'المسمي الوظيفي']) }}</div>
        </div>

        <div class=" js-font-resize form-group mt-15 px-50 text-dark">
            <label class=" js-font-resize form-label text-left">
                {{trans('public.job_title')}}
                <span class=" js-font-resize text-danger">*</span></label>

            <input type="text" id="reference_workplace" name="workplace" required class=" js-font-resize form-control"
                placeholder="{{trans('public.employer')}}">
            <div class=" js-font-resize invalid-feedback">{{ trans('validation.required', ['attribute' => 'مكان العمل']) }}</div>
        </div>

        <div class=" js-font-resize form-group mt-15 px-50 text-dark">
            <label class=" js-font-resize form-label text-left">
                {{trans('public.relationship_nature')}}
                <span class=" js-font-resize text-danger">*</span></label>

            <input type="text" id="reference_relationship" name="relationship" required class=" js-font-resize form-control"
                placeholder="{{trans('public.relationship_nature')}}">
            <div class=" js-font-resize invalid-feedback">{{ trans('validation.required', ['attribute' => 'طبيعة العلاقة']) }}</div>
        </div>


    </div>

    <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
        <button type="button" id="saveReference" class=" js-font-resize btn btn-sm btn-primary">{{ trans('public.save') }}</button>
        <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
    </div>
</div>
