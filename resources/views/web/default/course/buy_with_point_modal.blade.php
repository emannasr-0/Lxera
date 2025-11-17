<div class=" js-font-resize d-none" id="buyWithPointModal">
    <h3 class=" js-font-resize section-title font-16 text-dark-blue mb-25">{{ trans('update.buy_with_points') }}</h3>

    @if(!empty($user))
        <div class=" js-font-resize text-center">
            <img src="/assets/default/img/rewards/medal-2.png" class=" js-font-resize buy-with-points-modal-img" alt="medal">

            <p class=" js-font-resize font-14 font-weight-500 text-gray mt-30">
                <span class=" js-font-resize d-block">{{ trans('update.this_course_requires_n_points',['points' => $course->points]) }}</span>
                <span class=" js-font-resize d-block">{{ trans('update.you_have_n_points',['points' => $user->getRewardPoints()]) }}</span>

                @if($user->getRewardPoints() >= $course->points)
                    <span class=" js-font-resize d-block">{{ trans('update.do_you_want_to_proceed') }}</span>
                @else
                    <span class=" js-font-resize d-block text-danger">{{ trans('update.you_have_no_enough_points_for_this_course') }}</span>
                @endif
            </p>
        </div>

        <div class=" js-font-resize d-flex align-items-center mt-25">
            <a href="{{ ($user->getRewardPoints() >= $course->points) ? '/course/'. $course->slug .'/points/apply' : '#' }}" class=" js-font-resize btn btn-sm flex-grow-1 {{ ($user->getRewardPoints() >= $course->points) ? 'btn-primary js-buy-course-with-point' : 'bg-gray300 text-gray disabled' }}">{{ trans('update.buy') }}</a>
            <a href="/panel/rewards" class=" js-font-resize btn btn-outline-primary ml-15 btn-sm flex-grow-1">{{ trans('update.my_points') }}</a>
        </div>
    @endif
</div>
