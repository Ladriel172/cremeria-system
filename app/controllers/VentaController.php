<?php
/**
 * VentaController — Registra ventas vía AJAX/JSON
 * POST app/controllers/VentaController.php
 */
require_once __DIR__ . '/../../_app.php';
require_once PROJECT_ROOT . '/app/middleware/AuthMiddleware.php';
AuthMiddleware::isAuthenticated();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit();
}

// Leer JSON del body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit();
}

// Validar CSRF
if (empty($input['csrf_token']) || $input['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
    exit();
}

$usuarioId   = (int) $_SESSION['id'];
$metodoPago  = $input['metodo_pago'] ?? 'efectivo';
$montoPagado = (float) ($input['monto_pagado'] ?? 0);
$items       = $input['items'] ?? [];

// Validar método de pago
$metodosPermitidos = ['efectivo', 'tarjeta', 'transferencia', 'otro'];
if (!in_array($metodoPago, $metodosPermitidos)) $metodoPago = 'efectivo';

// Validar que haya items
if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
    exit();
}

try {
    $db->beginTransaction();

    // Verificar stock y calcular totales
    $subtotal = 0;
    $detalles = [];

    foreach ($items as $item) {
        $prodId  = (int)  ($item['id']       ?? 0);
        $qty     = (float)($item['cantidad']  ?? 0);
        $precioFE= (float)($item['precio']    ?? 0);

        if ($prodId <= 0 || $qty <= 0) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Item inválido en el carrito.']);
            exit();
        }

        // Obtener producto actualizado de la DB
        $stmtP = $db->prepare("SELECT id, nombre, precio, stock FROM productos WHERE id = ? AND activo = 1 LIMIT 1");
        $stmtP->execute([$prodId]);
        $prod = $stmtP->fetch();

        if (!$prod) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => "Producto #$prodId no encontrado."]);
            exit();
        }

        if ($prod['stock'] < $qty) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => "Stock insuficiente para \"{$prod['nombre']}\". Disponible: {$prod['stock']}."]);
            exit();
        }

        $precio   = (float) $prod['precio'];
        $lineSub  = $precio * $qty;
        $subtotal += $lineSub;

        $detalles[] = [
            'producto_id'     => $prodId,
            'nombre_producto' => $prod['nombre'],
            'cantidad'        => $qty,
            'precio_unitario' => $precio,
            'subtotal'        => $lineSub,
        ];
    }

    $total  = $subtotal;
    $cambio = max(0, $montoPagado - $total);

    // Generar folio único
    $folio = 'V-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

    // Insertar venta
    $stmtV = $db->prepare("INSERT INTO ventas
        (folio, usuario_id, subtotal, descuento, iva, total, metodo_pago, monto_pagado, cambio, estado)
        VALUES (?, ?, ?, 0, 0, ?, ?, ?, ?, 'completada')");
    $stmtV->execute([$folio, $usuarioId, $subtotal, $total, $metodoPago, $montoPagado, $cambio]);
    $ventaId = (int) $db->lastInsertId();

    // Insertar detalles y descontar stock
    $stmtD = $db->prepare("INSERT INTO detalle_ventas
        (venta_id, producto_id, nombre_producto, cantidad, precio_unitario, subtotal)
        VALUES (?, ?, ?, ?, ?, ?)");

    $stmtStock = $db->prepare("UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?");

    foreach ($detalles as $d) {
        $stmtD->execute([
            $ventaId, $d['producto_id'], $d['nombre_producto'],
            $d['cantidad'], $d['precio_unitario'], $d['subtotal']
        ]);

        $ok = $stmtStock->execute([$d['cantidad'], $d['producto_id'], $d['cantidad']]);
        if (!$ok || $stmtStock->rowCount() === 0) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => "Error al descontar stock de \"{$d['nombre_producto']}\"."]);
            exit();
        }
    }

    $db->commit();

    // Generar HTML del ticket
    $ticket = generarTicket($folio, $detalles, $subtotal, $total, $metodoPago, $montoPagado, $cambio, $_SESSION['usuario']);

    echo json_encode([
        'success' => true,
        'folio'   => $folio,
        'total'   => $total,
        'cambio'  => $cambio,
        'ticket'  => $ticket,
    ]);

} catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log("VentaController Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al registrar la venta. Intenta de nuevo.']);
}

/* ============================================================
   Genera HTML del ticket térmico
   ============================================================ */
function generarTicket($folio, $items, $subtotal, $total, $metodo, $pagado, $cambio, $vendedor) {
    $fecha = date('d/m/Y H:i');
    $linea = str_repeat('-', 32);

    $rowsHtml = '';
    foreach ($items as $item) {
        $rowsHtml .= sprintf(
            '<tr><td>%s<br><small>%s x $%.2f</small></td><td align="right">$%.2f</td></tr>',
            htmlspecialchars($item['nombre_producto']),
            $item['cantidad'],
            $item['precio_unitario'],
            $item['subtotal']
        );
    }

    $cambioRow = $metodo === 'efectivo'
        ? "<tr><td>Pagado:</td><td align='right'>$".number_format($pagado,2)."</td></tr>
           <tr><td><strong>Cambio:</strong></td><td align='right'><strong>$".number_format($cambio,2)."</strong></td></tr>"
        : '';

    return "
    <div style='font-family:monospace;font-size:12px;width:80mm;color:#000;'>
        <div style='text-align:center;margin-bottom:8px;'>
            <div style='font-size:16px;font-weight:bold;'>CREMERÍA FRANCIS</div>
            <div>Sistema POS Profesional</div>
            <div style='font-size:10px;'>$fecha</div>
        </div>
        <div style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding:4px 0;margin:6px 0;'>
            <div>Folio: <strong>$folio</strong></div>
            <div>Vendedor: $vendedor</div>
            <div>Pago: ".ucfirst($metodo)."</div>
        </div>
        <table style='width:100%;border-collapse:collapse;'>
            <thead>
                <tr style='border-bottom:1px solid #000;'>
                    <th align='left'>Producto</th>
                    <th align='right'>Total</th>
                </tr>
            </thead>
            <tbody>$rowsHtml</tbody>
        </table>
        <div style='border-top:1px dashed #000;margin-top:8px;padding-top:6px;'>
            <table style='width:100%;'>
                <tr><td>Subtotal:</td><td align='right'>$".number_format($subtotal,2)."</td></tr>
                <tr><td style='font-size:15px;font-weight:bold;'>TOTAL:</td><td align='right' style='font-size:15px;font-weight:bold;'>$".number_format($total,2)."</td></tr>
                $cambioRow
            </table>
        </div>
        <div style='text-align:center;margin-top:10px;font-size:10px;'>
            ¡Gracias por su compra!<br>Vuelva pronto
        </div>
    </div>";
}
