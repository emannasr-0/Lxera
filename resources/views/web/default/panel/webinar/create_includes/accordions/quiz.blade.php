<li data-id="{{ !empty($chapterItem) ? $chapterItem->id :'' }}" class=" js-font-resize accordion-row bg-secondary-acadima rounded-sm border border-gray300 mt-20 py-15 py-lg-30 px-10 px-lg-20">
    <div class=" js-font-resize d-flex align-items-center justify-content-between " role="tab" id="quiz_{{ !empty($quizInfo) ? $quizInfo->id :'record' }}">
        <div class=" js-font-resize d-flex align-items-center" href="#collapseQuiz{{ !empty($quizInfo) ? $quizInfo->id :'record' }}" aria-controls="collapseQuiz{{ !empty($quizInfo) ? $quizInfo->id :'record' }}" data-parent="#{{ !empty($chapter) ? 'chapterContentAccordion'.$chapter->id : 'quizzesAccordion' }}" role="button" data-toggle="collapse" aria-expanded="true">
            <span class=" js-font-resize chapter-icon2 chapter-content-icon mr-10">
                <i data-feather="award" class=" js-font-resize "></i>
            </span>

            <span class=" js-font-resize font-weight-bold text-black d-block">{{ !empty($quizInfo) ? $quizInfo->title : trans('public.add_new_quizzes') }}</span>
        </div>

        <div class=" js-font-resize d-flex align-items-center">

            @if(!empty($quizInfo) and $quizInfo->status != \App\Models\WebinarChapter::$chapterActive)
                <span class=" js-font-resize disabled-content-badge mr-10">{{ trans('public.disabled') }}</span>
            @endif

            @if(!empty($quizInfo) and !empty($chapterItem))
                <button type="button" data-item-id="{{ $quizInfo->id }}" data-item-type="{{ \App\Models\WebinarChapterItem::$chapterQuiz }}" data-chapter-id="{{ !empty($chapter) ? $chapter->id : '' }}" class=" js-font-resize js-change-content-chapter btn btn-sm btn-transparent text-gray mr-10">
                    <i data-feather="grid" class=" js-font-resize " height="20"></i>
                </button>
            @endif

            @if(!empty($chapter))
                <i data-feather="move" class=" js-font-resize move-icon mr-10 cursor-pointer" height="20"></i>
            @endif

            @if(!empty($quizInfo))
                <a href="/panel/quizzes/{{ $quizInfo->id }}/delete" class=" js-font-resize delete-action btn btn-sm btn-transparent text-gray">
                    <i data-feather="trash-2" class=" js-font-resize mr-10 cursor-pointer" height="20"></i>
                </a>
            @endif

            <i class=" js-font-resize collapse-chevron-icon" data-feather="chevron-down" height="20" href="#collapseQuiz{{ !empty($quizInfo) ? $quizInfo->id :'record' }}" aria-controls="collapseQuiz{{ !empty($quizInfo) ? $quizInfo->id :'record' }}" data-parent="#quizzesAccordion" role="button" data-toggle="collapse" aria-expanded="true"></i>
        </div>
    </div>

    <div id="collapseQuiz{{ !empty($quizInfo) ? $quizInfo->id :'record' }}" aria-labelledby="quiz_{{ !empty($quizInfo) ? $quizInfo->id :'record' }}" class=" js-font-resize  collapse @if(empty($quizInfo)) show @endif" role="tabpanel">
        <div class=" js-font-resize panel-collapse text-gray">
            @include('web.default.panel.quizzes.create_quiz_form',
                    [
                        'inWebinarPage' => true,
                        'selectedWebinar' => $webinar,
                        'quiz' => $quizInfo ?? null,
                        'quizQuestions' => !empty($quizInfo) ? $quizInfo->quizQuestions : [],
                        'chapters' => $webinar->chapters,
                        'webinarChapterPages' => !empty($webinarChapterPages)
                    ]
                )
        </div>
    </div>
</li>
