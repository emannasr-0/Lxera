<div class=" js-font-resize tab-pane mt-3 fade active show" id="become_instructor" role="tabpanel" aria-labelledby="become_instructor-tab">
    <div class=" js-font-resize row">
        <div class=" js-font-resize col-12">
            <table class=" js-font-resize table">
                <tr>
                    <td class=" js-font-resize text-left">{{ trans('admin/main.role') }}</td>
                    <td class=" js-font-resize text-left">{{ trans('site.extra_information') }}</td>
                    <td class=" js-font-resize text-center">{{ trans('public.certificate_and_documents') }}</td>
                </tr>

                <tr>
                    <td class=" js-font-resize text-left">{{ $becomeInstructor->role }}</td>
                    <td width="50%" class=" js-font-resize text-left">{{ $becomeInstructor->description ?? '-' }}</td>
                    <td class=" js-font-resize text-center">
                        @if(!empty($becomeInstructor->certificate))
                            <a href="{{ (strpos($becomeInstructor->certificate,'http') != false) ? $becomeInstructor->certificate : url($becomeInstructor->certificate) }}" target="_blank" class=" js-font-resize btn btn-sm btn-success">{{ trans('admin/main.show') }}</a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </table>


            @include('admin.includes.delete_button',[
                             'url' => getAdminPanelUrl().'/users/become_instructors/'. $becomeInstructor->id .'/reject',
                             'btnClass' => 'mt-3 btn btn-danger',
                             'btnText' => trans('admin/main.reject_request'),
                             'hideDefaultClass' => true
                             ])

            @include('admin.includes.delete_button',[
                             'url' => getAdminPanelUrl("/users/{$user->id}/acceptRequestToInstructor"),
                             'btnClass' => 'btn btn-success ml-1 mt-3',
                             'btnText' => trans('admin/main.accept_request'),
                             'hideDefaultClass' => true
                             ])
        </div>
    </div>
</div>
