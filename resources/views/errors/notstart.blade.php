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

        <h2 class="mt-25 font-36" id="countdown"></h2>
        <p class="mt-25 font-16">Time left for The diploma </p>
    </section>


<script>

    // Update the countdown every second
    var countdownInterval = setInterval(function() {

        var remainingTime = '<?php echo $remainingTime; ?>'

        // Calculate days, hours, minutes, and seconds
        var days = Math.floor(remainingTime / (1000 * 60 * 60 * 24));
        var hours = Math.floor((remainingTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 * 60));

        // Display the remaining time
        document.getElementById('countdown').innerHTML =' دقائق ' + minutes +' ساعات '+ hours + ' ايام ' + days  +' الوقت المتبقي';
        // If the countdown is over, stop updating
        if (remainingTime <= 0) {
            clearInterval(countdownInterval);
            document.getElementById('countdown').innerHTML = 'Course has ended.';
        }
    }, 1000);
</script>
@endsection
