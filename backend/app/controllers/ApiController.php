<?php
/**
 * Shenava - Base API Controller
 * Extended by all API controllers
 */

class ApiController extends Controller {

    protected Auth $auth;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
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