<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Panel\DashboardController;
use App\Http\Controllers\Api\Instructor\WebinarsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['prefix' => '/development'], function () {
    
    Route::get('test',[DashboardController::class,'test']);
    
    
    Route::get('/timezone', function () {
        return sendResponse(getListOfTimezones(), 'timezone list is retrieved successfully');
    });


    Route::middleware('api')->group(base_path('routes/api/auth.php'));

    Route::namespace('Web')->group(base_path('routes/api/guest.php'));

    Route::prefix('panel')->middleware('api.auth')->namespace('Panel')->group(base_path('routes/api/user.php'));

    Route::group(['namespace' => 'Config', 'middleware' => []], function () {
        Route::get('/config', ['uses' => 'ConfigController@list']);
    });

    // payment
    Route::group(['prefix' => 'payments', 'namespace' => 'Web'], function () {
        Route::get('/verify/{gateway}', ['as' => 'payment_verify', 'uses' => 'PaymentController@paymentVerify'])
            ->withoutMiddleware('api.identify');
        Route::post('/verify/{gateway}', ['as' => 'payment_verify_post', 'uses' => 'PaymentController@paymentVerify'])
            ->withoutMiddleware('api.identify');
        Route::get('/status', 'PaymentController@payStatus')->withoutMiddleware('api.identify');
        Route::get('/status/{order_id}', 'PaymentController@payStatus')->withoutMiddleware('api.identify');
        Route::get('/payku/callback/{id}', 'PaymentController@paykuPaymentVerify')->name('payku.result')
            ->withoutMiddleware('api.identify');
    });


    Route::group(['namespace' => 'Web', 'middleware' => ['api.auth']], function () {
        Route::get("/checkout/{order}", 'PaymentController@index');
        Route::post("/checkout", 'PaymentController@paymentRequest');
    });

    Route::prefix('instructor')->middleware(['api.auth', 'api.level-access:teacher'])->namespace('Instructor')->group(base_path('routes/api/instructor.php'));
    // Route::middleware('auth:api')->get('learning-page/{id}', [WebinarsController::class, 'getWebinarsLessons']);
    Route::middleware('auth:api')->get('learning-page/{id}', [WebinarsController::class, 'getWebinarsLessons']);
    Route::middleware('auth:api')->get('{bundle}/learning-page/{id}', [WebinarsController::class, 'getWebinarsLessons']);

    
});
