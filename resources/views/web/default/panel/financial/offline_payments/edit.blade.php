<button
    class="@if (empty($hideDefaultClass) or !$hideDefaultClass) {{ !empty($noBtnTransparent) ? '' : 'btn-transparent' }} text-primary @endif {{ $btnClass ?? '' }}"
    style="width: 90px" data-toggle="modal" data-target={{ '#confirmModal' . $id }} data-confirm-href="{{ $url }}"
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
            <div class="modal-header align-items-baseline">
                <h5 class="modal-title" id="confirmModalLabel">{{ 'تعديل الطلب واعادة ارساله ' }}</h5>
                <button type="button" class="close m-0" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="modal-body" method="POST" action="{{ $url }}" id="deleteForm" enctype="multipart/form-data">
                <div class="row">
                    @csrf


                    <div class="col-12 col-md-6 mb-25 mb-lg-0 js-offline-payment-input ">
                        <div class="form-group text-left">
                            <label class="input-label">{{ trans('financial.account') }}</label>
                            <select name="account" class="form-control @error('account') is-invalid @enderror">
                                <option selected disabled>{{ trans('financial.select_the_account') }}</option>

                                @foreach ($offlineBanks as $offlineBank)
                                    <option value="{{ $offlineBank->id }}"
                                        @if (!empty($payment) and $payment->offline_bank_id == $offlineBank->id) selected @endif>{{ $offlineBank->title }}
                                    </option>
                                @endforeach
                            </select>

                            @error('account')
                                <div class="invalid-feedback"> {{ $message }}</div>
                            @enderror
                        </div>
                    </div>



                    <div class="col-12 col-md-6 mb-25 mb-lg-0 js-offline-payment-input ">
                        <div class="form-group text-left">
                            <label for="IBAN" class="input-label"> اي بان (IBAN)</label>
                            <input type="text" name="IBAN" id="IBAN" value="{{ old('IBAN', $payment->iban) }}"
                                class="form-control @error('IBAN') is-invalid @enderror" />
                            @error('IBAN')
                                <div class="invalid-feedback"> {{ $message }}</div>
                            @enderror
                        </div>
                    </div>



                    <div class="col-12 mb-25 mb-lg-0 js-offline-payment-input ">
                        <div class="form-group text-left">
                            <label class="input-label">{{ trans('update.attach_the_payment_photo') }}</label>

                            {{-- <label for="attachmentFile" id="attachmentFileLabel{{$id}}"
                                class="custom-upload-input-group flex-row-reverse ">
                                <span class="custom-upload-icon text-white">
                                    <i data-feather="upload" width="18" height="18" class="text-white"></i>
                                </span>
                                <div class="custom-upload-input"></div>
                            </label> --}}

                            <input type="file" name="attachment" id="attachmentFile{{$id}}" accept=".jpeg,.jpg,.png"
                                class="form-control h-auto @error('attachment') is-invalid @enderror"
                                value="" />
                            @error('attachment')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>


                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary ml-3"
                        data-dismiss="modal">{{ trans('admin/main.cancel') }}</button>
                    <button type="submit"
                        class="btn btn-danger id="confirmAction">{{ trans('admin/main.send') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
