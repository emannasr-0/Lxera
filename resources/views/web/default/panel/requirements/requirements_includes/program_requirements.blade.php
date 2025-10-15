<div>
    <h2 class="mt-20 mb-20">متطلبات القبول في برنامج {{ $program->slug }}</h2>
    <ol type="1" class="ml-15">
        @foreach ($program->categoryRequirements as $requirement)
            <li style="list-style: inherit" class="mb-15">
                <span class="font-weight-bold">{{ $requirement->title }}</span>
                {{ $requirement->description }}
            </li>
        @endforeach

    </ol>
</div>
