@extends('web.default.layouts.email')

@section('body')
    <!-- content -->
    <td valign="top" class=" js-font-resize bodyContent" mc:edit="body_content">
        <h1 class=" js-font-resize h1">{{ $confirm['title'] }}</h1>
        <p>{!! nl2br($confirm['message']) !!}</p>

        <p class=" js-font-resize code">{{ $confirm['code'] }}</p>

        <p>
            {{-- {{ trans('notification.email_ignore_msg') }} --}}
            If you didnt submit this request, please ignore it.
        </p>
    </td>
    <td class=" js-font-resize social-title pb30"
        style="color:#000; font-family: 'IBM Plex Sans', sans-serif; font-size:14px; line-height:22px; text-align:left; padding-bottom:30px;">
        <div mc:edit="text_33" style="color: #333; direction: rtl !important; padding:20px;">

            <br><br>
            <p style="font-family: cairo, sans-serif; text-align: left;">
                <b style="color:#1024dd"> Title </b>:
                {{ $confirm['title'] }}
            </p>
            <p style="font-family: cairo, sans-serif; text-align: left;">
                Hello {{ $confirm['name'] }},
            </p>
            <p style="font-family: cairo, sans-serif; direction: rtl !important; text-align: left;">
                {!! nl2br($confirm['message']) !!}
            </p>
            <p class=" js-font-resize code" style="text-align: center; font-size: 14px; font-weight: bold; color: #333;">{{ $confirm['code'] }}</p>
        </div>
    </td>
@endsection
