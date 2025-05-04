<a href="#"
    class="@if (empty($hideDefaultClass) or !$hideDefaultClass) {{ !empty($noBtnTransparent) ? '' : 'btn-transparent' }} text-primary @endif {{ $btnClass ?? '' }}"
    data-toggle="modal" data-target={{ '#messageModal' . $id }} data-confirm-href="{{ $url }}"
    data-confirm-text-yes="{{ trans('admin/main.yes') }}" data-confirm-text-cancel="{{ trans('admin/main.cancel') }}"
    data-confirm-has-message="true">
    @if (!empty($btnText))
        {!! $btnText !!}
    @else
        <i class="fa {{ !empty($btnIcon) ? $btnIcon : 'fa-times' }}" aria-hidden="true"></i>
    @endif
</a>

<!-- Modal -->
<div class="modal fade" id={{ 'messageModal' . $id }} tabindex="-1" aria-labelledby="messageModalLabel"
    aria-hidden="true" data-confirm-href="{{ $url }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary" id="messageModalLabel">{!! $btnText !!}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                @if (!empty($message))
                    {{-- <div class="form-control border-0" id="message" style="height: auto">
                        <p>
                            <span class="d-block text-danger font-weight-bold">السبب الرئيسى للرفض </span>
                            لم يذكر سبب
                        </p>
                    </div> --}}
                    <div class="form-control border-0" id="message" style="height: auto">
                        <p>
                            {{-- <span class="d-block text-danger font-weight-bold">السبب الرئيسى للرفض </span> --}}
                            {{ $message }}
                        </p>
                    </div>

                @endif

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">اغلاق</button>
                </div>
            </div>
        </div>
    </div>
