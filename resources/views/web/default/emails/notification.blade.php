@extends('web.default.layouts.email')

@section('body')
    <td class="social-title pb30"
        style="color:#ffffff; font-family: 'IBM Plex Sans', sans-serif; font-size:14px; line-height:22px; text-align:right; padding-bottom:30px;">
        <div mc:edit="text_33" style="color: #333; direction: rtl !important;">

            <br><br>
            <p style="font-family: cairo, sans-serif; text-align: right;">
                {{ $notification['title'] }}
            </p>

            <p style="font-family: cairo, sans-serif; direction: rtl !important; text-align: right;">
                {!! $notification['message'] !!}
            </p>
        </div>
    </td>
@endsection
