<?php
require_once __DIR__ . '/_app.php';
require_once PROJECT_ROOT . '/app/middleware/AuthMiddleware.php';
require_once PROJECT_ROOT . '/app/middleware/RoleMiddleware.php';
AuthMiddleware::isAuthenticated();
RoleMiddleware::vendedor();

$hoy = date('Y-m-d');
$uid = (int) $_SESSION['id'];

$stmtV = $db->prepare("SELECT COALESCE(SUM(total),0) AS total, COUNT(*) AS cantidad FROM ventas WHERE usuario_id=? AND DATE(created_at)=? AND estado='completada'");
$stmtV->execute([$uid, $hoy]);
$misVentas = $stmtV->fetch();

$stmtUlt = $db->prepare("SELECT folio, total, metodo_pago, created_at FROM ventas WHERE usuario_id=? AND estado='completada' ORDER BY created_at DESC LIMIT 5");
$stmtUlt->execute([$uid]);
$ultimasVentas = $stmtUlt->fetchAll();

$pageTitle = 'Panel Vendedor';
$pageIcon  = 'bi-house';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Vendedor — Cremería Francis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/public/css/admin.css?v=3">
</head>
<body>

<?php include 'app/views/layouts/sidebar_vendedor.php'; ?>

<div class="main-content fade-in">

    <?php include 'app/views/layouts/navbar_admin.php'; ?>

    <div class="row g-3 mb-4">

        <div class="col-6 col-md-4">
            <div class="kpi-card blue">
                <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
                <div class="kpi-value">$<?= number_format($misVentas['total'], 2) ?></div>
                <div class="kpi-label">Mis ventas hoy</div>
                <div class="kpi-trend up">
                    <i class="bi bi-receipt"></i> <?= $misVentas['cantidad'] ?> transacciones
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="kpi-card green">
                <div class="kpi-icon"><i class="bi bi-calendar-check"></i></div>
                <div class="kpi-value"><?= date('d/m') ?></div>
                <div class="kpi-label"><?= date('l') ?></div>
                <div class="kpi-trend up"><i class="bi bi-clock"></i> <?= date('H:i') ?></div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <a href="<?= BASE_PATH ?>/app/views/ventas/index.php" class="dashboard-card blue" style="height:100%;min-height:130px;">
                <div class="card-icon"><i class="bi bi-cart3"></i></div>
                <h5>Ir al POS</h5>
                <p>Registrar nueva venta</p>
            </a>
        </div>

    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title"><i class="bi bi-receipt"></i> Mis últimas ventas</span>
        </div>
        <?php if (empty($ultimasVentas)): ?>
        <div class="panel-body text-center py-5">
            <i class="bi bi-receipt" style="font-size:40px;color:var(--text-muted);opacity:.4;"></i>
            <p style="color:var(--text-muted);margin-top:12px;font-size:14px;">Sin ventas registradas</p>
            <a href="<?= BASE_PATH ?>/app/views/ventas/index.php" class="btn-custom btn-primary-custom mt-2">
                <i class="bi bi-cart3"></i> Ir al POS
            </a>
        </div>
        <?php else: ?>
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Total</th>
                    <th>Pago</th>
                    <th>Hora</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($ultimasVentas as $v): ?>
            <tr>
                <td style="color:var(--primary);font-weight:600;font-family:monospace;"><?= htmlspecialchars($v['folio']) ?></td>
                <td class="text-success-c fw-bold">$<?= number_format($v['total'], 2) ?></td>
                <td>
                    <span class="badge-custom <?= $v['metodo_pago'] === 'efectivo' ? 'active' : 'info' ?>">
                        <?= ucfirst($v['metodo_pago']) ?>
                    </span>
                </td>
                <td style="color:var(--text-muted);font-size:12px;"><?= date('H:i', strtotime($v['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_PATH ?>/public/js/admin.js"></script>

</body>
</html>
