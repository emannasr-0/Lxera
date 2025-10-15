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
<div class="modal fade" id={{ 'confirmModal' . $id }} tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true"
    data-confirm-href="{{ $url }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">{{ 'تأكيد رفض الطلب' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="modal-body" method="GET" action="{{ $url }}" id="deleteForm"
                onsubmit="submitForm(event)">
                <label for="message" class="form-label">{{ 'اذكر سبب الرفض' }}</label>
                <textarea class="form-control" id="message" name="message" placeholder="اكتب بشكل مفصل سبب الرفض" required></textarea>
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


@push('scripts_bottom')
    <script>
        function submitForm(e) {
            e.preventDefault();
            let form = e.target;
            let confirmBtn = form.querySelector('#confirmAction');
            confirmBtn.disabled = true;
            confirmBtn.classList.add('loadingbar', 'danger');
            form.submit();
        }
    </script>
@endpush
