@extends('web.default.layouts.email')

@section('body')
    <!-- content -->
    <td valign="top" class="social-title pb-30" mc:edit="body_content"
        style="color:#ffffff; font-family: 'IBM Plex Sans', sans-serif; font-size:14px; line-height:22px; text-align:right; padding-bottom:30px;">
        <div class="container" style="color: #333; direction: rtl !important; padding:20px;">
            <div class="row justify-content-center ">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header mt-20" style="font-family: cairo, sans-serif; text-align: right; color: #fff;">{{ trans('auth.verify_your_email_address') }}</div>
                        <div class="card-body">
                            <div class="alert alert-success text-light" role="alert" style="font-family: cairo, sans-serif; direction: rtl !important; text-align: right; color: #fff;">
                                {{ trans('auth.verification_link_has_been_sent_to_your_email') }}
                            </div>
                            <a href="{{ url(getAdminPanelUrl('/reset-password/'.$token.'?email='.$email)) }}" style="color: #CCF5FF">{{ trans('auth.click_here') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </td>
@endsection
