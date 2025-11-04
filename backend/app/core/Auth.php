<?php
/**
 * Shenava - Authentication Class
 * Handles user authentication and authorization
 */

class Auth
{

    private $userModel;
    private $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->config = require_once APP_PATH . '/config/config.php';
    }

    /**
     * Login user
     * @param string $email
     * @param string $password
     * @return array|bool
     */
    public function login($email, $password)
    {
        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->verifyPassword($password, $user->password_hash)) {
            return false;
        }

        if (!$user->is_active) {
            throw new Exception('Account is deactivated');
        }

        // Update last login
        $this->userModel->update($user->id, ['last_login' => date('Y-m-d H:i:s')]);

        return $this->getUserData($user);
    }

    /**
     * Register new user
     * @param array $userData
     * @return array
     */
    public function register($userData)
    {
        // Check if email exists
        if ($this->userModel->findByEmail($userData['email'])) {
            throw new Exception('Email already registered');
        }

        // Check if username exists
        if ($this->userModel->findByUsername($userData['username'])) {
            throw new Exception('Username already taken');
        }

        // Hash password
        $userData['password_hash'] = $this->hashPassword($userData['password']);
        unset($userData['password']);

        // Generate UUID
        $userData['uuid'] = $this->generateUuid();

        // Create user
        $userId = $this->userModel->create($userData);

        if (!$userId) {
            throw new Exception('Failed to create user');
        }

        $user = $this->userModel->find($userId);
        return $this->getUserData($user);
    }

    /**
     * Hash password
     * @param string $password
     * @return string
     */
    private function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, [
            'cost' => $this->config['security']['bcrypt_cost']
        ]);
    }

    /**
     * Verify password
     * @param string $password
     * @param string $hash
     * @return bool
     */
    private function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate UUID
     * @return string
     */
    private function generateUuid()
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
     * Get user data for response (without sensitive info)
     * @param object $user
     * @return array
     */
    private function getUserData($user)
    {
        return [
            'id' => $user->uuid,
            'username' => $user->username,
            'email' => $user->email,
            'display_name' => $user->display_name,
            'avatar_url' => $user->avatar_url,
            'is_premium' => (bool)$user->is_premium,
            'dark_mode' => (bool)$user->dark_mode,
            'sleep_timer_enabled' => (bool)$user->sleep_timer_enabled,
            'sleep_timer_duration' => (int)$user->sleep_timer_duration,
            'driving_mode' => (bool)$user->driving_mode,
            'created_at' => $user->created_at
        ];
    }
}