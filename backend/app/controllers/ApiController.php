<?php
/**
 * Shenava - Base API Controller
 * Extended by all API controllers
 */

class ApiController extends Controller
{
    protected Auth $auth;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Load required classes manually if autoloader fails
        if (!class_exists('Auth')) {
            require_once APP_PATH . '/core/Auth.php';
        }
        if (!class_exists('UserModel')) {
            require_once APP_PATH . '/models/UserModel.php';
        }

        $this->auth = new Auth();
    }

    /**
     * Get pagination parameters from request
     * @return array
     */
    protected function getPaginationParams(): array
    {
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(50, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }
}