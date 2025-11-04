<?php
/**
 * Shenava - Response Formatter
 * Standardizes API responses
 */

class ResponseFormatter
{
    /**
     * Format success response
     */
    public static function success($data = null, string $message = 'Success', int $statusCode = 200): array
    {
        return [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ];
    }

    /**
     * Format error response
     */
    public static function error(string $message = 'Error', int $statusCode = 400, $errors = null): array
    {
        return [
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
            'timestamp' => time()
        ];
    }

    /**
     * Format pagination response
     */
    public static function paginate($data, array $pagination): array
    {
        return [
            'items' => $data,
            'pagination' => $pagination
        ];
    }
}