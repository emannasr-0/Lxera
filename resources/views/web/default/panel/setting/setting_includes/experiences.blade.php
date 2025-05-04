<section class="mt-30">
    <div class="d-flex justify-content-between align-items-center mb-10">
        <h2 class="section-title after-line">{{ trans('site.experiences') }}</h2>
        <button id="userAddExperiences" type="button"
            class="btn btn-primary btn-sm">{{ trans('site.add_experiences') }}</button>
    </div>

    <div id="userListExperiences">

        @php
            $pattern = '/title:\s*([^,]+),\s*year:\s*(.+)/';

        @endphp
        @if (!empty($experiences) and !$experiences->isEmpty())
            @foreach ($experiences as $experience)
                <div class="row mt-20">
                    <div class="col-12">
                        <div
                            class="experience-card py-15 py-lg-30 px-10 px-lg-25 rounded-sm panel-shadow bg-secondary-acadima d-flex align-items-center justify-content-between">
                            <div class="col-10 text-secondary font-weight-500 text-left experience-value"
                                experience-value="{{ $experience->value }}">
                                @if (preg_match($pattern, $experience->value, $matches))
                                    <div class="row">
                                        <p class="col-12 col-sm-6">
                                            {{trans('public.experience_field')}} {{ $matches[1] }}
                                        </p>
                                        <p class="col-12 col-sm-6">
                                            {{trans('public.years_of_experience')}} {{ trans('application_form.'.$matches[2]) }}
                                        </p>
                                    </div>
                                @else
                                    {{ $experience->value }}
                                @endif
                            </div>
                            <div class="col-2 text-right">
                                <div class="btn-group dropdown table-actions">
                                    <button type="button" class="btn-transparent dropdown-toggle"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i data-feather="more-vertical" height="20" class="text-black"></i>
                                    </button>
                                    <div class="dropdown-menu font-weight-normal bg-secondaary-acadima">
                                        <button type="button" data-experience-id="{{ $experience->id }}"
                                            data-user-id="{{ (!empty($user) and empty($new_user)) ? $user->id : '' }}"
                                            class="d-block btn-transparent edit-experience">{{ trans('public.edit') }}</button>
                                        <a href="/panel/setting/metas/{{ $experience->id }}/delete?user_id={{ (!empty($user) and empty($new_user)) ? $user->id : '' }}"
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
                'title' => trans('auth.experience_no_result'),
                'hint' => trans('auth.experience_no_result_hint'),
            ])
        @endif
    </div>

</section>

<div class="d-none" id="newExperienceModal">
    <h3 class="section-title after-line">{{ trans('site.new_experience') }}</h3>
    <div class="mt-20">
        <div  class="text-center">
           {{-- <img src="/assets/default/img/info.png" width="108" height="96" class="rounded-circle" alt=""> --}} 
            <div class="swal2-icon swal2-warning swal2-icon-show" style="display: flex;"><div class="swal2-icon-content">!</div></div>
            <h4 class="font-16 mt-20 text-black font-weight-bold">{{ trans('site.new_experience_hint') }}</h4>
            <span class="d-block mt-10 text-gray font-14">{{ trans('site.new_experience_exam') }}</span>
        </div>

        <div class="form-group mt-15 px-20">
            <label class="form-label text-left text-black">
                {{trans('public.mention_experience_field')}}
                 <span class="text-danger">*</span></label>

            <input type="text" id="new_experience_val" required class="form-control" placeholder="{{trans('panel.enter_field_of_expertise')}}">
            <div class="invalid-feedback">{{ trans('validation.required', ['attribute' => 'مجال الخبرة']) }}</div>
        </div>

        <div class="form-group mt-15 px-20">
            <label class="form-label text-left text-black">
                {{trans('public.choose_years_of_experience')}}
                <span class="text-danger">*</span></label>

            <div class="row mr-5 mt-5 col-12  text-gray">
                {{-- less than 5 --}}
                <div class="col-12 "> {{-- col-sm-4 --}}
                    <input type="radio" id="less_than_5" name="new_experience_val2"
                    value="less_than_5" required >
                    <label for="less_than_5">
                    {{ trans('application_form.less_than_5') }}
                    </label>
                </div>

                {{--  5-10 --}}
                <div class="col-12 "> {{-- col-sm-4 --}}
                    <input type="radio" id="5-10" name="new_experience_val2"
                    value="5-10" required >
                    <label for="5-10">
                    {{ trans('application_form.5-10') }}
                    </label>
                </div>

                {{-- more than 10 --}}
                <div class="col-12 "> {{-- col-sm-4 --}}
                    <input type="radio" id="more_than_10" name="new_experience_val2"
                    value="more_than_10" required >
                    <label for="more_than_10">
                        {{ trans('application_form.more_than_10') }}
                    </label>
                </div>

            </div>

            {{-- <input type="number" id="new_experience_val2" required class="form-control"
                placeholder="أذكر عدد سنوات الخبرة "> --}}
            <div class="invalid-feedback" id="experience_val2_feedback">{{ trans('validation.required', ['attribute' => 'عدد سنوت الخبرة']) }}</div>
        </div>
    </div>

    <div class="mt-30 d-flex align-items-center justify-content-end">
        <button type="button" id="saveExperience" class="btn btn-sm btn-primary">{{ trans('public.save') }}</button>
        <button type="button" class="btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
    </div>
</div>
