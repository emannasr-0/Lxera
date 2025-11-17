<div class=" js-font-resize d-none" id="meetingCreateSessionModal">
    <h3 class=" js-font-resize section-title after-line font-20 text-dark-blue mb-25">{{ trans('update.create_a_live_session') }}</h3>

    <div class=" js-font-resize d-flex align-items-center justify-content-center flex-column mt-20">
        <img src="/assets/default/img/meeting/live_session.svg" alt="" class=" js-font-resize " width="150" height="150">

        <h4 class=" js-font-resize js-for-create-session-text d-none mt-5">{{ trans('update.new_in-app_call_session') }}</h4>
        <p class=" js-font-resize js-for-create-session-text d-none mt-5 text-gray font-14">{{  trans('update.are_you_sure_to_create_an_in-app_live_session_for_this_meeting') }}</p>
        <p class=" js-font-resize js-for-create-session-text d-none mt-5 text-gray font-14"> {{ trans('update.your_meeting_date_is') }} <span class=" js-font-resize js-meeting-date"></span></p>

        <h4 class=" js-font-resize js-for-join-session-text d-none mt-5">{{ trans('update.join_the_live_session_now') }}</h4>
        <p class=" js-font-resize js-for-join-session-text d-none mt-5 text-gray font-14">{{ trans('update.live_session_created_successfully_and_you_can_join_it_right_now') }}</p>
    </div>

    <div class=" js-font-resize mt-30 d-flex align-items-center justify-content-end">
        <button type="button" data-item-id="" class=" js-font-resize js-create-meeting-session btn btn-sm btn-primary">{{ trans('public.create') }}</button>
        <a href="" target="_blank" class=" js-font-resize js-join-to-session d-none btn btn-sm btn-primary">{{ trans('footer.join') }}</a>
        <button type="button" class=" js-font-resize btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
    </div>
</div>

