<?php
// El archivo que incluye este navbar puede definir $pageTitle e $pageIcon
$pageTitle = $pageTitle ?? 'Panel Administrativo';
$pageIcon  = $pageIcon  ?? 'bi-grid-1x2';
$userName  = $_SESSION['usuario'] ?? 'Usuario';
$initials  = strtoupper(substr($userName, 0, 1));
?>
<div class="top-navbar">

    <div class="navbar-title">
        <i class="bi <?= htmlspecialchars($pageIcon) ?>"></i>
        <?= htmlspecialchars($pageTitle) ?>
    </div>

    <div class="navbar-right">

        <!-- Notificaciones stock bajo (futuro) -->
        <div class="navbar-user">
            <div class="navbar-avatar"><?= $initials ?></div>
            <span>
                <?= htmlspecialchars($userName) ?>
                <small style="display:block;font-size:10px;color:var(--text-muted);">
                    <?= htmlspecialchars(ucfirst($_SESSION['rol'] ?? 'admin')) ?>
                </small>
            </span>
        </div>

    </div>

</div>
