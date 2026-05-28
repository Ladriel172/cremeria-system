<?php
/**
 * UsuarioController — CRUD de usuarios
 * Acepta form-data (POST) y JSON
 */
require_once __DIR__ . '/../../_app.php';
require_once PROJECT_ROOT . '/app/middleware/AuthMiddleware.php';
require_once PROJECT_ROOT . '/app/middleware/RoleMiddleware.php';
AuthMiddleware::isAuthenticated();
RoleMiddleware::admin();

$isJson = (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);

if ($isJson) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    header('Content-Type: application/json; charset=utf-8');
} else {
    $input = $_POST;
}

// Validar CSRF
$token = $input['csrf_token'] ?? '';
if ($token !== ($_SESSION['csrf_token'] ?? '')) {
    if ($isJson) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
    } else {
        die("Token de seguridad inválido.");
    }
    exit();
}

$accion = $input['accion'] ?? '';

switch ($accion) {

    /* ---- CREAR USUARIO ---- */
    case 'crear':
        $nombre   = trim($input['nombre']   ?? '');
        $correo   = trim($input['correo']   ?? '');
        $password = $input['password'] ?? '';
        $rol      = in_array($input['rol'] ?? '', ['admin','vendedor']) ? $input['rol'] : 'vendedor';

        if (empty($nombre) || empty($correo) || empty($password)) {
            jsonOrRedirect($isJson, false, 'Nombre, email y contraseña son requeridos.',
                BASE_PATH . '/app/views/usuarios/index.php?error=campos');
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            jsonOrRedirect($isJson, false, 'El email no es válido.',
                BASE_PATH . '/app/views/usuarios/index.php?error=email');
        }
        if (strlen($password) < 6) {
            jsonOrRedirect($isJson, false, 'La contraseña debe tener al menos 6 caracteres.',
                BASE_PATH . '/app/views/usuarios/index.php?error=pass');
        }

        // Email único
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ?");
        $stmtCheck->execute([$correo]);
        if ($stmtCheck->fetchColumn() > 0) {
            jsonOrRedirect($isJson, false, 'El email ya está registrado.',
                BASE_PATH . '/app/views/usuarios/index.php?error=email_dup');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        $stmt = $db->prepare("INSERT INTO usuarios (nombre, correo, password, rol, estado) VALUES (?, ?, ?, ?, 'activo')");
        $stmt->execute([$nombre, $correo, $hash, $rol]);

        jsonOrRedirect($isJson, true, 'Usuario creado correctamente.',
            BASE_PATH . '/app/views/usuarios/index.php?msg=creado');
        break;

    /* ---- ACTUALIZAR USUARIO ---- */
    case 'actualizar':
        $id     = (int) ($input['id'] ?? 0);
        $nombre = trim($input['nombre']  ?? '');
        $correo = trim($input['correo']  ?? '');
        $rol    = in_array($input['rol'] ?? '', ['admin','vendedor']) ? $input['rol'] : 'vendedor';
        $pass   = $input['password'] ?? '';

        if (!$id || empty($nombre) || empty($correo)) {
            jsonOrRedirect($isJson, false, 'Nombre e email son requeridos.',
                BASE_PATH . '/app/views/usuarios/index.php?error=campos');
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            jsonOrRedirect($isJson, false, 'El email no es válido.',
                BASE_PATH . '/app/views/usuarios/index.php?error=email');
        }

        // Email único (excluyendo este usuario)
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ? AND id != ?");
        $stmtCheck->execute([$correo, $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            jsonOrRedirect($isJson, false, 'El email ya está en uso por otro usuario.',
                BASE_PATH . '/app/views/usuarios/index.php?error=email_dup');
        }

        if ($pass) {
            if (strlen($pass) < 6) {
                jsonOrRedirect($isJson, false, 'La contraseña debe tener al menos 6 caracteres.',
                    BASE_PATH . '/app/views/usuarios/index.php?error=pass');
            }
            $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 10]);
            $stmt = $db->prepare("UPDATE usuarios SET nombre=?, correo=?, rol=?, password=?, updated_at=datetime('now','localtime') WHERE id=?");
            $stmt->execute([$nombre, $correo, $rol, $hash, $id]);
        } else {
            $stmt = $db->prepare("UPDATE usuarios SET nombre=?, correo=?, rol=?, updated_at=datetime('now','localtime') WHERE id=?");
            $stmt->execute([$nombre, $correo, $rol, $id]);
        }

        jsonOrRedirect($isJson, true, 'Usuario actualizado correctamente.',
            BASE_PATH . '/app/views/usuarios/index.php?msg=actualizado');
        break;

    /* ---- TOGGLE ESTADO ---- */
    case 'toggle':
        $id = (int) ($input['id'] ?? 0);
        if (!$id) {
            header('Location: ' . BASE_PATH . '/app/views/usuarios/index.php?error=invalido');
            exit();
        }
        // No permitir bloquearse a sí mismo
        if ($id === (int) $_SESSION['id']) {
            header('Location: ' . BASE_PATH . '/app/views/usuarios/index.php?error=self');
            exit();
        }
        $stmt = $db->prepare("UPDATE usuarios SET estado = CASE WHEN estado='activo' THEN 'inactivo' ELSE 'activo' END WHERE id=?");
        $stmt->execute([$id]);
        header('Location: ' . BASE_PATH . '/app/views/usuarios/index.php?msg=bloqueado');
        exit();

    /* ---- RESET PASSWORD ---- */
    case 'reset_password':
        $id   = (int) ($input['id']       ?? 0);
        $pass = $input['password'] ?? '';

        if (!$id || strlen($pass) < 6) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            exit();
        }
        $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 10]);
        $stmt = $db->prepare("UPDATE usuarios SET password=?, updated_at=datetime('now','localtime') WHERE id=?");
        $stmt->execute([$hash, $id]);
        echo json_encode(['success' => true, 'message' => 'Contraseña actualizada.']);
        exit();

    /* ---- ELIMINAR USUARIO ---- */
    case 'eliminar':
        $id = (int) ($input['id'] ?? 0);
        if (!$id || $id === (int) $_SESSION['id']) {
            header('Location: ' . BASE_PATH . '/app/views/usuarios/index.php?error=invalido');
            exit();
        }
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id=?");
        $stmt->execute([$id]);
        header('Location: ' . BASE_PATH . '/app/views/usuarios/index.php?msg=eliminado');
        exit();

    default:
        header('Location: ' . BASE_PATH . '/app/views/usuarios/index.php');
        exit();
}

/* ============================================================
   Helper: JSON o redirect según el tipo de petición
   ============================================================ */
function jsonOrRedirect($isJson, $success, $message, $redirectUrl) {
    if ($isJson) {
        echo json_encode(['success' => $success, 'message' => $message]);
        exit();
    }
    header("Location: $redirectUrl");
    exit();
}
