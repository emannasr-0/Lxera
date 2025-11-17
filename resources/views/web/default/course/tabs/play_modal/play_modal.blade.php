<div class=" js-font-resize modal fade" id="playVideo" tabindex="-1" aria-labelledby="playVideoLabel" aria-hidden="true">
    <div class=" js-font-resize modal-dialog modal-lg modal-dialog-centered">
        <div class=" js-font-resize modal-content py-20">
            <div class=" js-font-resize d-flex align-items-center justify-content-between px-20">
                <h3 class=" js-font-resize section-title after-line"></h3>

                <button type="button" class=" js-font-resize close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25"></i>
                </button>
            </div>

            <div class=" js-font-resize mt-25 position-relative">
                <div class=" js-font-resize px-20">
                    <div class=" js-font-resize file-video-loading align-items-center justify-content-center py-50 text-center">
                        <img src="/assets/default/img/loading.gif" width="100" height="100">
                    </div>
                    <div class=" js-font-resize js-modal-video-content d-none">

                    </div>
                </div>

                @php
                    $notAllowSource = ['iframe', 'google_drive'];
                @endphp

                <div class=" js-font-resize modal-video-lists mt-15">

                    @if(!empty($filesWithoutChapter) and count($filesWithoutChapter))
                        @foreach($filesWithoutChapter as $video)
                            @if($video->isVideo() and !in_array($video->storage, $notAllowSource))
                                @include('web.default.course.tabs.play_modal.video_item', ['video' => $video])
                            @endif
                        @endforeach
                    @endif

                    @if(!empty($fileChapters) and count($fileChapters))
                        @foreach($fileChapters as $fileChapter)
                            @if(!empty($fileChapter->files) and count($fileChapter->files))
                                @php
                                    $hasVideoForPlay = false;

                                    foreach($fileChapter->files as $video) {
                                        if ($video->isVideo() and !in_array($video->storage, $notAllowSource)) {
                                            $hasVideoForPlay = true;
                                        }
                                    }
                                @endphp

                                @if($hasVideoForPlay)
                                    <div class=" js-font-resize d-flex justify-content-between align-items-center my-15 px-20">
                                        <h3 class=" js-font-resize section-title after-line">{{ $fileChapter->title }}</h3>
                                    </div>

                                        <div class=" js-font-resize accordion-content-wrapper mt-15" id="videosAccordion" role="tablist" aria-multiselectable="true">
                                        @foreach($fileChapter->files as $video)
                                            @if($video->isVideo() and !in_array($video->storage, $notAllowSource))
                                                @include('web.default.course.tabs.play_modal.video_item', ['video' => $video])
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
