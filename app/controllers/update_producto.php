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

if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    die("Token de seguridad inválido.");
}

$id           = filter_input(INPUT_POST, 'id',     FILTER_VALIDATE_INT);
$nombre       = trim($_POST['nombre'] ?? '');
$codigo       = trim($_POST['codigo_barras'] ?? '');
$descripcion  = trim($_POST['descripcion'] ?? '');
$precio       = filter_input(INPUT_POST, 'precio',       FILTER_VALIDATE_FLOAT);
$costo        = filter_input(INPUT_POST, 'costo',        FILTER_VALIDATE_FLOAT) ?: 0;
$stock        = filter_input(INPUT_POST, 'stock',        FILTER_VALIDATE_FLOAT);
$stock_minimo = filter_input(INPUT_POST, 'stock_minimo', FILTER_VALIDATE_FLOAT) ?: 5;
$categoria    = trim($_POST['categoria'] ?? '');
$imagen_actual = trim($_POST['imagen_actual'] ?? '');

$allowed_tipos = ['pieza','gramos','kg','litros','ml'];
$tipo_medida   = in_array($_POST['tipo_medida'] ?? '', $allowed_tipos) ? $_POST['tipo_medida'] : 'pieza';

if (!$id || empty($nombre) || $precio === false || $stock === false) {
    header('Location: ' . BASE_PATH . '/app/views/productos/index.php?error=invalido');
    exit();
}

// Procesar nueva imagen si se subió
$imagen = $imagen_actual;
if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $allowed_ext  = ['jpg','jpeg','png','webp'];
    $allowed_mime = ['image/jpeg','image/png','image/webp'];
    $ext  = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($_FILES['imagen']['tmp_name']);

    if (in_array($ext, $allowed_ext) && in_array($mime, $allowed_mime) && $_FILES['imagen']['size'] <= 5242880) {
        $nuevo_nombre = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destino = dirname(__DIR__, 2) . '/public/img/products/' . $nuevo_nombre;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
            // Borrar imagen anterior si existía
            if ($imagen_actual) {
                $ruta_vieja = dirname(__DIR__, 2) . '/public/img/products/' . $imagen_actual;
                if (file_exists($ruta_vieja)) @unlink($ruta_vieja);
            }
            $imagen = $nuevo_nombre;
        }
    }
}

try {
    $stmt = $db->prepare("UPDATE productos SET
        codigo_barras = ?, nombre = ?, descripcion = ?, precio = ?, costo = ?,
        stock = ?, stock_minimo = ?, tipo_medida = ?, categoria = ?, imagen = ?
        WHERE id = ? AND activo = 1");

    $stmt->execute([$codigo, $nombre, $descripcion, $precio, $costo,
                    $stock, $stock_minimo, $tipo_medida, $categoria, $imagen, $id]);

    header('Location: ' . BASE_PATH . '/app/views/productos/index.php?msg=actualizado');
    exit();

} catch (PDOException $e) {
    error_log("UpdateProducto Error: " . $e->getMessage());
    header('Location: ' . BASE_PATH . "/app/views/productos/edit.php?id={$id}&error=db");
    exit();
}
