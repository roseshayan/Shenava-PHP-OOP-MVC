<?php

/**
 * Shenava - Main Configuration
 */

return [
    // App settings
    'app' => [
        'name' => 'Shenava',
        'version' => '1.0.0',
        'debug' => true,
        'base_url' => 'http://localhost/shenava/backend/public'
    ],

    // API settings
    'api' => [
        'version' => 'v1',
        'cors_enabled' => true,
        'rate_limit' => 1000 // requests per hour
    ],

    // Security settings
    'security' => [
        'jwt_secret' => 'Sudo-Salt', // Change in production
        'bcrypt_cost' => 12,
        'csrf_enabled' => true
    ]
];