@php
    $canPlay = ($video->accessibility !== 'paid' or ($video->accessibility == 'paid' and !empty($user) and $hasBought));
@endphp

<div data-id="{{ $canPlay ? $video->id : '' }}" data-title="{{ $canPlay ? $video->title : '' }}" class=" js-font-resize accordion-row modal-video-item {{ $canPlay ? 'js-play-video cursor-pointer' : 'no-hover' }} ">
    <div class=" js-font-resize p-20 item-border">
        <div class=" js-font-resize d-flex align-items-center">
            @if($video->accessibility == 'paid')
                @if(!empty($user) and $hasBought)
                    <i data-feather="play-circle" width="20" height="20" class=" js-font-resize text-gray"></i>
                @else
                    <i data-feather="lock" width="20" height="20" class=" js-font-resize text-gray"></i>
                @endif
            @else
                <i data-feather="play-circle" width="20" height="20" class=" js-font-resize text-gray"></i>
            @endif


            <div class=" js-font-resize flex-grow-1 mx-15">
                <h3 class=" js-font-resize font-16 text-dark">{{ $video->title }}</h3>
            </div>

            <div class=" js-font-resize ">
                @if($video->storage == 'upload')
                    {{ $video->getFileDuration() }}
                @else
                    {{ trans('update.file_source_'.$video->storage) }}
                @endif
            </div>
        </div>

        <div id="collapseVideo{{ $video->id }}" aria-labelledby="videoTab_{{ $video->id }}" class=" js-font-resize pl-35 collapse" role="tabpanel">
            <div class=" js-font-resize text-gray text-12 mt-10">
                {!! nl2br(clean($video->description)) !!}
            </div>
        </div>
    </div>
</div>
