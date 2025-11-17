@extends(getTemplate().'.layouts.app')

@section('content')
    <section class=" js-font-resize cart-banner position-relative text-center">
        <div class=" js-font-resize container h-100">
            <div class=" js-font-resize row h-100 align-items-center justify-content-center text-center">
                <div class=" js-font-resize col-12 col-md-9 col-lg-7">
                    <h1 class=" js-font-resize font-30 text-white font-weight-bold">{{ $page->title }}</h1>
                </div>
            </div>
        </div>
    </section>

    <section class=" js-font-resize container mt-10 mt-md-40">
        <div class=" js-font-resize row">
            <div class=" js-font-resize col-12">
                <div class=" js-font-resize post-show mt-30">
                    {!! nl2br($page->content) !!}
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
