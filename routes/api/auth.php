<?php

use App\Http\Controllers\Admin\ConsultantsController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Panel\ConsultationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

//Consultation
Route::group(['prefix' => 'consultation'], function () {
    Route::get('meeting_times', [ConsultationController::class, 'meeting_times']);
    Route::get('webinars', [ConsultationController::class, 'webinars']);
    Route::get('bundles', [ConsultationController::class, 'bundles']);
    Route::get('timezones', [ConsultationController::class, 'timezones']);
    Route::post('/', [ConsultationController::class, 'consultation_post']);
});

Route::group(['namespace' => 'Auth', 'middleware' => ['api.request.type']], function () {

    Route::get('/registerForm', 'RegisterController@showRegistrationForm')->name('register_form');
    Route::post('/register/step/{step}', ['as' => 'register', 'uses' => 'RegisterController@stepRegister']);
    Route::post('/login', ['as' => 'login', 'uses' => 'AuthController@login']);
    Route::post('/register', ['as' => 'register', 'uses' => 'AuthController@register']);
    Route::get('/country_code', ['as' => 'country_code', 'uses' => 'AuthController@country_code']);

    //forgot passwords

    Route::post('/forget-password', [ForgotPasswordController::class, 'sendEmail']);

    Route::middleware('api.auth')->post('logout', 'AuthController@logout');
    Route::middleware('api.auth')->get('profile', 'AuthController@myProfile');
    Route::middleware('api.auth')->get('profile/brief', 'AuthController@BriefProfile');

    Route::post('/forget-password', ['as' => 'forgot', 'uses' => 'ForgotPasswordController@sendEmail']);
    Route::post('/reset-password/{token}', ['as' => 'updatePassword', 'uses' => 'ResetPasswordController@updatePassword']);
    Route::post('/verification', ['as' => 'verification', 'uses' => 'VerificationController@confirmCode']);
    Route::get('/google', ['as' => 'google', 'uses' => 'SocialiteController@redirectToGoogle']);
    Route::get('/facebook', ['as' => 'google', 'uses' => 'SocialiteController@redirectToFacebook']);

    Route::post('/facebook/callback', ['as' => 'facebook_callback', 'uses' => 'SocialiteController@handleFacebookCallback']);

    // Route::get('auth/google', [AuthController::class, 'redirectToGoogle']); // Redirect to Google
    // Route::get('google/callback', [AuthController::class, 'handleGoogleCallback']);

    // Route::get('/reff/{code}', 'ReferralController@referral');

    // Route::post('register', [AuthController::class, 'register']);

});

Route::group(['namespace' => 'Auth'], function () {
    Route::get('/google/callback/', ['as' => 'google_callback', 'uses' => 'SocialiteController@handleGoogleCallback']);
});


// Route::post('/logout', ['as' => 'logout', 'uses' => 'Auth\LoginController@logout', 'middleware' => ['api.auth']]);
