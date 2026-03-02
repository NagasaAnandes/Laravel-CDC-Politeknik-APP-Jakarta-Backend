<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'CDC Backend API',
        'status' => 'running',
        'docs' => '/api/v1'

    ]);
});

Route::get('/partner', function () {
    return response()->json([
        'message' => 'Partner UI handled by frontend'
    ]);
});

Route::get('/admin', function () {
    return response()->json([
        'message' => 'Admin UI handled by frontend'
    ]);
});
