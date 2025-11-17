<div class=" js-font-resize tab-pane mt-3 fade" id="registrationPackage" role="tabpanel" aria-labelledby="registrationPackage-tab">
    <div class=" js-font-resize row">
        <div class=" js-font-resize col-12 col-md-6">
            <form action="{{ getAdminPanelUrl() }}/users/groups/{{ $group->id }}/groupRegistrationPackage" method="Post">
                {{ csrf_field() }}

                <div class=" js-font-resize form-group custom-switches-stacked">
                    <label class=" js-font-resize custom-switch pl-0 d-flex align-items-center">
                        <input type="hidden" name="status" value="disabled">
                        <input type="checkbox" name="status" id="packageStatusSwitch" value="active" {{ (!empty($groupRegistrationPackage) and $groupRegistrationPackage->status == 'active') ? 'checked="checked"' : '' }} class=" js-font-resize custom-switch-input"/>
                        <span class=" js-font-resize custom-switch-indicator"></span>
                        <label class=" js-font-resize custom-switch-description mb-0 cursor-pointer" for="packageStatusSwitch">{{ trans('admin/main.active') }}</label>
                    </label>
                    <div class=" js-font-resize text-muted text-small mt-1">{{ trans('update.user_registration_packages_status_hint') }}</div>
                </div>

                @php
                    $packageItems = ['instructors_count','students_count','courses_capacity','courses_count','meeting_count'];
                @endphp

                @foreach($packageItems as $str)
                    <div class=" js-font-resize form-group">
                        <label>{{ trans('update.'.$str) }}</label>
                        <input type="text" class=" js-font-resize form-control @error($str) is-invalid @enderror" name="{{ $str }}" value="{{ !empty($groupRegistrationPackage) ? $groupRegistrationPackage->{$str} : '' }}">

                        @error($str)
                        <div class=" js-font-resize invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                @endforeach


                <div class=" js-font-resize  mt-4">
                    <button class=" js-font-resize btn btn-primary">{{ trans('admin/main.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
