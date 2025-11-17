@if(!empty($user->blog) and !$user->blog->isEmpty())
    <div class=" js-font-resize row">

        @foreach($user->blog as $post)
            <div class=" js-font-resize col-12 col-md-4">
                <div class=" js-font-resize mt-30">
                    @include('web.default.blog.grid-list',['post' => $post])
                </div>
            </div>
        @endforeach
    </div>
@else
    @include(getTemplate() . '.includes.no-result',[
        'file_name' => 'webinar.png',
        'title' => trans('update.instructor_not_have_posts'),
        'hint' => '',
    ])
@endif

