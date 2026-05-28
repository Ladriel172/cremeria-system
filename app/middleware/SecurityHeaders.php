<?php

namespace App\Middleware;

/**
 * Middleware SecurityHeaders
 * Agrega headers de seguridad HTTP
 */
class SecurityHeaders {

    /**
     * Agregar headers de seguridad
     */
    public static function apply() {
        // Prevenir clickjacking
        header('X-Frame-Options: SAMEORIGIN');

        // Prevenir MIME type sniffing
        header('X-Content-Type-Options: nosniff');

        // Activar XSS filter del navegador
        header('X-XSS-Protection: 1; mode=block');

        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Permissions Policy (antiguo Feature Policy)
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // Strict Transport Security (solo HTTPS)
        // header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

        // Content Security Policy (básico)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data:;");

        // Evitar caching de contenido sensible
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    }
}
