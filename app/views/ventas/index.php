<?php
require_once '../../../app/middleware/AuthMiddleware.php';
AuthMiddleware::isAuthenticated();

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../../../config/database.php';

// Productos activos con stock
$stmt = $db->query("SELECT id, codigo_barras, nombre, precio, stock, stock_minimo, tipo_medida, categoria, imagen FROM productos WHERE activo = 1 ORDER BY categoria ASC, nombre ASC");
$productos = $stmt->fetchAll();

$pageTitle = 'Punto de Venta';
$pageIcon  = 'bi-cart3';

$isAdmin = ($_SESSION['rol'] ?? '') === 'admin';
$sidebarInclude = $isAdmin
    ? '../layouts/sidebar_admin.php'
    : '../layouts/sidebar_vendedor.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Ventas — Cremería Francis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/admin.css?v=3">
    <style>
        body { overflow: hidden; }
        @media (max-width:992px) { body { overflow: auto; } }
    </style>
</head>
<body>

<?php include $sidebarInclude; ?>

<div class="main-content" style="padding:20px 24px;">

    <?php include '../layouts/navbar_admin.php'; ?>

    <!-- Layout POS -->
    <div class="pos-wrapper">

        <!-- ==================== PANEL IZQUIERDO ==================== -->
        <div class="pos-products-panel">

            <!-- Búsqueda / Código de barras -->
            <div class="pos-search-bar">
                <div class="pos-search-wrapper">
                    <i class="bi bi-upc-scan"></i>
                    <input type="text"
                           id="pos-search"
                           class="barcode-input"
                           placeholder="Buscar por nombre, código de barras o SKU..."
                           autocomplete="off"
                           autofocus>
                </div>
                <button class="btn-custom btn-secondary-custom" onclick="limpiarBusqueda()" title="Limpiar búsqueda">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Categorías (filtro rápido) -->
            <div style="display:flex;gap:8px;flex-wrap:wrap;flex-shrink:0;" id="filtros-cat">
                <button class="btn-ghost active-filter" onclick="filtrarCategoria('')" data-cat="">Todos</button>
                <?php
                $cats = array_unique(array_column($productos, 'categoria'));
                sort($cats);
                foreach ($cats as $cat): if (!$cat) continue; ?>
                <button class="btn-ghost" onclick="filtrarCategoria('<?= htmlspecialchars(addslashes($cat)) ?>')" data-cat="<?= htmlspecialchars($cat) ?>">
                    <?= htmlspecialchars($cat) ?>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Grid de productos -->
            <div class="pos-products-grid" id="productos-grid">
                <?php foreach ($productos as $prod): ?>
                <div class="pos-product-card <?= $prod['stock'] <= 0 ? 'out-of-stock' : '' ?>"
                     data-id="<?= $prod['id'] ?>"
                     data-nombre="<?= htmlspecialchars($prod['nombre'], ENT_QUOTES) ?>"
                     data-precio="<?= $prod['precio'] ?>"
                     data-stock="<?= $prod['stock'] ?>"
                     data-codigo="<?= htmlspecialchars($prod['codigo_barras'] ?? '', ENT_QUOTES) ?>"
                     data-cat="<?= htmlspecialchars($prod['categoria'] ?? '', ENT_QUOTES) ?>"
                     data-medida="<?= htmlspecialchars($prod['tipo_medida'], ENT_QUOTES) ?>"
                     onclick="agregarAlCarrito(this)"
                     title="<?= $prod['stock'] <= 0 ? 'Sin stock disponible' : 'Clic para agregar' ?>">

                    <?php if ($prod['imagen']): ?>
                    <img src="../../../public/img/products/<?= htmlspecialchars($prod['imagen']) ?>"
                         class="pos-product-img"
                         alt="<?= htmlspecialchars($prod['nombre']) ?>"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="pos-product-placeholder" style="display:none;">🧀</div>
                    <?php else: ?>
                    <div class="pos-product-placeholder">🧀</div>
                    <?php endif; ?>

                    <div class="pos-product-name"><?= htmlspecialchars($prod['nombre']) ?></div>
                    <div class="pos-product-price">$<?= number_format($prod['precio'], 2) ?></div>
                    <div class="pos-product-stock">
                        <?php if ($prod['stock'] <= 0): ?>
                        <span style="color:var(--danger);">Sin stock</span>
                        <?php elseif ($prod['stock'] <= $prod['stock_minimo']): ?>
                        <span style="color:var(--warning);">Stock bajo: <?= $prod['stock'] ?></span>
                        <?php else: ?>
                        Stock: <?= number_format($prod['stock'], 0) ?> <?= $prod['tipo_medida'] ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>

        <!-- ==================== PANEL DERECHO (CARRITO) ==================== -->
        <div class="pos-cart-panel">

            <div class="pos-cart-header">
                <h5><i class="bi bi-cart3"></i> Orden <span id="folio-display" style="font-size:11px;color:var(--text-muted);"></span></h5>
                <div style="display:flex;gap:8px;">
                    <button onclick="limpiarCarrito()" class="btn-ghost" style="font-size:12px;" title="Vaciar carrito">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>

            <!-- Items del carrito -->
            <div class="pos-cart-body" id="carrito-body">
                <div class="pos-cart-empty" id="carrito-vacio">
                    <i class="bi bi-cart3"></i>
                    <p>El carrito está vacío.<br>Selecciona o escanea un producto.</p>
                </div>
                <div id="carrito-items"></div>
            </div>

            <!-- Totales y pago -->
            <div class="pos-cart-footer">

                <div class="pos-total-row">
                    <span>Subtotal</span>
                    <span id="subtotal-val">$0.00</span>
                </div>
                <div class="pos-total-row">
                    <span>Descuento</span>
                    <span id="descuento-val" style="color:var(--danger);">- $0.00</span>
                </div>
                <div class="pos-total-row grand">
                    <span>TOTAL</span>
                    <span id="total-val">$0.00</span>
                </div>

                <!-- Método de pago -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;">
                    <button class="metodo-btn active" data-metodo="efectivo" onclick="selMetodo(this)">
                        <i class="bi bi-cash"></i> Efectivo
                    </button>
                    <button class="metodo-btn" data-metodo="tarjeta" onclick="selMetodo(this)">
                        <i class="bi bi-credit-card"></i> Tarjeta
                    </button>
                </div>

                <button class="btn-cobrar" id="btn-cobrar" onclick="cobrar()" disabled>
                    <i class="bi bi-cash-coin"></i> Cobrar
                </button>

            </div>
        </div>

    </div>

</div>

<!-- ==================== MODAL PAGO ==================== -->
<div class="modal fade" id="modal-pago" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content modal-dark">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash-coin"></i> Cobro</h5>
            </div>
            <div class="modal-body">
                <div style="text-align:center;margin-bottom:20px;">
                    <div style="font-size:13px;color:var(--text-muted);">Total a cobrar</div>
                    <div style="font-size:32px;font-weight:700;color:var(--success);" id="modal-total">$0.00</div>
                </div>

                <div id="efectivo-fields">
                    <label class="form-label-custom">Monto recibido</label>
                    <input type="number" id="monto-pagado" class="form-control-custom"
                           placeholder="0.00" step="0.01" min="0"
                           oninput="calcCambio()">
                    <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;" id="quick-amounts"></div>
                    <div class="pos-total-row mt-3" style="font-size:16px;">
                        <span>Cambio</span>
                        <span id="cambio-val" style="color:var(--success);font-weight:700;">$0.00</span>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-custom btn-secondary-custom" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn-custom btn-success-custom" id="btn-confirmar-pago" onclick="confirmarPago()">
                    <i class="bi bi-check-lg"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== TICKET (impresión) ==================== -->
<div id="ticket-print">
    <div id="ticket-content"></div>
</div>

<!-- Estilos extra para POS -->
<style>
.metodo-btn {
    background: var(--bg-body);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    color: var(--text-secondary);
    padding: 8px 4px;
    cursor: pointer;
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    transition: var(--transition);
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.metodo-btn:hover { border-color: var(--primary); color: var(--text-primary); }
.metodo-btn.active { background: var(--primary-glow); border-color: var(--primary); color: var(--text-primary); }
.active-filter { background: var(--primary-glow); border-color: var(--primary); color: var(--text-primary); }
.quick-btn {
    background: var(--bg-hover); border: 1px solid var(--border);
    border-radius: 6px; color: var(--text-primary);
    padding: 5px 10px; font-size: 13px; cursor: pointer;
    font-family:'Poppins',sans-serif; transition: var(--transition);
}
.quick-btn:hover { background: var(--primary); border-color: var(--primary); }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_PATH ?>/public/js/admin.js"></script>
<script>
/* ============================================================
   Estado del carrito
   ============================================================ */
let carrito = [];          // [{id, nombre, precio, stock, cantidad, medida}]
let metodoPago = 'efectivo';
let modalPago  = null;

document.addEventListener('DOMContentLoaded', () => {
    modalPago = new bootstrap.Modal(document.getElementById('modal-pago'));
});

/* ============================================================
   Agregar al carrito
   ============================================================ */
function agregarAlCarrito(card) {
    const id     = parseInt(card.dataset.id);
    const nombre = card.dataset.nombre;
    const precio = parseFloat(card.dataset.precio);
    const stock  = parseFloat(card.dataset.stock);
    const medida = card.dataset.medida;

    if (stock <= 0) {
        Toast.warning('Sin stock', `"${nombre}" no tiene unidades disponibles.`);
        return;
    }

    const idx = carrito.findIndex(i => i.id === id);
    if (idx >= 0) {
        if (carrito[idx].cantidad >= stock) {
            Toast.warning('Stock máximo', `Solo hay ${stock} ${medida} disponibles.`);
            return;
        }
        carrito[idx].cantidad++;
    } else {
        carrito.push({ id, nombre, precio, stock, cantidad: 1, medida });
    }

    renderCarrito();
    animarTarjeta(card);
}

function animarTarjeta(el) {
    el.style.transform = 'scale(0.94)';
    setTimeout(() => el.style.transform = '', 180);
}

/* ============================================================
   Cambiar cantidad
   ============================================================ */
function cambiarCantidad(id, delta) {
    const idx = carrito.findIndex(i => i.id === id);
    if (idx < 0) return;
    carrito[idx].cantidad += delta;
    if (carrito[idx].cantidad <= 0) carrito.splice(idx, 1);
    renderCarrito();
}

function setCantidad(id, val) {
    const idx = carrito.findIndex(i => i.id === id);
    if (idx < 0) return;
    const qty = parseFloat(val) || 0;
    if (qty <= 0) { carrito.splice(idx, 1); }
    else if (qty > carrito[idx].stock) {
        Toast.warning('Stock máximo', `Solo hay ${carrito[idx].stock} disponibles.`);
        carrito[idx].cantidad = carrito[idx].stock;
    } else {
        carrito[idx].cantidad = qty;
    }
    renderCarrito();
}

function quitarItem(id) {
    carrito = carrito.filter(i => i.id !== id);
    renderCarrito();
}

function limpiarCarrito() {
    if (!carrito.length) return;
    carrito = [];
    renderCarrito();
}

/* ============================================================
   Renderizar carrito
   ============================================================ */
function renderCarrito() {
    const wrap  = document.getElementById('carrito-items');
    const empty = document.getElementById('carrito-vacio');
    const btnCobrar = document.getElementById('btn-cobrar');

    if (!carrito.length) {
        wrap.innerHTML  = '';
        empty.style.display = 'flex';
        btnCobrar.disabled  = true;
        actualizarTotales();
        return;
    }

    empty.style.display = 'none';
    btnCobrar.disabled  = false;

    wrap.innerHTML = carrito.map(item => `
        <div class="cart-item" id="item-${item.id}">
            <div class="cart-item-info">
                <div class="cart-item-name" title="${escapeHtml(item.nombre)}">${escapeHtml(item.nombre)}</div>
                <div class="cart-item-price">$${item.precio.toFixed(2)} / ${item.medida}</div>
            </div>
            <div class="cart-qty-controls">
                <button class="cart-qty-btn" onclick="cambiarCantidad(${item.id}, -1)">
                    <i class="bi bi-dash"></i>
                </button>
                <input type="number"
                       class="cart-qty-val"
                       value="${item.cantidad}"
                       min="0.01"
                       step="1"
                       style="width:46px;text-align:center;background:var(--bg-input);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);font-family:'Poppins',sans-serif;"
                       onchange="setCantidad(${item.id}, this.value)">
                <button class="cart-qty-btn" onclick="cambiarCantidad(${item.id}, 1)">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="cart-item-subtotal">$${(item.precio * item.cantidad).toFixed(2)}</div>
            <button class="cart-item-delete" onclick="quitarItem(${item.id})">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `).join('');

    actualizarTotales();
}

function actualizarTotales() {
    const subtotal = carrito.reduce((s, i) => s + i.precio * i.cantidad, 0);
    document.getElementById('subtotal-val').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('descuento-val').textContent = '- $0.00';
    document.getElementById('total-val').textContent = '$' + subtotal.toFixed(2);
}

function escapeHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}

/* ============================================================
   Búsqueda y filtros
   ============================================================ */
let filtroActual = '';

document.getElementById('pos-search').addEventListener('input', function() {
    buscarProductos(this.value.trim(), filtroActual);
});

// Escáner de código de barras (Enter dispara búsqueda exacta)
document.getElementById('pos-search').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const q = this.value.trim();
        const card = document.querySelector(`.pos-product-card[data-codigo="${q}"]`);
        if (card && !card.classList.contains('out-of-stock')) {
            agregarAlCarrito(card);
            this.value = '';
            Toast.success('Producto agregado', card.dataset.nombre, 2000);
        } else if (q) {
            Toast.warning('No encontrado', 'Código de barras no coincide con ningún producto.');
        }
    }
});

function buscarProductos(q, cat) {
    document.querySelectorAll('.pos-product-card').forEach(card => {
        const nombre  = card.dataset.nombre.toLowerCase();
        const codigo  = (card.dataset.codigo || '').toLowerCase();
        const cardCat = card.dataset.cat || '';

        const matchQ   = !q   || nombre.includes(q.toLowerCase()) || codigo.includes(q.toLowerCase());
        const matchCat = !cat || cardCat === cat;

        card.style.display = (matchQ && matchCat) ? '' : 'none';
    });
}

function filtrarCategoria(cat) {
    filtroActual = cat;
    document.querySelectorAll('#filtros-cat .btn-ghost').forEach(btn => {
        btn.classList.toggle('active-filter', btn.dataset.cat === cat);
    });
    buscarProductos(document.getElementById('pos-search').value, cat);
}

function limpiarBusqueda() {
    document.getElementById('pos-search').value = '';
    buscarProductos('', filtroActual);
    document.getElementById('pos-search').focus();
}

/* ============================================================
   Método de pago
   ============================================================ */
function selMetodo(btn) {
    document.querySelectorAll('.metodo-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    metodoPago = btn.dataset.metodo;
    document.getElementById('efectivo-fields').style.display =
        metodoPago === 'efectivo' ? 'block' : 'none';
}

/* ============================================================
   Modal de cobro
   ============================================================ */
function cobrar() {
    if (!carrito.length) return;
    const total = carrito.reduce((s, i) => s + i.precio * i.cantidad, 0);
    document.getElementById('modal-total').textContent = '$' + total.toFixed(2);
    document.getElementById('monto-pagado').value = '';
    document.getElementById('cambio-val').textContent = '$0.00';

    // Botones de monto rápido
    const qtks = [50, 100, 200, 500];
    document.getElementById('quick-amounts').innerHTML = qtks
        .filter(v => v >= total)
        .map(v => `<button class="quick-btn" onclick="setMontoPagado(${v})">$${v}</button>`)
        .join('');

    document.getElementById('efectivo-fields').style.display =
        metodoPago === 'efectivo' ? 'block' : 'none';

    modalPago.show();
    setTimeout(() => document.getElementById('monto-pagado').focus(), 400);
}

function setMontoPagado(val) {
    document.getElementById('monto-pagado').value = val;
    calcCambio();
}

function calcCambio() {
    const total   = carrito.reduce((s, i) => s + i.precio * i.cantidad, 0);
    const pagado  = parseFloat(document.getElementById('monto-pagado').value) || 0;
    const cambio  = pagado - total;
    const el      = document.getElementById('cambio-val');
    el.textContent = '$' + Math.max(cambio, 0).toFixed(2);
    el.style.color = cambio < 0 ? 'var(--danger)' : 'var(--success)';
    document.getElementById('btn-confirmar-pago').disabled = metodoPago === 'efectivo' && cambio < 0;
}

/* ============================================================
   Confirmar venta — envía a VentaController.php
   ============================================================ */
function confirmarPago() {
    const total     = carrito.reduce((s, i) => s + i.precio * i.cantidad, 0);
    const pagado    = parseFloat(document.getElementById('monto-pagado').value) || (metodoPago === 'tarjeta' ? total : 0);
    const csrfToken = '<?= htmlspecialchars($_SESSION['csrf_token']) ?>';

    const payload = {
        csrf_token:   csrfToken,
        metodo_pago:  metodoPago,
        monto_pagado: pagado,
        items: carrito.map(i => ({
            id: i.id, cantidad: i.cantidad, precio: i.precio
        }))
    };

    document.getElementById('btn-confirmar-pago').disabled = true;
    document.getElementById('btn-confirmar-pago').innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';

    fetch('<?= BASE_PATH ?>/app/controllers/VentaController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            modalPago.hide();
            imprimirTicket(data.ticket);
            limpiarCarrito();
            Toast.success('Venta registrada', `Folio ${data.folio}`, 5000);
        } else {
            Toast.error('Error', data.message || 'No se pudo registrar la venta.');
        }
    })
    .catch(() => Toast.error('Error de red', 'No se pudo conectar con el servidor.'))
    .finally(() => {
        document.getElementById('btn-confirmar-pago').disabled = false;
        document.getElementById('btn-confirmar-pago').innerHTML = '<i class="bi bi-check-lg"></i> Confirmar';
    });
}

/* ============================================================
   Imprimir ticket
   ============================================================ */
function imprimirTicket(ticket) {
    document.getElementById('ticket-content').innerHTML = ticket;
    window.print();
}
</script>

</body>
</html>
