<?php
require_once __DIR__ . '/_app.php';

$usuarios = (int) $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$productos = (int) $db->query("SELECT COUNT(*) FROM productos")->fetchColumn();
$dbFile = __DIR__ . '/database/cremeria.db';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Cremeria Francis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height:100vh; display:flex; align-items:center; justify-content:center; background:#0f172a; color:#e5e7eb; font-family:Arial,sans-serif; }
        .box { width:min(560px,92vw); background:#111827; border:1px solid rgba(255,255,255,.08); border-radius:14px; padding:28px; }
        .muted { color:#94a3b8; }
        code { color:#93c5fd; }
    </style>
</head>
<body>
    <main class="box">
        <h1 class="h3 mb-3">Cremeria Francis listo</h1>
        <p class="muted">La base SQLite se creo o verifico correctamente.</p>
        <ul>
            <li>Base de datos: <code><?= htmlspecialchars($dbFile) ?></code></li>
            <li>Usuarios: <strong><?= $usuarios ?></strong></li>
            <li>Productos demo: <strong><?= $productos ?></strong></li>
        </ul>
        <div class="alert alert-dark border-secondary mt-3">
            Admin: <strong>admin@cremeria.com</strong> / <strong>admin123</strong><br>
            Vendedor: <strong>vendedor@cremeria.com</strong> / <strong>vendedor123</strong>
        </div>
        <a class="btn btn-primary" href="<?= BASE_PATH ?>/login.php">Entrar al sistema</a>
    </main>
</body>
</html>
