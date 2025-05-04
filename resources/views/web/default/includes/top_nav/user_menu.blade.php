<style>
.fill-black{
    fill:#818894;
}
.fill-brand{
    fill:#ED1088;
}
</style>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-WSVP27XBX1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-WSVP27XBX1');
</script>
@if(!empty($authUser))

    <div class="custom-dropdown navbar-auth-user-dropdown position-relative">
        <div class="custom-dropdown-toggle d-flex align-items-center navbar-user cursor-pointer">
            <img src="{{ asset('store/Acadima/acad-logo.webp') }}"
            class="rounded-circle" alt="{{ $authUser->full_name }}" style="max-width: 50px;">
        <span class="font-16 user-name ml-10 text-pink font-14">{{ $authUser->full_name }}</span>
        </div>

        <div class="custom-dropdown-body pb-10">

            <div class="dropdown-user-avatar d-flex align-items-center p-15 m-15 mb-10 rounded-sm border">
                <div class="size-40 rounded-circle position-relative">
                 {{--    <img src="{{ $authUser->getAvatar() }}" class="img-cover rounded-circle" alt="{{ $authUser->full_name }}"> --}}
                 <img src="{{ asset('store/Acadima/acad-logo.webp') }}"
                 class="img-cover rounded-circle" alt="{{ $authUser->full_name }}">

                </div>

                <div class="ml-5">
                    <div class="font-14 font-weight-bold text-dark">{{ $authUser->full_name }}</div>
                    <span class="mt-5 text-gray font-12">{{ $authUser->role->caption }}</span>
                </div>
            </div>

            <ul class="my-8">
                @if($authUser->isAdmin())
                    <li class="navbar-auth-user-dropdown-item">
                        <a href="{{ getAdminPanelUrl() }}" class="d-flex align-items-center w-100 px-15 py-10 text-gray font-14 bg-transparent">

                            @include('web.default.panel.includes.sidebar_icons.dashboard', ['class'=> "fill-white"])
                            <span class="ml-5">{{ trans('panel.dashboard') }}</span>
                        </a>
                    </li>


                    <li class="navbar-auth-user-dropdown-item">
                        <a href="{{ getAdminPanelUrl("/settings") }}" class="d-flex align-items-center w-100 px-15 py-10 text-gray font-14 bg-transparent">
                            {{--@include('web.default.panel.includes.sidebar_icons.settings', ['class'=> "fill-black"])--}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class= "fill-black">
                                <g id="Mask_Group_24" clip-path="url(#clip-path)" data-name="Mask Group 24" transform="translate(-25 -410)">
                                    <g id="settings" transform="translate(25 410)">
                                        <path id="Path_177" d="M12.753 24h-1.506a2.212 2.212 0 0 1-2.209-2.209v-.51a9.689 9.689 0 0 1-1.5-.625l-.361.361a2.209 2.209 0 0 1-3.125 0l-1.07-1.064a2.209 2.209 0 0 1 0-3.125l.361-.361a9.69 9.69 0 0 1-.625-1.5h-.51A2.212 2.212 0 0 1 0 12.753v-1.506a2.212 2.212 0 0 1 2.209-2.209h.51a9.692 9.692 0 0 1 .625-1.5l-.361-.361a2.209 2.209 0 0 1 0-3.125l1.064-1.07a2.209 2.209 0 0 1 3.125 0l.361.361a9.7 9.7 0 0 1 1.5-.625v-.51A2.212 2.212 0 0 1 11.247 0h1.506a2.212 2.212 0 0 1 2.209 2.209v.51a9.689 9.689 0 0 1 1.5.625l.361-.361a2.209 2.209 0 0 1 3.125 0l1.065 1.065a2.209 2.209 0 0 1 0 3.125l-.361.361a9.69 9.69 0 0 1 .625 1.5h.51A2.212 2.212 0 0 1 24 11.247v1.506a2.212 2.212 0 0 1-2.209 2.209h-.51a9.692 9.692 0 0 1-.625 1.5l.361.361a2.209 2.209 0 0 1 0 3.125l-1.065 1.065a2.209 2.209 0 0 1-3.125 0l-.361-.361a9.7 9.7 0 0 1-1.5.625v.51A2.212 2.212 0 0 1 12.753 24zm-4.985-4.82a8.288 8.288 0 0 0 2.148.892.7.7 0 0 1 .527.681v1.038a.8.8 0 0 0 .8.8h1.506a.8.8 0 0 0 .8-.8v-1.039a.7.7 0 0 1 .527-.681 8.288 8.288 0 0 0 2.148-.892.7.7 0 0 1 .855.108l.735.735a.8.8 0 0 0 1.135 0l1.065-1.065a.8.8 0 0 0 0-1.136l-.736-.736a.7.7 0 0 1-.108-.855 8.287 8.287 0 0 0 .892-2.148.7.7 0 0 1 .681-.527h1.038a.8.8 0 0 0 .8-.8v-1.508a.8.8 0 0 0-.8-.8h-1.028a.7.7 0 0 1-.681-.527 8.288 8.288 0 0 0-.892-2.148.7.7 0 0 1 .108-.855l.735-.735a.8.8 0 0 0 0-1.136l-1.065-1.069a.8.8 0 0 0-1.136 0l-.736.736a.7.7 0 0 1-.855.108 8.288 8.288 0 0 0-2.148-.892.7.7 0 0 1-.527-.681V2.209a.8.8 0 0 0-.8-.8h-1.509a.8.8 0 0 0-.8.8v1.039a.7.7 0 0 1-.527.681 8.288 8.288 0 0 0-2.148.892.7.7 0 0 1-.855-.108l-.735-.735a.8.8 0 0 0-1.135 0l-1.07 1.064a.8.8 0 0 0 0 1.136l.736.736a.7.7 0 0 1 .108.855 8.287 8.287 0 0 0-.892 2.148.7.7 0 0 1-.681.527H2.209a.8.8 0 0 0-.8.8v1.506a.8.8 0 0 0 .8.8h1.039a.7.7 0 0 1 .681.527 8.288 8.288 0 0 0 .892 2.148.7.7 0 0 1-.108.855l-.735.735a.8.8 0 0 0 0 1.136l1.065 1.065a.8.8 0 0 0 1.136 0l.736-.736a.706.706 0 0 1 .855-.108z" class ="@if (isset($class)) {{$class}} @else cls-3 @endif" data-name="Path 177"/>
                                        <path id="Path_178" d="M12 17.222A5.222 5.222 0 1 1 17.222 12 5.228 5.228 0 0 1 12 17.222zm0-9.038A3.816 3.816 0 1 0 15.816 12 3.82 3.82 0 0 0 12 8.184z" class ="@if (isset($class)) {{$class}} @else cls-3 @endif" data-name="Path 178"/>
                                    </g>
                                </g>
                            </svg>
                            <span class="ml-5">{{ trans('panel.settings') }}</span>
                        </a>
                    </li>
                @else
                    @can('show_panel')
                    <li class="navbar-auth-user-dropdown-item">
                        <a href="/panel" class="d-flex align-items-center w-100 px-15 py-10 text-gray font-14 bg-transparent">
                            @include('web.default.panel.includes.sidebar_icons.dashboard', ['class'=> "fill-black"])
                            <span class="ml-5 text-black">{{ trans('panel.dashboard') }}</span>
                        </a>
                    </li>
                    @endcan

                    @can('student_showClasses')
                    <li class="navbar-auth-user-dropdown-item">
                        <a href="{{ ($authUser->isUser()) ? '/panel/webinars/purchases' : '/panel/webinars' }}" class="d-flex align-items-center w-100 px-15 py-10 text-gray font-14 bg-transparent">
                            @include('web.default.panel.includes.sidebar_icons.studytable', ['class'=> "fill-black"])

                            <span class="ml-5 text-black" >{{ trans('panel.usermenu_webinars_tt') }}</span>
                        </a>
                    </li>
                    @endcan
                    @if(!$authUser->isUser())
                   {{-- @can('student_showFinance')
                        <li class="navbar-auth-user-dropdown-item">
                            <a href="/panel/financial/sales" class="d-flex align-items-center w-100 px-15 py-10 text-gray font-14 bg-transparent">
                            @include('web.default.panel.includes.sidebar_icons.requirements', ['class'=> "fill-black"])
                                <span class="ml-5 text-black">متطلبات القبول</span>
                            </a>
                        </li>
                    @endcan --}}
                    @endif
                   {{--  @if((\App\Student::where('user_id',$authUser->id)))
                       <li class="navbar-auth-user-dropdown-item">
                            <a href="/panel/financial/sales" class="d-flex align-items-center w-100 px-15 py-10 text-gray font-14 bg-transparent">
                            @include('web.default.panel.includes.sidebar_icons.requirements', ['class'=> "fill-black"])
                                <span class="ml-5 text-black">متطلبات القبول</span>
                            </a>
                        </li>
                    @endif --}}
                    @can('show_support')
                    <li class="navbar-auth-user-dropdown-item">
                        <a href="https://support.anasacademy.uk/" class="d-flex align-items-center w-100 px-15 py-10 text-gray font-14 bg-transparent">

                            @include('web.default.panel.includes.sidebar_icons.support', ['class'=> "fill-black"])
                            <span class="ml-5 text-black">{{ trans('panel.support_team') }} </span>
                        </a>
                    </li>
                    @endcan


                    <li class="navbar-auth-user-dropdown-item">
                        <a href="/panel/setting" class="d-flex align-items-center w-100 px-15 py-10 text-gray font-14 bg-transparent">
                             @include('web.default.panel.includes.sidebar_icons.setting', ['class'=> "fill-black"])
                            <span class="ml-5 text-black">{{ trans('panel.settings') }}</span>
                        </a>
                    </li>

                @endif

                <li class="navbar-auth-user-dropdown-item">
                    <a href="/logout" class="d-flex align-items-center w-100 px-15 py-10 text-gray font-14 bg-transparent">

                        @include("web.default.panel.includes.sidebar_icons.logout")
                       {{-- <img src="{{asset("assets/default/img/icons/user_menu/logout.svg")}}" alt=""> --}}
                      <span class="ml-5 text-danger">{{ trans('panel.sign_out') }}</span>
                    </a>
                </li>

            </ul>

        </div>
    </div>
@else
    <div class="d-flex align-items-center mr-md-50">
        <div class="custom-dropdown navbar-auth-user-dropdown position-relative mr-10">
              <a href="/login" class=" text-light font-14">
                <div class="custom-dropdown-toggle d-flex align-items-center navbar-user cursor-pointer">
                    <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip0_37_269)">
<path d="M20.1952 0H23.7188C23.7487 0.0872879 23.8255 0.0769512 23.8942 0.0838423C27.2356 0.372122 30.3696 1.33573 33.2272 3.09183C39.1065 6.70509 42.6313 11.9217 43.7581 18.7439C43.843 19.2562 43.8154 19.7856 43.9989 20.2818V23.8055C43.8693 23.856 43.9163 23.9732 43.906 24.0639C43.82 24.8024 43.7192 25.5386 43.577 26.2702C41.641 36.2199 32.9533 43.6497 22.8385 43.9874C18.3737 44.1367 14.2013 43.0846 10.4254 40.6945C4.71695 37.0801 1.32969 31.8968 0.238426 25.2182C0.157041 24.7186 0.192575 24.2006 0 23.7193V20.2818C0.0836785 20.2485 0.0779471 20.1738 0.0836785 20.1038C0.174235 19.0334 0.356493 17.9779 0.593774 16.9293C2.54245 8.30958 9.99442 1.45978 18.7428 0.240042C19.2277 0.172279 19.7321 0.205586 20.1952 0ZM33.7832 37.195C34.7484 36.4841 35.6207 35.6732 36.3864 34.7498C36.485 34.6476 36.5927 34.5511 36.6821 34.4408C40.7033 29.501 42.1453 23.8974 40.7331 17.6827C38.0118 5.70243 24.8892 -0.733908 13.6419 4.67335C5.1021 8.77933 0.887222 18.3695 3.54315 27.4601C4.33982 30.1844 5.67982 32.6204 7.58723 34.7348C8.37129 35.6525 9.24017 36.4806 10.2157 37.195C10.2799 37.2513 10.3383 37.3144 10.4071 37.3638C15.4255 41.0012 20.9437 42.1118 26.9605 40.5854C29.4709 39.9492 31.7531 38.8179 33.7821 37.195H33.7832Z" fill="#F7FAF6"/>
<path d="M33.783 37.195C33.8323 34.7315 33.2947 32.4126 31.9685 30.3234C29.7057 26.7607 26.4583 24.6968 22.2343 24.528C18.9639 24.397 16.1498 25.7408 13.8378 28.0425C11.4191 30.4498 10.2293 33.3923 10.2247 36.8091C10.2247 36.9378 10.2178 37.0664 10.2144 37.195C9.23887 36.4818 8.36999 35.6537 7.58594 34.7349C7.89314 32.7043 8.52245 30.7851 9.59537 29.0256C11.5257 25.8591 14.2435 23.67 17.7592 22.479C17.8268 22.456 17.8922 22.4273 18.0091 22.3802C15.1227 20.577 13.6142 18.0204 13.7736 14.6185C13.879 12.3582 14.8408 10.4482 16.5407 8.94478C20.0162 5.87247 25.2948 6.27331 28.3004 9.79583C31.4974 13.5423 30.75 19.6961 26.0525 22.355C31.8321 24.5349 35.4119 28.5535 36.3851 34.7487C35.6194 35.6721 34.7471 36.483 33.7819 37.1939L33.783 37.195ZM21.9821 20.6241C25.0358 20.6241 27.5014 18.1594 27.498 15.1101C27.4945 12.0871 25.0335 9.63389 21.9935 9.627C18.9754 9.61896 16.4948 12.0998 16.5006 15.1181C16.5063 18.1559 18.9639 20.6241 21.9821 20.6241Z" fill="#E23285"/>
</g>
<defs>
<clipPath id="clip0_37_269">
<rect width="44" height="44" fill="white"/>
</clipPath>
</defs>
</svg>

                    <span class="font-16 user-name ml-10 text-dark-blue font-14"> {{ trans('auth.login') }}</span>
                </div>
             </a>
        </div>
    </div>



@endif
