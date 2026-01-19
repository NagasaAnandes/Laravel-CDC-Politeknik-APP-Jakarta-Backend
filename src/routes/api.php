<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\JobController;
use App\Http\Controllers\Api\V1\EventController;

Route::prefix('v1')->group(function () {

    // ===== JOBS (Guest View) =====
    Route::get('jobs', [JobController::class, 'index']);
    Route::get('jobs/{job}', [JobController::class, 'show']);

    // ===== EVENTS (Guest View) =====
    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{event}', [EventController::class, 'show']);

    // ===== AUTH =====
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // ===== PROFILE =====
        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);

        // ===== JOBS =====
        Route::post('jobs/{job}/apply', [JobController::class, 'apply']);

        // ===== EVENTS =====
        Route::post('events/{event}/register', [EventController::class, 'register']);
        Route::get('my/events', [EventController::class, 'myEvents']);
    });
});
