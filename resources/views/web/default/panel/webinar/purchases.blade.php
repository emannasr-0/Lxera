@extends(getTemplate() . '.panel.layouts.panel_layout')

@push('styles_top')
@endpush
@php
    $totalWebinars = 0;
    if (!empty($sales) and !$sales->isEmpty()) {
        foreach ($sales as $sale) {
            $item = $sale->bundle;
            if (!empty($item) and !empty($item->bundleWebinars) and !$item->bundleWebinars->isEmpty()) {
                $totalWebinars += $item->bundleWebinars->count();
            }
        }
    }

@endphp
@section('content')
    {{-- <section>
        <h2 class="section-title">{{ trans('panel.my_activity') }}</h2>

        <div class="activities-container mt-25 p-20 p-lg-35">
           <div class="row">
               <div class="col-12 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
<svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip0_78_2397)">
<path d="M55.994 10.3885C55.9895 7.84927 55.0058 5.45802 53.3866 3.61078C51.7659 1.76354 49.5158 0.472271 46.9936 0.119562C46.4464 0.0433413 45.8857 0.0239125 45.3041 0.0239125H45.0335C38.4492 0.0239125 31.8618 0.0403523 25.2745 0.0403523C20.4079 0.0403523 15.5414 0.0313851 10.6749 0H10.6076C8.19457 0 5.79795 0.856365 3.89321 2.38078C1.98697 3.9067 0.572619 6.08722 0.0882102 8.75495C0.0598035 8.91038 0.0284067 9.05535 0 9.19285V29.8756C0.168945 30.871 0.430585 31.8155 0.811833 32.6988C1.19757 33.597 1.7044 34.4339 2.36074 35.2021C3.43123 36.4531 4.6258 37.4036 5.95493 38.0477C7.28556 38.6904 8.75523 39.0296 10.4013 39.0326C16.2187 39.0401 22.0361 39.0431 27.8535 39.0431C33.6709 39.0431 39.6736 39.0401 45.5837 39.0356C48.1508 39.0326 50.5519 38.0567 52.4028 36.4381C54.2537 34.8195 55.5455 32.5673 55.8998 30.0266C55.9641 29.5722 55.997 29.1254 55.997 28.6725V28.1868C55.997 24.6523 56 21.1192 56 17.5861C56 15.1859 55.9985 12.7872 55.994 10.3885ZM52.7706 28.3602C52.7616 30.4764 51.9468 32.3536 50.5878 33.6882C49.2302 35.0243 47.333 35.8149 45.204 35.8179C42.8911 35.8239 40.5797 35.8254 38.2683 35.8254C34.8355 35.8254 31.4043 35.8224 27.9731 35.8224H18.4404C15.9122 35.8224 13.384 35.8224 10.8543 35.8224C10.0036 35.8209 9.17087 35.7148 8.371 35.4697C7.57262 35.226 6.80863 34.8434 6.10444 34.3024C5.14908 33.5716 4.41499 32.6928 3.9261 31.696C3.43571 30.6991 3.19351 29.5887 3.18753 28.408C3.16959 25.3457 3.16061 22.2834 3.16061 19.2211C3.16061 16.3726 3.16809 13.5225 3.18454 10.6739C3.195 8.59205 3.99786 6.72837 5.33148 5.39525C6.66361 4.06064 8.52649 3.25508 10.6121 3.25209C16.425 3.23864 22.2379 3.23117 28.0508 3.23117C33.8637 3.23117 39.5854 3.23864 45.3535 3.25359C47.4391 3.25658 49.299 4.06213 50.6282 5.39973C51.9603 6.73435 52.7601 8.59952 52.7706 10.6844C52.7856 13.6017 52.7945 16.5205 52.7945 19.4378C52.7945 22.3552 52.7856 25.3876 52.7706 28.3617V28.3602Z" fill="black"/>
<path d="M38.0679 49.1775C37.887 47.8234 37.2845 46.6308 36.383 45.7117C35.4799 44.794 34.2764 44.1514 32.8844 43.9152C32.5824 43.8629 32.2759 43.839 31.9709 43.839C30.8631 43.8375 29.7567 44.1693 28.8014 44.7686C27.8475 45.3664 27.0476 46.2288 26.5438 47.2824C26.4511 47.4767 26.3434 47.6546 26.2029 47.813C26.0639 47.9714 25.8874 48.1044 25.6961 48.1926C25.569 48.2524 25.4389 48.2927 25.3088 48.3181C25.1369 48.3525 24.968 48.363 24.796 48.363H24.7885C17.186 48.3465 9.58202 48.3674 1.97501 48.3376H1.95707C1.54443 48.3376 1.21102 48.3944 0.94639 48.499C0.68176 48.6051 0.476933 48.75 0.288552 48.9742C0.185391 49.0998 0.0882102 49.2507 0 49.436V50.4852C0.143528 50.7811 0.304998 50.9903 0.484408 51.1458C0.689235 51.3191 0.923964 51.4372 1.23644 51.5104C1.44276 51.5583 1.68496 51.5837 1.96006 51.5837H1.978C9.60295 51.5538 17.2234 51.5732 24.8439 51.5568H24.8618C25.0517 51.5568 25.2371 51.5717 25.427 51.621C25.6138 51.6689 25.8037 51.7541 25.9667 51.8781C26.0743 51.9603 26.17 52.0559 26.2507 52.1576C26.3599 52.2936 26.4466 52.4415 26.5214 52.5985C26.9833 53.555 27.5903 54.2843 28.3394 54.8417C29.0735 55.3873 29.9481 55.7699 30.9663 56H33.034C33.2612 55.9402 33.481 55.8819 33.6933 55.8222C35.0045 55.447 36.1108 54.649 36.8898 53.6073C37.6702 52.5656 38.1202 51.2848 38.1188 49.9561C38.1188 49.6991 38.1023 49.439 38.0679 49.1775ZM34.8565 50.0727C34.8236 50.6406 34.6202 51.1637 34.3108 51.5971C33.9998 52.029 33.5827 52.3758 33.0953 52.591C32.7693 52.733 32.412 52.8152 32.0412 52.8152C31.9919 52.8152 31.941 52.8137 31.8917 52.8107C31.3236 52.7763 30.8003 52.5731 30.3667 52.2637C29.9346 51.9543 29.5878 51.5359 29.374 51.0486C29.2305 50.7243 29.1497 50.3671 29.1497 49.9965C29.1497 49.9457 29.1512 49.8964 29.1542 49.8455C29.1871 49.2791 29.3904 48.7545 29.6999 48.3226C30.0109 47.8892 30.428 47.5425 30.9154 47.3288C31.2413 47.1853 31.5987 47.1031 31.9695 47.1031C32.0188 47.1031 32.0696 47.1046 32.119 47.1076C32.6871 47.1419 33.2104 47.3452 33.644 47.6546C34.076 47.9654 34.4229 48.3824 34.6367 48.8711C34.7802 49.1954 34.861 49.5526 34.861 49.9247C34.861 49.9741 34.861 50.0234 34.8565 50.0727Z" fill="black"/>
<path d="M55.9955 49.9696V49.983C55.9895 50.4643 55.8176 50.8394 55.5365 51.1144C55.2525 51.3864 54.8488 51.5627 54.3285 51.5657C52.8528 51.5687 51.3757 51.5702 49.9 51.5702C48.4244 51.5702 46.9637 51.5687 45.4955 51.5657C44.9648 51.5627 44.5626 51.3849 44.2815 51.1114C44.0034 50.8334 43.8315 50.4508 43.8285 49.9502V49.9367C43.83 49.6946 43.8763 49.4824 43.9541 49.2941C44.0722 49.0146 44.2636 48.7889 44.5222 48.6245C44.7794 48.4601 45.1068 48.357 45.5015 48.3525H49.9105V47.68L49.912 48.3525H49.9778C50.8479 48.3525 51.7181 48.351 52.5867 48.351C53.1668 48.351 53.7469 48.351 54.3255 48.354C54.5916 48.3555 54.8264 48.4033 55.0282 48.4825C55.3317 48.6021 55.5649 48.7919 55.7309 49.0385C55.8953 49.2851 55.994 49.5945 55.9955 49.9696Z" fill="black"/>
<path d="M46.9996 14.1726C46.7649 12.7857 46.1354 11.5975 45.2294 10.7023C44.3234 9.8071 43.1408 9.20182 41.7787 9.00155C41.4932 8.95821 41.2091 8.93878 40.9265 8.93878C38.6316 8.93878 36.4652 10.2973 35.4097 12.5406C35.3394 12.69 35.2557 12.832 35.145 12.9635C35.0344 13.0951 34.8924 13.2116 34.7354 13.2923C34.6292 13.3476 34.5201 13.3865 34.411 13.4119C34.2659 13.4448 34.1254 13.4567 33.9878 13.4567H33.928C33.496 13.4463 33.0728 13.4313 32.6617 13.4313C32.2819 13.4313 31.9142 13.4433 31.5598 13.4836C29.0017 13.7706 26.7711 14.5283 24.8394 15.7508C22.9077 16.9749 21.2691 18.6697 19.9161 20.8621C19.8578 20.9578 19.8069 21.0415 19.7516 21.1222C19.6963 21.2044 19.641 21.2866 19.5378 21.3837C19.469 21.448 19.3704 21.5227 19.2298 21.5705C19.1506 21.5989 19.0594 21.6139 18.9727 21.6139C18.8934 21.6139 18.8172 21.6019 18.7529 21.5825C18.6886 21.5646 18.6333 21.5406 18.5869 21.5167C18.4942 21.4689 18.43 21.4196 18.3731 21.3733C18.261 21.2806 18.1728 21.1894 18.0592 21.0773C17.0096 20.0372 15.8719 19.254 14.6354 18.7265C13.399 18.2004 12.0594 17.9269 10.5897 17.9269C10.5449 17.9269 10.5 17.9269 10.4552 17.9269C10.0156 17.9329 9.64183 18.1032 9.36524 18.3797C9.09015 18.6577 8.91971 19.0373 8.91223 19.4737V19.5036C8.91223 19.9714 9.0752 20.357 9.33833 20.6424C9.60147 20.9264 9.96627 21.1102 10.4163 21.1416C10.9186 21.1745 11.4584 21.2104 12.0115 21.3314C13.713 21.705 15.1453 22.5764 16.1589 23.8153C17.1756 25.0513 17.7706 26.6445 17.8603 28.4289C17.8813 28.8429 17.9949 29.1732 18.1608 29.4258C18.3283 29.6769 18.5466 29.8562 18.8262 29.9683C19.013 30.043 19.2283 30.0849 19.4765 30.0849H19.487C19.8533 30.0834 20.1478 29.9877 20.387 29.8263C20.6247 29.6649 20.8131 29.4362 20.9372 29.1284C21.0194 28.9221 21.0718 28.6815 21.0822 28.408C21.1376 26.8896 21.4142 25.4444 21.9494 24.0963C22.4831 22.7482 23.277 21.4988 24.346 20.3779C25.5914 19.0687 27.0058 18.1271 28.5472 17.5234C30.0872 16.9181 31.7497 16.6476 33.5004 16.6476C33.5707 16.6476 33.641 16.6476 33.7112 16.6491C33.9071 16.652 34.0985 16.6655 34.2943 16.7088C34.4902 16.7537 34.692 16.8329 34.8729 16.9584C34.9925 17.0421 35.1002 17.1423 35.1944 17.2528C35.3185 17.4023 35.4186 17.5667 35.5054 17.7475C36.0182 18.8131 36.8106 19.665 37.7599 20.2524C38.7093 20.8397 39.8157 21.161 40.9505 21.161C41.402 21.161 41.8595 21.1102 42.3125 21.0056C43.7059 20.6828 44.8855 19.9101 45.7228 18.861C46.557 17.8133 47.0489 16.4951 47.0609 15.0708C47.04 14.7121 47.0414 14.4073 46.9996 14.1711V14.1726ZM43.3157 16.6296C43.0077 17.075 42.5816 17.4367 42.0807 17.6638C41.7473 17.8148 41.3781 17.9045 40.9923 17.9119H40.946C40.3704 17.9119 39.8306 17.7206 39.3836 17.4128C38.9381 17.1049 38.5763 16.6789 38.3505 16.1783C38.1995 15.8435 38.1098 15.4744 38.1023 15.0888V15.0439C38.1023 14.4685 38.2937 13.929 38.6002 13.4821C38.9082 13.0368 39.3328 12.6751 39.8351 12.4479C40.17 12.297 40.5393 12.2073 40.925 12.1998H40.9714C41.547 12.1998 42.0867 12.3911 42.5323 12.6975C42.9793 13.0054 43.3396 13.4313 43.5669 13.9335C43.7179 14.2668 43.8076 14.6359 43.8136 15.023C43.815 15.038 43.8151 15.0529 43.8151 15.0693C43.8136 15.6447 43.6222 16.1828 43.3157 16.6296Z" fill="black"/>
</g>
<defs>
<clipPath id="clip0_78_2397">
<rect width="56" height="56" fill="white"/>
</clipPath>
</defs>
</svg>
                        <strong class="font-30 text-light font-weight-bold mt-5"> {{ $totalWebinars }}</strong>
                        <span class="font-16 text-gray font-weight-500">إجمالي عدد المقررات</span>
                    </div>
                </div>

                 <div class="col-4 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/hours.svg" width="64" height="64" alt="">
                        <strong class="font-30 text-light font-weight-bold mt-5">{{ convertMinutesToHourAndMinute($hours) }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('home.hours') }}</span>
                    </div>
                </div>

                <div class="col-4 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/upcoming.svg" width="64" height="64" alt="">
                        <strong class="font-30 text-light font-weight-bold mt-5">{{ $upComing }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('panel.upcoming') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section> --}}

    <section class="mt-25">
        @if (!empty($sales) and !$sales->isEmpty())
            <div
                class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row mt-80">
                <h2 class="section-title">الدورات المسجلة </h2>
            </div>
            @foreach ($sales as $sale)
                @php
                    $item = !empty($sale->webinar) ? $sale->webinar : $sale->bundle;

                    $lastSession = !empty($sale->webinar) ? $sale->webinar->lastSession() : null;
                    $nextSession = !empty($sale->webinar) ? $sale->webinar->nextSession() : null;
                    $isProgressing = false;

                    if (
                        !empty($sale->webinar) and
                        $sale->webinar->start_date <= time() and
                        !empty($lastSession) and
                        $lastSession->date > time()
                    ) {
                        $isProgressing = true;
                    }

                @endphp

                @if (!empty($item))
                    <section class="mb-80">
                        <div class="d-flex justify-content-between align-items-center mt-30">
                            <h2 class="section-title after-line">{{ trans('product.course') }}
                                {{ $item->title }}</h2>
                        </div>

                        @if ($sale->webinar->start_date > time())
                            @include(getTemplate() . '.includes.no-result', [
                                'file_name' => 'student.png',
                                'title' => 'لم يتم بدأ المقررات بعد',
                                'hint' => '',
                                'btn' => [
                                    'url' => '/classes?sort=newest',
                                    'text' => trans('panel.start_learning'),
                                ],
                            ])
                        @elseif (!$sale->webinar->isUserHasAccessToContent())
                            <p class="text-center alert alert-warning" style="margin-top: 20px !important">
                                الوصول إلى محتوى الدورة غير متاح حاليًا
                            </p>
                        @else
                            <div class="row mt-10">
                                <div class="col-12">

                                    <div class="table-responsive">
                                        <table class="table table-striped text-center font-14">

                                            <tr>
                                                <th>ID</th>
                                                <th>اسم المقرر</th>
                                                <th class="text-left">{{ trans('public.instructor') }}</th>
                                                <th>{{ trans('public.start_date') }}</th>
                                                <th>المهام</th>
                                                <th>عدد التسليمات</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                            @php
                                                $totalHours = 0;

                                                $totalHours += $item->duration;
                                            @endphp

                                            @if (!empty($item->title))
                                                <tr>
                                                    <td>{{ $loop->index + 1 }}</td>
                                                    <th>{{ $item->title }}</th>

                                                    <td class="text-left">
                                                        {{ $item->teacher->full_name }}</td>
                                                    <td>{{ dateTimeFormat($item->start_date, 'j F Y ') }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            // dd($bundleitem->item->assignments);
                                                        @endphp
                                                        @if (!empty($item->assignments[0]))
                                                            <button
                                                                type="button"style="width: 110px; height: 50px; border: 1px solid #dc3545; color: #dc3545; background-color: transparent; border-radius: 10px;"
                                                                disabled> يوجد مهام </button>
                                                        @else
                                                            <button
                                                                type="button"style="width: 110px; height: 50px; border: 1px solid #28a745; color: #28a745; background-color: transparent; border-radius: 10px;"
                                                                disabled>لا يوجد مهام بعد</button>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @php
                                                            $assignmentCount = $item->assignments->count();
                                                            $assignmentHistoryCount = 0;
                                                        @endphp

                                                        @foreach ($item->assignments as $assignment)
                                                            {{-- @dump($assignment->assignmentHistory->status ) --}}
                                                            {{-- @dump($assignment->assignmentHistory->status=="not_submitted") --}}
                                                            @if ($assignment->assignmentHistory)
                                                                @php
                                                                    $assignmentHistoryCount++;
                                                                @endphp
                                                            @endif
                                                        @endforeach

                                                        @php
                                                            $remainingAssignments =
                                                                $assignmentCount - $assignmentHistoryCount;
                                                        @endphp

                                                        @if ($remainingAssignments > 0)
                                                            <button type="button"
                                                                style="width: 110px; height: 50px; border: 1px solid #dc3545; color: #dc3545; background-color: transparent; border-radius: 10px;"
                                                                disabled>{{ $remainingAssignments }}</button>
                                                        @else
                                                            <button type="button"
                                                                style="width: 110px; height: 50px; border: 1px solid #28a745; color: #28a745; background-color: transparent; border-radius: 10px;"
                                                                disabled>لا يوجد تسليمات</button>
                                                        @endif
                                                    </td>



                                                    <td>
                                                        @if ($item->duration != 0)
                                                            {{-- @if ($item->video_demo)
                                                            <a target="_blank" rel="noopener noreferrer"
                                                                class="btn btn-primary" style="width:190px;height:50px"
                                                                href="{{ $item->video_demo }}">اضغط هنا للذهاب
                                                                للمحاضرا</a>
                                                        @else
                                                            <button class="btn btn-primary"
                                                                style="width:190px;height:50px; background-color: #808080;"
                                                                disabled>اضغط هنا للذهاب للمحاضرا</button>
                                                        @endif --}}

                                                            <a class="btn btn-primary"
                                                                href="{{ url('/course/learning/' . $item->id) }}"
                                                                target="_blank" rel="noopener noreferrer">المحاضره
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            <tr>
                                                <th colspan="7">إجمالي عدد الساعات:
                                                    {{ $totalHours }}</th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </section>
                @endif
            @endforeach
        @else
            @include(getTemplate() . '.includes.no-result', [
                'file_name' => 'student.png',
                'title' => 'لم يتم بدأ المقررات بعد',
                'hint' => '',
                'btn' => ['url' => '/classes?sort=newest', 'text' => trans('panel.start_learning')],
            ])
        @endif
    </section>

    <div class="my-30">
        {{ $sales->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>

    @include('web.default.panel.webinar.join_webinar_modal')
@endsection

@push('scripts_bottom')
    <script>
        var undefinedActiveSessionLang = '{{ trans('webinars.undefined_active_session') }}';
    </script>

    <script src="/assets/default/js/panel/join_webinar.min.js"></script>
@endpush
