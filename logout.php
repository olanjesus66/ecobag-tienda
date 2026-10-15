<?php
require_once 'includes/conexion.php';
require_once 'includes/funciones.php';

// El token en la URL evita que otro sitio te cierre la sesión sin que lo notes
if (hash_equals($_SESSION['csrf'] ?? '', $_GET['t'] ?? '')) {
    borrarRecordatorio($pdo);

    $_SESSION = [];
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    session_destroy();
}

header('Location: index.php');
exit;