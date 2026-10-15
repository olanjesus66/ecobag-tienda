<?php
const ENVIO_COSTO = 80;           // costo de envío en MXN
const ENVIO_GRATIS_DESDE = 500;   // envío gratis arriba de este monto

function obtenerCarrito(PDO $pdo): array {
    $carrito = $_SESSION['carrito'] ?? [];

    $vacio = ['items' => [], 'subtotal' => 0, 'envio' => 0, 'total' => 0, 'cantidad' => 0];
    if (!$carrito) return $vacio;

    // Traer de la base de datos todos los productos del carrito en una sola consulta
    $ids    = array_keys($carrito);
    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $stmt   = $pdo->prepare("SELECT id, nombre, precio, imagen FROM productos WHERE id IN ($marcas)");
    $stmt->execute($ids);

    $items = [];
    $nuevo = [];        // carrito limpio (sin productos que ya no existen)
    $subtotal = 0;
    $cantidad = 0;

    foreach ($stmt->fetchAll() as $p) {
        $cant = (int)$carrito[$p['id']];
        $p['cantidad'] = $cant;
        $p['subtotal'] = $p['precio'] * $cant;

        $subtotal += $p['subtotal'];
        $cantidad += $cant;
        $nuevo[$p['id']] = $cant;
        $items[] = $p;
    }

    $_SESSION['carrito'] = $nuevo;

    if (!$items) return $vacio;

    $envio = ($subtotal > ENVIO_GRATIS_DESDE) ? 0 : ENVIO_COSTO;

    return [
        'items'    => $items,
        'subtotal' => $subtotal,
        'envio'    => $envio,
        'total'    => $subtotal + $envio,
        'cantidad' => $cantidad,
    ];
}

function dinero($n): string {
    return '$' . number_format($n, 0) . ' MXN';
}

// Crea el token de "Recordarme" y lo guarda en una cookie por 30 días
function crearRecordatorio(PDO $pdo, int $usuarioId): void {
    $selector  = bin2hex(random_bytes(9));    // 18 caracteres
    $validador = bin2hex(random_bytes(32));
    $expira    = time() + 60 * 60 * 24 * 30;

    $stmt = $pdo->prepare(
        "INSERT INTO sesiones_recordadas (usuario_id, selector, token_hash, expira) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$usuarioId, $selector, hash('sha256', $validador), date('Y-m-d H:i:s', $expira)]);

    setcookie('recordar', "$selector:$validador", [
        'expires'  => $expira,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
}

// Borra el token del navegador y de la base de datos
function borrarRecordatorio(PDO $pdo): void {
    if (!empty($_COOKIE['recordar'])) {
        $selector = explode(':', $_COOKIE['recordar'])[0];
        $pdo->prepare("DELETE FROM sesiones_recordadas WHERE selector = ?")->execute([$selector]);
    }
    setcookie('recordar', '', ['expires' => time() - 3600, 'path' => '/']);
}

// Para proteger páginas: si no hay sesión, manda al login y regresa después
function requiereLogin(): void {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php?volver=' . urlencode(basename($_SERVER['PHP_SELF'])));
        exit;
    }
}