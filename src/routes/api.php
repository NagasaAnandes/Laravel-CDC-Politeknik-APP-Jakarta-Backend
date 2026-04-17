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

        Route::post('jobs/{job}/apply', [JobController::class, 'apply'])
            ->whereNumber('job')
            ->middleware('throttle:60,1');

        /*
        |--------------------------------------------------------------------------
        | PROFILE
        |--------------------------------------------------------------------------
        */

        Route::prefix('profile')->group(function () {

            // BASIC PROFILE
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('/', [ProfileController::class, 'update'])
                ->middleware('throttle:30,1');

            /*
            |--------------------------------------------------------------------------
            | CAREER PROFILE
            |--------------------------------------------------------------------------
            */

            // EXPERIENCES
            Route::get('experiences', [ProfileController::class, 'experiences']);
            Route::post('experiences', [ProfileController::class, 'storeExperience'])
                ->middleware('throttle:30,1');

            Route::put('experiences/{id}', [ProfileController::class, 'updateExperience'])
                ->whereNumber('id')
                ->middleware('throttle:30,1');

            Route::delete('experiences/{id}', [ProfileController::class, 'deleteExperience'])
                ->whereNumber('id');

            // EDUCATIONS
            Route::get('educations', [ProfileController::class, 'educations']);
            Route::post('educations', [ProfileController::class, 'storeEducation'])
                ->middleware('throttle:30,1');

            Route::put('educations/{id}', [ProfileController::class, 'updateEducation'])
                ->whereNumber('id')
                ->middleware('throttle:30,1');

            Route::delete('educations/{id}', [ProfileController::class, 'deleteEducation'])
                ->whereNumber('id');

            // CERTIFICATES
            Route::post('certificates', [ProfileController::class, 'storeCertificate'])
                ->middleware('throttle:20,1');

            Route::get('certificates/{id}/download', [ProfileController::class, 'downloadCertificate'])
                ->whereNumber('id');

            Route::delete('certificates/{id}', [ProfileController::class, 'deleteCertificate'])
                ->whereNumber('id');
        });

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
