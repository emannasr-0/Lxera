<div class=" js-font-resize row">
    <div class=" js-font-resize col-12">
        <div class=" js-font-resize accordion-content-wrapper" id="certificateAccordion" role="tablist" aria-multiselectable="true">
            @foreach($quizzes as $quiz)
                @if(!empty($quiz->certificate))
                    <div class=" js-font-resize accordion-row rounded-sm border mt-20 p-15">
                        <div class=" js-font-resize d-flex align-items-center justify-content-between" role="tab" id="quizCertificate_{{ $quiz->id }}">

                            <div class=" js-font-resize d-flex align-items-center" href="#collapseQuizCertificate{{ $quiz->id }}" aria-controls="collapseQuizCertificate{{ $quiz->id }}" data-parent="#certificateAccordion" role="button" data-toggle="collapse" aria-expanded="true">
                                    <span class=" js-font-resize chapter-icon chapter-content-icon mr-15">
                                        <i data-feather="award" width="20" height="20" class=" js-font-resize text-gray"></i>
                                    </span>

                                <span class=" js-font-resize font-weight-bold font-14 text-secondary d-block">{{ $quiz->title }}</span>
                            </div>

                            <i class=" js-font-resize collapse-chevron-icon" data-feather="chevron-down" height="20" href="#collapseQuizCertificate{{ !empty($quiz) ? $quiz->id :'record' }}" aria-controls="collapseQuizCertificate{{ !empty($quiz) ? $quiz->id :'record' }}" data-parent="#certificateAccordion" role="button" data-toggle="collapse" aria-expanded="true"></i>
                        </div>

                        <div id="collapseQuizCertificate{{ $quiz->id }}" aria-labelledby="quizCertificate_{{ $quiz->id }}" class=" js-font-resize  collapse" role="tabpanel">
                            <div class=" js-font-resize panel-collapse">
                                <div class=" js-font-resize d-flex align-items-center justify-content-between mt-20">
                                    <div class=" js-font-resize d-flex align-items-center">
                                        @if(!empty($quiz->result))
                                            <div class=" js-font-resize d-flex align-items-center text-gray text-center font-14 mr-20">
                                                <i data-feather="calendar" width="18" height="18" class=" js-font-resize text-gray mr-5"></i>
                                                <span class=" js-font-resize line-height-1">{{ dateTimeFormat($quiz->result->created_at, 'j M Y') }}</span>
                                            </div>
                                        @endif

                                        <div class=" js-font-resize d-flex align-items-center text-gray text-center font-14 mr-20">
                                            <i data-feather="check-square" width="18" height="18" class=" js-font-resize text-gray mr-5"></i>
                                            <span class=" js-font-resize line-height-1">{{ trans('update.passed_grade') }}: {{ $quiz->pass_mark }}/{{ $quiz->quizQuestions->sum('grade') }}</span>
                                        </div>
                                    </div>
                                    <div class=" js-font-resize ">
                                        @if(!empty($user) and $quiz->can_download_certificate and $hasBought)
                                            <a href="/panel/quizzes/results/{{ $quiz->result->id }}/showCertificate" target="_blank" class=" js-font-resize course-content-btns btn btn-sm btn-primary">{{ trans('home.download') }}</a>
                                        @else
                                            <button type="button" class=" js-font-resize course-content-btns btn btn-sm btn-gray disabled {{ ((empty($user)) ? 'not-login-toast' : (!$hasBought ? 'not-access-toast' : (!$quiz->can_download_certificate ? 'can-not-download-certificate-toast' : ''))) }}">
                                                {{ trans('home.download') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
