<?php
/**
 * Shenava - Auth Controller
 * Handles user authentication endpoints
 */

class AuthController extends ApiController
{

    /**
     * User registration
     */
    public function register()
    {
        try {
            $data = $this->getRequestData();

            // Validate required fields
            $required = ['username', 'email', 'password'];
            $validation = $this->validateRequired($data, $required);

            if ($validation !== true) {
                return $this->error('Validation failed', 422, $validation);
            }

            // Sanitize data
            $userData = $this->sanitize($data);

            // Register user
            $user = $this->auth->register($userData);

            return $this->success($user, 'User registered successfully', 201);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * User login
     */
    public function login()
    {
        try {
            $data = $this->getRequestData();

            // Validate required fields
            $required = ['email', 'password'];
            $validation = $this->validateRequired($data, $required);

            if ($validation !== true) {
                return $this->error('Validation failed', 422, $validation);
            }

            // Sanitize data
            $credentials = $this->sanitize($data);

            // Login user
            $user = $this->auth->login($credentials['email'], $credentials['password']);

            if (!$user) {
                return $this->error('Invalid credentials', 401);
            }

            return $this->success($user, 'Login successful');

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Refresh access token
     */
    public function refreshToken()
    {
        try {
            $data = $this->getRequestData();
            $refreshToken = $data['refresh_token'] ?? '';

            if (empty($refreshToken)) {
                return $this->error('Refresh token is required', 422);
            }

            // Refresh token
            $userData = $this->auth->refreshToken($refreshToken);

            return $this->success($userData, 'Token refreshed successfully');

        } catch (Exception $e) {
            return $this->error($e->getMessage(), 401);
        }
    }
}