
<div class=" js-font-resize wizard-step-1">
    <h3 class=" js-font-resize font-20 text-dark font-weight-bold">{{ trans('update.your_skill_level') }}</h3>

    <span class=" js-font-resize d-block mt-30 text-gray wizard-step-num">
        {{ trans('update.step') }} 3/4
    </span>

    <div class=" js-font-resize form-group mt-30">
        <label class=" js-font-resize input-label font-weight-500">{{ trans('update.which_skill_level_do_you_want_to_learn') }}</label>

        <select name="level_of_training" class=" js-font-resize form-control mt-20">
            <option value="beginner" {{ (request()->get('level_of_training') == 'beginner') ? 'selected' : '' }}>{{ trans('update.beginner') }}</option>
            <option value="middle" {{ (empty(request()->get('level_of_training')) or request()->get('level_of_training') == 'middle') ? 'selected' : '' }}>{{ trans('update.middle') }}</option>
            <option value="expert" {{ (request()->get('level_of_training') == 'expert') ? 'selected' : '' }}>{{ trans('update.expert') }}</option>
        </select>
    </div>

</div>
