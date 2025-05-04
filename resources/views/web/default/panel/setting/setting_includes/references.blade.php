<section class="mt-30">
    <div class="d-flex justify-content-between align-items-center mb-10">
        <h2 class="section-title after-line">{{trans('public.known_people')}}</h2>
        <button id="userAddReferences" type="button" class="btn btn-primary btn-sm">{{trans('public.add_known_person')}}</button>
    </div>

    <div id="userListReferences">

        @if (!empty($references) and !$references->isEmpty())
            @foreach ($references as $reference)
                <div class="row mt-20">
                    <div class="col-12">
                        <div
                            class="reference-card py-15 py-lg-30 px-10 px-lg-25 rounded-sm panel-shadow bg-secondary-acadima d-flex align-items-center justify-content-between">
                            <div class="col-10 text-secondary font-weight-500 text-left reference-value"
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
                            <div class="col-2 text-right">
                                <div class="btn-group dropdown table-actions">
                                    <button type="button" class="btn-transparent dropdown-toggle"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i data-feather="more-vertical" height="20" class="text-dark"></i>
                                    </button>
                                    <div class="dropdown-menu font-weight-normal">
                                        <button type="button" data-reference-id="{{ $reference->id }}"
                                            data-user-id="{{ (!empty($user) and empty($new_user)) ? $user->id : '' }}"
                                            class="d-block btn-transparent edit-reference">{{ trans('public.edit') }}</button>
                                        <a href="/panel/setting/references/{{ $reference->id }}/delete?user_id={{ (!empty($user) and empty($new_user)) ? $user->id : '' }}"
                                            class="delete-action d-block mt-10 btn-transparent">{{ trans('public.delete') }}</a>
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

<div class="d-none" id="newReferenceModal">
    <h3 class="section-title after-line">{{trans('panel.add_id')}}</h3>
    <div class="mt-20">
        {{-- <div  class="text-center">
            <img src="/assets/default/img/info.png" width="108" height="96" class="rounded-circle" alt="">
            <h4 class="font-16 mt-20 text-black font-weight-bold">{{ trans('site.new_reference_hint') }}</h4>
            <span class="d-block mt-10 text-gray font-14">{{ trans('site.new_reference_exam') }}</span>
        </div> --}}

        <div class="form-group mt-15 px-50 text-dark">
            <label class="form-label text-left">
                {{trans('public.name')}}
                <span class="text-danger">*</span></label>

            <input type="text" id="reference_name" name ="name" required class="form-control"
                placeholder="{{trans('public.enter_name')}}">
            <div class="invalid-feedback">{{ trans('validation.required', ['attribute' => 'اسم المعرف']) }}</div>
        </div>
        <div class="form-group mt-15 px-50 text-dark">
            <label class="form-label text-left">
                {{trans('public.email')}}
                <span class="text-danger">*</span></label>

            <input type="text" id="reference_email" name="email" required class="form-control"
                placeholder="{{trans('public.enter_email')}} ">
            <div class="invalid-feedback">{{ trans('validation.required', ['attribute' => 'البريد الالكتروني']) }}
            </div>
        </div>

        <div class="form-group mt-15 px-50 text-dark">
            <label class="form-label text-left">
                {{trans('public.job_title')}}
                <span class="text-danger">*</span></label>

            <input type="text" id="reference_job_title" name="job_title" required class="form-control"
                placeholder="{{trans('public.job_title')}}">
            <div class="invalid-feedback">{{ trans('validation.required', ['attribute' => 'المسمي الوظيفي']) }}</div>
        </div>

        <div class="form-group mt-15 px-50 text-dark">
            <label class="form-label text-left">
                {{trans('public.job_title')}}
                <span class="text-danger">*</span></label>

            <input type="text" id="reference_workplace" name="workplace" required class="form-control"
                placeholder="{{trans('public.employer')}}">
            <div class="invalid-feedback">{{ trans('validation.required', ['attribute' => 'مكان العمل']) }}</div>
        </div>

        <div class="form-group mt-15 px-50 text-dark">
            <label class="form-label text-left">
                {{trans('public.relationship_nature')}}
                <span class="text-danger">*</span></label>

            <input type="text" id="reference_relationship" name="relationship" required class="form-control"
                placeholder="{{trans('public.relationship_nature')}}">
            <div class="invalid-feedback">{{ trans('validation.required', ['attribute' => 'طبيعة العلاقة']) }}</div>
        </div>


    </div>

    <div class="mt-30 d-flex align-items-center justify-content-end">
        <button type="button" id="saveReference" class="btn btn-sm btn-primary">{{ trans('public.save') }}</button>
        <button type="button" class="btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
    </div>
</div>
