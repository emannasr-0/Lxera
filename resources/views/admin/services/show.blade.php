
<button class="@if(empty($hideDefaultClass) or !$hideDefaultClass) {{ !empty($noBtnTransparent) ? '' : 'btn-transparent' }} text-primary @endif {{ $btnClass ?? '' }}"
        data-toggle="modal" data-target={{"#confirmModal".$service->id}}
        data-confirm-text-yes="{{ trans('admin/main.yes') }}"
        data-confirm-text-cancel="{{ trans('admin/main.cancel') }}"
        data-confirm-has-message="true"
        data-toggle="tooltip"
        data-toggle="modal"
        data-placement="top"
        title="{{ trans('admin/main.show') }}"
>
    @if(!empty($btnText))
        {!! $btnText !!}
    @else
        <i class="fa {{ !empty($btnIcon) ? $btnIcon : 'fa-times' }}" aria-hidden="true"></i>
    @endif
</button>

<!-- Modal -->
<div class="modal fade" id={{"confirmModal".$service->id}} tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">بيانات الخدمة الإلكترونية</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <section class="modal-body" >

                <div>
                    @if(!empty($service))
                    <div class="mb-2">
                        <p class="text-primary font-weight-bold mb-0">عنوان الخدمة الإلكترونية</p>
                        <p>{{$service->title}}</p>
                    </div>

                    <div class="mb-2">
                        <p class="text-primary font-weight-bold mb-0">سعر الخدمة الإلكترونية</p>
                        <p>{!!($service->price > 0) ? $service->price."ر.س" : "<span class='text-danger'>هذة الخدمة مجانية</span>" !!}</p>
                    </div>

                    <div class="mb-2">
                        <p class="text-primary font-weight-bold mb-0">وصف الخدمة الإلكترونية</p>
                        <p>{!! !empty($service->description)?$service->description :  "<span class='text-danger'>لا يوجد وصف</span>" !!}</p>
                    </div>

                    <div class="mb-2">
                        <p class="text-primary font-weight-bold mb-0">رابط التقديم للخدمة الإلكترونية</p>
                        <p>
                            <a href="{{$service->apply_link}}" target="_blank" class="text-dark">
                                <i class="fa fa-link" aria-hidden="true">{{$service->apply_link}}</i>
                            </a>
                        </p>
                    </div>

                    <div class="mb-2">
                        <p class="text-primary font-weight-bold mb-0">رابط مراجعه طلب سابق للخدمة الإلكترونية</p>
                        <p>
                            <a href="{{$service->review_link}}" target="_blank" class="text-dark">
                                <i class="fa fa-link" aria-hidden="true">{{$service->review_link}}</i>
                            </a>
                        </p>
                    </div>

                    <div class="mb-2">
                        <p class="text-primary font-weight-bold mb-0">حالة الخدمة الإلكترونية</p>
                        <p>{{trans('admin/main.'.$service->status)}}</p>
                    </div>

                    <div class="mb-2">
                        <p class="text-primary font-weight-bold mb-0">منشئ الخدمة الإلكترونية</p>
                        <p>{{$service->createdBy->full_name??''}}</p>
                    </div>

                    <div class="mb-2">
                        <p class="text-primary font-weight-bold mb-0">تاريخ انشاء الخدمة الإلكترونية</p>
                        <p>{{$service->created_at}}</p>
                    </div>

                    <div class="mb-2">
                        <p class="text-primary font-weight-bold mb-0">تاريخ اخر تعديل للخدمة الإلكترونية</p>
                        <p>{{$service->updated_at}}</p>
                    </div>

                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">إغلاق</button>
            </section>
        </div>
    </div>
</div>








