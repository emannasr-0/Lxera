<section class=" js-font-resize p-15 m-15 border rounded-lg bg-secondary-acadima">
    <div class=" js-font-resize assignment-top-stats d-flex flex-wrap flex-md-nowrap align-items-start align-items-lg-center justify-content-start justify-content-md-around">
        <div class=" js-font-resize col-6 col-md-2 assignment-top-stats__item d-flex align-items-center justify-content-center pb-5 pb-md-0">
            <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                <img src="/assets/default/img/activity/calendar.svg" class=" js-font-resize assignment-top-stats__icon" alt="">
                <span class=" js-font-resize font-20 text-black font-weight-bold mt-20">
                    {{ dateTimeFormat($assignment->deadline, 'j M Y') }}
                    </span>

                <strong class=" js-font-resize font-20 text-black font-weight-bold mt-5">
                    @if($assignmentDeadline)
                    (
                        {{ is_bool($assignmentDeadline) ? trans('update.unlimited') : trans('update.n_day', ['day' => ceil($assignmentDeadline)]) }}
                    )
                    @else
                        <span class=" js-font-resize text-danger">{{ trans('panel.expired') }}</span>
                    @endif
                </strong>
                <span class=" js-font-resize font-14 text-gray font-weight-500">{{ trans('update.deadline') }}</span>
            </div>
        </div>

        <div class=" js-font-resize col-6 col-md-2 assignment-top-stats__item d-flex align-items-center justify-content-center pb-5 pb-md-0">
            <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                <img src="/assets/default/img/activity/homework.svg" class=" js-font-resize assignment-top-stats__icon" alt="">
                <strong class=" js-font-resize font-20 text-black font-weight-bold mt-5">
                    @if(!empty($assignment->attempts))
                        {{ $submissionTimes }}/{{ $assignment->attempts  }}
                    @else
                        {{ trans('update.unlimited') }}
                    @endif
                </strong>
                <span class=" js-font-resize font-14 text-gray font-weight-500">{{ trans('update.submission_times') }}</span>
            </div>
        </div>

        <div class=" js-font-resize col-6 col-md-2 assignment-top-stats__item d-flex align-items-center justify-content-center pb-5 pb-md-0 mt-30 mt-md-0">
            <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                <img src="/assets/default/img/activity/45.svg" class=" js-font-resize assignment-top-stats__icon" alt="">
                <strong class=" js-font-resize font-20 text-black font-weight-bold mt-5">{{ $assignmentHistory->grade ?? 0 }}/{{ $assignment->grade }}</strong>
                <span class=" js-font-resize font-14 text-gray font-weight-500">{{ trans('quiz.your_grade') }}</span>
            </div>
        </div>

        <div class=" js-font-resize col-6 col-md-2 assignment-top-stats__item d-flex align-items-center justify-content-center pb-5 pb-md-0 mt-30 mt-md-0">
            <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                <img src="/assets/default/img/activity/58.svg" class=" js-font-resize assignment-top-stats__icon" alt="">
                <strong class=" js-font-resize font-20 text-black font-weight-bold mt-5">{{ $assignment->pass_grade }}</strong>
                <span class=" js-font-resize font-14 text-gray font-weight-500">{{ trans('update.min_grade') }}</span>
            </div>
        </div>

        <div class=" js-font-resize col-6 col-md-2 assignment-top-stats__item d-flex align-items-center justify-content-center pb-5 pb-md-0 mt-30 mt-md-0">
            <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                <img src="/assets/default/img/activity/88.svg" class=" js-font-resize assignment-top-stats__icon" alt="">
                <strong class=" js-font-resize font-20 text-black font-weight-bold mt-5">{{ trans('update.assignment_history_status_'.$assignmentHistory->status) }}</strong>
                <span class=" js-font-resize font-14 text-gray font-weight-500">{{ trans('public.status') }}</span>
            </div>
        </div>
    </div>

    <div class=" js-font-resize p-15 rounded-lg bg-info font-14 text-gray mt-20">{!! $assignment->description !!}</div>
</section>

@if(!empty($assignment->attachments) and count($assignment->attachments))
    <section class=" js-font-resize mt-25 container-fluid">
        <h2 class=" js-font-resize section-title">{{ trans('public.attachments') }}</h2>

        <div class=" js-font-resize row">
            @foreach($assignment->attachments as $attachment)
                <div class=" js-font-resize col-6 col-lg-3 mt-10">
                    <a href="{{ $attachment->getDownloadUrl() }}" target="_blank" class=" js-font-resize d-flex align-items-center p-10 border rounded-sm">
                        <span class=" js-font-resize chapter-icon bg-acadima-pink mr-10">
                            <i data-feather="file" class=" js-font-resize text-light" width="16" height="16"></i>
                        </span>

                        <div class=" js-font-resize ">
                            <h4 class=" js-font-resize font-12 text-gray font-weight-bold">{{ $attachment->title }}</h4>
                            <span class=" js-font-resize font-12 text-gray">{{ $attachment->getFileSize() }}</span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
@endif

<section class=" js-font-resize mt-25 container-fluid">
    <h2 class=" js-font-resize section-title">{{ trans('update.assignment_history') }}</h2>

    <section class=" js-font-resize  p-10 my-10 border rounded-lg bg-secondary-acadima">
        <div class=" js-font-resize row">
            <div class=" js-font-resize col-12 col-lg-4">
                @if(
                    $user->id != $assignment->creator_id and
                    (
                        $assignmentHistory->status == \App\Models\WebinarAssignmentHistory::$passed or
                        $assignmentHistory->status == \App\Models\WebinarAssignmentHistory::$notPassed or
                        !$assignmentDeadline or
                        (
                            !$checkHasAttempts and !empty($assignment->attempts) and $submissionTimes >= $assignment->attempts
                        )
                    )
                )
                    <div class=" js-font-resize d-flex align-items-center justify-content-center flex-column bg-secondary-acadima p-10 rounded-sm border h-100">
                        <div class=" js-font-resize learning-page-assignment-history-status-icon d-flex align-items-center justify-content-center">
                            @if($assignmentHistory->status == \App\Models\WebinarAssignmentHistory::$passed)
                                <img src="/assets/default/img/learning/assignment_passed.svg" class=" js-font-resize img-fluid" alt="">
                            @elseif($assignmentHistory->status == \App\Models\WebinarAssignmentHistory::$notPassed)
                                <img src="/assets/default/img/learning/no_assignment.svg" class=" js-font-resize img-fluid" alt="">
                            @else
                                <img src="/assets/default/img/learning/assignment_pending.svg" class=" js-font-resize img-fluid" alt="">
                            @endif
                        </div>

                        <div class=" js-font-resize d-flex align-items-center flex-column mt-10 text-center">
                            @if($assignmentHistory->status == \App\Models\WebinarAssignmentHistory::$passed)
                                <h3 class=" js-font-resize font-20 font-weight-bold text-pink text-center">{{ trans('update.assignment_passed_title') }}</h3>
                                <p class=" js-font-resize mt-5 text-gray font-14 text-center">{{ trans('update.assignment_passed_desc') }}</p>
                            @elseif($assignmentHistory->status == \App\Models\WebinarAssignmentHistory::$notPassed)
                                <h3 class=" js-font-resize font-20 font-weight-bold text-pink text-center">{{ trans('update.assignment_not_passed_title') }}</h3>
                                <p class=" js-font-resize mt-5 text-gray font-14 text-center">{{ trans('update.assignment_not_passed_desc') }}</p>
                            @elseif(!$assignmentDeadline)
                                <h3 class=" js-font-resize font-20 font-weight-bold text-pink text-center">{{ trans('update.assignment_deadline_error_title') }}</h3>
                                <p class=" js-font-resize mt-5 text-gray font-14 text-center">{{ trans('update.assignment_deadline_error_desc') }}</p>
                            @else
                                <h3 class=" js-font-resize font-20 font-weight-bold text-pink text-center">{{ trans('update.assignment_submission_error_title') }}</h3>
                                <p class=" js-font-resize mt-5 text-gray font-14 text-center">{{ trans('update.assignment_submission_error_desc') }}</p>
                            @endif
                        </div>
                    </div>
                @else
                    <div class=" js-font-resize bg-secondary-acadima p-10 rounded-sm border">
                        <h4 class=" js-font-resize font-16 font-weight-bold text-pink">
                            @if($user->id == $assignment->creator_id)
                                {{ trans('update.reply_to_the_conversation') }}
                            @else
                                {{ trans('update.send_assignment') }}
                            @endif
                        </h4>

                        <form method="post" action="/course/assignment/{{ $assignment->id }}/history/{{ $assignmentHistory->id }}/message">
                            {{ csrf_field() }}
                            <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">
                            <input type="hidden" name="assignment_history_id" value="{{ $assignmentHistory->id }}">
                            @if($user->id == $assignment->creator_id)
                                <input type="hidden" name="student_id" value="{{ $assignmentHistory->student_id }}">
                            @endif

                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('public.description') }}</label>
                                <textarea rows="6" name="description" class=" js-font-resize form-control"></textarea>
                                <div class=" js-font-resize invalid-feedback"></div>
                            </div>

                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('update.file_title') }} </label>
                                <input name="file_title" class=" js-font-resize form-control"/>
                                <div class=" js-font-resize invalid-feedback"></div>
                            </div>

                            <div class=" js-font-resize form-group">
                                <label class=" js-font-resize input-label">{{ trans('update.attach_a_file') }}</label>

                                <div class=" js-font-resize d-flex align-items-center">
                                    <div class=" js-font-resize input-group mr-10">
                                        <div class=" js-font-resize input-group-prepend">
                                            <button type="button" class=" js-font-resize input-group-text panel-file-manager" data-input="assignmentAttachmentInput" data-preview="holder">
                                                <i data-feather="upload" width="18" height="18" class=" js-font-resize text-light"></i>
                                            </button>
                                        </div>
                                        <input type="text" name="file_path" id="assignmentAttachmentInput" value="" class=" js-font-resize form-control" placeholder="{{ trans('update.assignment_attachments_placeholder') }}"/>
                                        <div class=" js-font-resize invalid-feedback"></div>
                                    </div>

                                    <button type="button" class=" js-font-resize js-save-history-message btn btn-acadima-primary btn-sm">{{ trans('update.send') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if($user->id == $assignment->creator_id)
                        <div class=" js-font-resize bg-secondary-acadima p-10 rounded-sm border mt-15">
                            <h4 class=" js-font-resize font-16 font-weight-bold text-dark">{{ trans('update.rate_the_assignment') }}</h4>

                            <form method="post" action="/course/assignment/{{ $assignment->id }}/history/{{ $assignmentHistory->id }}/setGrade">
                                <input type="hidden" name="student_id" value="{{ $assignmentHistory->student_id }}">

                                <div class=" js-font-resize form-group">
                                    <label class=" js-font-resize input-label">{{ trans('update.assignments_grade') }}</label>
                                    <div class=" js-font-resize d-flex align-items-start">
                                        <div class=" js-font-resize mr-10 w-100">
                                            <input name="grade" class=" js-font-resize form-control" placeholder="{{ trans('update.pass_grade') }}: {{ $assignment->pass_grade }}"/>
                                            <div class=" js-font-resize invalid-feedback"></div>
                                        </div>

                                        <button type="button" class=" js-font-resize js-save-history-rate btn btn-primary btn-sm">{{ trans('public.submit') }}</button>
                                    </div>
                                </div>
                                <p class=" js-font-resize font-12 text-gray">{{ trans('update.by_submitting_the_grade_you_the_assignment_will_be_closed') }}</p>
                            </form>
                        </div>
                    @endif
                @endif
            </div>

            <div class=" js-font-resize col-12 col-lg-8 mt-15 mt-lg-0">

                <div class=" js-font-resize h-100">

                    @if(!empty($assignmentHistory->messages) and count($assignmentHistory->messages))
                        @foreach($assignmentHistory->messages as $message)
                            <div class=" js-font-resize assignment-attachments-post p-15 border rounded-sm mb-10">
                                <div class=" js-font-resize d-flex align-items-center">
                                    <div class=" js-font-resize user-avatar rounded-circle">
                                        <img src="{{ $message->sender->getAvatar(50) }}" class=" js-font-resize img-cover rounded-circle" alt="{{ $message->sender->full_name }}">
                                    </div>
                                    <div class=" js-font-resize ml-10">
                                        <h4 class=" js-font-resize font-14 font-weight-500 text-dark">{{ $message->sender->full_name }}</h4>
                                        <span class=" js-font-resize d-block font-12 text-gray">{{ dateTimeFormat($message->created_at, 'j M Y | H:i') }}</span>
                                    </div>
                                    <div class=" js-font-resize ml-auto">
                                        <!-- Check if the user is an admin -->
                                        @if( auth()->user()->isTeacher())
                                            <form action="{{  '/assignment/histories/' . $assignmentHistory->id }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this assignment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class=" js-font-resize btn btn-danger btn-sm">Delete Assignment</button>
                                            </form>
                                            @endif
                                    </div>
                                </div>
                                <div class=" js-font-resize mt-15 font-14 text-gray">
                                    {!! $message->message !!}
                                </div>

                                @if(!empty($message->file_path))
                                    <div class=" js-font-resize d-flex flex-wrap align-items-center mt-10">
                                        <a href="{{ $message->getDownloadUrl($assignment->id) }}" target="_blank" class=" js-font-resize d-flex align-items-center text-gray bg-info-light border px-10 py-5 rounded-pill mr-10 mt-5">
                                            <i data-feather="paperclip" class=" js-font-resize text-gray" width="16" height="16"></i>
                                            <span class=" js-font-resize ml-5 font-12 text-gray">{{ !empty($message->file_title) ? $message->file_title : trans('update.attachment') }}</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class=" js-font-resize d-flex align-items-center justify-content-center flex-column h-100">
                            <div class=" js-font-resize learning-page-assignment-history-status-icon d-flex align-items-center justify-content-center">
                                <img src="/assets/default/img/learning/no_assignment.svg" class=" js-font-resize img-fluid" alt="">
                            </div>

                            <div class=" js-font-resize d-flex align-items-center flex-column mt-10 text-center">
                                <h3 class=" js-font-resize font-20 font-weight-bold text-pink text-center">{{ trans('update.no_assignment') }}</h3>
                                <p class=" js-font-resize mt-5 text-gray font-14 text-center">{{ trans('update.submit_your_assignment_and_evaluate_your_learning') }}</p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </section>
</section>
