@extends(getTemplate() . '.panel.layouts.panel_layout')



@section('content')
    <!-- Modal -->
    <div class=" js-font-resize " id='confirmModal' tabindex="-1">
        <div class=" js-font-resize ">
            <div class=" js-font-resize ">
                <div class=" js-font-resize modal-header">
                    <h5 class=" js-font-resize modal-title" id="confirmModalLabel"> طلب تأجيل برنامج </h5>
                </div>
                <form class=" js-font-resize modal-body" method="post" action="/panel/services/{{ $service->id }}/bundleDelay">
                    @csrf
                    @php
                        $user = auth()->user();
                        $purchasedFormBundles = $user->bundleSales;
                    @endphp

                    <div class=" js-font-resize form-group">
                        <label class=" js-font-resize input-label"> البرنامج :</label>
                        <select class=" js-font-resize form-control @error('from_bundle_id')  is-invalid @enderror" name="from_bundle_id" id="from_bundle_id" required>
                            <option value="" price="0" class=" js-font-resize placeholder" disabled selected>اختر البرنامج الذي تود تأجيله
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
                            <div class=" js-font-resize invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class=" js-font-resize form-group">
                        <label class=" js-font-resize input-label"> سبب التأجيل :</label>
                        <textarea class=" js-font-resize form-control
                        @error('reason')  is-invalid @enderror"
                        name="reason" id="reason" rows="10"  minlength="10" maxlength="1000" required
                        placeholder="أذكر سبب التأجيل">{{ old('reason') }}</textarea>

                        @error('reason')
                            <div class=" js-font-resize invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>



                    <div class=" js-font-resize modal-footer">

                        <button type="submit" class=" js-font-resize btn btn-danger" id="confirmAction">ارسال</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

