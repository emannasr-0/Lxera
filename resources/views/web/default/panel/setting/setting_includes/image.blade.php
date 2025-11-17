<section>
    <h3 class=" js-font-resize section-title after-line mt-35">{{ trans('auth.images') }}</h3>

    <div class=" js-font-resize row mt-20">
        <div class=" js-font-resize col-12 col-lg-4">

            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('auth.profile_image') }}</label>
                <img src="{{ (!empty($user)) ? $user->getAvatar(150) : '' }}" alt="" id="profileImagePreview" width="150" height="150" class=" js-font-resize rounded-circle my-15 d-block ml-5">

                <button id="selectAvatarBtn" type="button" class=" js-font-resize btn btn-sm btn-secondary select-image-cropit" data-ref-image="profileImagePreview" data-ref-input="profile_image">
                    <i data-feather="arrow-up" width="18" height="18" class=" js-font-resize text-dark mr-10"></i>
                    <span class=" js-font-resize text-dark">{{ trans('auth.select_image') }}</span>
                </button>

                <div class=" js-font-resize input-group">
                    <input type="hidden" name="profile_image" id="profile_image" class=" js-font-resize form-control @error('profile_image')  is-invalid @enderror"/>
                    @error('profile_image')
                    <div class=" js-font-resize invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- <div class=" js-font-resize col-12 col-lg-4">
            <div class=" js-font-resize form-group">
                <label class=" js-font-resize input-label">{{ trans('auth.profile_cover') }}</label>

                <img src="{{ (!empty($user)) ? $user->getCover() : '' }}" alt="" id="profileCoverPreview" height="150" class=" js-font-resize rounded-sm my-15 d-block w-100">

                <div class=" js-font-resize form-group">
                    <div class=" js-font-resize input-group">
                        <div class=" js-font-resize input-group-prepend">
                            <button type="button" class=" js-font-resize input-group-text panel-file-manager" data-input="cover_img" data-preview="holder">
                                <i data-feather="arrow-up" width="18" height="18" class=" js-font-resize text-white"></i>
                            </button>
                        </div>
                        <input type="text" name="cover_img" id="cover_img" value="{{ !empty($user) ? $user->cover_img : old('cover_img') }}" class=" js-font-resize form-control " placeholder="{{ trans('forms.course_cover_size') }}"/>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
</section>

<div class=" js-font-resize modal fade" id="avatarCropModalContainer" tabindex="-1" role="dialog" aria-labelledby="avatarCrop">
    <div class=" js-font-resize modal-dialog" role="document">
        <div class=" js-font-resize modal-content">
            <div class=" js-font-resize modal-header">
                <h4 class=" js-font-resize modal-title" id="myModalLabel">{{ trans('public.edit_selected_image') }}</h4>
            </div>
            <div class=" js-font-resize modal-body">
                <div id="imageCropperContainer">
                    <div class=" js-font-resize cropit-preview"></div>
                    <div class=" js-font-resize cropit-tools">
                        <div class=" js-font-resize d-flex align-items-center justify-content-center">
                            <div class=" js-font-resize mr-20">
                                <button type="button" class=" js-font-resize btn btn-transparent rotate-cw mr-10">
                                    <i data-feather="rotate-cw" width="18" height="18"></i>
                                </button>
                                <button type="button" class=" js-font-resize btn btn-transparent rotate-ccw">
                                    <i data-feather="rotate-ccw" width="18" height="18"></i>
                                </button>
                            </div>

                            <div class=" js-font-resize d-flex align-items-center justify-content-center">
                                <span>-</span>
                                <input type="range" class=" js-font-resize cropit-image-zoom-input mx-10">
                                <span>+</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button class=" js-font-resize btn btn-transparent" id="cancelAvatarCrop">{{ trans('public.cancel') }}</button>
                        <button class=" js-font-resize btn btn-green" id="storeAvatar">{{ trans('public.select') }}</button>
                    </div>
                    <input type="file" class=" js-font-resize cropit-image-input">
                </div>
            </div>
        </div>
    </div>
</div>
