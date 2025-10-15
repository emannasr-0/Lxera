<div>
    <div id="loading-message" class="text-warning font-bold mr-4 " style="display: none;font-size:20px;">
        <div class="spinner-grow text-warning" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        {{ trans('admin/main.loading_data') }}
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped font-14 ">
                <tr>
                    <th>{{ '#' }}</th>
                    <th class="text-left">{{ trans('admin/main.student_code') }}</th>
                    <th class="text-left">{{ trans('admin/main.student_name') }}</th>
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
                    <tr class="text-center">
                        <td>{{ ++$index }}</td>
                        <td class="text-left">
                            {{ $requirement->bundleStudent->student->registeredUser->user_code ?? '' }}
                        </td>
                        <td class="text-left">
                            <div class="d-flex align-items-center">
                                <div class="media-body ml-1">
                                    <div class="mt-0 mb-1 font-weight-bold">
                                        {{ $requirement->bundleStudent->student
                                            ? $requirement->bundleStudent->student->ar_name
                                            : $requirement->bundleStudent->student->registeredUser->full_name }}
                                    </div>

                                    @if ($requirement->bundleStudent->student->registeredUser->mobile ?? '')
                                        <div class="text-primary text-small font-600-bold">
                                            {{ $requirement->bundleStudent->student->registeredUser->mobile }}
                                        </div>
                                    @endif

                                    @if ($requirement->bundleStudent->student->registeredUser->email ?? '')
                                        <div class="text-primary text-small font-600-bold">
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
                                        {{ trans('admin/main.pdf_file') }} <i class="fas fa-file font-20"></i>
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
                                        {{ trans('admin/main.pdf_file') }} <i class="fas fa-file font-20"></i>
                                    @endif
                                </a>
                            @else
                                {{ trans('admin/main.not_available') }}
                            @endif
                        </td>
                        <td>
                            @if ($requirement->status == 'pending')
                                <span class="text-success"> {{ trans('admin/main.not_available') }}</span>
                            @elseif($requirement->status == 'approved')
                                <span class="text-primary"> {{ trans('admin/main.status_approved') }}</span>
                            @elseif($requirement->status == 'rejected')
                                <div class="text-danger">
                                    <span class=""> {{ trans('admin/main.status_rejected') }}</span>
                                    @include('admin.includes.message_button', [
                                        'url' => '#',
                                        'btnClass' => 'd-flex align-items-center mt-1',
                                        'btnText' =>
                                            '<span class="ml-2">' . __('admin/main.rejection_reason') . '</span>',
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
                        <td class="font-12">
                            {{ Carbon\Carbon::parse($requirement->created_at)->translatedFormat(handleDateAndTimeFormat('Y M j | H:i')) }}
                        </td>

                        <td width="200" class="">
                            <div class="d-flex justify-content-center align-items-baseline gap-3">
                                @can('admin_requirements_approve')
                                    <button class="btn btn-primary d-flex align-items-center btn-sm mt-1 ml-3"
                                        data-toggle="modal" data-target="#approve_modal"
                                        wire:click="approve({{ $requirement->id }})">
                                        <i class="fa fa-check"></i><span class="ml-2"> {{ trans('admin/main.accept') }}
                                    </button>
                                @endcan

                                @can('admin_requirements_reject')
                                    <button class="btn btn-danger d-flex align-items-center btn-sm mt-1" data-toggle="modal"
                                        data-target="#reject_modal" wire:click="reject({{ $requirement->id }})">
                                        <i class="fa fa-times"></i><span class="ml-2">
                                            {{ trans('admin/main.reject') }}
                                        </span>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </table>
            <div class="card-footer text-center">
                {{ $requirements->links() }}
            </div>
        </div>
    </div>

    <!--Approve modal -->
    <div wire:ignore.self class="modal fade" id="approve_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ trans('admin/main.confirm_acceptance') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div wire:loading class="text-warning text-bold m-3 p-3">
                        {{ trans('admin/main.updating_data') }}
                    </div>
                    <b>{{ $stu_name }}</b>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('requirment.approve', $requirement_id) }}" type="button"
                        class="btn btn-primary">
                        <span class="ml-2"> <i class="fa fa-check"></i> {{ trans('admin/main.accept') }}</span>
                    </a>
                    <button type="button" class="btn btn-danger mr-3" data-dismiss="modal">{{ trans('admin/main.close') }}</button>
                </div>
            </div>
        </div>
    </div>


    <!--END of Approve modal -->


    <!-- Rejection Modal -->
    <div wire:ignore.self class="modal fade" id="reject_modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">{{ trans('admin/main.confirm_rejection') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="modal-body" method="GET" action="{{ route('requirment.reject', $requirement_id) }}"
                    id="deleteForm" onsubmit="submitForm(event)">
                    <label for="message" class="form-label">{{ trans('admin/main.state_rejection_reason') }}</label>
                    <select name="reason" id="reason" class="form-control mb-3" required>

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
                    <textarea class="form-control" id="message" name="message" placeholder="{{ trans('admin/main.detailed_rejection_reason') }}"></textarea>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary ml-3"
                            data-dismiss="modal">{{ trans('admin/main.cancel') }}</button>
                        <button type="submit" class="btn btn-danger"
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
