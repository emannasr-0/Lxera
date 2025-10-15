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
                    <th>{{ '#' }}</th>
                    <th>{{ trans('admin/main.student_code') }}</th>

                    <th>{{ trans('admin/main.name') }}</th>

                    <th>الهوية الوطنية</th>
                    {{-- <th>{{ trans('admin/main.classes') }}</th>
                    <th>{{ trans('admin/main.appointments') }}</th>
                    <th>{{ trans('admin/main.wallet_charge') }}</th>
                    <th>{{ trans('admin/main.income') }}</th>
                    <th>{{ trans('admin/main.user_group') }}</th> --}}
                    <th> الدبلومات المسجلة</th>
                    {{-- <th>حاله الدفع</th> --}}
                    {{-- <th>{{ trans('admin/main.student_code') }}</th> --}}
                    <th>{{ trans('admin/main.register_date') }}</th>
                    <th>{{ trans('admin/main.status') }}</th>
                    <th width="120">{{ trans('admin/main.actions') }}</th>
                </tr>

                @foreach ($sales as $index => $sale)
                    <tr>
                        <td>{{ ++$index }}</td>
                        <td>{{ $sale->buyer?->user_code }}</td>

                        <td class="text-left">
                            <div class="d-flex align-items-center">
                                <div class="media-body ml-1">
                                    <div class="mt-0 mb-1 font-weight-bold">
                                        {{ $sale->buyer?->student ? $sale->buyer?->student->ar_name : $sale->buyer?->full_name }}
                                    </div>

                                    @if ($sale->buyer?->mobile)
                                        <div class="text-primary text-left font-600-bold" style="font-size:12px;">
                                            {{ $sale->buyer?->mobile }}</div>
                                    @endif

                                    @if ($sale->buyer?->email)
                                        <div class="text-primary text-small font-600-bold">{{ $sale->buyer?->email }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="text-left">
                            @if (!empty($sale->buyer?->student?->identity_img))
                                <a href="/store/{{ $sale->buyer?->student?->identity_img }}" target="_blank">
                                    <img src="/store/{{ $sale->buyer?->student?->identity_img }}" alt="image"
                                        width="100px" style="max-height:100px">
                                </a>
                            @else
                                <span class="text-warning"> {{ trans('admin/main.not_available') }} </span>
                            @endif
                        </td>


                        <td>
                            {{ $sale->bundle?->title }}
                        </td>

                        <td>
                            {{ dateTimeFormat($sale->created_at, 'j M Y | H:i') }}
                        </td>

                        <td>
                            @if ($sale->buyer?->ban and !empty($sale->buyer?->ban_end_at) and $sale->buyer?->ban_end_at > time())
                                <div class="mt-0 mb-1 font-weight-bold text-danger">{{ trans('admin/main.ban') }}
                                </div>
                                <div class="text-small font-600-bold">Until
                                    {{ dateTimeFormat($sale->buyer?->ban_end_at, 'Y/m/j') }}</div>
                            @else
                                <div
                                    class="mt-0 mb-1 font-weight-bold {{ $sale->buyer?->status == 'active' ? 'text-success' : 'text-warning' }}">
                                    {{ trans('admin/main.' . $sale->buyer?->status) }}</div>
                            @endif
                        </td>

                        <td class="text-center mb-2" width="120">

                            @can('admin_users_impersonate')
                                <a href="{{ getAdminPanelUrl() }}/users/{{ $sale->buyer?->id }}/impersonate"
                                    target="_blank" class="btn-transparent  text-primary" data-toggle="tooltip"
                                    data-placement="top" title="{{ trans('admin/main.login') }}">
                                    <i class="fa fa-user-shield"></i>
                                </a>
                            @endcan

                            @can('admin_users_edit')
                                <a href="{{ getAdminPanelUrl() }}/users/{{ $sale->buyer?->id }}/edit"
                                    class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top"
                                    title="{{ trans('admin/main.edit') }}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            @endcan

                            @can('admin_users_delete')
                                <button class="btn-transparent text-primary " data-toggle="modal"
                                    data-target="#delete_modal" wire:click="delete({{$sale->buyer->id}})">
                                    <i class="fa fa-times"></i>
                                </button>
                                {{-- @include('admin.includes.delete_button', [
                                    'url' => getAdminPanelUrl() . '/users/' . $sale->buyer?->id . '/delete',
                                    'btnClass' => '',
                                    'deleteConfirmMsg' => trans('update.user_delete_confirm_msg'),
                                ]) --}}
                            @endcan
                        </td>

                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    <div class="card-footer text-center">
        {{ $sales->appends(request()->input())->links() }}
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
                    <a href="{{route('users.delete',$stu_id)}}" type="button" class="btn btn-primary">
                        <span class="ml-2"> <i class="fa fa-check"></i> تأكيد</span>
                    </a>
                    <button type="button" class="btn btn-danger mr-3" data-dismiss="modal">اغلاق</button>
                </div>
            </div>
        </div>
    </div>


    <!--END of Delete modal -->
</div>
