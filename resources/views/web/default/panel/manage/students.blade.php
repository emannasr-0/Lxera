@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')

    <section>
        <h2 class=" js-font-resize section-title">{{ trans('quiz.students') }}</h2>

        <div class=" js-font-resize activities-container mt-25 p-20 p-lg-35">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/48.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $users->count() }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('quiz.students') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/49.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $activeCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('public.active') }}</span>
                    </div>
                </div>

                <div class=" js-font-resize col-4 d-flex align-items-center justify-content-center">
                    <div class=" js-font-resize d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/60.svg" width="64" height="64" alt="">
                        <strong class=" js-font-resize font-30 text-light font-weight-bold mt-5">{{ $inActiveCount }}</strong>
                        <span class=" js-font-resize font-16 text-gray font-weight-500">{{ trans('public.inactive') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class=" js-font-resize mt-35">
        <h2 class=" js-font-resize section-title">{{ trans('panel.filter_students') }}</h2>

        @include('web.default.panel.manage.filters')
    </section>

    <section class=" js-font-resize mt-35">
        <h2 class=" js-font-resize section-title">{{ trans('panel.students_list') }}</h2>

        @if(!empty($users) and !$users->isEmpty())
            <div class=" js-font-resize panel-section-card py-20 px-25 mt-20">
                <div class=" js-font-resize row">
                    <div class=" js-font-resize col-12 ">
                        <div class=" js-font-resize table-responsive">
                            <table class=" js-font-resize table custom-table text-center ">
                                <thead>
                                <tr>
                                    <th class=" js-font-resize text-left text-gray">{{ trans('auth.name') }}</th>
                                    <th class=" js-font-resize text-left text-gray">{{ trans('auth.email') }}</th>
                                    <th class=" js-font-resize text-center text-gray">{{ trans('public.phone') }}</th>
                                    <th class=" js-font-resize text-center text-gray">{{ trans('webinars.webinars') }}</th>
                                    <th class=" js-font-resize text-center text-gray">{{ trans('quiz.quizzes') }}</th>
                                    <th class=" js-font-resize text-center text-gray">{{ trans('panel.certificates') }}</th>
                                    <th class=" js-font-resize text-center text-gray">{{ trans('public.date') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($users as $user)

                                    <tr>
                                        <td class=" js-font-resize text-left">
                                            <div class=" js-font-resize user-inline-avatar d-flex align-items-center">
                                                <div class=" js-font-resize avatar bg-gray200">
                                                    <img src="{{ $user->getAvatar() }}" class=" js-font-resize img-cover" alt="">
                                                </div>
                                                <div class=" js-font-resize  ml-5">
                                                    <span class=" js-font-resize d-block text-light font-weight-500">{{ $user->full_name }}</span>
                                                    <span class=" js-font-resize mt-5 d-block font-12 text-{{ ($user->status == 'active') ? 'gray' : 'danger' }}">{{ ($user->status == 'active') ? trans('public.active') : trans('public.inactive') }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class=" js-font-resize text-left">
                                            <div class=" js-font-resize ">
                                                <span class=" js-font-resize d-block text-light font-weight-500">{{ $user->email }}</span>
                                                <span class=" js-font-resize mt-5 d-block font-12 text-gray">id : {{ $user->id }}</span>
                                            </div>
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            <span class=" js-font-resize text-light font-weight-500">{{ $user->mobile }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            <span class=" js-font-resize text-light font-weight-500">{{ count($user->getPurchasedCoursesIds()) }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            <span class=" js-font-resize text-light font-weight-500">{{ count($user->getActiveQuizzesResults()) }}</span>
                                        </td>
                                        <td class=" js-font-resize align-middle">
                                            <span class=" js-font-resize text-light font-weight-500">{{ count($user->certificates) }}</span>
                                        </td>
                                        <td class=" js-font-resize text-light font-weight-500 align-middle">{{ dateTimeFormat($user->created_at,'j M Y | H:i') }}</td>

                                        <td class=" js-font-resize text-right align-middle">
                                            <div class=" js-font-resize btn-group dropdown table-actions">
                                                <button type="button" class=" js-font-resize btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>
                                                <div class=" js-font-resize dropdown-menu">
                                                    <a href="{{ $user->getProfileUrl() }}" class=" js-font-resize btn-transparent webinar-actions d-block mt-10">{{ trans('public.profile') }}</a>
                                                    <a href="/panel/manage/students/{{ $user->id }}/edit" class=" js-font-resize btn-transparent webinar-actions d-block mt-10">{{ trans('public.edit') }}</a>
                                                    <a href="/panel/manage/students/{{ $user->id }}/delete" class=" js-font-resize webinar-actions d-block mt-10 delete-action">{{ trans('public.delete') }}</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        @else

            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'studentt.png',
                'title' => trans('panel.students_no_result'),
                'hint' =>  nl2br(trans('panel.students_no_result_hint')),
                'btn' => ['url' => '/panel/manage/students/new','text' => trans('panel.add_an_student')]
            ])
        @endif

    </section>

    <div class=" js-font-resize my-30">
        {{ $users->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
@endpush
