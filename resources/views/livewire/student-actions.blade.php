<div>
    <div id="loading-message" class="text-warning font-bold mr-4 " style="display: none;font-size:20px;">
        <div class="spinner-grow text-warning" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        جارى تحميل البيانات
    </div>
    <div class="card-body">
        <div class="table-responsive text-center">
            <table class="table table-striped font-14">
                <tr>
                    <th>#</th>
                    <th> {{ trans('admin/main.student_code') }}  </th>
                    <th> {{ trans('admin/main.name') }}          </th>
                    <th> {{ trans('admin/main.national_id') }}  </th>
                    <th> {{ trans('admin/main.registered_diplomas') }}  </th>
                    <th> {{ trans('admin/main.register_date') }} </th>
                    <th> {{ trans('admin/main.status') }}        </th>
                    <th width="120"> {{ trans('admin/main.actions') }} </th>
                </tr>

                @foreach ($users as $user)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $user->user_code ?? '----' }}</td>

                        <td class="text-left">
                            <div class="d-flex align-items-center">
                                <div class="media-body ml-1">
                                    <div class="mt-0 mb-1 font-weight-bold">
                                        {{-- @dump( $user->full_name) --}}
                                        {{ $user->en_name ? $user->en_name : $user->full_name }}
                                    </div>

                                    @if ($user->mobile)
                                        <div class="text-primary text-left font-600-bold" style="font-size:12px;">
                                            {{ $user->mobile }}</div>
                                    @endif

                                    @if ($user->email)
                                        <div class="text-primary text-small font-600-bold">
                                            {{ $user->email }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="text-left">
                            @if (!empty($user->student->identity_img))
                                <a href="/store/{{ $user->student->identity_img }}" target="_blank">
                                    <img src="/store/{{ $user->student->identity_img }}" alt="image"
                                        width="100px" style="max-height:100px">
                                </a>
                            @else
                                <span class="text-warning">  {{ trans('admin/main.not_available') }} </span>
                            @endif
                        </td>

                        <td>
                            @php
                                $purchases = $user->purchases;
                            @endphp

                            @if (!empty($purchases) and count($purchases) > 0)
                                @php
                                    $diploma = '';
                                    foreach ($purchases as $purchase) {
                                        $program = $purchase?->bundle?->title ?? $purchase?->webinar?->title;
                                        if ($program) {
                                            $diploma .= $program . ' , ';
                                        }
                                    }
                                    $diploma = preg_replace('/,(?!.*,)/u', '', $diploma);
                                @endphp
                                {{ !empty($diploma) ? $diploma : 'غير مسجل بعد' }}
                            @else
                                {{ $user?->appliedProgram?->title ?? 'غير مسجل بعد' }}
                            @endif
                        </td>




                        <td>{{ dateTimeFormat($user->created_at, 'j M Y | H:i') }}</td>

                        <td>
                            @if ($user->ban and !empty($user->ban_end_at) and $user->ban_end_at > time())
                                <div class="mt-0 mb-1 font-weight-bold text-danger">{{ trans('admin/main.ban') }}
                                </div>
                                <div class="text-small font-600-bold">Until
                                    {{ dateTimeFormat($user->ban_end_at, 'Y/m/j') }}</div>
                            @else
                                <div
                                    class="mt-0 mb-1 font-weight-bold {{ $user->status == 'active' ? 'text-success' : 'text-warning' }}">
                                    {{ trans('admin/main.' . $user->status) }}</div>
                            @endif
                        </td>

                        <td class="text-center mb-2" width="120">

                            @can('admin_users_impersonate')
                                <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/impersonate" target="_blank"
                                    class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top"
                                    title="{{ trans('admin/main.login') }}">
                                    <i class="fa fa-user-shield"></i>
                                </a>
                            @endcan

                            @can('admin_users_edit')
                                <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/edit"
                                    class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top"
                                    title="{{ trans('admin/main.edit') }}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            @endcan

                            @can('admin_users_delete')
                                <button class="btn-transparent text-primary " data-toggle="modal"
                                data-target="#delete_modal" wire:click="delete({{ $user->id }})">
                                    <i class="fa fa-times"></i>
                                </button>
                            @endcan
                        </td>

                    </tr>
                @endforeach
            </table>
            <div class="card-footer text-center">
                {{ $users->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
        <!--Delete modal -->
        <div wire:ignore.self class="modal fade" id="delete_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">هل انت متاكد من حذف هذا الطالب ؟</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div wire:loading class="text-warning text-bold m-3 p-3">
                            يتم تحديث البيانات ....
                        </div>
                        <b>{{ $stu_name }}</b>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('users.delete', $stu_id) }}" type="button"
                            class="btn btn-primary">
                            <span class="ml-2"> <i class="fa fa-check"></i> تأكيد</span>
                        </a>
                        <button type="button" class="btn btn-danger mr-3" data-dismiss="modal">اغلاق</button>
                    </div>
                </div>
            </div>
        </div>
    
    
        <!--END of Delete modal -->
</div>
