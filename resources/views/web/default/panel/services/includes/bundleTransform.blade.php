@extends(getTemplate() . '.panel.layouts.panel_layout')



@section('content')
    <!-- Modal -->
    <div class="" id='confirmModal' tabindex="-1">
        <div class="">
            <div class="">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel"> طلب تحويل من برنامج </h5>
                </div>
                <form class="modal-body" method="post" action="/panel/services/{{ $service->id }}/bundleTransform">
                    @csrf
                    @php
                        $user = auth()->user();
                        $purchasedFormBundles = $user->bundleSales;
                    @endphp

                    <div class="form-group">
                        <label class="input-label">محول من برنامج :</label>
                        <select class="form-control @error('from_bundle_id')  is-invalid @enderror" name="from_bundle_id" id="from_bundle_id" >
                            <option value="" price="0" class="placeholder" disabled selected>اختر التخصص الذي تود التحويل منه
                            </option>
                            @foreach ($purchasedFormBundles as $bundleSale)
                                @php
                                    $bundle = optional($bundleSale->bundle);
                                @endphp
                                @if ($bundle)
                                    <option value="{{ $bundle->id }}" price="{{ $bundle->price }}" @if(old('from_bundle_id')==$bundle->id) selected @endif>
                                        {{ $bundle->title }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('from_bundle_id')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="input-label">تحويل الي برنامج :</label><br>
                        <select id="to_bundle_id" class="form-control @error('to_bundle_id')  is-invalid @enderror"
                            name="to_bundle_id" required onchange="CertificateSectionToggle();">
                            <option selected disabled price="0">اختر البرنامج المرام التحويل إليه
                            </option>

                            {{-- Loop through top-level categories --}}
                            @foreach ($categories as $category)
                                <optgroup label="{{ $category->title }}">

                                    {{-- Display bundles directly under the current category --}}
                                    @foreach ($category->activeBundles as $bundleItem)
                                        <option value="{{ $bundleItem->id }}" price="{{ $bundleItem->price }}"
                                            has_certificate="{{ $bundleItem->has_certificate }}"
                                            early_enroll="{{ $bundleItem->early_enroll }}"
                                            @if (old('to_bundle_id') == $bundleItem->id) selected @endif>
                                            {{ $bundleItem->title }}</option>
                                    @endforeach

                                    {{-- Display bundles under subcategories --}}
                                    @foreach ($category->activeSubCategories as $subCategory)
                                        @foreach ($subCategory->activeBundles as $bundleItem)
                                            <option value="{{ $bundleItem->id }}" price="{{ $bundleItem->price }}"
                                                has_certificate="{{ $bundleItem->has_certificate }}"
                                                early_enroll="{{ $bundleItem->early_enroll }}"
                                                @if (old('to_bundle_id') == $bundleItem->id) selected @endif>
                                                {{ $bundleItem->title }}</option>
                                        @endforeach
                                    @endforeach

                                </optgroup>
                            @endforeach

                        </select>

                        @error('to_bundle_id')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- certificate --}}
                    <div class="form-group col-12  d-none" id="certificate_section">
                        <label style="width: auto">{{ trans('application_form.want_certificate') }} ؟
                            *</label>
                        <span class="text-danger font-12 font-weight-bold" id="certificate_message"> </span>
                        @error('certificate')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="row mr-5 mt-3">
                            {{-- want certificate --}}
                            <div class="col-sm-4 col">
                                <label for="want_certificate">
                                    <input type="radio" id="want_certificate" name="certificate" value="1"
                                        onchange="showCertificateMessage()"
                                        class=" @error('certificate') is-invalid @enderror"
                                        {{ old('certificate', $user->student->certificate ?? null) === '1' ? 'checked' : '' }}>
                                    نعم
                                </label>
                            </div>

                            {{-- does not want certificate --}}
                            <div class="col">
                                <label for="doesn't_want_certificate">
                                    <input type="radio" id="doesn't_want_certificate" name="certificate"
                                        onchange="showCertificateMessage()" value="0"
                                        class="@error('certificate') is-invalid @enderror"
                                        {{ old('certificate', $user->student->certificate ?? null) === '0' ? 'checked' : '' }}>
                                    لا
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-secondary d-none" id="price-diff">


                    </div>

                    <div class="modal-footer">

                        <button type="submit" class="btn btn-danger" id="confirmAction">ارسال</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@php
    $bundlesByCategory = [];
    foreach ($categories as $item) {
        $bundlesByCategory[$item->id] = $item->bundles;
    }
@endphp
@push('scripts_bottom')
    {{-- bundle toggle and education section toggle --}}
    {{-- <script>
        function toggleHiddenInput() {
            var bundles = @json($bundlesByCategory);

            let selectInput = document.getElementById("mySelect");

            let myForm = selectInput.closest('form');
            let hiddenInput = myForm.bundle_id;
            let certificateSection = myForm.certificate_section;
            var hiddenLabel = document.getElementById("hiddenLabel1");
            if (selectInput.value && hiddenInput) {
                var categoryId = selectInput.value;
                var categoryBundles = bundles[categoryId];

                if (categoryBundles) {
                    console.log(selectInput);
                    var options = categoryBundles.map(function(bundle) {
                        var isSelected = bundle.id == "{{ old('to_bundle_id', $student->bundle_id ?? null) }}" ?
                            'selected' : '';
                        return `<option value="${bundle.id}" ${isSelected} has_certificate="${bundle.has_certificate}">${bundle.title}</option>`;
                    }).join('');

                    hiddenInput.outerHTML =
                        '<select id="bundle_id" name="to_bundle_id"  class="form-control" onchange="CertificateSectionToggle()" required>' +
                        '<option value="" class="placeholder" disabled="" selected="selected">اختر التخصص الذي تود دراسته في اكاديمية انس للفنون</option>' +
                        options +
                        '</select>';


                    hiddenLabel.style.display = "block";
                    hiddenLabel.closest('div').classList.remove('d-none');
                }
            } else {
                hiddenInput.outerHTML =
                    '<select id="bundle_id" name="to_bundle_id"  class="form-control" onchange="CertificateSectionToggle()" >' +
                    '<option value="" class="placeholder" selected hidden >اختر التخصص الذي تود دراسته في اكاديمية انس للفنون</option> </select>';
                hiddenLabel.style.display = "none";
                hiddenLabel.closest('div').classList.add('d-none');
            }
        }
        toggleHiddenInput();
    </script> --}}

    {{-- price Section Toggle --}}
    <script>

        // function displayPriceDiff(){
        //     let priceDiff = document.getElementById('price-diff');
        //     let fromBundle = document.getElementById('from_bundle_id');
        //     let toBundle = document.getElementById('to_bundle_id');
        //     var fromBundlePrice = parseInt(fromBundle.options[ fromBundle.selectedIndex].getAttribute('price'));
        //     var toBundlePrice = parseInt(toBundle.options[ toBundle.selectedIndex].getAttribute('price'));
        //     console.log(toBundlePrice);
        //     console.log(fromBundlePrice);
        //     console.log("diff: " + (toBundlePrice -fromBundlePrice));
        //     if(toBundlePrice>fromBundlePrice){
        //         priceDiff.classList.remove('d-none');
        //         priceDiff.innerHTML = `<p>*سوف تقوم بدفع
        //                     <span  class="font-weight-bold text-primary"> ${toBundlePrice - fromBundlePrice} رس</span>
        //                     كفرق بين البرنامج المحول منه وإليه
        //                 </p>`;
        //     }else if(toBundlePrice<fromBundlePrice){
        //         priceDiff.classList.remove('d-none');
        //         priceDiff.innerHTML = `<p>*سوف تقوم بإستيرداد مبلغ
        //                     <span  class="font-weight-bold text-primary"> ${Math.abs(toBundlePrice - fromBundlePrice)} رس</span>
        //                     كفرق بين البرنامج المحول منه وإليه
        //                 </p>`;
        //     }else{
        //         priceDiff.classList.add('d-none');

        //     }
        // }

        //  displayPriceDiff();
    </script>

    {{-- Certificate Section Toggle --}}
    <script>
        function CertificateSectionToggle() {

            let certificateSection = document.getElementById("certificate_section");

            let bundleSelect = document.getElementById("to_bundle_id");
            // let certificateInputs = document.querySelectorAll("input[name='certificate']");

            // let myForm = event.target.closest('form');

            // let certificateSection = myForm.querySelector("#certificate_section");
            // let bundleSelect = myForm.querySelector("#bundle_id");
            // Get the selected option
            var selectedOption = bundleSelect.options[bundleSelect.selectedIndex];
            if (selectedOption.getAttribute('has_certificate') == 1) {
                certificateSection.classList.remove("d-none");
            } else {
                certificateSection.classList.add("d-none");

            }
        }

        function showCertificateMessage() {
            let messageSection = document.getElementById("certificate_message");
            let certificateOption = document.querySelector("input[name='certificate']:checked");
            if (certificateOption.value === "1") {
                messageSection.innerHTML = "سوف يحصل على خصم 23%"
            } else if (certificateOption.value === "0") {
                messageSection.innerHTML = "بيفوته الحصول علي خصم 23%"

            } else {
                messageSection.innerHTML = ""

            }
        }
        CertificateSectionToggle();
        showCertificateMessage();
    </script>
@endpush
