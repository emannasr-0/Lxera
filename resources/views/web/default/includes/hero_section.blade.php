<style>
    .form-title {
        font-family: sans-serif !important;
        font-style: normal;
        font-weight: 700;
        /* font-size: 36px; */
        line-height: 42px;
        color: #fff;
    }

    .hero {
        width: 100%;
        height: 50vh;
        /* background-color: #ED1088; */
        background-image:url('{{ asset('store/Acadima/Header005.png') }}');
        background-color:#fff;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        display: flex;
        flex-direction: column;
        flex-wrap: nowrap;
        justify-content: center;
        align-items: stretch;
    }

    @media(max-width:768px) {
        .hero {
            height: 50vh;
        }
    }
</style>

<header class="hero cart-banner position-relative">
    <section class="container hero-title ">
        {!! $inner !!}
    </section>
</header>
