<?php
/**
 * Shenava - API Routes
 * Define all API endpoints
 */

global $router;

$router->addRoute('POST', '/api/v1/auth/register', ['controller' => 'Auth', 'action' => 'register']);
$router->addRoute('POST', '/api/v1/auth/login', ['controller' => 'Auth', 'action' => 'login']);

// Books routes
$router->addRoute('GET', '/api/v1/books', ['controller' => 'Books', 'action' => 'index']);
$router->addRoute('GET', '/api/v1/books/featured', ['controller' => 'Books', 'action' => 'featured']);
$router->addRoute('GET', '/api/v1/books/{uuid}', ['controller' => 'Books', 'action' => 'show']);
$router->addRoute('GET', '/api/v1/books/category/{slug}', ['controller' => 'Books', 'action' => 'byCategory']);

// Categories routes
$router->addRoute('GET', '/api/v1/categories', ['controller' => 'Categories', 'action' => 'index']);