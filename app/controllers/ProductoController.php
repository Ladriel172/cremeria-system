<?php
require_once __DIR__ . '/../../_app.php';
require_once PROJECT_ROOT . '/app/middleware/AuthMiddleware.php';
require_once PROJECT_ROOT . '/app/middleware/RoleMiddleware.php';
AuthMiddleware::isAuthenticated();
RoleMiddleware::admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_PATH . '/app/views/productos/index.php');
    exit();
}

// Validar CSRF
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    die("Token de seguridad inválido.");
}


// Sanitizar y validar
$nombre       = trim($_POST['nombre'] ?? '');
$codigo       = trim($_POST['codigo_barras'] ?? '');
$descripcion  = trim($_POST['descripcion'] ?? '');
$precio       = filter_input(INPUT_POST, 'precio',       FILTER_VALIDATE_FLOAT);
$costo        = filter_input(INPUT_POST, 'costo',        FILTER_VALIDATE_FLOAT) ?: 0;
$stock        = filter_input(INPUT_POST, 'stock',        FILTER_VALIDATE_FLOAT);
$stock_minimo = filter_input(INPUT_POST, 'stock_minimo', FILTER_VALIDATE_FLOAT) ?: 5;
$categoria    = trim($_POST['categoria'] ?? '');

$allowed_tipos = ['pieza','gramos','kg','litros','ml'];
$tipo_medida   = in_array($_POST['tipo_medida'] ?? '', $allowed_tipos) ? $_POST['tipo_medida'] : 'pieza';

// Validaciones básicas
if (empty($nombre) || $precio === false || $precio < 0 || $stock === false || $stock < 0) {
    header('Location: ' . BASE_PATH . '/app/views/productos/create.php?error=invalido');
    exit();
}

// Procesar imagen
$imagen = '';
if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $allowed_ext  = ['jpg','jpeg','png','webp'];
    $allowed_mime = ['image/jpeg','image/png','image/webp'];
    $ext  = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($_FILES['imagen']['tmp_name']);

    if (in_array($ext, $allowed_ext) && in_array($mime, $allowed_mime) && $_FILES['imagen']['size'] <= 5242880) {
        $imagen = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destino = dirname(__DIR__, 2) . '/public/img/products/' . $imagen;
        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
            $imagen = '';
        }
    }
}

try {
    $stmt = $db->prepare("INSERT INTO productos
        (codigo_barras, nombre, descripcion, precio, costo, stock, stock_minimo, tipo_medida, categoria, imagen)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([$codigo, $nombre, $descripcion, $precio, $costo, $stock, $stock_minimo, $tipo_medida, $categoria, $imagen]);

    header('Location: ' . BASE_PATH . '/app/views/productos/index.php?msg=creado');
    exit();

} catch (PDOException $e) {
    error_log("ProductoController Error: " . $e->getMessage());
    header('Location: ' . BASE_PATH . '/app/views/productos/create.php?error=db');
    exit();
}
