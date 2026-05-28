<?php

namespace App\Helpers;

/**
 * Helper de Seguridad
 * Proporciona funciones para proteger contra ataques comunes
 */
class SecurityHelper {

    /**
     * Escapar HTML - Prevenir XSS
     * USO: echo escape($variable)
     */
    public static function escape($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitizar entrada - Remover caracteres peligrosos
     */
    public static function sanitizeInput($input) {
        if(is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }

        return trim(filter_var($input, FILTER_SANITIZE_STRING));
    }

    /**
     * Sanitizar email
     */
    public static function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitizar URL
     */
    public static function sanitizeUrl($url) {
        return filter_var(trim($url), FILTER_SANITIZE_URL);
    }

    /**
     * Generar token CSRF
     */
    public static function generateCsrfToken() {
        if(!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Obtener token CSRF desde sesión
     */
    public static function getCsrfToken() {
        return $_SESSION['csrf_token'] ?? null;
    }

    /**
     * Validar token CSRF
     */
    public static function validateCsrf($token) {
        $sessionToken = $_SESSION['csrf_token'] ?? null;

        if(!$sessionToken || !$token) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Hash de contraseña - Usar bcrypt
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, [
            'cost' => 10
        ]);
    }

    /**
     * Verificar contraseña
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Generar contraseña aleatoria
     */
    public static function generatePassword($length = 12) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        $password = '';

        for($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }

    /**
     * Validar formato de email
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validar tipo MIME de archivo
     */
    public static function isValidMimeType($filePath, $allowedTypes = []) {
        if(empty($allowedTypes)) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        return in_array($mimeType, $allowedTypes);
    }

    /**
     * Validar extensión de archivo
     */
    public static function isValidExtension($filename, $allowedExtensions = []) {
        if(empty($allowedExtensions)) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $allowedExtensions);
    }

    /**
     * Validar tamaño de archivo
     */
    public static function isValidFileSize($fileSize, $maxSizeInBytes = 5242880) { // 5MB default
        return $fileSize <= $maxSizeInBytes;
    }

    /**
     * Generar nombre de archivo seguro
     */
    public static function generateSafeFileName($originalName) {
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $name = pathinfo($originalName, PATHINFO_FILENAME);

        // Remover caracteres especiales
        $name = preg_replace('/[^A-Za-z0-9_-]/', '', $name);

        // Limitar longitud
        $name = substr($name, 0, 50);

        return time() . '_' . $name . '.' . $ext;
    }

    /**
     * Validar dirección IP
     */
    public static function getClientIp() {
        if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    /**
     * Registrar acción en auditoría
     */
    public static function logAudit($action, $table, $recordId, $before = null, $after = null) {
        global $db;

        try {
            $userId = $_SESSION['id'] ?? null;
            $ip = self::getClientIp();

            $query = "INSERT INTO logs_auditoria (usuario_id, accion, tabla, registro_id, antes, despues, ip_address)
                      VALUES (:usuario_id, :accion, :tabla, :registro_id, :antes, :despues, :ip)";

            $stmt = $db->prepare($query);

            $stmt->bindParam(':usuario_id', $userId, \PDO::PARAM_INT);
            $stmt->bindParam(':accion', $action);
            $stmt->bindParam(':tabla', $table);
            $stmt->bindParam(':registro_id', $recordId, \PDO::PARAM_INT);
            $stmt->bindParam(':antes', $before);
            $stmt->bindParam(':despues', $after);
            $stmt->bindParam(':ip', $ip);

            return $stmt->execute();

        } catch(\PDOException $e) {
            return false;
        }
    }
}
