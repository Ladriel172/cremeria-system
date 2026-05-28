<?php

/**
 * Configuración global — auto-detecta BASE_URL
 * Funciona en XAMPP (/cremeria-system/), PHP built-in server (/) y hosting remoto
 */
if (!defined('APP_NAME')) {

    define('APP_NAME', 'Cremería Francis');

    // ---- Auto-detect base path ----
    // __DIR__ = .../cremeria-system/config   → subimos un nivel
    $projectRoot = dirname(__DIR__);
    $docRoot     = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $projectDir  = rtrim(str_replace('\\', '/', $projectRoot), '/');

    if ($docRoot && strpos($projectDir, $docRoot) === 0) {
        // XAMPP / Apache: el proyecto está dentro de document_root
        $basePath = substr($projectDir, strlen($docRoot));
    } else {
        // PHP built-in server o el proyecto ES el document_root
        $basePath = '';
    }

    define('BASE_PATH', $basePath);                       // e.g. "/cremeria-system" o ""
    define('BASE_URL',  'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $basePath);
}
