<?php

namespace App\Core;

/**
 * Clase base para todos los controladores
 * Proporciona métodos comunes para renderizado y respuestas
 */
class BaseController {

    protected $db;

    public function __construct(PDO $db = null) {
        $this->db = $db;
    }

    /**
     * Renderizar vista con datos
     */
    protected function render($viewPath, $data = []) {
        extract($data);
        ob_start();
        require dirname(__DIR__) . "/views/{$viewPath}.php";
        return ob_get_clean();
    }

    /**
     * Redirigir a URL
     */
    protected function redirect($url) {
        header("Location: {$url}");
        exit();
    }

    /**
     * Respuesta JSON
     */
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Obtener entrada POST
     */
    protected function input($key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    /**
     * Obtener parámetro GET
     */
    protected function param($key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    /**
     * Validar que existe la sesión
     */
    protected function requireAuth() {
        if(!isset($_SESSION['usuario'])) {
            $basePath = defined('BASE_PATH') ? BASE_PATH : '';
            $this->redirect($basePath . '/index.html');
        }
    }

    /**
     * Validar rol específico
     */
    protected function requireRole($role) {
        $this->requireAuth();

        if($_SESSION['rol'] !== $role) {
            http_response_code(403);
            echo "Acceso denegado. Se requiere rol: {$role}";
            exit();
        }
    }

    /**
     * Validar token CSRF
     */
    protected function validateCsrf() {
        $token = $this->input('csrf_token');

        if(!$token || $token !== $_SESSION['csrf_token'] ?? null) {
            http_response_code(403);
            echo "Token CSRF inválido";
            exit();
        }
    }

    /**
     * Registrar en logs
     */
    protected function log($message, $level = 'INFO') {
        $logFile = dirname(__DIR__) . '/../../storage/logs/app.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$timestamp}] [{$level}] {$message}\n", FILE_APPEND);
    }
}
