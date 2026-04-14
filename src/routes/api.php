<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\JobController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\TracerSurveyController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Endpoints
    |--------------------------------------------------------------------------
    */

    Route::middleware('throttle:120,1')->group(function () {

        Route::get('announcements', [AnnouncementController::class, 'index']);
        Route::get('announcements/{id}', [AnnouncementController::class, 'show'])
            ->whereNumber('id');

        Route::get('jobs', [JobController::class, 'index']);
        Route::get('jobs/{job}', [JobController::class, 'show'])
            ->whereNumber('job');

        Route::get('events', [EventController::class, 'index']);
        Route::get('events/{event}', [EventController::class, 'show'])
            ->whereNumber('event');
    });

    /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    */

    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    /*
    |--------------------------------------------------------------------------
    | Protected
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update'])
            ->middleware('throttle:30,1');

        Route::post('jobs/{job}/apply', [JobController::class, 'apply'])
            ->whereNumber('job')
            ->middleware('throttle:60,1');

        /*
        |--------------------------------------------------------------------------
        | EVENTS (FIXED ORDER)
        |--------------------------------------------------------------------------
        */

        Route::get('events/my', [EventController::class, 'myEvents']);

        Route::post('events/{event}/register', [EventController::class, 'register'])
            ->whereNumber('event')
            ->middleware('throttle:10,1');

        /*
        |--------------------------------------------------------------------------
        | TRACER
        |--------------------------------------------------------------------------
        */

        Route::get('tracer/survey', [TracerSurveyController::class, 'survey']);

        Route::post('tracer/submit', [TracerSurveyController::class, 'submit'])
            ->middleware('throttle:30,1');
    });
});
