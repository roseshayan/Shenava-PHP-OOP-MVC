<?php

use JetBrains\PhpStorm\NoReturn;

/**
 * Shenava - Base Controller Class
 * Provides common functionality for all controllers
 */

class Controller
{

    protected Database $db;
    protected mixed $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = require_once APP_PATH . '/config/config.php';
    }

    /**
     * Send JSON response
     * @param mixed $data
     * @param int $statusCode
     */
    #[NoReturn]
    protected function jsonResponse(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send success response
     * @param mixed|null $data
     * @param string $message
     * @param int $statusCode
     */
    #[NoReturn]
    protected function success(mixed $data = null, string $message = 'Success', int $statusCode = 200): void
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];
        $this->jsonResponse($response, $statusCode);
    }

    /**
     * Send error response
     * @param string $message
     * @param int $statusCode
     * @param mixed|null $errors
     */
    #[NoReturn]
    protected function error(string $message = 'Error', int $statusCode = 400, mixed $errors = null): void
    {
        $response = [
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ];
        $this->jsonResponse($response, $statusCode);
    }

    /**
     * Get request data
     * @return array
     */
    protected function getRequestData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $input = file_get_contents('php://input');
            return json_decode($input, true) ?? [];
        }

        return $_POST;
    }

    /**
     * Validate required fields
     * @param array $data
     * @param array $requiredFields
     * @return array|bool
     */
    protected function validateRequired(array $data, array $requiredFields): bool|array
    {
        $errors = [];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = "The field '$field' is required.";
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Generate UUID v4
     * @return string
     */
    protected function generateUuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Sanitize input data
     * @param mixed $data
     * @return array|string
     */
    protected function sanitize(mixed $data): array|string
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }

        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}