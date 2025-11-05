@extends('web.default.layouts.email')

@section('body')
    <td class="social-title pb-30"
        style="color:#000; font-family: 'IBM Plex Sans', sans-serif; font-size:14px; line-height:22px; text-align:left; padding-bottom:30px;">
        <div mc:edit="text_33" style="color: #333; direction: rtl !important; padding:20px;">

            <br><br>
            <p style="font-family: cairo, sans-serif; text-align: left; color: #000;">
                {{-- <b style="color:#CCF5FF"> عنوان البطاقة</b>: --}}
                {{-- {{ trans('auth.verify_your_email_address') }} --}}
                Verify Your Email Address
            </p>

            <div class="alert alert-success text-light" style="font-family: cairo, sans-serif; direction: rtl !important; text-align: left; color: #000;">
                <p  role="alert">
                    {{-- {{ trans('auth.verification_link_has_been_sent_to_your_email') }} --}}
                    New verification link has been sent to your email address.
                </p>
                <a href="{{ url('/reset-password/' . $token . '?email=' . $email) }}" style="color: #1024dd" >
                    {{-- {{ trans('auth.click_here') }} --}}
                    Click here
                </a>

            </div>
        </div>
    </td>
@endsection
