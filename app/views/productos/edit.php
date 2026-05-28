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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit();
}

$stmt = $db->prepare("SELECT * FROM productos WHERE id = ? AND activo = 1 LIMIT 1");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    header("Location: index.php?msg=no_encontrado");
    exit();
}

$cats = $db->query("SELECT DISTINCT categoria FROM productos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria ASC")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Editar Producto';
$pageIcon  = 'bi-pencil';

function esc($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function sel($val, $opt) { return $val == $opt ? 'selected' : ''; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto — Cremería Francis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/admin.css?v=3">
</head>
<body>

<?php include '../layouts/sidebar_admin.php'; ?>

<div class="main-content fade-in">

    <?php include '../layouts/navbar_admin.php'; ?>

    <div class="page-header">
        <div>
            <div class="page-title"><i class="bi bi-pencil"></i> Editar Producto</div>
            <div class="page-subtitle"><?= esc($p['nombre']) ?></div>
        </div>
        <div class="page-actions">
            <a href="index.php" class="btn-custom btn-secondary-custom">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="form-card" style="max-width:900px;">

        <div class="form-section-title">Información del Producto</div>
        <div class="form-section-sub">Modifica los datos del producto. La imagen es opcional.</div>

        <form action="../../../app/controllers/update_producto.php"
              method="POST"
              enctype="multipart/form-data">

            <input type="hidden" name="id"         value="<?= esc($p['id']) ?>">
            <input type="hidden" name="csrf_token"  value="<?= esc($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="imagen_actual" value="<?= esc($p['imagen']) ?>">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label-custom">Código de Barras</label>
                    <input type="text" name="codigo_barras" class="form-control-custom"
                           value="<?= esc($p['codigo_barras']) ?>" autocomplete="off">
                </div>

                <div class="col-md-6">
                    <label class="form-label-custom">Nombre *</label>
                    <input type="text" name="nombre" class="form-control-custom"
                           value="<?= esc($p['nombre']) ?>" required autocomplete="off">
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">Precio de Venta *</label>
                    <input type="number" step="0.01" min="0" name="precio" class="form-control-custom"
                           value="<?= esc($p['precio']) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">Costo</label>
                    <input type="number" step="0.01" min="0" name="costo" class="form-control-custom"
                           value="<?= esc($p['costo']) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">Stock *</label>
                    <input type="number" step="0.01" min="0" name="stock" class="form-control-custom"
                           value="<?= esc($p['stock']) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">Stock Mínimo</label>
                    <input type="number" step="0.01" min="0" name="stock_minimo" class="form-control-custom"
                           value="<?= esc($p['stock_minimo']) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">Tipo de Medida *</label>
                    <select name="tipo_medida" class="form-control-custom" required>
                        <option value="pieza"  <?= sel($p['tipo_medida'],'pieza') ?>>Pieza</option>
                        <option value="gramos" <?= sel($p['tipo_medida'],'gramos') ?>>Gramos</option>
                        <option value="kg"     <?= sel($p['tipo_medida'],'kg') ?>>Kilogramos</option>
                        <option value="litros" <?= sel($p['tipo_medida'],'litros') ?>>Litros</option>
                        <option value="ml"     <?= sel($p['tipo_medida'],'ml') ?>>Mililitros</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">Categoría</label>
                    <input type="text" name="categoria" class="form-control-custom"
                           value="<?= esc($p['categoria']) ?>"
                           list="lista-categorias" autocomplete="off">
                    <datalist id="lista-categorias">
                        <?php foreach ($cats as $c): ?>
                        <option value="<?= esc($c) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <!-- Imagen actual -->
                <div class="col-md-6">
                    <label class="form-label-custom">Imagen Actual</label>
                    <?php if ($p['imagen']): ?>
                    <div style="display:flex;align-items:center;gap:14px;">
                        <img id="img-actual"
                             src="../../../public/img/products/<?= esc($p['imagen']) ?>"
                             style="height:90px;border-radius:10px;object-fit:cover;border:1px solid var(--border);"
                             alt="imagen actual">
                    </div>
                    <?php else: ?>
                    <div style="height:70px;display:flex;align-items:center;color:var(--text-muted);font-size:13px;">
                        Sin imagen
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label-custom">Nueva Imagen <span style="color:var(--text-muted);font-weight:400;">(opcional)</span></label>
                    <input type="file" name="imagen" class="form-control-custom"
                           accept="image/jpeg,image/png,image/webp" id="input-imagen">
                    <div style="font-size:11px;color:var(--text-muted);margin-top:6px;">
                        Deja vacío para mantener la imagen actual.
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label-custom">Descripción</label>
                    <textarea name="descripcion" class="form-control-custom"><?= esc($p['descripcion']) ?></textarea>
                </div>

            </div>

            <div class="mt-4 d-flex gap-3">
                <button type="submit" class="btn-custom btn-primary-custom">
                    <i class="bi bi-floppy"></i> Actualizar Producto
                </button>
                <a href="index.php" class="btn-custom btn-secondary-custom">Cancelar</a>
            </div>

        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_PATH ?>/public/js/admin.js"></script>
<script>
document.getElementById('input-imagen').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('img-actual');
        if (img) img.src = e.target.result;
    };
    reader.readAsDataURL(file);
});
</script>

</body>
</html>
