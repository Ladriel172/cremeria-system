<?php
require_once '../../../app/middleware/AuthMiddleware.php';
require_once '../../../app/middleware/RoleMiddleware.php';
AuthMiddleware::isAuthenticated();
RoleMiddleware::admin();

require_once '../../../config/database.php';

$msg = $_GET['msg'] ?? '';

$search = trim($_GET['q'] ?? '');
if ($search) {
    $stmt = $db->prepare("SELECT * FROM productos WHERE activo=1 AND (nombre LIKE :q OR codigo_barras LIKE :q OR categoria LIKE :q) ORDER BY nombre ASC");
    $stmt->execute([':q' => "%$search%"]);
} else {
    $stmt = $db->query("SELECT * FROM productos WHERE activo=1 ORDER BY nombre ASC");
}
$productos = $stmt->fetchAll();

$pageTitle = 'Productos';
$pageIcon  = 'bi-box-seam';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos — Cremería Francis</title>
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
    <?php if ($msg === 'creado'): ?>
    <div class="alert alert-auto-hide" style="background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#6EE7B7;border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:14px;">
        <i class="bi bi-check-circle me-2"></i> Producto creado correctamente.
    </div>
    <?php elseif ($msg === 'actualizado'): ?>
    <div class="alert alert-auto-hide" style="background:rgba(37,99,235,.12);border:1px solid rgba(37,99,235,.3);color:#93C5FD;border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:14px;">
        <i class="bi bi-check-circle me-2"></i> Producto actualizado correctamente.
    </div>
    <?php elseif ($msg === 'eliminado'): ?>
    <div class="alert alert-auto-hide" style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#FCA5A5;border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:14px;">
        <i class="bi bi-trash me-2"></i> Producto eliminado.
    </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="page-title"><i class="bi bi-box-seam"></i> Productos</div>
            <div class="page-subtitle"><?= count($productos) ?> productos en catálogo</div>
        </div>
        <div class="page-actions">
            <!-- Búsqueda -->
            <form method="GET" style="display:flex;gap:8px;">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                           placeholder="Buscar producto..."
                           class="form-control-custom" style="min-width:220px;">
                </div>
                <?php if ($search): ?>
                <a href="index.php" class="btn-custom btn-secondary-custom">
                    <i class="bi bi-x"></i>
                </a>
                <?php endif; ?>
            </form>
            <a href="create.php" class="btn-custom btn-primary-custom">
                <i class="bi bi-plus-lg"></i> Nuevo Producto
            </a>
        </div>
    </div>

    <!-- Tabla -->
    <div class="panel">
        <?php if (empty($productos)): ?>
        <div class="panel-body text-center py-5">
            <i class="bi bi-box-seam" style="font-size:50px;color:var(--text-muted);opacity:.3;"></i>
            <p style="color:var(--text-muted);margin-top:16px;font-size:14px;">
                <?= $search ? 'No se encontraron productos con "' . htmlspecialchars($search) . '"' : 'Sin productos registrados' ?>
            </p>
            <?php if (!$search): ?>
            <a href="create.php" class="btn-custom btn-primary-custom mt-2">
                <i class="bi bi-plus-lg"></i> Agregar primer producto
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <table class="table-custom" id="tabla-productos">
            <thead>
                <tr>
                    <th style="width:60px;">Img</th>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Categoría</th>
                    <th>Tipo</th>
                    <th style="width:100px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($productos as $p): ?>
            <?php
                $stockClass = 'stock-ok';
                if ($p['stock'] <= 0) $stockClass = 'stock-danger';
                elseif ($p['stock'] <= $p['stock_minimo']) $stockClass = 'stock-low';
            ?>
            <tr>
                <td>
                    <?php if ($p['imagen']): ?>
                    <img src="../../../public/img/products/<?= htmlspecialchars($p['imagen']) ?>"
                         class="product-image" alt="<?= htmlspecialchars($p['nombre']) ?>">
                    <?php else: ?>
                    <div class="product-image-placeholder"><i class="bi bi-image"></i></div>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="font-weight:600;font-size:14px;"><?= htmlspecialchars($p['nombre']) ?></div>
                    <?php if ($p['descripcion']): ?>
                    <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars(mb_substr($p['descripcion'], 0, 50)) ?>…</div>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;color:var(--text-muted);font-family:monospace;">
                    <?= htmlspecialchars($p['codigo_barras'] ?: '—') ?>
                </td>
                <td class="text-success-c fw-bold">$<?= number_format($p['precio'], 2) ?></td>
                <td>
                    <span class="<?= $stockClass ?>">
                        <?= number_format($p['stock'], 2, '.', '') ?>
                    </span>
                    <span style="font-size:11px;color:var(--text-muted);"> <?= htmlspecialchars($p['tipo_medida']) ?></span>
                </td>
                <td>
                    <?php if ($p['categoria']): ?>
                    <span class="badge-custom info"><?= htmlspecialchars($p['categoria']) ?></span>
                    <?php else: ?>
                    <span style="color:var(--text-muted);font-size:12px;">—</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;color:var(--text-muted);">
                    <?= htmlspecialchars(ucfirst($p['tipo_medida'])) ?>
                </td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn-icon edit" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button onclick="eliminarProducto(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nombre'])) ?>')"
                                class="btn-icon delete" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Formulario oculto para DELETE seguro -->
    <form id="form-delete" method="POST" action="../../../app/controllers/delete_producto.php" style="display:none;">
        <input type="hidden" name="id" id="delete-id">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_PATH ?>/public/js/admin.js"></script>
<script>
function eliminarProducto(id, nombre) {
    Swal.fire({
        title: '¿Eliminar producto?',
        html: `<span style="color:#CBD5E1;">Se eliminará <strong style="color:#F8FAFC;">${nombre}</strong>.<br>Esta acción no se puede deshacer.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#334155',
        confirmButtonText: '<i class="bi bi-trash"></i> Eliminar',
        cancelButtonText: 'Cancelar',
        background: '#1E293B',
        color: '#F8FAFC',
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('delete-id').value = id;
            document.getElementById('form-delete').submit();
        }
    });
}
</script>

</body>
</html>
