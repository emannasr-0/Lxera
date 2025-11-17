<div>
    <div id="loading-message" class=" js-font-resize text-warning font-bold mr-4 " style="display: none;font-size:20px;">
        <div class=" js-font-resize spinner-grow text-warning" role="status">
            <span class=" js-font-resize sr-only">Loading...</span>
        </div>
        {{ trans('admin/main.loading_data') }}
    </div>
    <div class=" js-font-resize card-body">
        <div class=" js-font-resize table-responsive">
            <table class=" js-font-resize table table-striped font-14 ">
                <tr>
                    <th>{{ '#' }}</th>
                    <th class=" js-font-resize text-left">{{ trans('admin/main.student_code') }}</th>
                    <th class=" js-font-resize text-left">{{ trans('admin/main.student_name') }}</th>
                    <th>{{ trans('admin/main.registered_program') }}</th>
                    <th>{{ trans('admin/main.specialization') }}</th>
                    <th>{{ trans('admin/main.id_attachment') }}</th>
                    <th>{{ trans('admin/main.admission_requirements_attachment') }}</th>
                    <th>{{ trans('admin/main.request_status') }}</th>
                    <th>{{ trans('admin/main.admin') }}</th>
                    <th>{{ trans('admin/main.request_submission_date') }}</th>
                    <th width="120">{{ trans('admin/main.actions') }}</th>
                </tr>
                @foreach ($requirements as $index => $requirement)
                    <tr class=" js-font-resize text-center">
                        <td>{{ ++$index }}</td>
                        <td class=" js-font-resize text-left">
                            {{ $requirement->bundleStudent->student->registeredUser->user_code ?? '' }}
                        </td>
                        <td class=" js-font-resize text-left">
                            <div class=" js-font-resize d-flex align-items-center">
                                <div class=" js-font-resize media-body ml-1">
                                    <div class=" js-font-resize mt-0 mb-1 font-weight-bold">
                                        {{ $requirement->bundleStudent->student
                                            ? $requirement->bundleStudent->student->ar_name
                                            : $requirement->bundleStudent->student->registeredUser->full_name }}
                                    </div>

                                    @if ($requirement->bundleStudent->student->registeredUser->mobile ?? '')
                                        <div class=" js-font-resize text-primary text-small font-600-bold">
                                            {{ $requirement->bundleStudent->student->registeredUser->mobile }}
                                        </div>
                                    @endif

                                    @if ($requirement->bundleStudent->student->registeredUser->email ?? '')
                                        <div class=" js-font-resize text-primary text-small font-600-bold">
                                            {{ $requirement->bundleStudent->student->registeredUser->email }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </td>
                        <td>{{ $requirement->bundleStudent->bundle->category->slug ?? '' }}</td>
                        <td>{{ $requirement->bundleStudent->bundle->title ?? ''}}</td>
                        <td>
                            @if (!empty($requirement->identity_attachment))
                                <a href="/store/{{ $requirement->identity_attachment }}" target="_blank">
                                    @if (pathinfo($requirement->identity_attachment, PATHINFO_EXTENSION) != 'pdf')
                                        <img src="/store/{{ $requirement->identity_attachment }}"
                                            alt="identity_attachment" width="100px" style="max-height:100px">
                                    @else
                                        {{ trans('admin/main.pdf_file') }} <i class=" js-font-resize fas fa-file font-20"></i>
                                    @endif
                                </a>
                            @else
                                {{ trans('admin/main.not_available') }}
                            @endif
                        </td>
                        <td>
                            @if (!empty($requirement->admission_attachment))
                                <a href="/store/{{ $requirement->admission_attachment }}" target="_blank">
                                    @if (pathinfo($requirement->admission_attachment, PATHINFO_EXTENSION) != 'pdf')
                                        <img src="/store/{{ $requirement->admission_attachment }}"
                                            alt="admission_attachment" width="100px" style="max-height:100px">
                                    @else
                                        {{ trans('admin/main.pdf_file') }} <i class=" js-font-resize fas fa-file font-20"></i>
                                    @endif
                                </a>
                            @else
                                {{ trans('admin/main.not_available') }}
                            @endif
                        </td>
                        <td>
                            @if ($requirement->status == 'pending')
                                <span class=" js-font-resize text-success"> {{ trans('admin/main.not_available') }}</span>
                            @elseif($requirement->status == 'approved')
                                <span class=" js-font-resize text-primary"> {{ trans('admin/main.status_approved') }}</span>
                            @elseif($requirement->status == 'rejected')
                                <div class=" js-font-resize text-danger">
                                    <span class=" js-font-resize "> {{ trans('admin/main.status_rejected') }}</span>
                                    @include('admin.includes.message_button', [
                                        'url' => '#',
                                        'btnClass' => 'd-flex align-items-center mt-1',
                                        'btnText' =>
                                            '<span class=" js-font-resize ml-2">' . __('admin/main.rejection_reason') . '</span>',
                                        'hideDefaultClass' => true,
                                        'deleteConfirmMsg' => __('admin/main.rejection_reason_example'),
                                        'message' => $requirement->message,
                                        'id' => $requirement->id,
                                    ])
                                </div>
                            @endif
                        </td>
                        <td>{{ $requirement->admin ? $requirement->admin->full_name : '' }}
                        </td>
                        <td class=" js-font-resize font-12">
                            {{ Carbon\Carbon::parse($requirement->created_at)->translatedFormat(handleDateAndTimeFormat('Y M j | H:i')) }}
                        </td>

                        <td width="200" class=" js-font-resize ">
                            <div class=" js-font-resize d-flex justify-content-center align-items-baseline gap-3">
                                @can('admin_requirements_approve')
                                    <button class=" js-font-resize btn btn-primary d-flex align-items-center btn-sm mt-1 ml-3"
                                        data-toggle="modal" data-target="#approve_modal"
                                        wire:click="approve({{ $requirement->id }})">
                                        <i class=" js-font-resize fa fa-check"></i><span class=" js-font-resize ml-2"> {{ trans('admin/main.accept') }}
                                    </button>
                                @endcan

                                @can('admin_requirements_reject')
                                    <button class=" js-font-resize btn btn-danger d-flex align-items-center btn-sm mt-1" data-toggle="modal"
                                        data-target="#reject_modal" wire:click="reject({{ $requirement->id }})">
                                        <i class=" js-font-resize fa fa-times"></i><span class=" js-font-resize ml-2">
                                            {{ trans('admin/main.reject') }}
                                        </span>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </table>
            <div class=" js-font-resize card-footer text-center">
                {{ $requirements->links() }}
            </div>
        </div>
    </div>

    <!--Approve modal -->
    <div wire:ignore.self class=" js-font-resize modal fade" id="approve_modal" tabindex="-1" role="dialog">
        <div class=" js-font-resize modal-dialog" role="document">
            <div class=" js-font-resize modal-content">
                <div class=" js-font-resize modal-header">
                    <h5 class=" js-font-resize modal-title" id="exampleModalLabel">{{ trans('admin/main.confirm_acceptance') }}</h5>
                    <button type="button" class=" js-font-resize close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class=" js-font-resize modal-body">
                    <div wire:loading class=" js-font-resize text-warning text-bold m-3 p-3">
                        {{ trans('admin/main.updating_data') }}
                    </div>
                    <b>{{ $stu_name }}</b>
                </div>
                <div class=" js-font-resize modal-footer">
                    <a href="{{ route('requirment.approve', $requirement_id) }}" type="button"
                        class=" js-font-resize btn btn-primary">
                        <span class=" js-font-resize ml-2"> <i class=" js-font-resize fa fa-check"></i> {{ trans('admin/main.accept') }}</span>
                    </a>
                    <button type="button" class=" js-font-resize btn btn-danger mr-3" data-dismiss="modal">{{ trans('admin/main.close') }}</button>
                </div>
            </div>
        </div>
    </div>


    <!--END of Approve modal -->


    <!-- Rejection Modal -->
    <div wire:ignore.self class=" js-font-resize modal fade" id="reject_modal" tabindex="-1">
        <div class=" js-font-resize modal-dialog">
            <div class=" js-font-resize modal-content">
                <div class=" js-font-resize modal-header">
                    <h5 class=" js-font-resize modal-title" id="confirmModalLabel">{{ trans('admin/main.confirm_rejection') }}</h5>
                    <button type="button" class=" js-font-resize close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class=" js-font-resize modal-body" method="GET" action="{{ route('requirment.reject', $requirement_id) }}"
                    id="deleteForm" onsubmit="submitForm(event)">
                    <label for="message" class=" js-font-resize form-label">{{ trans('admin/main.state_rejection_reason') }}</label>
                    <select name="reason" id="reason" class=" js-font-resize form-control mb-3" required>

                        <option value="" selected disabled>{{ trans('admin/main.select_rejection_reason') }}</option>

                        <option value="يوجد مشكلة في مرفق  بطاقة الهوية الوطنية أو جواز السفر">
                            {{ trans('admin/main.issue_id_passport') }}
                        </option>
                        <option value="يوجد مشكلة في مرفق  شهادة البكالوريوس">
                            {{ trans('admin/main.issue_bachelor_certificate') }}
                        </option>
                        <option value="يوجد مشكلة في مرفق  شهادة الثانوية">
                            {{ trans('admin/main.issue_highschool_certificate') }}
                        </option>
                        <option value="يوجد مشكلة في مرفق السجل الأكاديمي">
                            {{ trans('admin/main.issue_academic_record') }}
                        </option>
                        <option value="يوجد مشكلة في مرفق السيرة الذاتية ">
                            {{ trans('admin/main.issue_cv') }}
                        </option>
                        <option value="يوجد مشكلة في مرفق الغرض من الدراسة">
                            {{ trans('admin/main.issue_study_purpose') }}
                        </option>
                        <option value="يوجد مشكلة في مرفق الخبرة العملية التخصص المقدم اليه">
                            {{ trans('admin/main.issue_experience') }}
                        </option>
                        <option value="يوجد مشكلة في مرفق التوصية العلمية والمهنية">
                            {{ trans('admin/main.issue_recommendation') }}
                        </option>
                        <option value="يوجد مشكلة في مرفق  الخلفية المهنية">
                            {{ trans('admin/main.issue_professional_background') }}
                        </option>

                    </select>
                    <textarea class=" js-font-resize form-control" id="message" name="message" placeholder="{{ trans('admin/main.detailed_rejection_reason') }}"></textarea>
                    <div class=" js-font-resize modal-footer">
                        <button type="button" class=" js-font-resize btn btn-secondary ml-3"
                            data-dismiss="modal">{{ trans('admin/main.cancel') }}</button>
                        <button type="submit" class=" js-font-resize btn btn-danger"
                            id="confirmAction">{{ trans('admin/main.send') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--End of Rejection Modal -->
    <script>
        document.getElementById('loading-message').style.display = 'block';
        window.addEventListener('load', function() {
            document.getElementById('loading-message').style.display = 'none';
        });
    </script>
</div>
