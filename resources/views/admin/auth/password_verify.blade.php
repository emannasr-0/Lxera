@extends('web.default.layouts.email')

@section('body')
    <!-- content -->
    <td valign="top" class="social-title pb-30" mc:edit="body_content"
        style="color:#ffffff; font-family: 'IBM Plex Sans', sans-serif; font-size:14px; line-height:22px; text-align:left; padding-bottom:30px;">
        <div class="container" style="color: #333; direction: rtl !important; padding:20px;">
            <div class="row justify-content-center ">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header mt-20" style="font-family: cairo, sans-serif; text-align: left; color: #000;">
                            {{-- {{ trans('auth.verify_your_email_address') }} --}}
                            Verify Your Email Address
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success text-dark" role="alert" style="font-family: cairo, sans-serif; direction: rtl !important; text-align: left; color: #000;">
                                {{-- {{ trans('auth.verification_link_has_been_sent_to_your_email') }} --}}
New verification link has been sent to your email address
                            </div>
                            <a href="{{ url(getAdminPanelUrl('/reset-password/'.$token.'?email='.$email)) }}" style="color: #1024dd">
                                {{-- {{ trans('auth.click_here') }} --}}
                                Click here
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </td>
@endsection
