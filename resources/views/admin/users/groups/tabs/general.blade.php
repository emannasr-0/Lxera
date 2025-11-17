<div class=" js-font-resize tab-pane mt-3 fade active show" id="general" role="tabpanel" aria-labelledby="general-tab">
    <div class=" js-font-resize row">
        <div class=" js-font-resize col-12 col-md-8 col-lg-6">
            <form action="{{ getAdminPanelUrl() }}/users/groups/{{ !empty($group) ? $group->id.'/update' : 'store' }}" method="Post">
                {{ csrf_field() }}

                <div class=" js-font-resize form-group">
                    <label>{{ trans('admin/main.name') }}</label>
                    <input type="text" name="name"
                           class=" js-font-resize form-control  @error('name') is-invalid @enderror"
                           value="{{ !empty($group) ? $group->name : old('name') }}"/>
                    @error('name')
                    <div class=" js-font-resize invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class=" js-font-resize form-group ">
                    <label>{{ trans('admin/main.user_group_commission_rate') }}</label>
                    <div class=" js-font-resize input-group">
                        <div class=" js-font-resize input-group-prepend">
                            <div class=" js-font-resize input-group-text">
                                <i class=" js-font-resize fas fa-percentage"></i>
                            </div>
                        </div>

                        <input type="number"
                               name="commission"
                               class=" js-font-resize spinner-input form-control text-center @error('commission') is-invalid @enderror"
                               value="{{ !empty($group) ? $group->commission : old('commission') }}"
                               placeholder="{{ trans('admin/main.user_group_commission_rate_placeholder') }}" maxlength="3" min="0" max="100">

                        @error('commission')
                        <div class=" js-font-resize invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class=" js-font-resize text-muted text-small mt-1">{{ trans('admin/main.user_group_commission_rate_hint') }}</div>
                </div>

                <div class=" js-font-resize form-group ">
                    <label>{{ trans('admin/main.user_group_discount_rate') }}</label>
                    <div class=" js-font-resize input-group">
                        <div class=" js-font-resize input-group-prepend">
                            <div class=" js-font-resize input-group-text">
                                <i class=" js-font-resize fas fa-percentage"></i>
                            </div>
                        </div>
                        <input type="number"
                               name="discount"
                               class=" js-font-resize form-control spinner-input text-center @error('discount') is-invalid @enderror"
                               value="{{ !empty($group) ? $group->discount : old('discount') }}"
                               placeholder="{{ trans('admin/main.user_group_discount_rate_placeholder') }}" maxlength="3" min="0" max="100">
                        @error('discount')
                        <div class=" js-font-resize invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class=" js-font-resize text-muted text-small mt-1">{{ trans('admin/main.user_group_discount_rate_hint') }}</div>
                </div>


                <div class=" js-font-resize form-group">
                    <label class=" js-font-resize input-label d-block">{{ trans('admin/main.users') }}</label>
                    <select name="users[]" multiple="multiple" class=" js-font-resize form-control search-user-select2"
                            data-search-option="for_user_group"
                            data-placeholder="{{ trans('public.search_user') }}">

                        @if(!empty($userGroups) and $userGroups->count() > 0)
                            @foreach($userGroups as $userGroup)
                                <option value="{{ $userGroup->user_id }}" selected>{{ $userGroup->user->full_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class=" js-font-resize form-group custom-switches-stacked">
                    <label class=" js-font-resize custom-switch pl-0">
                        <input type="hidden" name="status" value="inactive">
                        <input type="checkbox" name="status" id="preloadingSwitch" value="active" {{ (!empty($group) and $group->status == 'active') ? 'checked="checked"' : '' }} class=" js-font-resize custom-switch-input"/>
                        <span class=" js-font-resize custom-switch-indicator"></span>
                        <label class=" js-font-resize custom-switch-description mb-0 cursor-pointer" for="preloadingSwitch">{{ trans('admin/main.active') }}</label>
                    </label>
                </div>

                <div class=" js-font-resize  mt-4">
                    <button class=" js-font-resize btn btn-primary">{{ trans('admin/main.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
