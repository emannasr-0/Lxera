<div>
    {{-- course --}}
    <div class="form-group">
        <label for="application2" class="form-label" id="all_course">الدورات التدربيه<span
                class="text-danger">*</span></label>
        <select id="mySelect2" name="webinar_id" class="form-control @error('webinar_id') is-invalid @enderror">
            <option selected hidden value="">اختر الدورة التدربيه التي تريد دراستها
                في
                اكاديمية انس للفنون </option>

            @foreach ($courses as $course)
                <option value="{{ $course->id }}" @if (old('webinar_id', request()->webinar_id) == $course->id) selected @endif>
                    {{ $course->title }} </option>
            @endforeach

        </select>

        @error('webinar_id')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>
    {{-- programs --}}
    <section class="" id="diplomas_section">
        <div class="form-group mt-15">
            <label class="input-label">البرنامج</label>

            <select id="bundle_id" class="custom-select @error('bundle_id')  is-invalid @enderror" name="bundle_id"
                wire:model.live="bundle_id2">
                <option selected hidden value="">اختر البرنامج التدربي الذي تريد دراسته
                    في
                    اكاديمية انس للفنون </option>

                {{-- Loop through top-level categories --}}
                @foreach ($categories as $category)
                    <optgroup label="{{ $category->title }}">

                        {{-- Display bundles directly under the current category --}}
                        @foreach ($category->activeBundles as $bundleItem)
                            <option value="{{ $bundleItem->id }}" has_certificate="{{ $bundleItem->has_certificate }}"
                                early_enroll="{{ $bundleItem->early_enroll }}"
                                @if (old('bundle_id', request()->bundle_id) == $bundleItem->id) selected @endif>
                                {{ $bundleItem->title }}</option>
                        @endforeach

                        {{-- Display bundles under subcategories --}}
                        @foreach ($category->activeSubCategories as $subCategory)
                            @foreach ($subCategory->activeBundles as $bundleItem)
                                <option value="{{ $bundleItem->id }}"
                                    has_certificate="{{ $bundleItem->has_certificate }}"
                                    early_enroll="{{ $bundleItem->early_enroll }}"
                                    @if (old('bundle_id', request()->bundle_id) == $bundleItem->id) selected @endif>
                                    {{ $bundleItem->title }}</option>
                            @endforeach
                        @endforeach

                    </optgroup>
                @endforeach
            </select>
            @error('bundle_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </section>
</div>
