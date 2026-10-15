<?php
require_once '../includes/conexion.php';
header('Content-Type: application/json');

$id       = (int)($_POST['id'] ?? 0);
$cantidad = (int)($_POST['cantidad'] ?? 1);
$cantidad = max(1, min(99, $cantidad));   // siempre entre 1 y 99

$stmt = $pdo->prepare("SELECT id FROM productos WHERE id = ?");
$stmt->execute([$id]);

if (!$stmt->fetch()) {
    echo json_encode(['ok' => false, 'mensaje' => 'Producto no existe']);
    exit;
}

if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];
$_SESSION['carrito'][$id] = ($_SESSION['carrito'][$id] ?? 0) + $cantidad;

echo json_encode(['ok' => true, 'total' => array_sum($_SESSION['carrito'])]);