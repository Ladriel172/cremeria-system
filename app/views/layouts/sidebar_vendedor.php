<?php
$currentPath = $_SERVER['PHP_SELF'];
function isActiveVendedor($paths) {
    global $currentPath;
    foreach ((array)$paths as $p) {
        if (strpos($currentPath, $p) !== false) return ' active';
    }
    return '';
}
?>
<div class="sidebar" id="sidebar">

    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">🧀</div>
            <div class="sidebar-logo-text">
                Cremería Francis
                <small>Punto de Venta</small>
            </div>
        </div>
        <button id="toggleSidebar" class="toggle-btn" title="Colapsar menú">
            <i class="bi bi-layout-sidebar-reverse"></i>
        </button>
    </div>

    <ul class="sidebar-menu">

        <li>
            <a href="<?= BASE_PATH ?>/dashboard_vendedor.php"
               class="<?= isActiveVendedor(['dashboard_vendedor']) ?>"
               data-tooltip="Inicio">
                <i class="bi bi-house"></i>
                <span class="menu-text">Inicio</span>
            </a>
        </li>

        <li>
            <a href="<?= BASE_PATH ?>/app/views/ventas/index.php"
               class="<?= isActiveVendedor(['ventas']) ?>"
               data-tooltip="POS Ventas">
                <i class="bi bi-cart3"></i>
                <span class="menu-text">POS Ventas</span>
            </a>
        </li>

    </ul>

    <div class="sidebar-footer">
        <ul class="sidebar-menu">
            <li>
                <a href="<?= BASE_PATH ?>/logout.php"
                   data-tooltip="Cerrar Sesión"
                   style="color:#EF4444;">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="menu-text">Cerrar Sesión</span>
                </a>
            </li>
        </ul>
    </div>

</div>
