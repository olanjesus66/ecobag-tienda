<?php
// Cambia a true SOLO en tu computadora mientras desarrollas
const MODO_DESARROLLO = true;

ini_set('display_errors', MODO_DESARROLLO ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

date_default_timezone_set('America/Mexico_City');

$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'httponly' => true,      // JavaScript no puede leer la cookie de sesión
    'samesite' => 'Lax',     // el navegador no la manda desde otros sitios
    'secure'   => $https,
]);
session_start();

// Token anti-CSRF disponible en todas las páginas
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$cfg  = require __DIR__ . '/config.php';
$host = $cfg['host'];
$db   = $cfg['db'];
$user = $cfg['user'];
$pass = $cfg['pass'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('No pudimos conectar con la base de datos.');
}

// ---------- Restaurar sesión desde la cookie "Recordarme" ----------
if (!isset($_SESSION['usuario_id']) && !empty($_COOKIE['recordar'])) {
    $partes = explode(':', $_COOKIE['recordar']);
    $ok = false;

    if (count($partes) === 2) {
        [$selector, $validador] = $partes;

        $stmt = $pdo->prepare("
            SELECT s.token_hash, u.id, u.nombre
            FROM sesiones_recordadas s
            JOIN usuarios u ON u.id = s.usuario_id
            WHERE s.selector = ? AND s.expira > ?
        ");
        $stmt->execute([$selector, date('Y-m-d H:i:s')]);
        $fila = $stmt->fetch();

        if ($fila && hash_equals($fila['token_hash'], hash('sha256', $validador))) {
            session_regenerate_id(true);
            $_SESSION['usuario_id']     = (int)$fila['id'];
            $_SESSION['usuario_nombre'] = $fila['nombre'];
            $ok = true;
        }
    }

    if (!$ok) {   // cookie inválida o vencida: la borramos
        setcookie('recordar', '', ['expires' => time() - 3600, 'path' => '/']);
    }
}