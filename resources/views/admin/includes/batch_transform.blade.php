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
                <h5 class="modal-title" id="confirmModalLabel">{{ $title }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="modal-body" method="post" action="{{ $url }}" id="form{{ $id }}">
                @csrf

                <label class="input-label">من البرنامج</label>
                <select class="form-control" name="from_bundle_id" id="diploma1" required>
                    @if ($bundle)
                        <option value="{{ $bundle->id }}" selected>
                            {{ $bundle->title }} ({{ $bundle->batch?->title }})
                        </option>
                    @endif

                </select>
                @error('from_bundle_id')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror

                    {{-- specialization --}}
                    <div class="form-group mt-5">
                        <label class="input-label"> إلي البرنامج <span class="text-danger">*</span> </label>
                        <select id="bundle_id" class="custom-select @error('to_bundle_id')  is-invalid @enderror"
                            name="to_bundle_id" required>
                            <option selected hidden value="">اختر البرنامج </option>


                            {{-- Loop through top-level categories --}}
                            @foreach ($categories as $category)
                                <optgroup label="{{ $category->title }}">

                                    {{-- Display bundles directly under the current category --}}
                                    @foreach ($category->bundles as $bundleItem)
                                        <option value="{{ $bundleItem->id }}"
                                            has_certificate="{{ $bundleItem->has_certificate }}"
                                            early_enroll="{{ $bundleItem->early_enroll }}">
                                            {{ $bundleItem->title }} ({{ $bundleItem->batch?->title }}) </option>
                                    @endforeach

                                    {{-- Display bundles under subcategories --}}
                                    @foreach ($category->subCategories as $subCategory)
                                        @foreach ($subCategory->bundles as $bundleItem)
                                            <option value="{{ $bundleItem->id }}"
                                                has_certificate="{{ $bundleItem->has_certificate }}"
                                                early_enroll="{{ $bundleItem->early_enroll }}">
                                                {{ $bundleItem->title }} ({{ $bundleItem->batch?->title }}) </option>
                                        @endforeach
                                    @endforeach

                                </optgroup>
                            @endforeach

                        </select>


                        @error('bundle_id')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">الغاء</button>
                    <button type="submit" class="btn btn-danger" id="confirmAction">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
