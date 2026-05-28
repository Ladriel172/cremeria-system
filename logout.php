<?php
require_once __DIR__ . '/_app.php';
$_SESSION = [];
session_destroy();
header('Location: ' . BASE_PATH . '/index.html');
exit();
