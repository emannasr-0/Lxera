<?php

use App\Http\Controllers\panel\StripeConnectController;
use App\Models\InstallmentOrder;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\LearningPageController;

/*
|--------------------------------------------------------------------------
| User Panel Routes
|--------------------------------------------------------------------------
*/

// Route::get('stripe', [StripeConnectController::class, 'index']);

// Route::post('stripe_connect', [StripeConnectController::class, 'createConnectAccount'])->name('stripe.connect.create');


// Route::get('/stripe/refresh', function () {
//     // This is called if the user cancels onboarding
//     return 'stripe refresh';
// })->name('stripe.refresh');

// Route::get('/stripe/return', function () {
//     // This is called if the user completes onboarding
//     return 'stripe return';
// })->name('stripe.return');

// Route::post('/stripe/transfer', [StripeConnectController::class, 'transferToConnectedAccount'])->name('stripe.transfer');
Route::group(['namespace' => 'Web', 'prefix' => 'panel', 'middleware' => ['check_mobile_app', 'impersonate', 'panel', 'share', 'check_maintenance']], function () {

    // new enrollment
    Route::group(['prefix' => 'newEnrollment'], function () {
        Route::get('/', 'ApplyController@newEnrollment');
        Route::post('/step', 'ApplyController@storeNewEnrollment');
    });
    Route::get('/{bundle}/book_seat', 'ApplyController@bookSeat');
});


Route::group(['namespace' => 'Panel', 'prefix' => 'panel', 'middleware' => ['check_mobile_app', 'impersonate', 'panel', 'share', 'check_maintenance']], function () {
    Route::get('/course/{WebinarId}/showCertificate/{format?}', 'CertificateController@makeCourseCertificate');

    Route::get('/bundle/{bundleId}/showCertificate/{format?}', 'CertificateController@makeBundleCertificate');
    //  Route::get('/bundle/{bundleId}/showCertificate', 'CertificateController@makeBundleCertificate');
    Route::get('/', 'DashboardController@dashboard')->middleware('can:show_panel');

    Route::group(['prefix' => 'users'], function () {
        Route::post('/search', 'UserController@search');
        Route::post('/contact-info', 'UserController@contactInfo');
        Route::post('/offlineToggle', 'UserController@offlineToggle');
        Route::get('/{id}/getInfo', 'UserController@getUserInfo');
    });

    Route::group(['prefix' => 'webinars', 'middleware' => 'can:student_showClasses'], function () {
        Route::group(['middleware' => 'user.not.access'], function () {
            Route::get('/', 'WebinarController@index');
            Route::get('/new', 'WebinarController@create');
            Route::get('/invitations', 'WebinarController@invitations');
            Route::post('/store', 'WebinarController@store');
            Route::get('/{id}/step/4', 'WebinarController@edit');
            // Route::get('/{id}/step/{step?}', 'WebinarController@edit');
            Route::get('/{id}/edit', 'WebinarController@edit')->name('panel_edit_webinar');
            Route::post('/{id}/update', 'WebinarController@update');
            Route::get('/{id}/delete', 'WebinarController@destroy');
            Route::get('/{id}/duplicate', 'WebinarController@duplicate');
            Route::get('/{id}/export-students-list', 'WebinarController@exportStudentsList');
            Route::post('/order-items', 'WebinarController@orderItems');
            Route::post('/{id}/getContentItemByLocale', 'WebinarController@getContentItemByLocale');

            Route::group(['prefix' => '{webinar_id}/statistics'], function () {
                Route::get('/', 'WebinarStatisticController@index');
            });
        });

        Route::get('/organization_classes', 'WebinarController@organizationClasses');
        Route::get('/{id}/sale/{sale_id}/invoice', 'WebinarController@invoice');
        Route::get('/{id}/getNextSessionInfo', 'WebinarController@getNextSessionInfo');

        Route::group(['prefix' => 'purchases'], function () {
            Route::get('/', 'WebinarController@purchases');
            Route::post('/getJoinInfo', 'WebinarController@getJoinInfo');
        });

        Route::post('/search', 'WebinarController@search');

        Route::group(['prefix' => 'comments'], function () {
            Route::get('/', 'CommentController@myClassComments');
            Route::post('/store', 'CommentController@store');
            Route::post('/{id}/update', 'CommentController@update');
            Route::get('/{id}/delete', 'CommentController@destroy');
            Route::post('/{id}/reply', 'CommentController@reply');
            Route::post('/{id}/report', 'CommentController@report');
        });

        Route::get('/my-comments', 'CommentController@myComments');

        Route::group(['prefix' => 'favorites'], function () {
            Route::get('/', 'FavoriteController@index');
            Route::get('/{id}/delete', 'FavoriteController@destroy');
        });
    });

    Route::group(['prefix' => 'upcoming_courses', 'middleware' => 'can:student_showUpcomingCourses'], function () {
        Route::group(['middleware' => 'user.not.access'], function () {
            Route::get('/', 'UpcomingCoursesController@index');
            Route::get('/new', 'UpcomingCoursesController@create');
            Route::post('/store', 'UpcomingCoursesController@store');
            Route::get('/{id}/step/{step?}', 'UpcomingCoursesController@edit');
            Route::get('/{id}/edit', 'UpcomingCoursesController@edit');
            Route::post('/{id}/update', 'UpcomingCoursesController@update');
            Route::get('/{id}/delete', 'UpcomingCoursesController@destroy');
            Route::post('/order-items', 'UpcomingCoursesController@orderItems');

            Route::get('/{id}/followers', 'UpcomingCoursesController@followers');
            Route::get('/{id}/assign-course', 'UpcomingCoursesController@assignCourseModal');
            Route::post('/{id}/assign-course', 'UpcomingCoursesController@storeAssignCourse');
        });

        Route::get('/followings', 'UpcomingCoursesController@followings');
    });

    Route::group(['prefix' => 'quizzes', 'middleware' => 'can:student_showTest'], function () {
        Route::group(['middleware' => 'user.not.access'], function () {

            Route::get('/', 'QuizController@index');
            Route::get('/new', 'QuizController@create');
            Route::post('/store', 'QuizController@store');
            Route::get('/{id}/edit', 'QuizController@edit')->name('panel_edit_quiz');
            Route::post('/{id}/update', 'QuizController@update');
            Route::get('/{id}/delete', 'QuizController@destroy');
            Route::post('/{id}/order-items', 'QuizController@orderItems');
        });
        Route::get('/{id}/start', 'QuizController@start');
        Route::post('/{id}/store-result', 'QuizController@quizzesStoreResult');
        Route::get('/{quizResultId}/status', 'QuizController@status')->name('quiz_status');

        Route::get('/my-results', 'QuizController@myResults');
        Route::get('/opens', 'QuizController@opens');

        Route::get('/{quizResultId}/result', 'QuizController@showResult');

        Route::group(['prefix' => 'results'], function () {
            Route::get('/', 'QuizController@results');
            Route::get('/{quizResultId}/delete', 'QuizController@destroyQuizResult');
            Route::get('/{quizResultId}/showCertificate', 'CertificateController@makeCertificate');
        });

        Route::get('/{quizResultId}/edit-result', 'QuizController@editResult');
        Route::post('/{quizResultId}/update-result', 'QuizController@updateResult');
    });

    Route::group(['prefix' => 'quizzes-questions'], function () {
        Route::post('/store', 'QuizQuestionController@store');
        Route::get('/{id}/edit', 'QuizQuestionController@edit');
        Route::get('/{id}/getQuestionByLocale', 'QuizQuestionController@getQuestionByLocale');
        Route::post('/{id}/update', 'QuizQuestionController@update');
        Route::get('/{id}/delete', 'QuizQuestionController@destroy');
    });

    Route::group(['prefix' => 'filters'], function () {
        Route::get('/get-by-category-id/{categoryId}', 'FilterController@getByCategoryId');
    });

    Route::group(['prefix' => 'tickets'], function () {
        Route::post('/store', 'TicketController@store');
        Route::post('/{id}/update', 'TicketController@update');
        Route::get('/{id}/delete', 'TicketController@destroy');
    });

    Route::group(['prefix' => 'sessions'], function () {
        Route::post('/store', 'SessionController@store');
        Route::post('/start-recording', 'SessionController@startRecording')->name('start_recording');
        Route::post('/{id}/update', 'SessionController@update');
        Route::get('/{id}/delete', 'SessionController@destroy');
        Route::get('/{id}/joinToBigBlueButton', 'SessionController@joinToBigBlueButton');
        Route::get('/{id}/joinToAgora', 'SessionController@joinToAgora');
        Route::get('/{id}/endAgora', 'SessionController@endAgora');
        Route::get('/{id}/toggleUsersJoinToAgora', 'SessionController@toggleUsersJoinToAgora');
        Route::get('/{id}/joinToJitsi', 'SessionController@joinToJitsi');
    });

    Route::group(['prefix' => 'chapters'], function () {
        Route::get('/{id}', 'ChapterController@getChapter');
        Route::get('/getAllByWebinarId/{webinar_id}', 'ChapterController@getAllByWebinarId');
        Route::post('/store', 'ChapterController@store');
        Route::post('/{id}/update', 'ChapterController@update');
        Route::get('/{id}/delete', 'ChapterController@destroy');
        Route::post('/change', 'ChapterController@change');
    });

    Route::group(['prefix' => 'files'], function () {
        Route::post('/store', 'FileController@store');
        Route::post('/{id}/update', 'FileController@update');
        Route::get('/{id}/delete', 'FileController@destroy');
    });

    Route::group(['prefix' => 'assignments', 'middleware' => 'can:student_showTasks'], function () {
        Route::post('/store', 'AssignmentController@store');
        Route::post('/{id}/update', 'AssignmentController@update');
        Route::get('/{id}/delete', 'AssignmentController@destroy');
    });

    Route::group(['prefix' => 'text-lesson'], function () {
        Route::post('/store', 'TextLessonsController@store');
        Route::post('/{id}/update', 'TextLessonsController@update');
        Route::get('/{id}/delete', 'TextLessonsController@destroy');
    });

    Route::group(['prefix' => 'prerequisites'], function () {
        Route::post('/store', 'PrerequisiteController@store');
        Route::post('/{id}/update', 'PrerequisiteController@update');
        Route::get('/{id}/delete', 'PrerequisiteController@destroy');
    });

    Route::group(['prefix' => 'faqs'], function () {
        Route::post('/store', 'FAQController@store');
        Route::post('/{id}/update', 'FAQController@update');
        Route::get('/{id}/delete', 'FAQController@destroy');
    });

    Route::group(['prefix' => 'webinar-extra-description'], function () {
        Route::post('/store', 'WebinarExtraDescriptionController@store');
        Route::post('/{id}/update', 'WebinarExtraDescriptionController@update');
        Route::get('/{id}/delete', 'WebinarExtraDescriptionController@destroy');
    });

    Route::group(['prefix' => 'webinar-quiz'], function () {
        Route::post('/store', 'WebinarQuizController@store');
        Route::post('/{id}/update', 'WebinarQuizController@update');
        Route::get('/{id}/delete', 'WebinarQuizController@destroy');
    });


    Route::group(['prefix' => 'certificates'], function () {
        Route::get('/', 'CertificateController@lists');
        Route::get('/certificates_template', 'CertificateController@certificateLists');
        Route::get('/achievements', 'CertificateController@achievements');

        Route::group(['prefix' => 'webinars'], function () {
            Route::get('/', 'WebinarCertificateController@index');
            Route::get('/{certificateId}/show', 'WebinarCertificateController@makeCertificate');
        });
    });

    Route::group(['prefix' => 'meetings', 'middleware' => 'can:student_showSession'], function () {
        Route::get('/reservation', 'ReserveMeetingController@reservation');
        Route::get('/requests', 'ReserveMeetingController@requests');

        Route::get('/settings', 'MeetingController@setting')->name('meeting_setting');
        Route::post('/{id}/update', 'MeetingController@update');
        Route::post('saveTime', 'MeetingController@saveTime');
        Route::post('deleteTime', 'MeetingController@deleteTime');
        Route::post('temporaryDisableMeetings', 'MeetingController@temporaryDisableMeetings');

        Route::get('/{id}/join', 'ReserveMeetingController@join');
        Route::post('/create-link', 'ReserveMeetingController@createLink');
        Route::get('/{id}/finish', 'ReserveMeetingController@finish');

        Route::post('/{id}/add-session', 'ReserveMeetingController@addLiveSession');
    });

    Route::group(['prefix' => 'financial'], function () {
        Route::get('/sales', 'SaleController@index');
        // Route::get('/summary', 'AccountingController@index');
        Route::get('/payout', 'PayoutController@index');
        Route::post('/request-payout', 'PayoutController@requestPayout');
        Route::get('/account', 'AccountingController@account');
        Route::post('/charge', 'AccountingController@charge');

        Route::group(['prefix' => 'offline-payments'], function () {
            Route::get('/', 'AccountingController@getOfflinePayment');
            Route::get('/{id}/edit', 'AccountingController@account');
            Route::post('/{id}/update', 'AccountingController@updateOfflinePayment');
            Route::get('/{id}/delete', 'AccountingController@deleteOfflinePayment');
            Route::get('/{id}/cancel', 'AccountingController@cancelOfflinePayment');
        });

        Route::group(['prefix' => 'subscribes'], function () {
            Route::get('/', 'SubscribesController@index');
            Route::get('/{id}/installments', 'SubscribesController@getInstallmentsBySubscribe');
        });
        Route::post('/pay-subscribes', 'SubscribesController@pay');

        Route::group(['prefix' => 'registration-packages'], function () {
            Route::get('/', 'RegistrationPackagesController@index')->name('panelRegistrationPackagesLists');
            Route::get('/{id}/installments', 'RegistrationPackagesController@getInstallmentsByRegistrationPackage');
            Route::post('/pay-registration-packages', 'RegistrationPackagesController@pay')->name('payRegistrationPackage');
        });

        Route::group(['prefix' => 'installments'], function () {
            // Route::get('/', 'InstallmentsController@index');
            Route::get('/', function () {
                $user_id = auth()->user()->id;

                if (InstallmentOrder::where('user_id', $user_id)->where('status', '!=', 'paying')->exists()) {
                    return app('App\Http\Controllers\Panel\InstallmentsController')->index();
                }

                return  view("errors.404", ['pageTitle' => trans('public.error_404_page_title')]);
            })->name('installments.index');

            Route::get('/{id}/details', 'InstallmentsController@show');
            Route::get('/{id}/cancel', 'InstallmentsController@cancelVerification');
            Route::get('/{id}/pay_upcoming_part', 'InstallmentsController@payUpcomingPart');
            Route::get('/{id}/steps/{step_id}/pay', 'InstallmentsController@payStep');
        });
    });

    Route::group(['prefix' => 'setting'], function () {
        Route::get('/step/{step?}', 'UserController@setting');
        Route::get('/', 'UserController@setting');
        Route::post('/', 'UserController@update');
        Route::post('/metas', 'UserController@storeMetas');
        Route::post('metas/{meta_id}/update', 'UserController@updateMeta');
        Route::get('metas/{meta_id}/delete', 'UserController@deleteMeta');
        Route::post('/references', 'UserController@storeReference');
        Route::post('references/{reference_id}/update', 'UserController@updateReference');
        Route::get('references/{reference_id}/delete', 'UserController@deleteReference');
        Route::get('/deleteAccount', 'UserController@deleteAccount');
    });

    // requirements Routes
    Route::group(['prefix' => 'requirements'], function () {
        Route::get('/', 'UserController@requirementIndex');
        Route::get('/applied', 'UserController@requirementPaymentStep');
        Route::get('/step/1', 'UserController@requirementIndex');
        Route::get('/step/2', 'UserController@account');
    });

    Route::get('/courses/applied', 'UserController@appliedCourses');
    // services Routes
    Route::group(['prefix' => 'services'], function () {
        Route::get('/', 'ServiceController@index');
        Route::get('/requests', 'ServiceController@requests');
        Route::get('/{service}/bundleTransform', 'ServiceController@bundleTransformRequest');
        Route::post('/{service}/bundleTransform', 'ServiceController@bundleTransform');
        Route::get('/{service}/bundleBridging', 'ServiceController@bundleBridgingRequest');
        Route::post('/{service}/bundleBridging', 'ServiceController@bundleBridging');
        Route::get('/{service}/bundleDelay', 'ServiceController@bundleDelayRequest');
        Route::post('/{service}/bundleDelay', 'ServiceController@bundleDelay');
        Route::get('/{service}/apply', 'ServiceController@store');
    });

    Route::get('/bundleBridging/{bundleBridging}/pay', 'ServiceController@bundleBridgingPay');
    Route::get('/bundletransform/{bundleTransform}/pay', 'ServiceController@bundleTransformPay');
    Route::get('/bundletransform/{bundleTransform}/refund', 'ServiceController@bundleTransformRefund');

    Route::group(['prefix' => 'support', 'middleware' => 'can:show_support'], function () {
        Route::get('/', 'SupportsController@index');
        Route::get('/new', 'SupportsController@create');
        Route::post('/store', 'SupportsController@store');
        Route::get('{id}/conversations', 'SupportsController@index');
        Route::post('{id}/conversations', 'SupportsController@storeConversations');
        Route::get('{id}/close', 'SupportsController@close');
        Route::group(['prefix' => 'tickets'], function () {
            Route::get('/', 'SupportsController@tickets');
            Route::get('{id}/conversations', 'SupportsController@tickets');
        });
    });

    Route::group(['prefix' => 'marketing', 'middleware' => ['user.not.access', 'can:student_showMarkting']], function () {
        Route::get('/special_offers', 'SpecialOfferController@index')->name('special_offer_index');
        Route::post('/special_offers/store', 'SpecialOfferController@store');
        Route::get('/special_offers/{id}/disable', 'SpecialOfferController@disable');
        Route::get('/promotions', 'MarketingController@promotions');
        Route::post('/pay-promotion', 'MarketingController@payPromotion');
    });

    Route::group(['prefix' => 'marketing'], function () {
        Route::get('/affiliates', 'AffiliateController@affiliates');

        /* Registration Bonus */
        Route::get('/registration_bonus', 'RegistrationBonusController@index');
    });

    Route::group(['prefix' => 'noticeboard'], function () {
        Route::get('/', 'NoticeboardController@index');
        Route::get('/new', 'NoticeboardController@create');
        Route::post('/store', 'NoticeboardController@store');
        Route::get('/{noticeboard_id}/edit', 'NoticeboardController@edit');
        Route::post('/{noticeboard_id}/update', 'NoticeboardController@update');
        Route::get('/{noticeboard_id}/delete', 'NoticeboardController@delete');
        Route::get('/{noticeboard_id}/saveStatus', 'NoticeboardController@saveStatus');
    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', 'NotificationsController@index');
        Route::get('/{id}/saveStatus', 'NotificationsController@saveStatus');
        Route::get('/mark-all-as-read', 'NotificationsController@markAllAsRead');
    });

    // organization instructor and students route
    Route::group(['prefix' => 'manage'], function () {
        Route::get('/{user_type}', 'UserController@manageUsers');
        Route::get('/{user_type}/new', 'UserController@createUser');
        Route::post('/{user_type}/new', 'UserController@storeUser');
        Route::get('/{user_type}/{user_id}/edit', 'UserController@editUser');
        Route::get('/{user_type}/{user_id}/edit/step/{step?}', 'UserController@editUser');
        Route::get('/{user_type}/{user_id}/delete', 'UserController@deleteUser');
    });

    Route::group(['prefix' => 'rewards', 'middleware' => 'can:show_reward'], function () {
        Route::get('/', 'RewardController@index');
        Route::post('/exchange', 'RewardController@exchange');
    });

    Route::group(['prefix' => 'store', 'namespace' => 'Store', 'middleware' => 'can:student_showStore'], function () {
        Route::group(['middleware' => 'user.not.access'], function () {

            Route::group(['prefix' => 'products'], function () {
                Route::get('/', 'ProductController@index');
                Route::get('/new', 'ProductController@create');
                Route::post('/store', 'ProductController@store');
                Route::get('/{id}/step/{step?}', 'ProductController@edit');
                Route::get('/{id}/edit', 'ProductController@edit');
                Route::post('/{id}/update', 'ProductController@update');
                Route::get('/{id}/delete', 'ProductController@destroy');
                Route::post('/{id}/getContentItemByLocale', 'ProductController@getContentItemByLocale');

                Route::group(['prefix' => 'filters'], function () {
                    Route::get('/get-by-category-id/{categoryId}', 'ProductFilterController@getByCategoryId');
                });

                Route::group(['prefix' => 'specifications'], function () {
                    Route::get('/{id}/get', 'ProductSpecificationController@getItem');
                    Route::post('/store', 'ProductSpecificationController@store');
                    Route::post('/{id}/update', 'ProductSpecificationController@update');
                    Route::get('/{id}/delete', 'ProductSpecificationController@destroy');
                    Route::post('/order-items', 'ProductSpecificationController@orderItems');
                    Route::post('/search', 'ProductSpecificationController@search');
                    Route::get('/get-by-category-id/{categoryId}', 'ProductSpecificationController@getByCategoryId');
                });

                Route::group(['prefix' => 'files'], function () {
                    Route::post('/store', 'ProductFileController@store');
                    Route::post('/{id}/update', 'ProductFileController@update');
                    Route::get('/{id}/delete', 'ProductFileController@destroy');
                    Route::post('/order-items', 'ProductFileController@orderItems');
                });

                Route::group(['prefix' => 'faqs'], function () {
                    Route::post('/store', 'ProductFaqController@store');
                    Route::post('/{id}/update', 'ProductFaqController@update');
                    Route::get('/{id}/delete', 'ProductFaqController@destroy');
                    Route::post('/order-items', 'ProductFaqController@orderItems');
                });

                Route::group(['prefix' => 'comments'], function () {
                    Route::get('/', 'CommentController@index');
                });
            });

            Route::group(['prefix' => 'sales'], function () {
                Route::get('/', 'SaleController@index');
                Route::get('/{id}/productOrder/{order_id}/invoice', 'SaleController@invoice');
                Route::get('/{id}/getProductOrder/{order_id}', 'SaleController@getProductOrder');
                Route::post('/{id}/productOrder/{order_id}/setTrackingCode', 'SaleController@setTrackingCode');
            });
        });

        Route::group(['prefix' => 'purchases'], function () {
            Route::get('/', 'MyPurchaseController@index');
            Route::get('/{id}/getProductOrder/{order_id}', 'MyPurchaseController@getProductOrder');
            Route::get('/{id}/productOrder/{order_id}/setGotTheParcel', 'MyPurchaseController@setGotTheParcel');
            Route::get('/{id}/productOrder/{order_id}/invoice', 'MyPurchaseController@invoice');
        });


        Route::group(['prefix' => 'products'], function () {

            Route::get('/my-comments', 'CommentController@myComments');

            Route::get('/files/{id}/download', 'ProductFileController@download');
        });
    });

    Route::group(['prefix' => 'assignments', 'middleware' => 'can:student_showTasks'], function () {
        Route::get('/my-assignments', 'AssignmentController@myAssignments');
        Route::get('/my-courses-assignments', 'AssignmentController@myCoursesAssignments');
        Route::get('/{id}/students', 'AssignmentController@students');
    });

    Route::group(['prefix' => 'bundles'], function () {
        Route::post('/purchase/{id?}', 'BundlesController@purchase_bundle')->name('purchase_bundle');
        Route::group(['prefix' => 'purchases'], function () {
            Route::get('/', 'BundlesController@purchases');
            Route::post('/getJoinInfo', 'BundlesController@getJoinInfo');
        });
        Route::group(['middleware' => 'user.not.access'], function () {
            Route::get('/', 'BundlesController@index');
            Route::get('/new', 'BundlesController@create');
            Route::post('/store', 'BundlesController@store');

            Route::get('/{id}/step/{step?}', 'BundlesController@edit');
            Route::get('/{id}/edit', 'BundlesController@edit');
            Route::post('/{id}/update', 'BundlesController@update');
            Route::get('/{id}/delete', 'BundlesController@destroy');
            Route::post('/{id}/getContentItemByLocale', 'BundlesController@getContentItemByLocale');
            Route::get('/{id}/courses', 'BundlesController@courses');
            Route::get('/{id}/export-students-list', 'BundlesController@exportStudentsList');
        });
        // requirements Routes
        Route::get('/{studentBundleId}/requirements', 'UserController@createRequirement');
        Route::post('/{studentBundleId}/requirements', 'UserController@storeRequirement')->name('requirements.store');
        Route::get('/{bundle}/course/learning/{id}', [LearningPageController::class, 'index']);
    });

    Route::group(['prefix' => 'bundle-webinars'], function () {
        Route::post('/store', 'BundleWebinarsController@store');
        Route::post('/{id}/update', 'BundleWebinarsController@update');
        Route::get('/{id}/delete', 'BundleWebinarsController@destroy');
    });

    Route::group(['prefix' => 'course-noticeboard'], function () {
        Route::get('/', 'CourseNoticeboardController@index');
        Route::get('/new', 'CourseNoticeboardController@create');
        Route::post('/store', 'CourseNoticeboardController@store');
        Route::get('/{noticeboard_id}/edit', 'CourseNoticeboardController@edit');
        Route::post('/{noticeboard_id}/update', 'CourseNoticeboardController@update');
        Route::get('/{noticeboard_id}/delete', 'CourseNoticeboardController@delete');
        Route::get('/{noticeboard_id}/saveStatus', 'CourseNoticeboardController@saveStatus');
    });

    Route::group(['prefix' => 'forums', 'middleware' => 'can:student_showForums'], function () {
        Route::get('/topics', 'ForumsController@topics');
        Route::get('/topics/{id}/removeBookmarks', 'ForumsController@removeBookmarks');
        Route::get('/posts', 'ForumsController@posts');
        Route::get('/bookmarks', 'ForumsController@bookmarks');
    });

    Route::group(['prefix' => 'blog'], function () {
        Route::group(['prefix' => 'posts'], function () {
            Route::get('/', 'BlogPostsController@index');
            Route::get('/new', 'BlogPostsController@create');
            Route::post('/store', 'BlogPostsController@store');
            Route::get('/{post_id}/edit', 'BlogPostsController@edit');
            Route::post('/{post_id}/update', 'BlogPostsController@update');
            Route::get('/{post_id}/delete', 'BlogPostsController@delete');
        });

        Route::group(['prefix' => 'comments'], function () {
            Route::get('/', 'BlogCommentsController@index');
        });
    });
});
