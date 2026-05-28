<?php
require_once '../../../app/middleware/AuthMiddleware.php';
require_once '../../../app/middleware/RoleMiddleware.php';
AuthMiddleware::isAuthenticated();
RoleMiddleware::admin();

require_once '../../../config/database.php';

// --- Filtros de fecha ---
$desde = $_GET['desde'] ?? date('Y-m-01');       // Primer día del mes
$hasta = $_GET['hasta'] ?? date('Y-m-d');         // Hoy

// Validar fechas
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) $desde = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) $hasta = date('Y-m-d');
if ($desde > $hasta) [$desde, $hasta] = [$hasta, $desde];

// --- KPIs del período ---
$stmtKpi = $db->prepare("
    SELECT
        COALESCE(SUM(total),0)   AS total_vendido,
        COUNT(*)                  AS num_ventas,
        COALESCE(AVG(total),0)   AS ticket_promedio,
        COALESCE(SUM(CASE WHEN metodo_pago='efectivo'   THEN total ELSE 0 END),0) AS efectivo,
        COALESCE(SUM(CASE WHEN metodo_pago='tarjeta'    THEN total ELSE 0 END),0) AS tarjeta,
        COALESCE(SUM(CASE WHEN metodo_pago='transferencia' THEN total ELSE 0 END),0) AS transferencia
    FROM ventas
    WHERE DATE(created_at) BETWEEN ? AND ? AND estado = 'completada'
");
$stmtKpi->execute([$desde, $hasta]);
$kpi = $stmtKpi->fetch();

// --- Ventas por día (para gráfica de líneas) ---
$stmtDias = $db->prepare("
    SELECT DATE(created_at) AS dia, COALESCE(SUM(total),0) AS total, COUNT(*) AS n
    FROM ventas
    WHERE DATE(created_at) BETWEEN ? AND ? AND estado='completada'
    GROUP BY DATE(created_at)
    ORDER BY dia ASC
");
$stmtDias->execute([$desde, $hasta]);
$ventasDias = $stmtDias->fetchAll();

// --- Top 10 productos más vendidos ---
$stmtTop = $db->prepare("
    SELECT dv.nombre_producto,
           COALESCE(SUM(dv.cantidad),0) AS total_qty,
           COALESCE(SUM(dv.subtotal),0) AS total_monto
    FROM detalle_ventas dv
    JOIN ventas v ON dv.venta_id = v.id
    WHERE DATE(v.created_at) BETWEEN ? AND ? AND v.estado='completada'
    GROUP BY dv.nombre_producto
    ORDER BY total_qty DESC
    LIMIT 10
");
$stmtTop->execute([$desde, $hasta]);
$topProductos = $stmtTop->fetchAll();

// --- Últimas ventas del período ---
$stmtVentas = $db->prepare("
    SELECT v.folio, v.total, v.metodo_pago, v.created_at, u.nombre AS vendedor,
           COUNT(dv.id) AS num_productos
    FROM ventas v
    JOIN usuarios u ON v.usuario_id = u.id
    LEFT JOIN detalle_ventas dv ON dv.venta_id = v.id
    WHERE DATE(v.created_at) BETWEEN ? AND ? AND v.estado='completada'
    GROUP BY v.id
    ORDER BY v.created_at DESC
    LIMIT 20
");
$stmtVentas->execute([$desde, $hasta]);
$ventasList = $stmtVentas->fetchAll();

// --- Datos para Chart.js ---
$chartLabels  = array_column($ventasDias, 'dia');
$chartTotales = array_column($ventasDias, 'total');
$topNombres   = array_column($topProductos, 'nombre_producto');
$topCants     = array_column($topProductos, 'total_qty');

$pageTitle = 'Reportes';
$pageIcon  = 'bi-bar-chart-line';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes — Cremería Francis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/admin.css?v=3">
</head>
<body>

<?php include '../layouts/sidebar_admin.php'; ?>

<div class="main-content fade-in">

    <?php include '../layouts/navbar_admin.php'; ?>

    <!-- Header con filtros -->
    <div class="page-header">
        <div>
            <div class="page-title"><i class="bi bi-bar-chart-line"></i> Reportes</div>
            <div class="page-subtitle">
                <?= date('d/m/Y', strtotime($desde)) ?> — <?= date('d/m/Y', strtotime($hasta)) ?>
            </div>
        </div>
        <div class="page-actions">
            <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:6px;">
                    <label style="font-size:13px;color:var(--text-muted);white-space:nowrap;">Desde</label>
                    <input type="date" name="desde" value="<?= $desde ?>"
                           class="form-control-custom" style="width:145px;">
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <label style="font-size:13px;color:var(--text-muted);white-space:nowrap;">Hasta</label>
                    <input type="date" name="hasta" value="<?= $hasta ?>"
                           class="form-control-custom" style="width:145px;">
                </div>
                <button type="submit" class="btn-custom btn-primary-custom">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </form>
            <!-- Atajos de período -->
            <div style="display:flex;gap:6px;">
                <a href="?desde=<?= date('Y-m-d') ?>&hasta=<?= date('Y-m-d') ?>" class="btn-ghost" style="font-size:12px;">Hoy</a>
                <a href="?desde=<?= date('Y-m-d', strtotime('monday this week')) ?>&hasta=<?= date('Y-m-d') ?>" class="btn-ghost" style="font-size:12px;">Esta semana</a>
                <a href="?desde=<?= date('Y-m-01') ?>&hasta=<?= date('Y-m-d') ?>" class="btn-ghost" style="font-size:12px;">Este mes</a>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row g-3 mb-4">

        <div class="col-6 col-lg-3">
            <div class="kpi-card blue">
                <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
                <div class="kpi-value">$<?= number_format($kpi['total_vendido'], 0) ?></div>
                <div class="kpi-label">Total vendido</div>
                <div class="kpi-trend up">
                    <i class="bi bi-receipt"></i> <?= $kpi['num_ventas'] ?> transacciones
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card green">
                <div class="kpi-icon"><i class="bi bi-graph-up"></i></div>
                <div class="kpi-value">$<?= number_format($kpi['ticket_promedio'], 2) ?></div>
                <div class="kpi-label">Ticket promedio</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card amber">
                <div class="kpi-icon"><i class="bi bi-cash"></i></div>
                <div class="kpi-value">$<?= number_format($kpi['efectivo'], 0) ?></div>
                <div class="kpi-label">En efectivo</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card cyan">
                <div class="kpi-icon"><i class="bi bi-credit-card"></i></div>
                <div class="kpi-value">$<?= number_format($kpi['tarjeta'], 0) ?></div>
                <div class="kpi-label">Con tarjeta</div>
            </div>
        </div>

    </div>

    <!-- Gráficas -->
    <div class="row g-3 mb-4">

        <!-- Ventas por día -->
        <div class="col-lg-8">
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">
                        <i class="bi bi-graph-up-arrow"></i> Ventas por día
                    </span>
                </div>
                <div class="panel-body">
                    <?php if (empty($ventasDias)): ?>
                    <div style="text-align:center;padding:40px;color:var(--text-muted);">
                        <i class="bi bi-bar-chart" style="font-size:40px;opacity:.3;"></i>
                        <p style="margin-top:10px;font-size:13px;">Sin datos en este período</p>
                    </div>
                    <?php else: ?>
                    <div class="chart-wrapper">
                        <canvas id="chartVentas"></canvas>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Métodos de pago -->
        <div class="col-lg-4">
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">
                        <i class="bi bi-pie-chart"></i> Métodos de pago
                    </span>
                </div>
                <div class="panel-body">
                    <?php if ($kpi['num_ventas'] == 0): ?>
                    <div style="text-align:center;padding:40px;color:var(--text-muted);">
                        <i class="bi bi-pie-chart" style="font-size:40px;opacity:.3;"></i>
                        <p style="margin-top:10px;font-size:13px;">Sin datos</p>
                    </div>
                    <?php else: ?>
                    <div class="chart-wrapper">
                        <canvas id="chartMetodos"></canvas>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Top Productos + Lista de ventas -->
    <div class="row g-3">

        <!-- Top productos -->
        <div class="col-lg-5">
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">
                        <i class="bi bi-trophy"></i> Top Productos
                    </span>
                    <span style="font-size:12px;color:var(--text-muted);">Por unidades vendidas</span>
                </div>
                <?php if (empty($topProductos)): ?>
                <div class="panel-body text-center py-4">
                    <p style="color:var(--text-muted);font-size:13px;">Sin datos en este período</p>
                </div>
                <?php else: ?>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th>Uds.</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($topProductos as $i => $tp): ?>
                    <tr>
                        <td style="color:var(--text-muted);font-size:12px;">
                            <?php if ($i === 0): ?>🥇<?php elseif ($i === 1): ?>🥈<?php elseif ($i === 2): ?>🥉<?php else: echo $i+1; endif; ?>
                        </td>
                        <td style="font-size:13px;"><?= htmlspecialchars($tp['nombre_producto']) ?></td>
                        <td style="font-weight:600;color:var(--primary);"><?= number_format($tp['total_qty'], 0) ?></td>
                        <td class="text-success-c" style="font-size:13px;font-weight:600;">$<?= number_format($tp['total_monto'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Últimas ventas -->
        <div class="col-lg-7">
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">
                        <i class="bi bi-receipt"></i> Detalle de Ventas
                    </span>
                    <span style="font-size:12px;color:var(--text-muted);">Últimas 20</span>
                </div>
                <?php if (empty($ventasList)): ?>
                <div class="panel-body text-center py-4">
                    <p style="color:var(--text-muted);font-size:13px;">Sin ventas en este período</p>
                </div>
                <?php else: ?>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Vendedor</th>
                            <th>Prods.</th>
                            <th>Total</th>
                            <th>Pago</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ventasList as $v): ?>
                    <tr>
                        <td style="color:var(--primary);font-weight:600;font-size:12px;font-family:monospace;">
                            <?= htmlspecialchars($v['folio']) ?>
                        </td>
                        <td style="font-size:13px;"><?= htmlspecialchars($v['vendedor']) ?></td>
                        <td style="color:var(--text-muted);font-size:13px;"><?= $v['num_productos'] ?></td>
                        <td class="text-success-c fw-bold">$<?= number_format($v['total'], 2) ?></td>
                        <td>
                            <span class="badge-custom <?= $v['metodo_pago'] === 'efectivo' ? 'active' : 'info' ?>">
                                <?= ucfirst($v['metodo_pago']) ?>
                            </span>
                        </td>
                        <td style="font-size:11px;color:var(--text-muted);">
                            <?= date('d/m H:i', strtotime($v['created_at'])) ?>
                        </td>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="<?= BASE_PATH ?>/public/js/admin.js"></script>
<script>
Chart.defaults.color          = '#CBD5E1';
Chart.defaults.borderColor    = 'rgba(255,255,255,0.07)';
Chart.defaults.font.family    = 'Poppins';

// ---- Gráfica de ventas por día ----
<?php if (!empty($ventasDias)): ?>
new Chart(document.getElementById('chartVentas'), {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Ventas ($)',
            data:  <?= json_encode($chartTotales) ?>,
            borderColor: '#2563EB',
            backgroundColor: 'rgba(37,99,235,0.12)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#2563EB',
            pointRadius: 4,
            pointHoverRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => '$' + parseFloat(ctx.parsed.y).toLocaleString('es-MX', {minimumFractionDigits:2})
                }
            }
        },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' } },
            y: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { callback: v => '$' + v.toLocaleString('es-MX') }
            }
        }
    }
});
<?php endif; ?>

// ---- Gráfica de métodos de pago ----
<?php if ($kpi['num_ventas'] > 0): ?>
new Chart(document.getElementById('chartMetodos'), {
    type: 'doughnut',
    data: {
        labels: ['Efectivo', 'Tarjeta', 'Transferencia'],
        datasets: [{
            data: [
                <?= (float) $kpi['efectivo'] ?>,
                <?= (float) $kpi['tarjeta'] ?>,
                <?= (float) $kpi['transferencia'] ?>
            ],
            backgroundColor: ['#10B981','#2563EB','#06B6D4'],
            borderWidth: 0,
            hoverOffset: 8,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 16, boxWidth: 12 } },
            tooltip: {
                callbacks: {
                    label: ctx => ` $${parseFloat(ctx.parsed).toLocaleString('es-MX', {minimumFractionDigits:2})}`
                }
            }
        },
        cutout: '65%',
    }
});
<?php endif; ?>
</script>

</body>
</html>
