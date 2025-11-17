@extends(getTemplate().'.layouts.app')

@section('content')
    <section class=" js-font-resize cart-banner position-relative text-center">
        <div class=" js-font-resize container h-100">
            <div class=" js-font-resize row h-100 align-items-center justify-content-center text-center">
                <div class=" js-font-resize col-12 col-md-9 col-lg-7">

                    <h1 class=" js-font-resize font-30 text-white font-weight-bold">{{ $post->title }}</h1>

                    <div class=" js-font-resize d-flex flex-column flex-sm-row align-items-center align-sm-items-start justify-content-between">
                        @if(!empty($post->author))
                            <span class=" js-font-resize mt-10 mt-md-20 font-16 font-weight-500 text-white">{{ trans('public.created_by') }}
                                @if($post->author->isTeacher())
                                    <a href="{{ $post->author->getProfileUrl() }}" target="_blank" class=" js-font-resize text-white text-decoration-underline">{{ $post->author->full_name }}</a>
                                @elseif(!empty($post->author->full_name))
                                    <span class=" js-font-resize text-white text-decoration-underline">{{ $post->author->full_name }}</span>
                                @endif
                        </span>
                        @endif

                        <span class=" js-font-resize mt-10 mt-md-20 font-16 font-weight-500 text-white">{{ trans('public.in') }}
                            <a href="{{ $post->category->getUrl() }}" class=" js-font-resize text-white text-decoration-underline">{{ $post->category->title }}</a>
                        </span>

                        <span class=" js-font-resize mt-10 mt-md-20 font-16 font-weight-500 text-white">{{ dateTimeFormat($post->created_at, 'j M Y') }}</span>

                        <div class=" js-font-resize js-share-blog d-flex align-items-center cursor-pointer mt-10 mt-md-20">
                            <div class=" js-font-resize icon-box ">
                                <i data-feather="share-2" class=" js-font-resize text-white" width="20" height="20"></i>
                            </div>
                            <div class=" js-font-resize ml-5 font-16 font-weight-500 text-white">{{ trans('public.share') }}</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <section class=" js-font-resize container mt-10 mt-md-40">
        <div class=" js-font-resize row">
            <div class=" js-font-resize col-12 col-lg-8">
                <div class=" js-font-resize post-show mt-30">

                    <div class=" js-font-resize post-img pb-30">
                        <img src="{{ $post->image }}" alt="">
                    </div>


                    {!! nl2br($post->content) !!}
                </div>

                {{-- post Comments --}}
                @if($post->enable_comment)
                    @include('web.default.includes.comments',[
                            'comments' => $post->comments,
                            'inputName' => 'blog_id',
                            'inputValue' => $post->id
                        ])
                @endif
                {{-- ./ post Comments --}}

            </div>
            <div class=" js-font-resize col-12 col-lg-4">
                @if(!empty($post->author) and !empty($post->author->full_name))
                    <div class=" js-font-resize rounded-lg shadow-sm mt-35 p-20 course-teacher-card d-flex align-items-center flex-column">
                        <div class=" js-font-resize teacher-avatar mt-5">
                            <img src="{{ $post->author->getAvatar(100) }}" class=" js-font-resize img-cover" alt="">
                        </div>
                        <h3 class=" js-font-resize mt-10 font-20 font-weight-bold text-secondary">{{ $post->author->full_name }}</h3>

                        @if(!empty($post->author->role))
                            <span class=" js-font-resize mt-5 font-weight-500 font-14 text-gray">{{ $post->author->role->caption }}</span>
                        @endif

                        <div class=" js-font-resize mt-25 d-flex align-items-center  w-100">
                            <a href="/blog?author={{ $post->author->id }}" class=" js-font-resize btn btn-sm btn-primary btn-block px-15">{{ trans('public.author_posts') }}</a>
                        </div>
                    </div>
                @endif

                {{-- categories --}}
                <div class=" js-font-resize p-20 mt-30 rounded-sm shadow-lg border border-gray300">
                    <h3 class=" js-font-resize category-filter-title font-16 font-weight-bold text-dark-blue">{{ trans('categories.categories') }}</h3>

                    <div class=" js-font-resize pt-15">
                        @foreach($blogCategories as $blogCategory)
                            <a href="{{ $blogCategory->getUrl() }}" class=" js-font-resize font-14 text-dark-blue d-block mt-15">{{ $blogCategory->title }}</a>
                        @endforeach
                    </div>
                </div>

                {{-- recent_posts --}}
                <div class=" js-font-resize p-20 mt-30 rounded-sm shadow-lg border border-gray300">
                    <h3 class=" js-font-resize category-filter-title font-20 font-weight-bold text-dark-blue">{{ trans('site.recent_posts') }}</h3>

                    <div class=" js-font-resize pt-15">

                        @foreach($popularPosts as $popularPost)
                            <div class=" js-font-resize popular-post d-flex align-items-start mt-20">
                                <div class=" js-font-resize popular-post-image rounded">
                                    <img src="{{ $popularPost->image }}" class=" js-font-resize img-cover rounded" alt="{{ $popularPost->title }}">
                                </div>
                                <div class=" js-font-resize popular-post-content d-flex flex-column ml-10">
                                    <a href="{{ $popularPost->getUrl() }}">
                                        <h3 class=" js-font-resize font-14 text-dark-blue">{{ truncate($popularPost->title,40) }}</h3>
                                    </a>
                                    <span class=" js-font-resize mt-auto font-12 text-gray">{{ dateTimeFormat($popularPost->created_at, 'j M Y') }}</span>
                                </div>
                            </div>
                        @endforeach

                        <a href="/blog" class=" js-font-resize btn btn-sm btn-primary btn-block mt-30">{{ trans('home.view_all') }} {{ trans('site.posts') }}</a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    @include('web.default.blog.share_modal')
@endsection

@push('scripts_bottom')
    <script>
        var webinarDemoLang = '{{ trans('webinars.webinar_demo') }}';
        var replyLang = '{{ trans('panel.reply') }}';
        var closeLang = '{{ trans('public.close') }}';
        var saveLang = '{{ trans('public.save') }}';
        var reportLang = '{{ trans('panel.report') }}';
        var reportSuccessLang = '{{ trans('panel.report_success') }}';
        var messageToReviewerLang = '{{ trans('public.message_to_reviewer') }}';
        var copyLang = '{{ trans('public.copy') }}';
        var copiedLang = '{{ trans('public.copied') }}';
    </script>

    <script src="/assets/default/js/parts/comment.min.js"></script>
    <script src="/assets/default/js/parts/blog.min.js"></script>
@endpush
