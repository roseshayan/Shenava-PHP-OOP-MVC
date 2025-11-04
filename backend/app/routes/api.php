<?php
/**
 * Shenava - API Routes
 */

global $router;

// Root endpoint
$router->addRoute('GET', '/', ['controller' => 'Home', 'action' => 'index']);
$router->addRoute('GET', '/api/v1', ['controller' => 'Home', 'action' => 'index']);
$router->addRoute('GET', '/api/v1/health', ['controller' => 'Home', 'action' => 'health']);

// Auth routes
$router->addRoute('POST', '/api/v1/auth/register', ['controller' => 'Auth', 'action' => 'register']);
$router->addRoute('POST', '/api/v1/auth/login', ['controller' => 'Auth', 'action' => 'login']);
$router->addRoute('POST', '/api/v1/auth/refresh', ['controller' => 'Auth', 'action' => 'refreshToken']);

// Books routes
$router->addRoute('GET', '/api/v1/books', ['controller' => 'Books', 'action' => 'index']);
$router->addRoute('GET', '/api/v1/books/featured', ['controller' => 'Books', 'action' => 'featured']);
$router->addRoute('GET', '/api/v1/books/{uuid}', ['controller' => 'Books', 'action' => 'show']);
$router->addRoute('GET', '/api/v1/books/category/{slug}', ['controller' => 'Books', 'action' => 'byCategory']);

// Categories routes
$router->addRoute('GET', '/api/v1/categories', ['controller' => 'Categories', 'action' => 'index']);

// Audio routes
$router->addRoute('POST', '/api/v1/audio/progress', ['controller' => 'Audio', 'action' => 'updateProgress']);
$router->addRoute('GET', '/api/v1/audio/history', ['controller' => 'Audio', 'action' => 'listeningHistory']);
$router->addRoute('GET', '/api/v1/audio/chapter/{uuid}', ['controller' => 'Audio', 'action' => 'getChapterAudio']);

// User routes
$router->addRoute('GET', '/api/v1/user/profile', ['controller' => 'User', 'action' => 'profile']);
$router->addRoute('PUT', '/api/v1/user/preferences', ['controller' => 'User', 'action' => 'updatePreferences']);
$router->addRoute('GET', '/api/v1/user/favorites', ['controller' => 'User', 'action' => 'getFavorites']);
$router->addRoute('POST', '/api/v1/user/favorites/{book_uuid}', ['controller' => 'User', 'action' => 'addFavorite']);
$router->addRoute('DELETE', '/api/v1/user/favorites/{book_uuid}', ['controller' => 'User', 'action' => 'removeFavorite']);

// Debug route
$router->addRoute('GET', '/debug', ['controller' => 'Home', 'action' => 'debug']);