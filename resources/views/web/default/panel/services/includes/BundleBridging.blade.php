@extends(getTemplate() . '.panel.layouts.panel_layout')



@section('content')
    <!-- Modal -->
    <div class="" id='confirmModal' tabindex="-1">
        <div class="">
            <div class="">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel"> طلب {{ trans('update.bridging') }} </h5>
                </div>
                <form class="modal-body" method="post" action="/panel/services/{{ $service->id }}/bundleBridging">
                    @csrf
                    @php
                        $user = auth()->user();
                        $purchasedBundles = $user->purchasedBundles;
                    @endphp

                    <div class="form-group">
                        {{-- <input type="hidden" name="bridging_id" id="bridging_id"> --}}
                        <label class="input-label">{{ trans('update.bridging') }} من برنامج :</label>
                        <select class="form-control" name="from_bundle_id" id="from_bundle_id"
                            onchange="toggleHiddenInput();displayPriceDiff() ">
                            <option value="" price="0" class="placeholder" disabled selected>اختر التخصص الذي تود
                                ال{{ trans('update.bridging') }} منه
                            </option>
                            @foreach ($purchasedBundles as $bundleSale)
                                @php
                                    $bundle = optional($bundleSale->bundle);
                                @endphp
                                @if ($bundle)
                                    <option value="{{ $bundle->id }}" price="{{ $bundle->price }}"
                                        @if (old('from_bundle_id') == $bundle->id) selected @endif>
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
                        <label class="input-label">{{ trans('update.bridging') }} الي برنامج :</label><br>
                        <select id="bridging_id" class="form-control @error('bridging_id')  is-invalid @enderror"
                            name="bridging_id" required onchange="">
                            <option selected disabled bridging="0">اختر البرنامج المراد ال{{ trans('update.bridging') }}
                                إليه
                            </option>
                        </select>

                        @error('bridging_id')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group text-secondary d-none" id="price-diff">

                    </div>

                    <div class="modal-footer">

                        <button type="submit" class="btn btn-danger" id="confirmAction" disabled>ارسال</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@php
    $bridging = [];
    foreach ($bundles as $item) {
        foreach ($item->bridgingBundles as $fromBundle) {
            // If the bundle ID is not already in the bridging array, create an empty array
            if (!isset($bridging[$fromBundle->id])) {
                $bridging[$fromBundle->id] = ['bridgings' => []];
            }
            // Add the current item to the bridgings array
            $bridging[$fromBundle->id]['bridgings'][] = $item;
        }
    }
@endphp
@push('scripts_bottom')
    {{-- bundle toggle and education section toggle --}}
    <script>
        const bundles = @json($bridging);
        const x = @json($bundles);
        let priceDiff = document.getElementById('price-diff');
        console.log(bundles);
        console.log(x);

        function toggleHiddenInput() {

            let from_bundle_idInput = document.getElementById("from_bundle_id");
            let bridging_idInput = document.getElementById("bridging_id");

            let myForm = from_bundle_idInput.closest('form');

            if (from_bundle_idInput.value) {
                var fromBundleId = from_bundle_idInput.value;
                var availabeToBundles = bundles[fromBundleId];

                console.log(fromBundleId, availabeToBundles);


                if (availabeToBundles?.bridgings) {
                     var options = '';
                    for (let bridging of availabeToBundles.bridgings) {

                        var isSelected = bridging.id == "{{ old('bridging_id') }}" ?
                            'selected' : '';
                         options +=
                            `<option value="${bridging.id}" price="${bridging.price}" title="${bridging.title}" ${isSelected} >${bridging.title}</option>`;
                    }

                    bridging_idInput.outerHTML =
                        ` <select id="bridging_id" class="form-control @error('bridging_id')  is-invalid @enderror"
                            name="bridging_id" required onchange="displayPriceDiff()">
                            <option selected disabled bridging="0">اختر البرنامج المراد ال{{ trans('update.bridging') }}إليه
                            </option>
                            ${options}
                        </select>`;

                    document.getElementById("bridging_id").closest('div').classList.remove('d-none');
                    // document.getElementById("bridging_id").value = `${availabeToBundles?.bridging?.id}`;
                    confirmAction.removeAttribute('disabled');
                } else {
                    bridging_idInput.outerHTML =
                        "<p  id='bridging_id' class='text-danger' bridging='0'>غير متوفر {{ trans('update.bridging') }} لهذا البرنامج*</p>";
                    confirmAction.setAttribute('disabled', true);
                    document.getElementById("bridging_id").value = null;
                    priceDiff.classList.add('d-none');
                }
            } else {
                bridging_idInput.outerHTML =
                    ` <select id="bridging_id" class="form-control @error('bridging_id')  is-invalid @enderror"
                            name="bridging_id" required onchange="displayPriceDiff()">
                            <option selected disabled price="0">اختر البرنامج المراد ال{{ trans('update.bridging') }} إليه
                            </option>
                        </select>`;
                confirmAction.setAttribute('disabled', true);
                document.getElementById("bridging_id").value = null;
                priceDiff.classList.add('d-none');
                //    document.getElementById("to_bundle_id").closest('div').classList.add('d-none');
            }
        }

        toggleHiddenInput();
    </script>

    {{-- price Section Toggle --}}
    <script>
        function displayPriceDiff() {
            let priceDiff = document.getElementById('price-diff');
            let fromBundle = document.getElementById('from_bundle_id');
            let bridgingBundle = document.getElementById('bridging_id');
            var fromBundlePrice = parseInt(fromBundle.options[fromBundle.selectedIndex].getAttribute('price'));

            let selectedBridge = bridgingBundle?.options[bridgingBundle?.selectedIndex] ?? null;

            // let selectedBridge = Object.values(bundles).find(obj => obj.bridging.id == id);

            if (selectedBridge && bridgingBundle?.selectedIndex != 0) {
                console.log(selectedBridge);

                priceDiff.classList.remove('d-none');
                priceDiff.innerHTML = `<p>*سوف تقوم بدفع
                            <span  class="font-weight-bold text-primary"> ${selectedBridge.getAttribute('price')} رس</span>
                            ثمن برنامج ال{{ trans('update.bridging') }} " ${selectedBridge.getAttribute('title')}"
                        </p>`;
            } else {
                priceDiff.classList.add('d-none');

            }
        }

        displayPriceDiff();
    </script>
@endpush
