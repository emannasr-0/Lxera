<?php

use App\Http\Controllers\Admin\WebinarController;
use App\Http\Controllers\Api\Panel\AssignmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Instructor\WebinarsController;
use App\Http\Controllers\Api\Instructor\BundleController;
use App\Http\Controllers\Api\Panel\QuizzesController;
use App\Http\Controllers\Api\Web\CertificatesController;

Route::prefix('{url_name}')->group(function () {
    Route::group([], function () {

        Route::group(['prefix' => 'webinars', 'middleware' => 'can:student_showClasses'], function () {
            Route::group(['middleware' => 'user.not.access'], function () {
                Route::get('/', [WebinarController::class, 'index']);
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

        /***** bundles *****/
        Route::get('bundles/{bundle}/export', ['uses' => 'BundleController@export'])->middleware('api.level-access:teacher');
        Route::apiResource('bundles', BundleController::class)->middleware('api.level-access:teacher');
        Route::apiResource('bundles.webinars', BundleWebinarController::class)->middleware('api.level-access:teacher')->only(['index']);


        Route::group(['prefix' => 'quizzes'], function () {
            Route::get('/list', ['uses' => 'QuizzesController@results']);
            Route::post('/', ['uses' => 'QuizzesController@store']);
            Route::put('/{id}', ['uses' => 'QuizzesController@update']);
            Route::delete('/{id}', ['uses' => 'QuizzesController@destroy']);
        });
        //  Route::get('sales', ['uses' => 'SalesController@list']);
        Route::group(['prefix' => 'meetings'], function () {
            Route::get('/', function () {
                dd('ff');
            });

            Route::get('/requests', ['uses' => 'ReserveMeetingController@requests']);
            Route::post('/create-link', ['uses' => 'ReserveMeetingController@createLink']);
            Route::post('/{id}/finish', ['uses' => 'ReserveMeetingController@finish']);
        });
        Route::group(['prefix' => 'comments'], function () {
            Route::get('/', ['uses' => 'CommentsController@myClassComments']);
            Route::post('/{id}/reply', ['uses' => 'CommentsController@reply']);
        });
        Route::group(['prefix' => 'assignments'], function () {
            Route::get('/{assignment}/students', ['uses' => 'AssignmentController@submmision']);
            Route::get('/students', ['uses' => 'AssignmentController@students']);
            Route::get('/', ['uses' => 'AssignmentController@index']);
            Route::post('/histories/{assignment_history}/rate', ['uses' => 'AssignmentController@setGrade']);
        });

        Route::get('/webinar_learning-page/{id}', [WebinarsController::class, 'getWebinarsLessons']);

        Route::prefix('teacher')->middleware('auth:api')->group(function () {

            //webinars
            Route::get('/webinars', [WebinarsController::class, 'getTeacherWebinars']);
            Route::get('/webinars_content', [WebinarsController::class, 'getWebinarContent']);
            Route::get('/webinar_learning-page/{id}', [WebinarsController::class, 'getWebinarsLessons']);

            //bundles
            Route::get('/bundles', [BundleController::class, 'index']);
            Route::get('/bundle/courses', [BundleController::class, 'courses']);
            Route::get('/bundle/course/learning_page', [BundleController::class, 'course_learning']);
            Route::get('/{bundle}/course/learning/{id}', [WebinarsController::class, 'getWebinarsLessons']);

            //assignments
            Route::get('/my-courses-assignments', [AssignmentController::class, 'myCoursesAssignments']);
            Route::get('assignments/{id}/students', [AssignmentController::class, 'students']);
            // Route::get('course/learning/{id}?type=assignment&item={bundle}&student={id}',[WebinarsController::class, 'getWebinarsLessons']);

            //quizzes
            Route::get('/quizzes', [QuizzesController::class, 'index']);
            Route::get('/webinars_list', [QuizzesController::class, 'get_webinars_quizzes']);
            Route::post('/quizzes/store', [QuizzesController::class, 'store']);
            Route::get('/quizzes/results', [QuizzesController::class, 'results']);

            //certificates
            Route::get('/certificates', [CertificatesController::class, 'index']);
            Route::post('/certificate_verfiy', [CertificatesController::class, 'checkValidate']);
            Route::get('/certificates/achievements', [CertificatesController::class, 'achievements']);
        });
    });
});
