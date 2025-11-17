<div>
    <h2 class=" js-font-resize mt-20 mb-20 text-secondary">متطلبات القبول في برنامج {{ $program->slug }}</h2>
    <ol type="1" class=" js-font-resize ml-15">
        @foreach ($program->categoryRequirements as $requirement)
            <li style="list-style: inherit" class=" js-font-resize mb-15">
                <span class=" js-font-resize font-weight-bold">{{ $requirement->title }}</span>
                {{ $requirement->description }}
            </li>
        @endforeach

    </ol>
</div>
