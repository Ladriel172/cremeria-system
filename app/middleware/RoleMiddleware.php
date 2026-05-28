<?php

class RoleMiddleware {

    private static function boot() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!defined('BASE_PATH')) {
            $dir = __DIR__;
            while (!file_exists($dir . '/_app.php') && dirname($dir) !== $dir) $dir = dirname($dir);
            require_once $dir . '/_app.php';
        }
    }

    public static function admin() {
        self::boot();
        if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            header('Location: ' . BASE_PATH . '/dashboard_vendedor.php');
            exit();
        }
    }

    public static function vendedor() {
        self::boot();
        if (empty($_SESSION['rol'])) {
            header('Location: ' . BASE_PATH . '/index.html');
            exit();
        }
        if ($_SESSION['rol'] !== 'vendedor') {
            header('Location: ' . BASE_PATH . '/dashboard_admin.php');
            exit();
        }
    }

    public static function anyRole() {
        self::boot();
        if (empty($_SESSION['rol'])) {
            header('Location: ' . BASE_PATH . '/index.html');
            exit();
        }
    }
}
