<?php
require_once __DIR__ . '/_app.php';
require_once PROJECT_ROOT . '/app/middleware/AuthMiddleware.php';
require_once PROJECT_ROOT . '/app/middleware/RoleMiddleware.php';
AuthMiddleware::isAuthenticated();
RoleMiddleware::admin();

// --- KPIs del día ---
$hoy = date('Y-m-d');

// Total ventas hoy
$stmtVentas = $db->prepare("SELECT COALESCE(SUM(total),0) AS total, COUNT(*) AS cantidad FROM ventas WHERE DATE(created_at) = ? AND estado = 'completada'");
$stmtVentas->execute([$hoy]);
$ventasHoy = $stmtVentas->fetch();

// Total productos
$stmtProds = $db->query("SELECT COUNT(*) AS total, SUM(CASE WHEN stock <= stock_minimo THEN 1 ELSE 0 END) AS bajo_stock FROM productos WHERE activo = 1");
$statsProductos = $stmtProds->fetch();

// Total usuarios
$stmtUsers = $db->query("SELECT COUNT(*) AS total FROM usuarios WHERE estado = 'activo'");
$totalUsuarios = $stmtUsers->fetchColumn();

// Ventas últimos 7 días (para mini chart)
$stmtChart = $db->prepare("SELECT DATE(created_at) AS dia, COALESCE(SUM(total),0) AS total FROM ventas WHERE DATE(created_at) >= DATE('now','localtime','-7 days') AND estado='completada' GROUP BY DATE(created_at) ORDER BY dia ASC");
$stmtChart->execute();
$chartData = $stmtChart->fetchAll();

// Últimas ventas
$stmtUltimasVentas = $db->query("SELECT v.folio, v.total, v.metodo_pago, v.created_at, u.nombre AS vendedor FROM ventas v JOIN usuarios u ON v.usuario_id = u.id WHERE v.estado='completada' ORDER BY v.created_at DESC LIMIT 5");
$ultimasVentas = $stmtUltimasVentas->fetchAll();

// Productos con stock bajo
$stmtBajoStock = $db->query("SELECT nombre, stock, stock_minimo, tipo_medida FROM productos WHERE stock <= stock_minimo AND activo=1 ORDER BY stock ASC LIMIT 5");
$productosBajoStock = $stmtBajoStock->fetchAll();

$pageTitle = 'Dashboard';
$pageIcon  = 'bi-grid-1x2';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Cremería Francis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/public/css/admin.css?v=3">
</head>
<body>

<?php include 'app/views/layouts/sidebar_admin.php'; ?>

<div class="main-content fade-in">

    <?php include 'app/views/layouts/navbar_admin.php'; ?>

    <!-- KPIs -->
    <div class="row g-3 mb-4">

        <div class="col-6 col-lg-3">
            <div class="kpi-card blue">
                <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
                <div class="kpi-value"><?= '$' . number_format($ventasHoy['total'], 2) ?></div>
                <div class="kpi-label">Ventas hoy</div>
                <div class="kpi-trend up">
                    <i class="bi bi-receipt"></i>
                    <?= $ventasHoy['cantidad'] ?> transacciones
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card green">
                <div class="kpi-icon"><i class="bi bi-box-seam"></i></div>
                <div class="kpi-value"><?= $statsProductos['total'] ?? 0 ?></div>
                <div class="kpi-label">Productos activos</div>
                <?php if (($statsProductos['bajo_stock'] ?? 0) > 0): ?>
                <div class="kpi-trend down">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?= $statsProductos['bajo_stock'] ?> con stock bajo
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card amber">
                <div class="kpi-icon"><i class="bi bi-people"></i></div>
                <div class="kpi-value"><?= $totalUsuarios ?></div>
                <div class="kpi-label">Usuarios activos</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card cyan">
                <div class="kpi-icon"><i class="bi bi-calendar-check"></i></div>
                <div class="kpi-value"><?= date('d/m') ?></div>
                <div class="kpi-label"><?= date('l', strtotime('today')) ?></div>
                <div class="kpi-trend up">
                    <i class="bi bi-clock"></i> <?= date('H:i') ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Accesos rápidos -->
    <div class="row g-3 mb-4">

        <div class="col-6 col-md-3">
            <a href="<?= BASE_PATH ?>/app/views/ventas/index.php" class="dashboard-card blue">
                <div class="card-icon"><i class="bi bi-cart3"></i></div>
                <h5>POS Ventas</h5>
                <p>Registrar una venta</p>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="<?= BASE_PATH ?>/app/views/productos/index.php" class="dashboard-card green">
                <div class="card-icon"><i class="bi bi-box-seam"></i></div>
                <h5>Productos</h5>
                <p>Gestionar catálogo</p>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="<?= BASE_PATH ?>/app/views/usuarios/index.php" class="dashboard-card amber">
                <div class="card-icon"><i class="bi bi-people"></i></div>
                <h5>Usuarios</h5>
                <p>Administrar accesos</p>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="<?= BASE_PATH ?>/app/views/reportes/index.php" class="dashboard-card red">
                <div class="card-icon"><i class="bi bi-bar-chart-line"></i></div>
                <h5>Reportes</h5>
                <p>Ver estadísticas</p>
            </a>
        </div>

    </div>

    <!-- Tablas -->
    <div class="row g-3">

        <!-- Últimas ventas -->
        <div class="col-lg-7">
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">
                        <i class="bi bi-receipt"></i> Últimas Ventas
                    </span>
                    <a href="<?= BASE_PATH ?>/app/views/reportes/index.php" class="btn-ghost" style="font-size:12px;">
                        Ver todas <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <?php if (empty($ultimasVentas)): ?>
                <div class="panel-body text-center py-5">
                    <i class="bi bi-receipt" style="font-size:40px;color:var(--text-muted);opacity:.4;"></i>
                    <p style="color:var(--text-muted);margin-top:12px;font-size:14px;">Sin ventas registradas hoy</p>
                    <a href="<?= BASE_PATH ?>/app/views/ventas/index.php" class="btn-custom btn-primary-custom mt-2">
                        <i class="bi bi-cart3"></i> Ir al POS
                    </a>
                </div>
                <?php else: ?>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Vendedor</th>
                            <th>Total</th>
                            <th>Pago</th>
                            <th>Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ultimasVentas as $v): ?>
                        <tr>
                            <td><span style="color:var(--primary);font-weight:600;"><?= htmlspecialchars($v['folio']) ?></span></td>
                            <td><?= htmlspecialchars($v['vendedor']) ?></td>
                            <td class="text-success-c fw-bold">$<?= number_format($v['total'], 2) ?></td>
                            <td>
                                <span class="badge-custom info">
                                    <i class="bi bi-<?= $v['metodo_pago'] === 'efectivo' ? 'cash' : 'credit-card' ?>"></i>
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

        <!-- Stock bajo -->
        <div class="col-lg-5">
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">
                        <i class="bi bi-exclamation-triangle" style="color:var(--warning);"></i> Stock Bajo
                    </span>
                    <a href="<?= BASE_PATH ?>/app/views/productos/index.php" class="btn-ghost" style="font-size:12px;">
                        Ver productos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <?php if (empty($productosBajoStock)): ?>
                <div class="panel-body text-center py-5">
                    <i class="bi bi-check-circle" style="font-size:40px;color:var(--success);opacity:.6;"></i>
                    <p style="color:var(--text-muted);margin-top:12px;font-size:14px;">¡Todo el stock está bien!</p>
                </div>
                <?php else: ?>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Stock</th>
                            <th>Mínimo</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($productosBajoStock as $p): ?>
                        <tr>
                            <td style="font-size:13px;"><?= htmlspecialchars($p['nombre']) ?></td>
                            <td>
                                <span class="<?= $p['stock'] == 0 ? 'stock-danger' : 'stock-low' ?>">
                                    <?= $p['stock'] ?> <?= $p['tipo_medida'] ?>
                                </span>
                            </td>
                            <td style="color:var(--text-muted);font-size:13px;"><?= $p['stock_minimo'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_PATH ?>/public/js/admin.js"></script>

</body>
</html>
