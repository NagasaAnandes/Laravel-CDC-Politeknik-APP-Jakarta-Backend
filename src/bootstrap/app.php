<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Validation\ValidationException;
use App\Support\ApiResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Validation
        $exceptions->render(function (ValidationException $e, $request) {
            return ApiResponse::validation($e->errors());
        });

        // Model Not Found (findOrFail)
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            return ApiResponse::notFound('Resource not found');
        });

        // Authorization (Policy)
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            return ApiResponse::forbidden('You are not allowed to perform this action');
        });

        // Fallback (optional but recommended)
        $exceptions->render(function (\Throwable $e, $request) {
            if (config('app.debug')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return ApiResponse::error('Server error', 500);
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule
            ->command('jobs:deactivate-expired')
            ->daily()
            ->withoutOverlapping()
            ->runInBackground();

        $schedule
            ->command('events:deactivate-expired')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();
    })
    ->create();
