<div class=" js-font-resize blog-grid-card">
    <div class=" js-font-resize blog-grid-image">
        <img src="{{ $post->image }}" class=" js-font-resize img-cover" alt="{{ $post->title }}">

        <span class=" js-font-resize badge created-at d-flex align-items-center">
            <i data-feather="calendar" width="20" height="20" class=" js-font-resize mr-5"></i>
            <span>{{ dateTimeFormat($post->created_at, 'j M Y') }}</span>
        </span>
    </div>

    <div class=" js-font-resize blog-grid-detail">
        <a href="{{ $post->getUrl() }}">
            <h3 class=" js-font-resize blog-grid-title mt-10">{{ $post->title }}</h3>
        </a>

        <div class=" js-font-resize mt-20 blog-grid-desc">{!! truncate(strip_tags($post->description), 160) !!}</div>

        <div class=" js-font-resize blog-grid-footer d-flex align-items-center justify-content-between mt-15">
            <span>
                <i data-feather="user" width="20" height="20" class=" js-font-resize "></i>
                 @if(!empty($post->author->full_name))
                <span class=" js-font-resize ml-5">{{ $post->author->full_name }}</span>
                 @endif
              </span>

            <span class=" js-font-resize d-flex align-items-center">
                <i data-feather="message-square" width="20" height="20" class=" js-font-resize "></i>
                <span class=" js-font-resize ml-5">{{ $post->comments_count }}</span>
            </span>
        </div>
    </div>
</div>
