<button class="@if(empty($hideDefaultClass) or !$hideDefaultClass) {{ !empty($noBtnTransparent) ? '' : 'btn-transparent' }} text-primary @endif {{ $btnClass ?? '' }}"
        data-toggle="modal" data-target={{"#confirmModal".$id}}
        data-confirm-href="{{ $url }}"
        data-confirm-text-yes="{{ trans('admin/main.yes') }}"
        data-confirm-text-cancel="{{ trans('admin/main.cancel') }}"
        data-confirm-has-message="true"
>
    @if(!empty($btnText))
        {!! $btnText !!}
    @else
        <i class="fa {{ !empty($btnIcon) ? $btnIcon : 'fa-times' }}" aria-hidden="true"></i>
    @endif
</button>

<!-- Modal -->
<div class="modal fade" id={{"confirmModal".$id}} tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true" data-confirm-href="{{ $url }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">تحويل الدبلومة</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="modal-body" method="post" action="{{ $url }}" id="form{{$id}}">
                @csrf
                @php
                    $purchasedFormBundles = $user->purchasedFormBundle();
                @endphp
                <label class="input-label">محول من برنامج :</label>
                <select class="form-control" name="fromDiploma" id="diploma1">
                    @foreach ($purchasedFormBundles as $bundleSale)
                        @php
                            $bundle = optional($bundleSale->bundle);
                        @endphp
                        @if ($bundle)
                            <option value="{{ $bundle->id }}">
                                {{ $bundle->title }}
                            </option>
                        @endif
                    @endforeach
                </select><br>
                @error('category_id')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
                <label class="input-label">تحويل الي برنامج :</label><br>
                <div class="container_form mt-25">
                    {{-- diploma --}}
                    <div class="form-group">
                        <label for="application"
                            class="form-label">{{ trans('application_form.application') }}*</label>
                        <select id="mySelect{{$id}}" name="category_id" required
                            class="form-control" onchange="toggleHiddenInput(event)">
                            <option disabled selected hidden value="">اختر  الدرجة العلمية التي تريد دراستها في اكاديما
                                 </option>
                            @foreach ($category as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('category_id', $user->student->category_id ?? null) == $item->id ? 'selected' : '' }}>
                                    {{ $item->title }} </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- specialization --}}
                    <div class="form-group">
                        <label class="hidden-element" id="hiddenLabel1"
                            for="name">
                            {{ trans('application_form.specialization') }}*
                        </label>
                        <input type="text" id="bundle_id" name="toDiploma"
                            required class="hidden-element form-control"
                            value="{{ old('toDiploma', $user->student ? $user->student->bundle_id : '') }}">
                        @error('toDiploma')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- certificate --}}
                    <div class="form-group col-12  d-none"
                        id="certificate_section">
                        <label style="width: auto">{{ trans('application_form.want_certificate') }} ؟
                            *</label>
                        <span class="text-danger font-12 font-weight-bold"
                            id="certificate_message"> </span>
                        @error('certificate')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="row mr-5 mt-3">
                            {{-- want certificate --}}
                            <div class="col-sm-4 col">
                                <label for="want_certificate">
                                    <input type="radio" id="want_certificate"
                                        name="certificate" value="1"
                                        onchange="showCertificateMessage(event)"
                                        class=" @error('certificate') is-invalid @enderror"
                                        {{ old('certificate', $user->student->certificate ?? null) === '1' ? 'checked' : '' }}>
                                    نعم
                                </label>
                            </div>

                            {{-- does not want certificate --}}
                            <div class="col">
                                <label for="doesn't_want_certificate">
                                    <input type="radio"
                                        id="doesn't_want_certificate"
                                        name="certificate"
                                        onchange="showCertificateMessage(event)"
                                        value="0"
                                        class="@error('certificate') is-invalid @enderror"
                                        {{ old('certificate', $user->student->certificate ?? null) === '0' ? 'checked' : '' }}>
                                    لا
                                </label>
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


