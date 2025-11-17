@extends('web.default.layouts.email')

@section('body')
    <td class=" js-font-resize social-title pb30"
        style="color:#000; font-family: 'IBM Plex Sans', sans-serif; font-size:14px; line-height:22px; text-align:left; padding-bottom:30px;">
        <div mc:edit="text_33" style="color: #333; direction: rtl !important; padding:20px;">

            <br><br>
            <p style="font-family: cairo, sans-serif; text-align: left;">
                {{ $notification['title'] }}
            </p>

            <p style="font-family: cairo, sans-serif; direction: rtl !important; text-align: left;">
                {!! $notification['message'] !!}
            </p>
        </div>
    </td>
@endsection
