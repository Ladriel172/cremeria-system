<?php
require_once '../../../app/middleware/AuthMiddleware.php';
require_once '../../../app/middleware/RoleMiddleware.php';
AuthMiddleware::isAuthenticated();
RoleMiddleware::admin();

// Generar token CSRF
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../../../config/database.php';

// Categorías para el select
$cats = $db->query("SELECT DISTINCT categoria FROM productos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria ASC")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Nuevo Producto';
$pageIcon  = 'bi-plus-circle';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto — Cremería Francis</title>
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
            <div class="page-title"><i class="bi bi-plus-circle"></i> Nuevo Producto</div>
            <div class="page-subtitle">Agrega un producto al catálogo</div>
        </div>
        <div class="page-actions">
            <a href="index.php" class="btn-custom btn-secondary-custom">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="form-card" style="max-width:900px;">

        <div class="form-section-title">Información del Producto</div>
        <div class="form-section-sub">Completa todos los campos obligatorios marcados con *</div>

        <form action="../../../app/controllers/ProductoController.php"
              method="POST"
              enctype="multipart/form-data"
              id="form-producto">

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label-custom">Código de Barras</label>
                    <input type="text" name="codigo_barras" class="form-control-custom"
                           placeholder="7501000123456" autocomplete="off">
                </div>

                <div class="col-md-6">
                    <label class="form-label-custom">Nombre *</label>
                    <input type="text" name="nombre" class="form-control-custom"
                           placeholder="Nombre del producto" required autocomplete="off">
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">Precio de Venta *</label>
                    <input type="number" step="0.01" min="0" name="precio" class="form-control-custom"
                           placeholder="0.00" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">Costo</label>
                    <input type="number" step="0.01" min="0" name="costo" class="form-control-custom"
                           placeholder="0.00">
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">Stock Inicial *</label>
                    <input type="number" step="0.01" min="0" name="stock" class="form-control-custom"
                           placeholder="0" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">Stock Mínimo</label>
                    <input type="number" step="0.01" min="0" name="stock_minimo" class="form-control-custom"
                           placeholder="5" value="5">
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">Tipo de Medida *</label>
                    <select name="tipo_medida" class="form-control-custom" required>
                        <option value="pieza">Pieza</option>
                        <option value="gramos">Gramos</option>
                        <option value="kg">Kilogramos</option>
                        <option value="litros">Litros</option>
                        <option value="ml">Mililitros</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">Categoría</label>
                    <input type="text" name="categoria" class="form-control-custom"
                           placeholder="Lácteos, Bebidas..."
                           list="lista-categorias" autocomplete="off">
                    <datalist id="lista-categorias">
                        <?php foreach ($cats as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="col-md-6">
                    <label class="form-label-custom">Imagen del Producto</label>
                    <input type="file" name="imagen" class="form-control-custom"
                           accept="image/jpeg,image/png,image/webp">
                    <div style="font-size:11px;color:var(--text-muted);margin-top:6px;">
                        JPG, PNG o WEBP. Máximo 5 MB.
                    </div>
                </div>

                <div class="col-md-6" id="preview-wrap" style="display:none;">
                    <label class="form-label-custom">Vista previa</label>
                    <img id="img-preview" src="" alt="preview"
                         style="height:100px;border-radius:10px;object-fit:cover;border:1px solid var(--border);">
                </div>

                <div class="col-12">
                    <label class="form-label-custom">Descripción</label>
                    <textarea name="descripcion" class="form-control-custom"
                              placeholder="Descripción del producto..."></textarea>
                </div>

            </div>

            <div class="mt-4 d-flex gap-3">
                <button type="submit" class="btn-custom btn-primary-custom">
                    <i class="bi bi-floppy"></i> Guardar Producto
                </button>
                <a href="index.php" class="btn-custom btn-secondary-custom">
                    Cancelar
                </a>
            </div>

        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_PATH ?>/public/js/admin.js"></script>
<script>
// Preview de imagen
document.querySelector('[name="imagen"]').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('img-preview').src = e.target.result;
        document.getElementById('preview-wrap').style.display = 'block';
    };
    reader.readAsDataURL(file);
});
</script>

</body>
</html>
