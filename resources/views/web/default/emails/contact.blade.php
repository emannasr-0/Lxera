@extends('web.default.layouts.email')

@section('body')
    <!-- content -->
    <td class="social-title pb30"
        style="color:#000; font-family: 'IBM Plex Sans', sans-serif; font-size:14px; line-height:22px; text-align:left; padding-bottom:30px;">
        <div mc:edit="text_33" style="color: #333; direction: rtl !important;">

            <br><br>
            <p style="font-family: cairo, sans-serif; text-align: left;">
                <b style="color:#1024dd"> Title </b>:
                {{ $contact->subject }}
            </p>
            <p style="font-family: cairo, sans-serif; text-align: left;">
                {{-- {{ trans('admin/main.user_name') }} --}}
                Username
                 : {{ $contact->name }},
            </p>
            <p style="font-family: cairo, sans-serif; direction: rtl !important; text-align: left;">
                {!! nl2br($contact->reply) !!}
            </p>
        </div>
    </td>
@endsection
