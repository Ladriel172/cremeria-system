<?php
require_once '../../../app/middleware/AuthMiddleware.php';
require_once '../../../app/middleware/RoleMiddleware.php';
AuthMiddleware::isAuthenticated();
RoleMiddleware::admin();

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../../../config/database.php';

$msg   = $_GET['msg']   ?? '';
$error = $_GET['error'] ?? '';

// Obtener todos los usuarios
$usuarios = $db->query("SELECT id, nombre, correo, rol, estado, created_at FROM usuarios ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Usuarios';
$pageIcon  = 'bi-people';

function esc($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios — Cremería Francis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/admin.css?v=3">
</head>
<body>

<?php include '../layouts/sidebar_admin.php'; ?>

<div class="main-content fade-in">

    <?php include '../layouts/navbar_admin.php'; ?>

    <!-- Alertas -->
    <?php if ($msg): ?>
    <?php
    $alertMap = [
        'creado'      => 'rgba(16,185,129,.12)|rgba(16,185,129,.3)|#6EE7B7|bi-check-circle|Usuario creado correctamente.',
        'actualizado' => 'rgba(37,99,235,.12)|rgba(37,99,235,.3)|#93C5FD|bi-check-circle|Usuario actualizado correctamente.',
        'eliminado'   => 'rgba(239,68,68,.12)|rgba(239,68,68,.3)|#FCA5A5|bi-trash|Usuario eliminado.',
        'bloqueado'   => 'rgba(245,158,11,.12)|rgba(245,158,11,.3)|#FCD34D|bi-lock|Estado del usuario actualizado.',
    ];
    $alertStyle = $alertMap[$msg] ?? 'rgba(16,185,129,.12)|rgba(16,185,129,.3)|#6EE7B7|bi-info-circle|Operación exitosa.';
    [$bg, $border, $color, $icon, $text] = explode('|', $alertStyle);
    ?>
    <div class="alert-auto-hide" style="background:<?= $bg ?>;border:1px solid <?= $border ?>;color:<?= $color ?>;border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:14px;">
        <i class="bi <?= $icon ?> me-2"></i> <?= esc($text) ?>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="page-title"><i class="bi bi-people"></i> Usuarios</div>
            <div class="page-subtitle"><?= count($usuarios) ?> usuarios registrados</div>
        </div>
        <div class="page-actions">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="search-usuarios" placeholder="Buscar usuario..."
                       class="form-control-custom" oninput="filtrarTabla(this.value)">
            </div>
            <button class="btn-custom btn-primary-custom" onclick="abrirModal()">
                <i class="bi bi-person-plus"></i> Nuevo Usuario
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="panel">
        <?php if (empty($usuarios)): ?>
        <div class="panel-body text-center py-5">
            <i class="bi bi-people" style="font-size:50px;color:var(--text-muted);opacity:.3;"></i>
            <p style="color:var(--text-muted);margin-top:16px;font-size:14px;">Sin usuarios registrados</p>
        </div>
        <?php else: ?>
        <table class="table-custom" id="tabla-usuarios">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th style="width:140px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td style="color:var(--text-muted);font-size:12px;"><?= $u['id'] ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="user-avatar-sm <?= $u['rol'] === 'admin' ? 'avatar-admin' : 'avatar-vendedor' ?>">
                            <?= strtoupper(substr($u['nombre'], 0, 1)) ?>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:14px;"><?= esc($u['nombre']) ?></div>
                            <?php if ($u['id'] === (int)$_SESSION['id']): ?>
                            <div style="font-size:10px;color:var(--primary);">Tú</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td style="font-size:13px;color:var(--text-secondary);"><?= esc($u['correo']) ?></td>
                <td>
                    <span class="badge-custom <?= $u['rol'] === 'admin' ? 'info' : 'active' ?>">
                        <i class="bi bi-<?= $u['rol'] === 'admin' ? 'shield-check' : 'person' ?>"></i>
                        <?= ucfirst($u['rol']) ?>
                    </span>
                </td>
                <td>
                    <span class="badge-custom <?= $u['estado'] === 'activo' ? 'active' : 'inactive' ?>">
                        <i class="bi bi-circle-fill" style="font-size:7px;"></i>
                        <?= ucfirst($u['estado']) ?>
                    </span>
                </td>
                <td style="font-size:12px;color:var(--text-muted);">
                    <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                </td>
                <td>
                    <div style="display:flex;gap:5px;">
                        <button onclick="editarUsuario(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)"
                                class="btn-icon edit" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button onclick="toggleEstado(<?= $u['id'] ?>, '<?= esc($u['nombre']) ?>', '<?= $u['estado'] ?>')"
                                class="btn-icon toggle" title="<?= $u['estado'] === 'activo' ? 'Bloquear' : 'Activar' ?>">
                            <i class="bi bi-<?= $u['estado'] === 'activo' ? 'lock' : 'unlock' ?>"></i>
                        </button>
                        <button onclick="resetPassword(<?= $u['id'] ?>, '<?= esc($u['nombre']) ?>')"
                                class="btn-icon reset" title="Restablecer contraseña">
                            <i class="bi bi-key"></i>
                        </button>
                        <?php if ($u['id'] !== (int)$_SESSION['id']): ?>
                        <button onclick="eliminarUsuario(<?= $u['id'] ?>, '<?= esc($u['nombre']) ?>')"
                                class="btn-icon delete" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<!-- ==================== MODAL CREAR/EDITAR ==================== -->
<div class="modal fade" id="modal-usuario" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-titulo">
                    <i class="bi bi-person-plus"></i> Nuevo Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-usuario" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" id="campo-accion" value="crear">
                    <input type="hidden" name="id"     id="campo-id">
                    <input type="hidden" name="csrf_token" value="<?= esc($_SESSION['csrf_token']) ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label-custom">Nombre completo *</label>
                            <input type="text" name="nombre" id="campo-nombre" class="form-control-custom"
                                   placeholder="Nombre Apellido" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Email *</label>
                            <input type="email" name="correo" id="campo-correo" class="form-control-custom"
                                   placeholder="usuario@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Rol *</label>
                            <select name="rol" id="campo-rol" class="form-control-custom" required>
                                <option value="vendedor">Vendedor</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="wrap-password">
                            <label class="form-label-custom">Contraseña *</label>
                            <input type="password" name="password" id="campo-password"
                                   class="form-control-custom" placeholder="Mínimo 6 caracteres"
                                   minlength="6">
                        </div>
                    </div>

                    <div id="msg-modal" style="display:none;margin-top:14px;padding:10px 14px;border-radius:8px;font-size:13px;"></div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-custom btn-secondary-custom" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-custom btn-primary-custom" id="btn-guardar-usuario">
                        <i class="bi bi-floppy"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formularios ocultos para acciones POST -->
<form id="form-toggle"  method="POST" action="../../../app/controllers/UsuarioController.php" style="display:none;">
    <input type="hidden" name="accion" value="toggle">
    <input type="hidden" name="id"     id="toggle-id">
    <input type="hidden" name="csrf_token" value="<?= esc($_SESSION['csrf_token']) ?>">
</form>
<form id="form-delete-user" method="POST" action="../../../app/controllers/UsuarioController.php" style="display:none;">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id"     id="delete-user-id">
    <input type="hidden" name="csrf_token" value="<?= esc($_SESSION['csrf_token']) ?>">
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_PATH ?>/public/js/admin.js"></script>
<script>
const modalEl = document.getElementById('modal-usuario');
const modal   = new bootstrap.Modal(modalEl);
let modoEdicion = false;

function abrirModal() {
    modoEdicion = false;
    document.getElementById('modal-titulo').innerHTML = '<i class="bi bi-person-plus"></i> Nuevo Usuario';
    document.getElementById('form-usuario').action    = '../../../app/controllers/UsuarioController.php';
    document.getElementById('campo-accion').value     = 'crear';
    document.getElementById('campo-id').value         = '';
    document.getElementById('campo-nombre').value     = '';
    document.getElementById('campo-correo').value     = '';
    document.getElementById('campo-rol').value        = 'vendedor';
    document.getElementById('campo-password').value   = '';
    document.getElementById('campo-password').required = true;
    document.getElementById('wrap-password').querySelector('label').textContent = 'Contraseña *';
    ocultarMsgModal();
    modal.show();
    setTimeout(() => document.getElementById('campo-nombre').focus(), 400);
}

function editarUsuario(u) {
    modoEdicion = true;
    document.getElementById('modal-titulo').innerHTML = '<i class="bi bi-pencil"></i> Editar Usuario';
    document.getElementById('form-usuario').action    = '../../../app/controllers/UsuarioController.php';
    document.getElementById('campo-accion').value     = 'actualizar';
    document.getElementById('campo-id').value         = u.id;
    document.getElementById('campo-nombre').value     = u.nombre;
    document.getElementById('campo-correo').value     = u.correo;
    document.getElementById('campo-rol').value        = u.rol;
    document.getElementById('campo-password').value   = '';
    document.getElementById('campo-password').required = false;
    document.getElementById('wrap-password').querySelector('label').textContent = 'Nueva contraseña (dejar vacío para no cambiar)';
    ocultarMsgModal();
    modal.show();
}

function toggleEstado(id, nombre, estadoActual) {
    const accion = estadoActual === 'activo' ? 'bloquear' : 'activar';
    Swal.fire({
        title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} a "${nombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: estadoActual === 'activo' ? '#EF4444' : '#10B981',
        cancelButtonColor: '#334155',
        confirmButtonText: accion.charAt(0).toUpperCase() + accion.slice(1),
        cancelButtonText: 'Cancelar',
        background: '#1E293B', color: '#F8FAFC',
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('toggle-id').value = id;
            document.getElementById('form-toggle').submit();
        }
    });
}

function resetPassword(id, nombre) {
    Swal.fire({
        title: `Restablecer contraseña`,
        html: `<span style="color:#CBD5E1;">Ingresa la nueva contraseña para <strong style="color:#F8FAFC;">${nombre}</strong></span>`,
        input: 'password',
        inputPlaceholder: 'Nueva contraseña (mín. 6 caracteres)',
        inputAttributes: { minlength: 6, autocomplete: 'new-password' },
        showCancelButton: true,
        confirmButtonColor: '#06B6D4',
        cancelButtonColor: '#334155',
        confirmButtonText: '<i class="bi bi-key"></i> Restablecer',
        cancelButtonText: 'Cancelar',
        background: '#1E293B', color: '#F8FAFC',
        preConfirm: v => {
            if (!v || v.length < 6) {
                Swal.showValidationMessage('La contraseña debe tener al menos 6 caracteres.');
            }
            return v;
        }
    }).then(r => {
        if (r.isConfirmed) {
            fetch('../../../app/controllers/UsuarioController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    accion: 'reset_password',
                    id: id,
                    password: r.value,
                    csrf_token: '<?= esc($_SESSION['csrf_token']) ?>'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) Toast.success('Contraseña actualizada', `La contraseña de "${nombre}" fue restablecida.`);
                else Toast.error('Error', data.message || 'No se pudo restablecer.');
            });
        }
    });
}

function eliminarUsuario(id, nombre) {
    Swal.fire({
        title: '¿Eliminar usuario?',
        html: `<span style="color:#CBD5E1;">Se eliminará <strong style="color:#F8FAFC;">${nombre}</strong>.<br>Esta acción no se puede deshacer.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#334155',
        confirmButtonText: '<i class="bi bi-trash"></i> Eliminar',
        cancelButtonText: 'Cancelar',
        background: '#1E293B', color: '#F8FAFC',
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('delete-user-id').value = id;
            document.getElementById('form-delete-user').submit();
        }
    });
}

// Submit del modal via AJAX para no recargar
document.getElementById('form-usuario').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-guardar-usuario');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';

    const fd = new FormData(this);

    fetch(this.action, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            modal.hide();
            Toast.success('Éxito', data.message);
            setTimeout(() => location.reload(), 1200);
        } else {
            mostrarMsgModal(data.message, 'error');
        }
    })
    .catch(() => mostrarMsgModal('Error de red. Intenta de nuevo.', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-floppy"></i> Guardar';
    });
});

function mostrarMsgModal(msg, tipo) {
    const el = document.getElementById('msg-modal');
    el.style.display = 'block';
    el.style.background = tipo === 'error' ? 'rgba(239,68,68,.12)' : 'rgba(16,185,129,.12)';
    el.style.border     = tipo === 'error' ? '1px solid rgba(239,68,68,.3)' : '1px solid rgba(16,185,129,.3)';
    el.style.color      = tipo === 'error' ? '#FCA5A5' : '#6EE7B7';
    el.textContent      = msg;
}
function ocultarMsgModal() {
    document.getElementById('msg-modal').style.display = 'none';
}

function filtrarTabla(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#tabla-usuarios tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>

</body>
</html>
