<?php
require_once '../includes/conexion.php';
require_once '../includes/funciones.php';
header('Content-Type: application/json');

$id     = (int)($_POST['id'] ?? 0);
$accion = $_POST['accion'] ?? '';

// Solo actuamos si ese producto realmente está en el carrito
if (isset($_SESSION['carrito'][$id])) {
    switch ($accion) {
        case 'mas':
            if ($_SESSION['carrito'][$id] < 99) $_SESSION['carrito'][$id]++;
            break;
        case 'menos':
            if ($_SESSION['carrito'][$id] > 1) $_SESSION['carrito'][$id]--;
            break;
        case 'eliminar':
            unset($_SESSION['carrito'][$id]);
            break;
    }
}

$c = obtenerCarrito($pdo);

// Datos de la línea que se modificó
$cantidadItem = 0;
$subtotalItem = 0;
foreach ($c['items'] as $it) {
    if ((int)$it['id'] === $id) {
        $cantidadItem = $it['cantidad'];
        $subtotalItem = $it['subtotal'];
    }
}

echo json_encode([
    'ok'            => true,
    'vacio'         => empty($c['items']),
    'cantidad_item' => $cantidadItem,
    'subtotal_item' => dinero($subtotalItem),
    'subtotal'      => dinero($c['subtotal']),
    'envio'         => dinero($c['envio']),
    'total'         => dinero($c['total']),
    'contador'      => $c['cantidad'],
]);