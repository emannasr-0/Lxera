<section class=" js-font-resize mt-30">
    <div class=" js-font-resize d-flex justify-content-between align-items-center mb-10">
        <h2 class=" js-font-resize section-title after-line">{{trans('panel.business_links')}}</h2>
        <button id="userAddLinks" type="button" class=" js-font-resize btn btn-primary btn-sm">{{trans('panel.add_link')}}</button>
    </div>

    <div id="userListLinks">

        @php
            $pattern = '/title:\s*(.*?),\s*year:\s*(\d+)/';
        @endphp
        @if(!empty($links) and !$links->isEmpty())
            @foreach($links as $link)

                <div class=" js-font-resize row mt-20">
                    <div class=" js-font-resize col-12">
                        <div class=" js-font-resize link-card py-15 py-lg-30 px-10 px-lg-25 rounded-sm panel-shadow bg-secondary-acadima d-flex align-items-center justify-content-between">
                            <div class=" js-font-resize col-10 text-secondary font-weight-500 text-left link-value" link-value="{{ $link->value }}" >
                                @if (preg_match($pattern, $link->value, $matches))
                                <div class=" js-font-resize row">
                                    <p class=" js-font-resize col-12 col-sm-6">
                                        {{trans('public.experience_field')}} {{ $matches[1] }}
                                    </p>
                                    <p class=" js-font-resize col-12 col-sm-6">
                                        {{trans('public.years_of_experience')}} {{ $matches[2] }} {{trans('panel.years')}}
                                    </p>
                                </div>

                                @else
                                {{ $link->value }}
                                @endif
                            </div>
                            <div class=" js-font-resize col-2 text-right">
                                <div class=" js-font-resize btn-group dropdown table-actions">
                                    <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i data-feather="more-vertical" height="20" class=" js-font-resize text-black"></i>
                                    </button>
                                    <div class=" js-font-resize dropdown-menu font-weight-normal">
                                        <button type="button" data-link-id="{{ $link->id }}" data-user-id="{{ (!empty($user) and empty($new_user)) ? $user->id : '' }}" class=" js-font-resize d-block btn-transparent edit-link">{{ trans('public.edit') }}</button>
                                        <a href="/panel/setting/metas/{{ $link->id }}/delete?user_id={{ (!empty($user) and empty($new_user)) ? $user->id : '' }}" class=" js-font-resize delete-action d-block mt-10 btn-transparent">{{ trans('public.delete') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @endforeach
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'exp.png',
                'title' => trans('public.no_previous_work_links'),
                'hint' => trans('public.attach_work_links'),
            ])
        @endif
    </div>

</section>

<div class=" js-font-resize d-none" id="newLinkModal">
    <h3 class=" js-font-resize section-title after-line">{{trans('public.add_new_link')}}</h3>
    <div class=" js-font-resize mt-20 text-center">
    {{-- <img src="/assets/default/img/info.png" width="108" height="96" class=" js-font-resize rounded-circle" alt=""> --}}
    <div class=" js-font-resize swal2-icon swal2-warning swal2-icon-show" style="display: flex;"><div class=" js-font-resize swal2-icon-content">!</div></div>

        <h4 class=" js-font-resize font-16 mt-20 text-black font-weight-bold">{{trans('panel.add_link_in_one_line')}}</h4>
        <span class=" js-font-resize d-block mt-10 text-gray font-14">{{trans('public.example')}} https://www.behance.net/gallery/206425103/</span>
        <div class=" js-font-resize form-group mt-15 px-20">
            <input type="url" id="new_link_val" required class=" js-font-resize form-control" placeholder=" {{trans('public.enter_link_address')}}">
            <div class=" js-font-resize invalid-feedback">{{ trans('validation.required',['attribute' => 'عنوان الرابط']) }}</div>
        </div>

    </div>

    <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
        <button type="button" id="saveLink" class=" js-font-resize btn btn-sm btn-primary">{{ trans('public.save') }}</button>
        <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
    </div>
</div>
