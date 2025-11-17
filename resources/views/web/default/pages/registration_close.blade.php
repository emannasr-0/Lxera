@extends(getTemplate() . '.layouts.app')

@section('content')
    <div class=" js-font-resize container">
        <div class=" js-font-resize row justify-content-center">
            <div class=" js-font-resize col-md-8 col-11">
                <div class=" js-font-resize d-flex justify-content-center align-items-center flex-column">

                    <img src="/store/1/close.png" alt="" class=" js-font-resize col-10 col-md-8">

                    <p class=" js-font-resize font-20">
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
