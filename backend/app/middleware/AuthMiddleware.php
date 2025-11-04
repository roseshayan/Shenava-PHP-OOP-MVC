<?php
/**
 * Shenava - Authentication Middleware
 */

class AuthMiddleware
{
    private Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    /**
     * Verify JWT token
     */
    public function authenticate(): object
    {
        $token = $this->getBearerToken();

        if (!$token) {
            http_response_code(401);
            echo json_encode(ResponseFormatter::error('Access token required', 401));
            exit;
        }

        try {
            return $this->auth->verifyToken($token);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(ResponseFormatter::error('Invalid token', 401));
            exit;
        }
    }

    /**
     * Get bearer token from header
     */
    private function getBearerToken(): ?string
    {
        $headers = $this->getAuthorizationHeader();

        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Get authorization header
     */
    private function getAuthorizationHeader(): ?string
    {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );

            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        return $headers;
    }
}