<?php
/**
 * Shenava - Base Controller Class
 * Provides common functionality for all controllers
 */

class Controller
{

    protected $db;
    protected $config;

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
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send success response
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     */
    protected function success($data = null, $message = 'Success', $statusCode = 200)
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
     * @param mixed $errors
     */
    protected function error($message = 'Error', $statusCode = 400, $errors = null)
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
    protected function getRequestData()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
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
    protected function validateRequired($data, $requiredFields)
    {
        $errors = [];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = "The field '{$field}' is required.";
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Generate UUID v4
     * @return string
     */
    protected function generateUuid()
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
     * @return mixed
     */
    protected function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }

        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}