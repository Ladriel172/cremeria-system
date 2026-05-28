<?php
require_once __DIR__ . '/../../_app.php';
require_once PROJECT_ROOT . '/app/middleware/AuthMiddleware.php';
require_once PROJECT_ROOT . '/app/middleware/RoleMiddleware.php';
AuthMiddleware::isAuthenticated();
RoleMiddleware::admin();

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_PATH . '/app/views/productos/index.php');
    exit();
}

// Validar CSRF
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    die("Token de seguridad inválido.");
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . BASE_PATH . '/app/views/productos/index.php?error=invalido');
    exit();
}

try {
    // Obtener imagen antes de eliminar
    $stmt = $db->prepare("SELECT imagen FROM productos WHERE id = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$id]);
    $prod = $stmt->fetch();

    if (!$prod) {
        header('Location: ' . BASE_PATH . '/app/views/productos/index.php?error=no_encontrado');
        exit();
    }

    // Soft delete
    $stmtDel = $db->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
    $stmtDel->execute([$id]);

    // Borrar imagen física
    if ($prod['imagen']) {
        $ruta = dirname(__DIR__, 2) . '/public/img/products/' . $prod['imagen'];
        if (file_exists($ruta)) @unlink($ruta);
    }

    header('Location: ' . BASE_PATH . '/app/views/productos/index.php?msg=eliminado');
    exit();

} catch (PDOException $e) {
    error_log("DeleteProducto Error: " . $e->getMessage());
    header('Location: ' . BASE_PATH . '/app/views/productos/index.php?error=db');
    exit();
}
