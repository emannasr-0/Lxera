<button
    class="@if (empty($hideDefaultClass) or !$hideDefaultClass) {{ !empty($noBtnTransparent) ? '' : 'btn-transparent' }} text-primary @endif {{ $btnClass ?? '' }}"
    data-toggle="modal" data-target={{ '#confirmModal' . $id }} data-confirm-href="{{ $url }}"
    data-confirm-text-yes="{{ trans('admin/main.yes') }}" data-confirm-text-cancel="{{ trans('admin/main.cancel') }}"
    data-confirm-has-message="true">
    @if (!empty($btnText))
        {!! $btnText !!}
    @else
        <i class="fa {{ !empty($btnIcon) ? $btnIcon : 'fa-times' }}" aria-hidden="true"></i>
    @endif
</button>

<!-- Modal -->
<div class="modal fade" id={{ 'confirmModal' . $id }} tabindex="-1" aria-labelledby="confirmModalLabel"
    aria-hidden="true" data-confirm-href="{{ $url }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">{{ $title ?? 'تحويل' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="modal-body" method="post" action="{{ $url }}" id="form{{ $id }}">
                @csrf

                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="form-group">
                    <label class="input-label" class="form-label">تحويل من*</label>
                    {{-- <input type="text" name="from" class="form-control" value="{{ $from->title ?? $from->name ?? '' }}" readonly> --}}
                     <select  name="from" required class="form-control"
                        onchange="toggleHiddenInput(event)">
                        <option  selected value="{{ $from->id }}">{{ $from->title ?? $from->name ?? '' }}</option>
                    </select>
                    @error('from')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="form-group">

                    <label class="input-label" class="form-label">تحويل الي *</label>
                    <select id="mySelect{{ $id }}" name="to" required class="form-control"
                        onchange="toggleHiddenInput(event)">
                        <option disabled selected hidden value="">اختر</option>
                        @foreach ($items as $item)
                        <option value="{{ $item->id }}"
                            {{ old('to') == $item->id ? 'selected' : '' }}>
                            {{ $item->title ?? $item->name ?? "" }} </option>
                            @endforeach
                    </select>
                    @error('to')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">الغاء</button>
                    <button type="submit" class="btn btn-danger" id="confirmAction">تحويل</button>
                </div>
            </form>
        </div>
    </div>
</div>
