<?php
/**
 * Shenava - Authentication Class
 * Handles user authentication and authorization
 */

class Auth
{
    private UserModel $userModel;
    private mixed $config;

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
     */
    public function login(string $email, string $password): bool|array
    {
        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->verifyPassword($password, $user->password_hash)) {
            return false;
        }

        if (!$user->is_active) {
            throw new Exception('Account is deactivated');
        }

        // Update last login
        try {
            $this->userModel->update($user->id, ['last_login' => date('Y-m-d H:i:s')]);
        } catch (Exception $e) {
            // Ignore if last_login field doesn't exist
            error_log('last_login field update failed: ' . $e->getMessage());
        }

        return $this->getUserData($user);
    }

    /**
     * Register new user
     */
    public function register(array $userData): array
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
     */
    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, [
            'cost' => $this->config['security']['bcrypt_cost'] ?? 12
        ]);
    }

    /**
     * Verify password
     */
    private function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate UUID
     */
    private function generateUuid(): string
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
     * Get user data for response (with token)
     */
    private function getUserData(object $user): array
    {
        $userData = [
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

        // Generate and add JWT token
        $userData['token'] = $this->generateToken($user);
        $userData['refresh_token'] = $this->generateRefreshToken($user);
        $userData['token_type'] = 'Bearer';
        $userData['expires_in'] = 3600 * 24 * 7; // 7 days in seconds

        return $userData;
    }

    /**
     * Generate JWT token
     */
    private function generateToken(object $user): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'iss' => $this->config['app']['name'] ?? 'Shenava',
            'sub' => $user->uuid,
            'iat' => time(),
            'exp' => time() + (3600 * 24 * 7), // 7 days
            'type' => 'access',
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        $base64Header = $this->base64UrlEncode($header);
        $base64Payload = $this->base64UrlEncode($payload);
        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $this->config['security']['jwt_secret'] ?? 'default_secret', true);
        $base64Signature = $this->base64UrlEncode($signature);

        return "$base64Header.$base64Payload.$base64Signature";
    }

    /**
     * Generate refresh token
     */
    private function generateRefreshToken(object $user): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'iss' => $this->config['app']['name'] ?? 'Shenava',
            'sub' => $user->uuid,
            'iat' => time(),
            'exp' => time() + (3600 * 24 * 30), // 30 days
            'type' => 'refresh',
            'user_id' => $user->id
        ]);

        $base64Header = $this->base64UrlEncode($header);
        $base64Payload = $this->base64UrlEncode($payload);
        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $this->config['security']['jwt_secret'] ?? 'default_secret', true);
        $base64Signature = $this->base64UrlEncode($signature);

        return "$base64Header.$base64Payload.$base64Signature";
    }

    /**
     * Verify JWT token
     */
    public function verifyToken(string $token): object
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }

        list($base64Header, $base64Payload, $base64Signature) = $parts;

        $signature = $this->base64UrlDecode($base64Signature);
        $expectedSignature = hash_hmac('sha256', "$base64Header.$base64Payload", $this->config['security']['jwt_secret'] ?? 'default_secret', true);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new Exception('Invalid token signature');
        }

        $payload = json_decode($this->base64UrlDecode($base64Payload));

        if ($payload->exp < time()) {
            throw new Exception('Token expired');
        }

        // Verify user exists and is active
        $user = $this->userModel->find($payload->user_id);
        if (!$user || !$user->is_active) {
            throw new Exception('User not found or inactive');
        }

        return $user;
    }

    /**
     * Refresh access token
     */
    public function refreshToken(string $refreshToken): array
    {
        try {
            $user = $this->verifyToken($refreshToken);

            // Verify it's a refresh token
            $parts = explode('.', $refreshToken);
            $payload = json_decode($this->base64UrlDecode($parts[1]));

            if ($payload->type !== 'refresh') {
                throw new Exception('Invalid token type');
            }

            return $this->getUserData($user);

        } catch (Exception $e) {
            throw new Exception('Token refresh failed: ' . $e->getMessage());
        }
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}