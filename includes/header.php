<?php require_once __DIR__ . '/conexion.php';
$pagina = basename($_SERVER['PHP_SELF']);
$totalCarrito = isset($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>EcoBag</title>
  <link rel="stylesheet" href="css/estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<header class="header">
  <a href="index.php" class="logo"><i class="fa-solid fa-leaf"></i> EcoBag</a>

  <nav class="menu">
    <a href="index.php"     class="<?= $pagina == 'index.php' ? 'activo' : '' ?>">Inicio</a>
    <a href="productos.php" class="<?= $pagina == 'productos.php' ? 'activo' : '' ?>">Productos</a>
    <a href="#">Nosotros</a>
    <a href="#">Contacto</a>
  </nav>

  <div class="iconos">
    <a href="#"><i class="fa-solid fa-magnifying-glass"></i></a>
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <div class="menu-usuario">
          <button type="button" class="btn-usuario">
            <i class="fa-regular fa-user"></i>
            <span><?= htmlspecialchars(explode(' ', $_SESSION['usuario_nombre'])[0]) ?></span>
          </button>
          <div class="desplegable">
            <a href="logout.php?t=<?= $_SESSION['csrf'] ?>">Cerrar sesión</a>
          </div>
        </div>
      <?php else: ?>
        <a href="login.php"><i class="fa-regular fa-user"></i></a>
      <?php endif; ?>
    <a href="carrito.php" class="icono-carrito">
      <i class="fa-solid fa-cart-shopping"></i>
      <span id="contador-carrito"><?= $totalCarrito ?></span>
    </a>
    <button class="btn-menu" aria-label="Abrir menú"><i class="fa-solid fa-bars"></i></button>
  </div>
</header>