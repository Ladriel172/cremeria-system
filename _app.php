<?php

/**
 * Bootstrap global — incluir al inicio de cada página PHP
 * Define constantes, inicia sesión, carga DB
 */

// Detectar raíz del proyecto desde cualquier subdirectorio
if (!defined('PROJECT_ROOT')) {
    // Sube directorios hasta encontrar _app.php
    $dir = __DIR__;
    while (!file_exists($dir . '/_app.php') && dirname($dir) !== $dir) {
        $dir = dirname($dir);
    }
    define('PROJECT_ROOT', $dir);
}

require_once PROJECT_ROOT . '/config/config.php';
require_once PROJECT_ROOT . '/config/database.php';

// Sesión segura
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE_PATH ?: '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Token CSRF global
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
