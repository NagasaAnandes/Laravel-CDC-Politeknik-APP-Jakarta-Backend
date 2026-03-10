<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'CDC Backend API',
        'status' => 'running',
        'docs' => '/api/v1'

    ]);
});

Route::get('/debug-intended', function () {
    return session()->get('url.intended');
});
