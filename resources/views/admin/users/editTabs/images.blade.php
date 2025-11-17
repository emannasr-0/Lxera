<div class=" js-font-resize tab-pane mt-3 fade" id="images" role="tabpanel" aria-labelledby="images-tab">
    <div class=" js-font-resize row">
        <div class=" js-font-resize col-12 col-md-6">
            <form action="{{ getAdminPanelUrl() }}/users/{{ $user->id .'/updateImage' }}" method="Post">
                {{ csrf_field() }}

                <div class=" js-font-resize form-group mt-15">
                    <label class=" js-font-resize input-label">{{ trans('admin/main.avatar') }}</label>
                    <div class=" js-font-resize input-group">
                        <div class=" js-font-resize input-group-prepend">
                            <button type="button" class=" js-font-resize input-group-text admin-file-manager" data-input="avatar" data-preview="holder">
                                <i class=" js-font-resize fa fa-chevron-up"></i>
                            </button>
                        </div>
                        <input type="text" name="avatar" id="avatar" value="{{ !empty($user->avatar) ? $user->getAvatar() : old('image_cover') }}" class=" js-font-resize form-control"/>
                        <div class=" js-font-resize input-group-append">
                            <button type="button" class=" js-font-resize input-group-text admin-file-view" data-input="avatar">
                                <i class=" js-font-resize fa fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class=" js-font-resize form-group mt-15">
                    <label class=" js-font-resize input-label">{{ trans('admin/main.cover_image') }}</label>
                    <div class=" js-font-resize input-group">
                        <div class=" js-font-resize input-group-prepend">
                            <button type="button" class=" js-font-resize input-group-text admin-file-manager" data-input="cover_img" data-preview="holder">
                                <i class=" js-font-resize fa fa-chevron-up"></i>
                            </button>
                        </div>
                        <input type="text" name="cover_img" id="cover_img" value="{{ !empty($user->cover_img) ? $user->cover_img : old('image_cover') }}" class=" js-font-resize form-control"/>
                        <div class=" js-font-resize input-group-append">
                            <button type="button" class=" js-font-resize input-group-text admin-file-view" data-input="cover_img">
                                <i class=" js-font-resize fa fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>


                <div class=" js-font-resize  mt-4">
                    <button class=" js-font-resize btn btn-primary">{{ trans('admin/main.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
