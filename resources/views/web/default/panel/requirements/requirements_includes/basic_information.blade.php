<form method="post" id="userRequirmentForm" class="mt-30" enctype="multipart/form-data"
    action="/panel/bundles/{{ $studentBundleId }}/requirements">
    {{ csrf_field() }}

    <h1 class="section-title after-line text-center text-light g-3">نموذج تقديم متطلبات القبول</h1>

    <div class="row mt-20 p-5">

        {{-- requirement fields --}}
        <div class="col-12 col-lg-6">

            <div class="form-group p-5 ">
                <label class="text-light" for="user_code">رقم الطالب *</label>
                <input type="text" name="user_code" id="user_code"
                    class="form-control @error('user_code')  is-invalid @enderror" required readonly
                    value="{{ $user_code }}" />
                @error('user_code')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
                {{-- <p>الحد الأقصى هو 7 من الأحرف.</p>
                    <p>تم ارسال رقم الطالب على البريد الالكتروني المرسل عند تقديمك طلب قبول جديد</p> --}}
            </div>


            <div class="form-group p-5 ">
                <label class="text-light" for="program">البرنامج المراد التسجيل فيه *</label>


                <input type="text" name="program" id="program"
                    class="form-control @error('program')  is-invalid @enderror" required readonly
                    value="{{ preg_replace('/\s+/', ' ', trim($program->slug)) }}" />

                @error('program')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>


            <div class="form-group p-5 ">
                <label class="text-light" for="specialization">التخصص المطلوب *</label>

                <input type="text" name="specialization" id="specialization"
                    class="form-control @error('specialization')  is-invalid @enderror" required readonly
                    value="{{ $bundle->title }}" />

                @error('specialization')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>


            <div class="form-group p-5 ">
                <label class="text-light" for="identity_type">اختر نوع الهوية المرفقة *</label>
                <select id="identity_type" class="form-control d-block @error('identity_type')  is-invalid @enderror"
                    name="identity_type" required>
                    <option value="" class="placeholder" disabled
                        {{ old('identity_type') == '' ? 'selected' : '' }}>اختر نوع الهوية
                    </option>
                    <option value="الهوية الوطنية" {{ old('identity_type') == 'الهوية الوطنية' ? 'selected' : '' }}>
                        الهوية الوطنية</option>
                    <option value="جواز السفر" {{ old('identity_type') == 'جواز السفر' ? 'selected' : '' }}>جواز السفر
                    </option>
                </select>
                @error('identity_type')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group p-5 {{ old('identity_type') != '' ? 'd-block' : 'd-none' }}" id="identity_attach">
                <label class="text-light" for="identity_attachment">ارفق صورة {{ old('identity_type') }} *</label>
                <input type="file" name="identity_attachment" id="identity_attachment"
                    class="form-control @error('identity_attachment')  is-invalid @enderror" placeholder="" required
                    value="{{ old('identity_attachment') }}" accept=".pdf,.jpeg,.jpg,.png" />
                @error('identity_attachment')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
                <p class="text-primary">.PDF JPEG JPG PNG امتداد الملف المسموح به</p>
            </div>
            {{--
                <div class="form-group p-5 ">
 
                    <label class="text-light" for="admission_attachment">ارفق متطلبات القبول *</label>
                    <input type="file" name="admission_attachment" id="admission_attachment"
                        class="form-control @error('admission_attachment')  is-invalid @enderror" placeholder="" required
                        value="{{ old('admission_attachment') }}" accept="application/pdf" />
                    @error('admission_attachment')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                     <p class="text-primary">برجاء ارفاق المتطلبات من 2 الى نهاية المتطلبات في ملف واحد بصيغة PDF ولا يتعدي
                        حجم الملف 20
                        ميجا</p>
                    <p class="text-primary"> برجاء تنزيل نموزج طلب الالتحاق وملئ البيانات وحفظه كصيغه PDF ثم رفعه
                        <a href="{{ asset('files/_نموذج طلب التحاق.docx') }}" class="text-secondary font-weight-bold"
                            download>انقر هنا لتحميل نموزج طلب الالتحاق
                        </a>
                    </p>

                </div>
            --}}
            <div class="form-group p-5 ">
                <label class="text-light" for="study_purpose">الغرض من الدراسة*</label>
                <p class="text-light font-italic">
                    أكتب فقرة لا تقل عن 250 كلمة تشرح فيها أسباب رغبتك في الالتحاق بالبرنامج المقدم إليه (Statement of Purpose)
                </p>
                <textarea name="study_purpose" id=""  rows="15" minlength="250" class="form-control mt-5" required></textarea>
                @error('study_purpose')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
    </div>


    {{-- addmission requirements --}}
    <div class="col-12 col-lg-5 ml-20 text-light">
        @include('web.default.panel.requirements.requirements_includes.program_requirements')
    </div>
    </div>


    <div class="d-flex align-items-baseline">
        <input type="checkbox" name="accept" class="d-inline-block mr-10" required>
        <p class="text-light">اقر أنا المسجل بياناتي اعلاه بموافقتي على لائحة الحقوق والوجبات واحكام وشروط القبول والتسجيل، كما أقر
            بالتزامي
            التام بمضمونها، وبمسؤوليتي التامة عن أية مخالفات قد تصدر مني لها ، مما يترتب عليه كامل الأحقية للاكاديمية في
            مسائلتي عن تلك المخالفات والتصرفات المخالفة للوائح المشار إليها في عقد اتفاقية التحاق متدربـ/ـة
            <a href="https://anasacademy.uk/wp-content/uploads/2023/12/نموذج-عقد-اتفاقية-التحاق-متدربـ-النسخة-الاخيرة.pdf"
                target="_blank" class="text-primary">انقر هنا للمشاهدة</a>
        </p>
    </div>


    <button type="submit" name="submit" id="btn_submit" class="btn btn-primary mt-20" data-alt-text="جاري إرسال طلبك"
        data-submit-text="اضغط لإرسال متطلبات القبول" value="form_submit">اضغط لإرسال متطلبات القبول</button>


</form>

<script>
    let identity_type = document.getElementById('identity_type');
    let identity_attach = document.getElementById('identity_attach');
    identity_type.addEventListener('change', function() {
        identity_attach.classList.add("d-block");
        identity_attach.classList.remove("d-none");
        identity_attach.firstElementChild.innerText = " ارفق صورة  " + identity_type.value + " *";
    });

    document.onload = function() {
        if (identity_type.value != "")
            identity_attach.classList.add("d-block");
        identity_attach.classList.remove("d-none");
        identity_attach.firstElementChild.innerText = " ارفق صورة  " + identity_type.value + " *";
    };
</script>
