<?php
require_once __DIR__ . '/_app.php';
require_once PROJECT_ROOT . '/app/helpers/SecurityHelper.php';
require_once PROJECT_ROOT . '/app/core/BaseModel.php';
require_once PROJECT_ROOT . '/app/models/User.php';

use App\Models\User;
use App\Helpers\SecurityHelper;

// Ya autenticado → redirigir
if (!empty($_SESSION['usuario'])) {
    header('Location: ' . BASE_PATH . '/' . ($_SESSION['rol'] === 'admin' ? 'dashboard_admin.php' : 'dashboard_vendedor.php'));
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $postedToken)) {
        $error = 'Token de seguridad inválido. Recarga la página.';
    } else {
        $correo   = filter_var(trim($_POST['correo'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($correo) || empty($password)) {
            $error = 'Email y contraseña son requeridos.';
        } else {
            $userModel = new User($db);
            $user      = $userModel->authenticate($correo, $password);

            if ($user) {
                session_regenerate_id(true);
                $_SESSION['id']      = $user['id'];
                $_SESSION['usuario'] = $user['nombre'];
                $_SESSION['correo']  = $user['correo'];
                $_SESSION['rol']     = $user['rol'];

                header('Location: ' . BASE_PATH . '/' . ($user['rol'] === 'admin' ? 'dashboard_admin.php' : 'dashboard_vendedor.php'));
                exit();
            } else {
                $error = 'Email o contraseña incorrectos.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Cremería Francis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563EB;
            --bg-dark: #0F172A;
            --bg-card: #1E293B;
            --text:    #F8FAFC;
            --muted:   #64748B;
            --border:  rgba(255,255,255,0.07);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: radial-gradient(ellipse at 60% 0%, #1e3a8a 0%, #0F172A 55%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }
        .login-wrap { width:100%; max-width:420px; padding:20px; }
        .login-card {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 44px 40px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.45);
            border: 1px solid var(--border);
            animation: fadeUp .4s ease;
        }
        @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
        .logo { text-align:center; margin-bottom:32px; }
        .logo-icon {
            width:64px; height:64px;
            background: linear-gradient(135deg, #2563EB, #7C3AED);
            border-radius:18px;
            display:flex; align-items:center; justify-content:center;
            font-size:28px; margin:0 auto 14px;
        }
        .logo h1 { font-size:22px; font-weight:700; color:var(--text); }
        .logo p  { font-size:13px; color:var(--muted); margin-top:4px; }
        .field { margin-bottom:18px; }
        .field label { display:block; font-size:13px; color:#CBD5E1; font-weight:500; margin-bottom:7px; }
        .field input {
            width:100%; padding:12px 16px;
            background:#111827; border:1px solid var(--border);
            border-radius:12px; color:var(--text); font-size:14px;
            font-family:'Poppins',sans-serif; outline:none; transition:.25s;
        }
        .field input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.18); background:#0F172A; }
        .field input::placeholder { color:var(--muted); }
        .btn-login {
            width:100%; padding:13px;
            background:var(--primary); color:#fff; border:none;
            border-radius:12px; font-size:15px; font-weight:600;
            font-family:'Poppins',sans-serif; cursor:pointer;
            transition:.25s; margin-top:6px;
        }
        .btn-login:hover { background:#1D4ED8; transform:translateY(-1px); box-shadow:0 8px 24px rgba(37,99,235,.35); }
        .alert-err {
            background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.25);
            color:#FCA5A5; border-radius:10px; padding:11px 15px;
            font-size:13px; margin-bottom:18px;
        }
        .demo-box {
            background:rgba(255,255,255,.03); border:1px solid var(--border);
            border-radius:10px; padding:12px 15px; margin-top:22px;
            font-size:12px; color:var(--muted);
        }
        .demo-box strong { color:#CBD5E1; }
        .divider { text-align:center; color:var(--muted); font-size:11px; margin:14px 0 0; }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">

        <div class="logo">
            <div class="logo-icon">🧀</div>
            <h1>Cremería Francis</h1>
            <p>Sistema POS Profesional</p>
        </div>

        <?php if ($error): ?>
        <div class="alert-err">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="field">
                <label>Email</label>
                <input type="email" name="correo" placeholder="usuario@example.com"
                       value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                       required autocomplete="email" autofocus>
            </div>

            <div class="field">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="••••••••"
                       required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>

        <div class="demo-box">
            <div><strong>Admin:</strong> admin@cremeria.com / <strong>admin123</strong></div>
            <div style="margin-top:5px;"><strong>Vendedor:</strong> vendedor@cremeria.com / <strong>vendedor123</strong></div>
        </div>

        <div class="divider">Cremería Francis © 2026</div>
    </div>
</div>
</body>
</html>
