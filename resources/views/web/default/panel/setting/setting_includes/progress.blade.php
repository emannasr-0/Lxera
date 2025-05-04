@php
    $progressSteps = [
        1 => [
            'lang' => 'public.basic_information',
            'icon' => 'basic-info',
        ],

        2 => [
            'lang' => 'public.images',
            'icon' => 'images',
        ],
    ];

    if ($user->student) {

        $progressSteps[3] = [
            'lang' => 'public.personal_information',
            'icon' => 'about',
        ];

        $progressSteps[4] = [
            'lang' => 'public.educations',
            'icon' => 'graduate',
        ];

        $progressSteps[5] = [
            'lang' => 'public.extra_information',
            'icon' => 'extra_info',
        ];
        $progressSteps[6] = [
            'lang' => 'public.relatives_information',
            'icon' => 'basic-info',
        ];


        $progressSteps[7] = [
            'lang' => 'public.experiences',
            'icon' => 'experiences',
        ];
        $progressSteps[8] = [
            'lang' => 'public.work_links',
            'icon' => 'links',
        ];
        $progressSteps[9] = [
            'lang' => 'public.known_people',
            'icon' => 'experiences',
        ];
    }

    // $progressSteps[10] = [
    //     'lang' => 'public.occupations',
    //     'icon' => 'skills',
    // ];

    // $progressSteps[11] = [
    //     'lang' => 'public.identity_and_financial',
    //     'icon' => 'financial',
    // ];

    // $progressSteps[12] = [
    //     'lang' => 'public.zoom_api',
    //     'icon' => 'zoom'
    // ];

    $currentStep = empty($currentStep) ? 1 : $currentStep;
@endphp


<div class="webinar-progress d-block d-lg-flex align-items-center p-15 panel-shadow bg-secondary-acadima rounded-sm">

    @foreach ($progressSteps as $key => $step)
        <div class="progress-item d-flex align-items-center">
            <a href="@if (!empty($organization_id)) /panel/manage/{{ $user_type ?? 'instructors' }}/{{ $user->id }}/edit/step/{{ $key }} @else /panel/setting/step/{{ $key }} @endif"
                class="progress-icon p-10 d-flex align-items-center justify-content-center rounded-circle {{ $key == $currentStep ? 'active' : '' }}"
                data-toggle="tooltip" data-placement="top" title="{{ trans($step['lang']) }}">
                <img src="/assets/default/img/icons/{{ $step['icon'] }}.svg" class="img-cover" alt="">
            </a>

            <div class="ml-10 {{ $key == $currentStep ? '' : 'd-lg-none' }}">
                <h4 class="font-16 text-secondary font-weight-bold">{{ trans($step['lang']) }}</h4>
            </div>
        </div>
    @endforeach
</div>
