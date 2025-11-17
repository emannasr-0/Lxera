@extends(getTemplate() . '.panel.layouts.panel_layout')

@section('content')
    <!-- Modal -->
    <div class=" js-font-resize " id='confirmModal' tabindex="-1">
        <div class=" js-font-resize ">
            <div class=" js-font-resize ">
                <div class=" js-font-resize modal-header">
                    <h5 class=" js-font-resize modal-title" id="confirmModalLabel">إضافة خدمة الشهادة</h5>
                </div>
                <form class=" js-font-resize modal-body" method="post" action="/panel/services/{{ $service->id }}/certificate">
                    @csrf

                    <div class=" js-font-resize form-group">
                        <label class=" js-font-resize input-label">الاسم باللغة العربية:</label>
                        <input type="text" class=" js-font-resize form-control @error('ar_name') is-invalid @enderror" name="ar_name" id="ar_name" value="{{ old('ar_name') }}" required>
                        @error('ar_name')
                            <div class=" js-font-resize invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class=" js-font-resize form-group">
                        <label class=" js-font-resize input-label">رقم  الهاتف للتواصل:</label>
                        <input type="text" class=" js-font-resize form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{ old('phone') }}" required>
                        @error('phone')
                            <div class=" js-font-resize invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class=" js-font-resize form-group">
                        <label class=" js-font-resize input-label">البريد الإلكتروني:</label>
                        <input type="email" class=" js-font-resize form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class=" js-font-resize invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class=" js-font-resize form-group">
                        <label class=" js-font-resize input-label">العنوان الوطني بشكل صحيح :</label>
                        <textarea class=" js-font-resize form-control @error('address') is-invalid @enderror" name="address" id="address" rows="4" required>{{ old('address') }}</textarea>
                        @error('address')
                            <div class=" js-font-resize invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class=" js-font-resize modal-footer">
                        <button type="submit" class=" js-font-resize btn btn-danger" id="confirmAction">إرسال</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
