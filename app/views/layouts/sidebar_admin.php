<?php
// Detectar página activa para highlight del menú
$currentFile = basename($_SERVER['PHP_SELF']);
$currentPath = $_SERVER['PHP_SELF'];

function isActive($paths) {
    global $currentFile, $currentPath;
    foreach ((array)$paths as $p) {
        if (strpos($currentPath, $p) !== false) return ' active';
    }
    return '';
}
?>
<div class="sidebar" id="sidebar">

    <!-- HEADER -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">🧀</div>
            <div class="sidebar-logo-text">
                Cremería Francis
                <small>Sistema POS</small>
            </div>
        </div>
        <button id="toggleSidebar" class="toggle-btn" title="Colapsar menú">
            <i class="bi bi-layout-sidebar-reverse"></i>
        </button>
    </div>

    <!-- MENU PRINCIPAL -->
    <ul class="sidebar-menu">

        <li>
            <a href="<?= BASE_PATH ?>/dashboard_admin.php"
               class="<?= isActive(['dashboard_admin']) ?>"
               data-tooltip="Dashboard">
                <i class="bi bi-grid-1x2"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        <li><div class="sidebar-divider"></div></li>

        <li>
            <a href="<?= BASE_PATH ?>/app/views/productos/index.php"
               class="<?= isActive(['productos']) ?>"
               data-tooltip="Productos">
                <i class="bi bi-box-seam"></i>
                <span class="menu-text">Productos</span>
            </a>
        </li>

        <li>
            <a href="<?= BASE_PATH ?>/app/views/ventas/index.php"
               class="<?= isActive(['ventas']) ?>"
               data-tooltip="POS Ventas">
                <i class="bi bi-cart3"></i>
                <span class="menu-text">POS Ventas</span>
            </a>
        </li>

        <li><div class="sidebar-divider"></div></li>

        <li>
            <a href="<?= BASE_PATH ?>/app/views/usuarios/index.php"
               class="<?= isActive(['usuarios']) ?>"
               data-tooltip="Usuarios">
                <i class="bi bi-people"></i>
                <span class="menu-text">Usuarios</span>
            </a>
        </li>

        <li>
            <a href="<?= BASE_PATH ?>/app/views/reportes/index.php"
               class="<?= isActive(['reportes']) ?>"
               data-tooltip="Reportes">
                <i class="bi bi-bar-chart-line"></i>
                <span class="menu-text">Reportes</span>
            </a>
        </li>

    </ul>

    <!-- FOOTER -->
    <div class="sidebar-footer">
        <ul class="sidebar-menu">
            <li>
                <a href="<?= BASE_PATH ?>/logout.php"
                   data-tooltip="Cerrar Sesión"
                   style="color: #EF4444;">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="menu-text">Cerrar Sesión</span>
                </a>
            </li>
        </ul>
    </div>

</div>
