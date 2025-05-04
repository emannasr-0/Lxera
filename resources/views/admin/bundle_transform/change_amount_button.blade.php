<button class="@if(empty($hideDefaultClass) or !$hideDefaultClass) {{ !empty($noBtnTransparent) ? '' : 'btn-transparent' }} text-primary @endif {{ $btnClass ?? '' }}"
        data-toggle="modal" data-target={{"#confirmModalx".$id}}
        data-confirm-href="{{ $url }}"
        data-confirm-text-yes="{{ trans('admin/main.yes') }}"
        data-confirm-text-cancel="{{ trans('admin/main.cancel') }}"
        data-confirm-has-message="true"
        style="width: max-content"
>
    @if(!empty($btnText))
        {!! $btnText !!}
    @else
        <i class="fa {{ !empty($btnIcon) ? $btnIcon : 'fa-times' }}" aria-hidden="true"></i>
    @endif
</button>

<!-- Modal -->
<div class="modal fade" id={{"confirmModalx".$id}} tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true" data-confirm-href="{{ $url }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">{{ "تأكيد تغيير المبلغ"}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="modal-body" method="post" action="{{ $url }}" onsubmit="submitForm(event)">
                @csrf
                <label for="amount" class="form-label">{{ "ادخل قيمة المبلغ " }}</label>
                <input type="number" class="form-control" id="amount" name="amount" min="0" value="{{ $amount??0 }}" required>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">{{ trans('admin/main.cancel') }}</button>
                    <button type="submit" class="btn btn-danger" id="confirmAction">تغيير</button>
                </div>
            </form>
        </div>
    </div>
</div>


