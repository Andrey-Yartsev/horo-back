<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::get('/', 'Auth\MainController@get');
    Route::post('google/login', 'Auth\GoogleController@login');
    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('logout', 'Auth\MainController@logout');
    Route::post('email/check', 'Auth\EmailController@check');
    Route::post('email/forget', 'Auth\EmailController@forget');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
    Route::post('password/check', 'Auth\CheckPasswordController@check')->middleware('auth');
    Route::post('password/change', 'Auth\ChangePasswordController@change')->middleware('auth');
});

Route::prefix('stripe')->group(function () {
    Route::prefix('checkout')->group(function () {
        Route::post('sessions/create', 'Stripe\CheckoutSessionController@create')->middleware('auth');
    });
});

// Route::post('subscribe', 'Stripe\CheckoutSessionController@create')->middleware('auth');

Route::prefix('onboarding')->group(function () {
    Route::post('name', 'OnboardingController@applyName');
    Route::post('gender', 'OnboardingController@applyGender');
    Route::post('birth_date', 'OnboardingController@applyBirthDate');
    Route::post('birth_time', 'OnboardingController@applyBirthTime');
    Route::post('birth_place', 'OnboardingController@applyBirthPlace');
    Route::post('relationships', 'OnboardingController@applyRelationships');
    Route::post('interests', 'OnboardingController@applyInterests');
    Route::post('notifications', 'OnboardingController@applyNotifications');
});

Route::prefix('places')->group(function () {
    Route::get('autocomplete', 'PlaceController@autocomplete');
});

Route::prefix('users')->group(function () {
    Route::prefix('{user}')->group(function () {
        Route::post('unlock_full_access_closed', 'UserController@unlockFullAccessClosed')->middleware('auth');
        Route::post('subscription/initiate', 'UserController@initiateSubscription')->middleware('auth');
        Route::post('subscription/validate', 'UserController@validateSubscription')->middleware('auth');
        Route::post('subscription/create', 'UserController@createSubscription')->middleware('auth');
        Route::post('subscription/want_to_cancel', 'UserController@wantToCancelSubscription')->middleware('auth');
        Route::post('update', 'UserController@update')->middleware('auth');
        Route::get('reports/{report_type}/price', 'UserController@getReportPrice')->middleware('auth');
        Route::post('reports/{report_type}/initiate', 'UserController@initiateReport')->middleware('auth');
        Route::post('reports/{report_type}/validate', 'UserController@validateReport')->middleware('auth');
        Route::post('reports/{report_type}/create', 'UserController@createReport')->middleware('auth');
    });
});

Route::get('planets/{planet_slug}', 'PlanetController@get');
Route::get('compatibility/{sign0_code}/{sign1_code}', 'CompatibilityController@index');

Route::prefix('pages')->group(function () {
    Route::get('today', 'PageController@today');
    Route::get('you/basic', 'PageController@youBasic');
    Route::get('you/planets', 'PageController@youPlanets');
    Route::get('you/houses', 'PageController@youHouses');
});
