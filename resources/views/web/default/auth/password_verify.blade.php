@extends('web.default.layouts.email')

@section('body')
    <td class="social-title pb-30"
        style="color:#000; font-family: 'IBM Plex Sans', sans-serif; font-size:14px; line-height:22px; text-align:right; padding-bottom:30px;">
        <div mc:edit="text_33" style="color: #333; direction: rtl !important; padding-right:20px;">

            <br><br>
            <p style="font-family: cairo, sans-serif; text-align: right; color: #000;">
                {{-- <b style="color:#CCF5FF"> عنوان البطاقة</b>: --}}
                {{ trans('auth.verify_your_email_address') }}
            </p>

            <div class="alert alert-success text-light" style="font-family: cairo, sans-serif; direction: rtl !important; text-align: right; color: #000;">
                <p  role="alert">
                    {{ trans('auth.verification_link_has_been_sent_to_your_email') }}
                </p>
                <a href="{{ url('/reset-password/' . $token . '?email=' . $email) }}" style="color: #c14b93" >{{ trans('auth.click_here') }}</a>

            </div>
        </div>
    </td>
@endsection
