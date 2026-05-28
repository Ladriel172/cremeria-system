<?php

namespace App\Middleware;

use App\Helpers\SecurityHelper;

/**
 * Middleware CSRF
 * Valida tokens CSRF en requests POST/PUT/DELETE
 */
class CsrfMiddleware {

    /**
     * Validar token CSRF
     */
    public static function validate() {
        // Iniciar sesión si no está iniciada
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Generar token si no existe
        SecurityHelper::generateCsrfToken();

        // Validar solo en POST/PUT/DELETE
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if(in_array($method, ['POST', 'PUT', 'DELETE'])) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

            if(!$token || !SecurityHelper::validateCsrf($token)) {
                http_response_code(403);
                echo json_encode(['error' => 'Token CSRF inválido']);
                exit();
            }
        }
    }

    /**
     * Generar campo oculto para formularios
     */
    public static function field() {
        $token = SecurityHelper::generateCsrfToken();
        return "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . "'>";
    }
}
