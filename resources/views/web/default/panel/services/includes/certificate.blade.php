@extends(getTemplate() . '.panel.layouts.panel_layout')

@section('content')
    <!-- Modal -->
    <div class="" id='confirmModal' tabindex="-1">
        <div class="">
            <div class="">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">إضافة خدمة الشهادة</h5>
                </div>
                <form class="modal-body" method="post" action="/panel/services/{{ $service->id }}/certificate">
                    @csrf

                    <div class="form-group">
                        <label class="input-label">الاسم باللغة العربية:</label>
                        <input type="text" class="form-control @error('ar_name') is-invalid @enderror" name="ar_name" id="ar_name" value="{{ old('ar_name') }}" required>
                        @error('ar_name')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="input-label">رقم  الهاتف للتواصل:</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{ old('phone') }}" required>
                        @error('phone')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="input-label">البريد الإلكتروني:</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="input-label">العنوان الوطني بشكل صحيح :</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="address" rows="4" required>{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger" id="confirmAction">إرسال</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
