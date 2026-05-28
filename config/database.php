<?php

/**
 * Conexión a base de datos — SQLite (portable) o MySQL (XAMPP)
 *
 * SQLite: funciona sin instalar nada, perfect para demo / ZIP portable.
 * MySQL : descomenta las líneas de abajo si prefieres XAMPP con MySQL.
 */

// ============================================================
// MODO SQLite (predeterminado — portable, sin servidor)
// ============================================================
function createSQLiteConnection(): PDO {
    $dbDir  = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database';
    $dbFile = $dbDir . DIRECTORY_SEPARATOR . 'cremeria.db';

    if (!is_dir($dbDir)) mkdir($dbDir, 0755, true);

    $pdo = new PDO('sqlite:' . $dbFile, null, null, [
        PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA foreign_keys = ON');

    // Auto-crear tablas si no existen
    initDatabase($pdo);

    return $pdo;
}

// ============================================================
// MODO MySQL (opcional — descomenta si usas XAMPP + MySQL)
// ============================================================
// function createMySQLConnection(): PDO {
//     $host   = 'localhost';
//     $dbname = 'cremeria_db';
//     $user   = 'root';
//     $pass   = '';
//     return new PDO(
//         "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
//         $user, $pass,
//         [
//             PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
//             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//             PDO::ATTR_EMULATE_PREPARES => false,
//         ]
//     );
// }

// ============================================================
// INICIALIZAR ESQUEMA SQLite
// ============================================================
function initDatabase(PDO $pdo): void {
    // Si la tabla usuarios ya existe, no hacer nada
    $check = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='usuarios'")->fetch();
    if ($check) return;

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS usuarios (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre     TEXT NOT NULL,
        correo     TEXT NOT NULL UNIQUE,
        password   TEXT NOT NULL,
        rol        TEXT NOT NULL DEFAULT 'vendedor' CHECK(rol IN ('admin','vendedor')),
        estado     TEXT NOT NULL DEFAULT 'activo'   CHECK(estado IN ('activo','inactivo')),
        created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
    );

    CREATE TABLE IF NOT EXISTS categorias (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre      TEXT NOT NULL UNIQUE,
        descripcion TEXT,
        activo      INTEGER NOT NULL DEFAULT 1,
        created_at  TEXT NOT NULL DEFAULT (datetime('now','localtime'))
    );

    CREATE TABLE IF NOT EXISTS productos (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo_barras TEXT,
        nombre        TEXT NOT NULL,
        descripcion   TEXT,
        precio        REAL NOT NULL DEFAULT 0,
        costo         REAL NOT NULL DEFAULT 0,
        stock         REAL NOT NULL DEFAULT 0,
        stock_minimo  REAL NOT NULL DEFAULT 5,
        tipo_medida   TEXT NOT NULL DEFAULT 'pieza'
                       CHECK(tipo_medida IN ('pieza','gramos','kg','litros','ml')),
        categoria     TEXT,
        imagen        TEXT,
        activo        INTEGER NOT NULL DEFAULT 1,
        created_at    TEXT NOT NULL DEFAULT (datetime('now','localtime')),
        updated_at    TEXT NOT NULL DEFAULT (datetime('now','localtime'))
    );

    CREATE TABLE IF NOT EXISTS ventas (
        id           INTEGER PRIMARY KEY AUTOINCREMENT,
        folio        TEXT NOT NULL UNIQUE,
        usuario_id   INTEGER NOT NULL,
        subtotal     REAL NOT NULL DEFAULT 0,
        descuento    REAL NOT NULL DEFAULT 0,
        iva          REAL NOT NULL DEFAULT 0,
        total        REAL NOT NULL DEFAULT 0,
        metodo_pago  TEXT NOT NULL DEFAULT 'efectivo'
                      CHECK(metodo_pago IN ('efectivo','tarjeta','transferencia','otro')),
        monto_pagado REAL NOT NULL DEFAULT 0,
        cambio       REAL NOT NULL DEFAULT 0,
        estado       TEXT NOT NULL DEFAULT 'completada'
                      CHECK(estado IN ('completada','anulada','pendiente')),
        notas        TEXT,
        created_at   TEXT NOT NULL DEFAULT (datetime('now','localtime')),
        updated_at   TEXT NOT NULL DEFAULT (datetime('now','localtime')),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    );

    CREATE TABLE IF NOT EXISTS detalle_ventas (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        venta_id        INTEGER NOT NULL,
        producto_id     INTEGER NOT NULL,
        nombre_producto TEXT NOT NULL,
        cantidad        REAL NOT NULL DEFAULT 1,
        precio_unitario REAL NOT NULL DEFAULT 0,
        descuento       REAL NOT NULL DEFAULT 0,
        subtotal        REAL NOT NULL DEFAULT 0,
        FOREIGN KEY (venta_id)    REFERENCES ventas(id) ON DELETE CASCADE,
        FOREIGN KEY (producto_id) REFERENCES productos(id)
    );

    CREATE TABLE IF NOT EXISTS movimientos_stock (
        id             INTEGER PRIMARY KEY AUTOINCREMENT,
        producto_id    INTEGER NOT NULL,
        usuario_id     INTEGER NOT NULL,
        tipo           TEXT NOT NULL CHECK(tipo IN ('entrada','salida','ajuste','venta','devolucion')),
        cantidad       REAL NOT NULL,
        stock_antes    REAL NOT NULL,
        stock_despues  REAL NOT NULL,
        referencia     TEXT,
        notas          TEXT,
        created_at     TEXT NOT NULL DEFAULT (datetime('now','localtime')),
        FOREIGN KEY (producto_id) REFERENCES productos(id),
        FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)
    );

    CREATE TABLE IF NOT EXISTS logs_auditoria (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id  INTEGER,
        accion      TEXT NOT NULL,
        tabla       TEXT,
        registro_id INTEGER,
        antes       TEXT,
        despues     TEXT,
        ip_address  TEXT,
        created_at  TEXT NOT NULL DEFAULT (datetime('now','localtime'))
    );
    ");

    // Datos iniciales
    seedDatabase($pdo);
}

function seedDatabase(PDO $pdo): void {
    // Usuario admin — contraseña: admin123
    $hash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 10]);
    $pdo->prepare("INSERT OR IGNORE INTO usuarios (nombre,correo,password,rol,estado) VALUES (?,?,?,?,?)")
        ->execute(['Administrador', 'admin@cremeria.com', $hash, 'admin', 'activo']);

    // Usuario vendedor — contraseña: vendedor123
    $hashV = password_hash('vendedor123', PASSWORD_BCRYPT, ['cost' => 10]);
    $pdo->prepare("INSERT OR IGNORE INTO usuarios (nombre,correo,password,rol,estado) VALUES (?,?,?,?,?)")
        ->execute(['María Vendedora', 'vendedor@cremeria.com', $hashV, 'vendedor', 'activo']);

    // Productos demo
    $productos = [
        ['7501055300001','Leche Lala 1L',     'Leche entera pasteurizada', 22.00, 14.50, 50, 10, 'litros',  'Lácteos'],
        ['7501055300002','Queso Manchego 400g','Queso manchego rebanado',   65.00, 42.00, 30,  5, 'gramos',  'Lácteos'],
        ['7501055300003','Crema Lala 200g',    'Crema ácida lista',         28.00, 18.00, 40,  8, 'gramos',  'Lácteos'],
        ['7501055300004','Coca-Cola 600ml',    'Refresco Coca-Cola',        18.00, 11.00,100, 20, 'litros',  'Bebidas'],
        ['7501055300005','Agua Bonafont 1L',   'Agua purificada',           14.00,  8.00, 80, 15, 'litros',  'Bebidas'],
        ['7501055300006','Pan Bimbo Blanco',   'Pan de caja grande',        35.00, 22.00, 40, 10, 'pieza',   'Panadería'],
        ['7501055300007','Papas Sabritas 45g', 'Papas fritas originales',   16.50, 10.00, 60, 12, 'pieza',   'Botanas'],
        ['7501055300008','Jamón del Diablo',   'Lata 198g',                 22.00, 14.00, 25,  5, 'pieza',   'Abarrotes'],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO productos
        (codigo_barras,nombre,descripcion,precio,costo,stock,stock_minimo,tipo_medida,categoria)
        VALUES (?,?,?,?,?,?,?,?,?)");
    foreach ($productos as $p) $stmt->execute($p);
}

// ============================================================
// CREAR CONEXIÓN GLOBAL $db
// ============================================================
try {
    $db = createSQLiteConnection();
} catch (Exception $e) {
    error_log("DB Error: " . $e->getMessage());
    die(json_encode(['error' => 'Error de base de datos. Revisa el archivo database/cremeria.db']));
}
