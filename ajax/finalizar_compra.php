<?php
require_once '../includes/conexion.php';
require_once '../includes/funciones.php';
header('Content-Type: application/json');

function responder(array $datos, int $codigo = 200): void {
    http_response_code($codigo);
    echo json_encode($datos);
    exit;
}

// 1. Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
}

// 2. Token anti-CSRF
if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
    responder(['ok' => false, 'mensaje' => 'La sesión expiró. Recarga la página.'], 403);
}

// 3. Debe haber iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    responder(['ok' => false, 'login' => true, 'url' => 'login.php?volver=carrito.php'], 401);
}

// 4. Carrito con precios leídos de la base de datos
$c = obtenerCarrito($pdo);
if (!$c['items']) {
    responder(['ok' => false, 'vacio' => true, 'mensaje' => 'Tu carrito está vacío.'], 400);
}

// 5. Guardar todo dentro de una transacción
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, envio) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['usuario_id'], $c['total'], $c['envio']]);
    $pedidoId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare(
        "INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)"
    );
    foreach ($c['items'] as $it) {
        $stmt->execute([$pedidoId, $it['id'], $it['cantidad'], $it['precio']]);
    }

    $pdo->commit();

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Error al guardar pedido: ' . $e->getMessage());
    responder(['ok' => false, 'mensaje' => 'No pudimos procesar tu pedido. Inténtalo de nuevo.'], 500);
}

// 6. Todo salió bien: ahora sí vaciamos el carrito
unset($_SESSION['carrito']);

responder([
    'ok'     => true,
    'pedido' => 'EB' . str_pad((string)$pedidoId, 5, '0', STR_PAD_LEFT),   // EB00012
    'total'  => dinero($c['total']),
]);