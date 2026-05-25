<?php

use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\JobController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'CDC Backend API',
        'status' => 'running',
        'docs' => '/api/v1'

    ]);
});

Route::get('/debug-intended', function () {
    return response()->json([
        'url.intended' => session('url.intended', ''),
    ]);
});

Route::middleware('throttle:120,1')->group(function () {
    Route::get('/jobs', [JobController::class, 'index']);
    Route::get('/jobs/{job}', [JobController::class, 'show'])
        ->whereNumber('job');

    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show'])
        ->whereNumber('event');

    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])
        ->whereNumber('announcement');
});
