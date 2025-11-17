@if(!empty($course->chapters) and count($course->chapters))
    <div class=" js-font-resize accordion-content-wrapper mt-15" id="chapterAccordion" role="tablist" aria-multiselectable="true">
        @foreach($course->chapters as $chapter)
            <div class=" js-font-resize accordion-row bg-secondary-acadima rounded-sm border border-gray200 mb-2">
                <div class=" js-font-resize d-flex align-items-center justify-content-between p-10" role="tab" id="chapter_{{ $chapter->id  }}">
                    <div class=" js-font-resize d-flex align-items-center" href="#collapseChapter{{ $chapter->id  }}" aria-controls="collapseChapter{{ $chapter->id  }}" data-parent="#chapterAccordion" role="button" data-toggle="collapse" aria-expanded="true">
                        <span class=" js-font-resize chapter-icon mr-10">
                            <i data-feather="grid" class=" js-font-resize " width="20" height="20"></i>
                        </span>

                        <div class=" js-font-resize ">
                            <span class=" js-font-resize font-weight-bold font-14 text-pink d-block">{{ $chapter->title }}</span>

                            <span class=" js-font-resize font-12 text-gray d-block">
                                {{ $chapter->getTopicsCount(true) }} {{ trans('public.topic') }}
                            </span>
                        </div>
                    </div>

                    <div class=" js-font-resize d-flex align-items-center">
                        <i class=" js-font-resize collapse-chevron-icon feather-chevron-down text-gray" data-feather="chevron-down" height="20" href="#collapseChapter{{ $chapter->id  }}" aria-controls="collapseChapter{{ $chapter->id  }}" data-parent="#chapterAccordion" role="button" data-toggle="collapse" aria-expanded="true"></i>
                    </div>
                </div>

                <div id="collapseChapter{{ $chapter->id  }}" aria-labelledby="chapter_{{ $chapter->id  }}" class=" js-font-resize collapse" role="tabpanel">
                    <div class=" js-font-resize panel-collapse text-gray">

                        @if(!empty($chapter->chapterItems) and count($chapter->chapterItems))
                            @foreach($chapter->chapterItems as $chapterItem)
                                @if($chapterItem->type == \App\Models\WebinarChapterItem::$chapterSession and !empty($chapterItem->session) and $chapterItem->session->status == 'active')
                                    @include('web.default.course.learningPage.components.content_tab.content' , ['item' => $chapterItem->session, 'type' => 'session'])
                                @elseif($chapterItem->type == \App\Models\WebinarChapterItem::$chapterFile and !empty($chapterItem->file) and $chapterItem->file->status == 'active')
                                    @include('web.default.course.learningPage.components.content_tab.content' , ['item' => $chapterItem->file, 'type' => 'file'])
                                @elseif($chapterItem->type == \App\Models\WebinarChapterItem::$chapterTextLesson and !empty($chapterItem->textLesson) and $chapterItem->textLesson->status == 'active')
                                    @include('web.default.course.learningPage.components.content_tab.content' , ['item' => $chapterItem->textLesson, 'type' => 'text_lesson'])
                                @elseif($chapterItem->type == \App\Models\WebinarChapterItem::$chapterAssignment and !empty($chapterItem->assignment) and $chapterItem->assignment->status == 'active')
                                    @include('web.default.course.learningPage.components.content_tab.assignment-content-tab' ,['item' => $chapterItem->assignment])
                                @elseif($chapterItem->type == \App\Models\WebinarChapterItem::$chapterQuiz and !empty($chapterItem->quiz) and $chapterItem->quiz->status == 'active')
                                    @include('web.default.course.learningPage.components.quiz_tab.quiz' ,['item' => $chapterItem->quiz, 'type' => 'quiz'])
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
