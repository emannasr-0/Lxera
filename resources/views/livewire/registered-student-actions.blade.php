<div>
    @php
        $segments = explode('/', request()->path());
        $lastSegment = end($segments);
    @endphp

    <div id="loading-message" class=" js-font-resize text-warning font-bold mr-4 " style="display: none;font-size:20px;">
        <div class=" js-font-resize spinner-grow text-warning" role="status">
            <span class=" js-font-resize sr-only">Loading...</span>
        </div>
        جارى تحميل البيانات
    </div>
    <div class=" js-font-resize card-body">
        <div class=" js-font-resize table-responsive text-center">
            <table class=" js-font-resize table table-striped font-14">
                <tr>
                    <th>{{ '#' }}</th>

                    @if ($lastSegment === 'users')
                        <th> {{ trans('admin/main.student_code') }}  </th>
                    @endif

                    <th> {{ trans('admin/main.name') }}          </th>
                    {{-- <th>{{ trans('admin/main.classes') }}</th>
                    <th>{{ trans('admin/main.appointments') }}</th>
                    <th>{{ trans('admin/main.wallet_charge') }}</th>
                    <th>{{ trans('admin/main.income') }}</th>
                    <th>{{ trans('admin/main.user_group') }}</th> --}}
                    @if ($lastSegment === 'users')
                        <th> {{ trans('admin/main.national_id') }}  </th>
                    @endif
                    <th> {{ trans('admin/main.registered_diplomas') }}  </th>

                    {{-- <th>حاله الدفع</th> --}}
                    {{-- <th>{{ trans('admin/main.student_code') }}</th> --}}
                    <th>{{ trans('admin/main.register_date') }}</th>
                    <th>{{ trans('admin/main.status') }}</th>
                    <th width="120">{{ trans('admin/main.actions') }}</th>
                </tr>

                @foreach ($users as $index => $user)
                    <tr>
                        <td>{{ ++$index }}</td>
                        @if ($lastSegment === 'users')
                            <td>{{ $user->user_code ?? '---' }}</td>
                        @endif

                        <td class=" js-font-resize text-left">
                            <div class=" js-font-resize d-flex align-items-center">
                                <div class=" js-font-resize media-body ml-1">
                                    <div class=" js-font-resize mt-0 mb-1 font-weight-bold">
                                        {{ $user->student ? $user->student->ar_name : $user->full_name }}</div>

                                    @if ($user->mobile || $user->student)
                                        <div class=" js-font-resize text-primary text-left font-600-bold" style="font-size:12px;">
                                            {{ $user->mobile ?? $user->student->phone }}</div>
                                    @endif

                                    @if ($user->email)
                                        <div class=" js-font-resize text-primary text-small font-600-bold">{{ $user->email }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>


                        <td>{{ $user->appliedProgram->title ?? '---' }}</td>
                        <td>{{ dateTimeFormat($user->created_at, 'j M Y | H:i') }}</td>


                        <td>
                            @if ($user->ban and !empty($user->ban_end_at) and $user->ban_end_at > time())
                                <div class=" js-font-resize mt-0 mb-1 font-weight-bold text-danger">{{ trans('admin/main.ban') }}
                                </div>
                                <div class=" js-font-resize text-small font-600-bold">Until
                                    {{ dateTimeFormat($user->ban_end_at, 'Y/m/j') }}</div>
                            @else
                                <div
                                    class=" js-font-resize mt-0 mb-1 font-weight-bold {{ $user->status == 'active' ? 'text-success' : 'text-warning' }}">
                                    {{ trans('admin/main.' . $user->status) }}</div>
                            @endif
                        </td>

                        <td class=" js-font-resize text-center mb-2" width="120">
                            @can('admin_users_transform')
                                @if (!empty($user->student))
                                    @include('admin.includes.confirm_transform_button', [
                                        'url' => getAdminPanelUrl() . '/users/' . $user->id . '/transform',
                                        'btnClass' => 'btn-transparent  text-primary',
                                        'btnText' => '<i class=" js-font-resize fa fa-retweet"></i>',
                                        'hideDefaultClass' => true,
                                        'id' => $user->id,
                                    ])
                                @endif
                            @endcan

                            @can('admin_users_impersonate')
                                <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/impersonate" target="_blank"
                                    class=" js-font-resize btn-transparent  text-primary" data-toggle="tooltip" data-placement="top"
                                    title="{{ trans('admin/main.login') }}">
                                    <i class=" js-font-resize fa fa-user-shield"></i>
                                </a>
                            @endcan

                            @can('admin_users_edit')
                                <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/edit"
                                    class=" js-font-resize btn-transparent  text-primary" data-toggle="tooltip" data-placement="top"
                                    title="{{ trans('admin/main.edit') }}">
                                    <i class=" js-font-resize fa fa-edit"></i>
                                </a>
                            @endcan

                            @can('admin_users_delete')
                                <button class=" js-font-resize btn-transparent text-primary " data-toggle="modal"
                                    data-target="#delete_modal" wire:click="delete({{ $user->id }})">
                                    <i class=" js-font-resize fa fa-times"></i>
                                </button>
                            @endcan
                        </td>

                    </tr>
                @endforeach
            </table>

        </div>
        <div class=" js-font-resize card-footer text-center">
            {{ $users->appends(request()->input())->links() }}
        </div>
    </div>

    <!--Delete modal -->
    <div wire:ignore.self class=" js-font-resize modal fade" id="delete_modal" tabindex="-1" role="dialog">
        <div class=" js-font-resize modal-dialog" role="document">
            <div class=" js-font-resize modal-content">
                <div class=" js-font-resize modal-header">
                    <h5 class=" js-font-resize modal-title" id="exampleModalLabel">هل انت متاكد من حذف هذا الطالب ؟</h5>
                    <button type="button" class=" js-font-resize close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class=" js-font-resize modal-body">
                    <div wire:loading class=" js-font-resize text-warning text-bold m-3 p-3">
                        يتم تحديث البيانات ....
                    </div>
                    <b>{{ $stu_name }}</b>
                </div>
                <div class=" js-font-resize modal-footer">
                    <a href="{{ route('users.delete', $stu_id) }}" type="button" class=" js-font-resize btn btn-primary">
                        <span class=" js-font-resize ml-2"> <i class=" js-font-resize fa fa-check"></i> تأكيد</span>
                    </a>
                    <button type="button" class=" js-font-resize btn btn-danger mr-3" data-dismiss="modal">اغلاق</button>
                </div>
            </div>
        </div>
    </div>


    <!--END of Delete modal -->
</div>
