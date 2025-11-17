@extends(getTemplate().'.layouts.app')

@section('content')
    @php
        $get404ErrorPageSettings = get404ErrorPageSettings();
    @endphp

    <section class=" js-font-resize my-50 container text-center">
        <div class=" js-font-resize row justify-content-md-center">
            <div class=" js-font-resize col col-md-6">
                <img src="{{ $get404ErrorPageSettings['error_image'] ?? '' }}" class=" js-font-resize img-cover " alt="">
            </div>
        </div>

        <h2 class=" js-font-resize mt-25 font-36">Access Denied</h2>
        <p class=" js-font-resize mt-25 font-16">This course is part of a paid Diploma, and it appears you haven't purchased it. please consider purchasing the Diploma.</p>
    </section>
@endsection
