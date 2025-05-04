<button
    class="@if (empty($hideDefaultClass) or !$hideDefaultClass) {{ !empty($noBtnTransparent) ? '' : 'btn-transparent' }} text-primary @endif {{ $btnClass ?? '' }}"
    data-toggle="modal" data-target={{ '#importModal' }} data-confirm-href="{{ $url }}"
    data-confirm-text-yes="{{ trans('admin/main.yes') }}" data-confirm-text-cancel="{{ trans('admin/main.cancel') }}"
    data-confirm-has-message="true">
    @if (!empty($btnText))
        {!! $btnText !!}
    @else
        <i class="fa {{ !empty($btnIcon) ? $btnIcon : 'fa-times' }}" aria-hidden="true"></i>
    @endif
</button>

<style>

</style>
<!-- Modal -->
<div class="modal fade" id={{ 'importModal' }} tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true"
    data-confirm-href="{{ $url }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">تسجيل طلاب من ملف اكسيل</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="modal-body" method="post" action="{{ $url }}" id="deleteForm"
                enctype="multipart/form-data" onsubmit="submitForm(event)">

                @csrf
                <div class="">
                    <label for="excelFile"> قم برفع الملف*</label>
                    <input type="file" name="file" id="excelFile" class="form-control-file border rounded"
                        placeholder="yy" required accept=".xlsx,.xls">
                    <p class="text-primary">.xlsx xls امتداد الملف المسموح به</p>
                    <p class="text-danger">ملاحظة: يرجي عدم اضافة اكثر من 40 صف (طالب) في ملف الاكسل المرفع</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary ml-3"
                        data-dismiss="modal">{{ trans('admin/main.cancel') }}</button>
                    <button type="submit" class="btn btn-danger" id="confirmAction">استخراج</button>
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
