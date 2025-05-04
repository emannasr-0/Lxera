@extends(getTemplate().'.layouts.app')

@section('content')
    @php
        $get404ErrorPageSettings = get404ErrorPageSettings();
    @endphp

    <section class="my-50 container text-center">
        <div class="row justify-content-md-center">
            <div class="col col-md-6">
                <img src="{{ $get404ErrorPageSettings['error_image'] ?? '' }}" class="img-cover " alt="">
            </div>
        </div>

        <h2 class="mt-25 font-36">Access Denied</h2>
        <p class="mt-25 font-16">This course is part of a paid Diploma, and it appears you haven't purchased it. please consider purchasing the Diploma.</p>
    </section>
@endsection
