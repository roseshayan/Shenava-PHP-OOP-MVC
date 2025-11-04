<?php
/**
 * Shenava - Autoloader Class
 * PSR-4 compatible autoloader
 */

class Autoloader
{
    /**
     * Register autoloader
     */
    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * Load class file
     * @param string $className
     */
    public function loadClass(string $className): void
    {
        // Remove any namespace
        $className = str_replace('\\', '/', $className);

        // Define possible paths with exact file names
        $paths = [
            APP_PATH . '/core/' . $className . '.php',
            APP_PATH . '/controllers/' . $className . '.php',
            APP_PATH . '/models/' . $className . '.php',
            APP_PATH . '/middleware/' . $className . '.php',

            // Also try without 'Controller' suffix
            APP_PATH . '/controllers/' . str_replace('Controller', '', $className) . '.php',

            // For core classes without path
            APP_PATH . '/' . $className . '.php'
        ];

        foreach ($paths as $filePath) {
            if (file_exists($filePath)) {
                require_once $filePath;
                return;
            }
        }

        // Debug: Show which class is missing
        if (isset($_GET['debug_autoload'])) {
            error_log("Autoloader: Class '$className' not found. Searched paths: " . implode(', ', $paths));
        }
    }
}