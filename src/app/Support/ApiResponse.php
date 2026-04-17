<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Success response
     */
    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Error response
     */
    public static function error(
        string $message = 'Something went wrong',
        int $code = 400,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Validation error shortcut
     */
    public static function validation(
        array $errors,
        ?string $message = 'Validation failed'
    ): JsonResponse {
        return self::error($message, 422, $errors);
    }

    /**
     * Not found shortcut
     */
    public static function notFound(
        ?string $message = 'Resource not found'
    ): JsonResponse {
        return self::error($message, 404);
    }

    /**
     * Unauthorized shortcut
     */
    public static function unauthorized(
        ?string $message = 'Unauthorized'
    ): JsonResponse {
        return self::error($message, 401);
    }

    /**
     * Forbidden shortcut
     */
    public static function forbidden(
        ?string $message = 'Forbidden'
    ): JsonResponse {
        return self::error($message, 403);
    }
}
