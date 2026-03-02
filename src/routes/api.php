<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\JobController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\AnnouncementController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Endpoints (Throttle Protected)
    |--------------------------------------------------------------------------
    */

    Route::middleware('throttle:120,1')->group(function () {

        // ANNOUNCEMENTS
        Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('announcements/{id}', [AnnouncementController::class, 'show'])->name('announcements.show');

        // JOBS
        Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
        Route::get('jobs/{job}', [JobController::class, 'show'])->name('jobs.show');

        // EVENTS
        Route::get('events', [EventController::class, 'index'])->name('events.index');
        Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1');

    /*
    |--------------------------------------------------------------------------
    | Authenticated Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('auth.me');

        // PROFILE
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [ProfileController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('profile.update');

        // JOB APPLY
        Route::post('jobs/{job}/apply', [JobController::class, 'apply'])
            ->middleware('throttle:60,1')
            ->name('jobs.apply');

        // EVENT REGISTER
        Route::post('events/{event}/register', [EventController::class, 'register'])
            ->middleware('throttle:30,1')
            ->name('events.register');

        Route::get('events/my', [EventController::class, 'myEvents'])
            ->name('events.mine');
    });
});
