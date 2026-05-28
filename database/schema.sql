-- ============================================================
-- CREMERÍA FRANCIS - Schema de Base de Datos
-- Versión: 2.0 | Motor: MySQL 5.7+
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `cremeria_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `cremeria_db`;

-- ============================================================
-- TABLA: usuarios
-- ============================================================
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`     VARCHAR(100) NOT NULL,
    `correo`     VARCHAR(150) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `rol`        ENUM('admin','vendedor') NOT NULL DEFAULT 'vendedor',
    `estado`     ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_correo`  (`correo`),
    INDEX `idx_rol`     (`rol`),
    INDEX `idx_estado`  (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: categorias
-- ============================================================
CREATE TABLE IF NOT EXISTS `categorias` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`      VARCHAR(80) NOT NULL UNIQUE,
    `descripcion` TEXT,
    `activo`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: productos
-- ============================================================
CREATE TABLE IF NOT EXISTS `productos` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo_barras` VARCHAR(50),
    `nombre`        VARCHAR(150) NOT NULL,
    `descripcion`   TEXT,
    `precio`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `costo`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `stock`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `stock_minimo`  DECIMAL(10,2) NOT NULL DEFAULT 5.00,
    `tipo_medida`   ENUM('pieza','gramos','litros','kg','ml') NOT NULL DEFAULT 'pieza',
    `categoria`     VARCHAR(80),
    `imagen`        VARCHAR(255),
    `activo`        TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_codigo_barras` (`codigo_barras`),
    INDEX `idx_nombre`        (`nombre`),
    INDEX `idx_categoria`     (`categoria`),
    INDEX `idx_activo`        (`activo`),
    INDEX `idx_stock`         (`stock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: ventas
-- ============================================================
CREATE TABLE IF NOT EXISTS `ventas` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `folio`          VARCHAR(30) NOT NULL UNIQUE,
    `usuario_id`     INT UNSIGNED NOT NULL,
    `subtotal`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `descuento`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `iva`            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `metodo_pago`    ENUM('efectivo','tarjeta','transferencia','otro') NOT NULL DEFAULT 'efectivo',
    `monto_pagado`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `cambio`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `estado`         ENUM('completada','anulada','pendiente') NOT NULL DEFAULT 'completada',
    `notas`          TEXT,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_folio` (`folio`),
    INDEX `idx_usuario_id`  (`usuario_id`),
    INDEX `idx_estado`      (`estado`),
    INDEX `idx_created_at`  (`created_at`),
    CONSTRAINT `fk_venta_usuario`
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: detalle_ventas
-- ============================================================
CREATE TABLE IF NOT EXISTS `detalle_ventas` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `venta_id`        INT UNSIGNED NOT NULL,
    `producto_id`     INT UNSIGNED NOT NULL,
    `nombre_producto` VARCHAR(150) NOT NULL,
    `cantidad`        DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    `precio_unitario` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `descuento`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `subtotal`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`id`),
    INDEX `idx_venta_id`    (`venta_id`),
    INDEX `idx_producto_id` (`producto_id`),
    CONSTRAINT `fk_detalle_venta`
        FOREIGN KEY (`venta_id`) REFERENCES `ventas`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_detalle_producto`
        FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: movimientos_stock
-- ============================================================
CREATE TABLE IF NOT EXISTS `movimientos_stock` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `producto_id` INT UNSIGNED NOT NULL,
    `usuario_id`  INT UNSIGNED NOT NULL,
    `tipo`        ENUM('entrada','salida','ajuste','venta','devolucion') NOT NULL,
    `cantidad`    DECIMAL(10,2) NOT NULL,
    `stock_antes` DECIMAL(10,2) NOT NULL,
    `stock_despues` DECIMAL(10,2) NOT NULL,
    `referencia`  VARCHAR(50) COMMENT 'Folio de venta u otra referencia',
    `notas`       TEXT,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_producto_id` (`producto_id`),
    INDEX `idx_usuario_id`  (`usuario_id`),
    INDEX `idx_tipo`        (`tipo`),
    INDEX `idx_created_at`  (`created_at`),
    CONSTRAINT `fk_mov_producto`
        FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_mov_usuario`
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: logs_auditoria
-- ============================================================
CREATE TABLE IF NOT EXISTS `logs_auditoria` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id` INT UNSIGNED,
    `accion`     VARCHAR(100) NOT NULL,
    `tabla`      VARCHAR(60),
    `registro_id` INT UNSIGNED,
    `antes`      JSON,
    `despues`    JSON,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(255),
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_usuario_id` (`usuario_id`),
    INDEX `idx_accion`     (`accion`),
    INDEX `idx_tabla`      (`tabla`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: sesiones_caja (para corte de caja futuro)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sesiones_caja` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id`       INT UNSIGNED NOT NULL,
    `fondo_inicial`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_ventas`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_efectivo`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_tarjeta`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `diferencia`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `estado`           ENUM('abierta','cerrada') NOT NULL DEFAULT 'abierta',
    `abierta_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `cerrada_at`       TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_usuario_id` (`usuario_id`),
    INDEX `idx_estado`     (`estado`),
    CONSTRAINT `fk_sesion_usuario`
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATOS INICIALES
-- ============================================================

-- Usuario administrador por defecto
-- Contraseña: Admin123! (cambiar en producción)
INSERT INTO `usuarios` (`nombre`, `correo`, `password`, `rol`, `estado`)
VALUES (
    'Administrador',
    'admin@cremeria.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'activo'
) ON DUPLICATE KEY UPDATE `id` = `id`;

-- Usuario vendedor demo
-- Contraseña: Vendedor1!
INSERT INTO `usuarios` (`nombre`, `correo`, `password`, `rol`, `estado`)
VALUES (
    'María Vendedora',
    'vendedor@cremeria.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'vendedor',
    'activo'
) ON DUPLICATE KEY UPDATE `id` = `id`;

-- Categorías base
INSERT IGNORE INTO `categorias` (`nombre`) VALUES
    ('Lácteos'),
    ('Bebidas'),
    ('Botanas'),
    ('Carnes'),
    ('Frutas y Verduras'),
    ('Panadería'),
    ('Limpieza'),
    ('Abarrotes');

-- Productos de ejemplo
INSERT INTO `productos`
    (`codigo_barras`, `nombre`, `descripcion`, `precio`, `costo`, `stock`, `stock_minimo`, `tipo_medida`, `categoria`)
VALUES
    ('7501055300075', 'Leche Lala 1L', 'Leche entera pasteurizada', 22.00, 15.00, 50, 10, 'litros', 'Lácteos'),
    ('7501055300076', 'Queso Manchego 400g', 'Queso manchego rebanado', 65.00, 45.00, 30, 5, 'gramos', 'Lácteos'),
    ('7501055300077', 'Coca-Cola 600ml', 'Refresco Coca-Cola botella', 18.00, 11.00, 100, 20, 'litros', 'Bebidas'),
    ('7501055300078', 'Pan Bimbo Blanco', 'Pan de caja blanco grande', 35.00, 24.00, 40, 8, 'pieza', 'Panadería'),
    ('7501055300079', 'Crema Lala 200g', 'Crema ácida lista para servir', 28.00, 19.00, 25, 5, 'gramos', 'Lácteos')
ON DUPLICATE KEY UPDATE `id` = `id`;
