<button
    class="@if (empty($hideDefaultClass) or !$hideDefaultClass) {{ !empty($noBtnTransparent) ? '' : 'btn-transparent' }} text-primary @endif {{ $btnClass ?? '' }}"
    data-toggle="modal" data-target={{ '#editBatchModal' . $class->id ?? 0 }} data-confirm-href="{{ $url }}"
    data-confirm-text-yes="{{ trans('admin/main.yes') }}" data-confirm-text-cancel="{{ trans('admin/main.cancel') }}"
    data-confirm-has-message="true">
    @if (!empty($btnText))
        {!! $btnText !!}
    @else
        <i class="fa {{ !empty($btnIcon) ? $btnIcon : 'fa-times' }}" aria-hidden="true"></i>
    @endif
</button>

@push('models')
    <!-- Modal -->
    <div class="modal fade" id={{ 'editBatchModal' . $class->id ?? 0 }} tabindex="-1" aria-labelledby="editBatchModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    @if (isset($class->id))
                        <h5 class="modal-title" id="confirmModalLabel">تعديل دفعة </h5>
                    @else
                        <h5 class="modal-title" id="confirmModalLabel">إنشاء دفعة جديدة</h5>
                    @endif

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="{{ $url }}" method="post" class="modal-body">
                    @csrf
                    @if (isset($class))
                        @method('put')
                    @endif
                    <div class="">
                        <div class="form-group">
                            <label for="title">عنوان الدفعة الدراسية</label>
                            <input type="text" name="title" id="title" class="form-control"
                                value="{{ $class?->title }}">

                        </div>

                        <div class="form-group mt-15 js-start_date">
                            <div class="form-group">
                                <label class="input-label">{{ trans('public.start_date') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="dateInputGroupPrepend">
                                            <i class="fa fa-calendar-alt "></i>
                                        </span>
                                    </div>

                                    <input type="text" name="start_date"
                                        value="{{ old('start_date', $class?->start_date) }}"
                                        class="form-control @error('start_date')  is-invalid @enderror datetimepicker"
                                        aria-describedby="dateInputGroupPrepend" />
                                    @error('start_date')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-15 js-start_date">
                            <div class="form-group">
                                <label class="input-label">{{ trans('public.end_date') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="dateInputGroupPrepend">
                                            <i class="fa fa-calendar-alt "></i>
                                        </span>
                                    </div>

                                    <input type="text" name="end_date" value="{{ old('end_date', $class?->end_date) }}"
                                        class="form-control @error('end_date')  is-invalid @enderror datetimepicker"
                                        aria-describedby="dateInputGroupPrepend" />
                                    @error('end_date')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">الغاء</button>
                        <button type="submit" class="btn btn-danger" id="confirmAction">حفظ</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endpush
