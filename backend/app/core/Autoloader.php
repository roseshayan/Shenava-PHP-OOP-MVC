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
    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * Load class file
     * @param string $className
     */
    public function loadClass($className)
    {
        // Convert namespace to file path
        $className = str_replace('\\', '/', $className);
        $filePath = APP_PATH . '/' . $className . '.php';

        if (file_exists($filePath)) {
            require_once $filePath;
        }
    }
}