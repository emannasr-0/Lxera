<button class=" js-font-resize @if(empty($hideDefaultClass) or !$hideDefaultClass) {{ !empty($noBtnTransparent) ? '' : 'btn-transparent' }} text-primary @endif {{ $btnClass ?? '' }}"
        data-toggle="modal" data-target={{"#confirmModal".$id}}
        data-confirm-href="{{ $url }}"
        data-confirm-text-yes="{{ trans('admin/main.yes') }}"
        data-confirm-text-cancel="{{ trans('admin/main.cancel') }}"
        data-confirm-has-message="true"
>
    @if(!empty($btnText))
        {!! $btnText !!}
    @else
        <i class=" js-font-resize fa {{ !empty($btnIcon) ? $btnIcon : 'fa-times' }}" aria-hidden="true"></i>
    @endif
</button>

<!-- Modal -->
<div class=" js-font-resize modal fade" id={{"confirmModal".$id}} tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true" data-confirm-href="{{ $url }}">
    <div class=" js-font-resize modal-dialog">
        <div class=" js-font-resize modal-content">
            <div class=" js-font-resize modal-header">
                <h5 class=" js-font-resize modal-title" id="confirmModalLabel">{{ "تأكيد رفض الطلب"}}</h5>
                <button type="button" class=" js-font-resize close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class=" js-font-resize modal-body" method="GET" action="{{ $url }}" id="deleteForm">
                <label for="message" class=" js-font-resize form-label">{{ "اذكر سبب الرفض" }}</label>
                <select name="reason" id="reason" class=" js-font-resize form-control mb-3" required>

                    <option value=""  selected disabled>اختر سبب الرفض</option>

                    <option value="يوجد مشكلة في مرفق  بطاقة الهوية الوطنية أو جواز السفر">
                        يوجد مشكلة في مرفق  بطاقة الهوية الوطنية أو جواز السفر
                    </option>
                    <option value="يوجد مشكلة في مرفق  شهادة البكالوريوس">يوجد مشكلة في مرفق  شهادة البكالوريوس</option>
                    <option value="يوجد مشكلة في مرفق  شهادة الثانوية">يوجد مشكلة في مرفق  شهادة الثانوية</option>
                    <option value="يوجد مشكلة في مرفق السجل الأكاديمي">يوجد مشكلة في مرفق السجل الأكاديمي</option>
                    <option value="يوجد مشكلة في مرفق السيرة الذاتية ">يوجد مشكلة في مرفق السيرة الذاتية </option>
                    <option value="يوجد مشكلة في مرفق الغرض من الدراسة">يوجد مشكلة في مرفق الغرض من الدراسة </option>
                    <option value="يوجد مشكلة في مرفق الخبرة العملية التخصص المقدم اليه">
                        يوجد مشكلة في مرفق الخبرة العملية التخصص المقدم اليه</option>
                    <option value="يوجد مشكلة في مرفق التوصية العلمية والمهنية">يوجد مشكلة في مرفق توصية العلمية والمهنية</option>
                    <option value="يوجد مشكلة في مرفق  الخلفية المهنية">يوجد مشكلة في مرفق  الخلفية المهنية</option>

                </select>
                <textarea class=" js-font-resize form-control" id="message" name="message" placeholder="اكتب بشكل مفصل سبب الرفض"></textarea>
                <div class=" js-font-resize modal-footer">
                    <button type="button" class=" js-font-resize btn btn-secondary ml-3" data-dismiss="modal">{{ trans('admin/main.cancel') }}</button>
                    <button type="submit" class=" js-font-resize btn btn-danger id="confirmAction">{{ trans('admin/main.send')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>


