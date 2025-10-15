@extends(getTemplate() . '.layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-11">
                <div class="d-flex justify-content-center align-items-center flex-column">

                    <img src="/store/1/close.png" alt="" class="col-10 col-md-8">

                    <p class="font-20">
                        @if (isset($message))
                            {{ $message }}
                        @else
                            التسجيل مغلق حاليا ترقب حتي يتم فتحه مجددا.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
