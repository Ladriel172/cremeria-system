<?php

class AuthMiddleware {

    private static function boot() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!defined('BASE_PATH')) {
            // Fallback si se llama antes del bootstrap
            $dir = __DIR__;
            while (!file_exists($dir . '/_app.php') && dirname($dir) !== $dir) $dir = dirname($dir);
            require_once $dir . '/_app.php';
        }
    }

    public static function isAuthenticated() {
        self::boot();
        if (empty($_SESSION['usuario'])) {
            header('Location: ' . BASE_PATH . '/index.html');
            exit();
        }
    }
}
