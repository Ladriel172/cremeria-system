<?php
require_once __DIR__ . '/_app.php';

if (empty($_SESSION['usuario'])) {
    header('Location: ' . BASE_PATH . '/login.php');
    exit();
}

$target = ($_SESSION['rol'] ?? '') === 'admin'
    ? '/dashboard_admin.php'
    : '/dashboard_vendedor.php';

header('Location: ' . BASE_PATH . $target);
exit();
